<?php
/** Helpers for application-generated SQL backups. */
const AVERO_BACKUP_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backups';

function avero_backup_tables(mysqli $db): array
{
    $result = $db->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
    if (!$result)
        throw new RuntimeException('Tidak dapat membaca daftar tabel: ' . $db->error);
    $tables = [];
    while ($row = $result->fetch_row())
        $tables[] = $row[0];
    return $tables;
}

function avero_make_backup(mysqli $db, string $type = 'manual'): string
{
    if (!is_dir(AVERO_BACKUP_DIR) && !mkdir(AVERO_BACKUP_DIR, 0700, true) && !is_dir(AVERO_BACKUP_DIR)) {
        throw new RuntimeException('Folder backup gagal dibuat.');
    }
    $sql = "-- AVERO_APP_BACKUP_V1\n-- Database: avero_servis\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n";
    foreach (avero_backup_tables($db) as $table) {
        $safe = '`' . str_replace('`', '``', $table) . '`';
        $create = $db->query("SHOW CREATE TABLE $safe")->fetch_row();
        if (!$create)
            throw new RuntimeException("Gagal membaca struktur tabel $table.");
        $sql .= "DROP TABLE IF EXISTS $safe;\n" . $create[1] . ";\n";
        $rows = $db->query("SELECT * FROM $safe");
        while ($row = $rows->fetch_assoc()) {
            $columns = array_map(fn($column) => '`' . str_replace('`', '``', $column) . '`', array_keys($row));
            $values = [];
            foreach ($row as $value)
                $values[] = $value === null ? 'NULL' : "'" . $db->real_escape_string((string) $value) . "'";
            $sql .= 'INSERT INTO ' . $safe . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ");\n";
        }
    }
    $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
    $name = 'avero_backup_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.sql';
    $path = AVERO_BACKUP_DIR . DIRECTORY_SEPARATOR . $name;
    if (file_put_contents($path, $sql, LOCK_EX) === false)
        throw new RuntimeException('File backup SQL gagal ditulis.');
    @chmod($path, 0600);
    $registry = avero_backup_registry();
    $registry[$name] = ['created_at' => date('Y-m-d H:i:s'), 'type' => $type, 'sha256' => hash_file('sha256', $path), 'size' => filesize($path)];
    uksort($registry, static function ($nameA, $nameB) use (&$registry) {
        $timeOrder = strcmp((string) ($registry[$nameB]['created_at'] ?? ''), (string) ($registry[$nameA]['created_at'] ?? ''));
        return $timeOrder !== 0 ? $timeOrder : strcmp($nameB, $nameA);
    });
    foreach (array_slice(array_keys($registry), 5) as $oldName) {
        $oldPath = AVERO_BACKUP_DIR . DIRECTORY_SEPARATOR . $oldName;
        if (is_file($oldPath))
            @unlink($oldPath);
        unset($registry[$oldName]);
    }
    avero_save_backup_registry($registry);
    return $name;
}

function avero_backup_registry(): array
{
    $path = AVERO_BACKUP_DIR . DIRECTORY_SEPARATOR . 'index.json';
    if (!is_file($path))
        return [];
    $data = json_decode((string) file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

function avero_save_backup_registry(array $registry): void
{
    if (file_put_contents(AVERO_BACKUP_DIR . DIRECTORY_SEPARATOR . 'index.json', json_encode($registry, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX) === false) {
        throw new RuntimeException('Daftar backup gagal disimpan.');
    }
}

function avero_valid_backup(string $name): bool
{
    if (!preg_match('/^avero_backup_[0-9]{8}_[0-9]{6}_[a-f0-9]{6}\.sql$/', $name))
        return false;
    $entry = avero_backup_registry()[$name] ?? null;
    $path = AVERO_BACKUP_DIR . DIRECTORY_SEPARATOR . $name;
    return is_array($entry) && is_file($path) && hash_equals((string) ($entry['sha256'] ?? ''), (string) hash_file('sha256', $path));
}

/** Split SQL while respecting quoted strings and identifiers. */
function avero_split_sql(string $sql): array
{
    $out = [];
    $part = '';
    $quote = '';
    $escaped = false;
    $length = strlen($sql);
    for ($i = 0; $i < $length; $i++) {
        $c = $sql[$i];
        if ($quote !== '') {
            $part .= $c;
            if ($escaped) {
                $escaped = false;
                continue;
            }
            if ($c === '\\' && $quote !== '`') {
                $escaped = true;
                continue;
            }
            if ($c === $quote) {
                if ($i + 1 < $length && $sql[$i + 1] === $quote) {
                    $part .= $sql[++$i];
                    continue;
                }
                $quote = '';
            }
            continue;
        }
        if ($c === "'" || $c === '"' || $c === '`') {
            $quote = $c;
            $part .= $c;
            continue;
        }
        if ($c === ';') {
            if (trim($part) !== '')
                $out[] = trim($part);
            $part = '';
            continue;
        }
        if ($c === '-' && ($sql[$i + 1] ?? '') === '-') {
            while ($i < $length && $sql[$i] !== "\n")
                $i++;
            $part .= "\n";
            continue;
        }
        if ($c === '#') {
            while ($i < $length && $sql[$i] !== "\n")
                $i++;
            $part .= "\n";
            continue;
        }
        $part .= $c;
    }
    if (trim($part) !== '')
        $out[] = trim($part);
    return $out;
}

function avero_split_sql_values(string $valueList): array
{
    $values = [];
    $value = '';
    $quoted = false;
    $escaped = false;
    $length = strlen($valueList);
    for ($i = 0; $i < $length; $i++) {
        $char = $valueList[$i];
        if ($quoted) {
            $value .= $char;
            if ($escaped) {
                $escaped = false;
                continue;
            }
            if ($char === '\\') {
                $escaped = true;
                continue;
            }
            if ($char === "'") {
                if (($valueList[$i + 1] ?? '') === "'") {
                    $value .= $valueList[++$i];
                    continue;
                }
                $quoted = false;
            }
            continue;
        }
        if ($char === "'") {
            $quoted = true;
            $value .= $char;
            continue;
        }
        if ($char === ',') {
            $values[] = trim($value);
            $value = '';
            continue;
        }
        $value .= $char;
    }
    if ($quoted || $escaped)
        throw new RuntimeException('Nilai SQL memiliki tanda kutip yang tidak lengkap.');
    if (trim($value) !== '')
        $values[] = trim($value);
    return $values;
}

function avero_is_sql_string_literal(string $value): bool
{
    $length = strlen($value);
    if (strtoupper($value) === 'NULL')
        return true;
    if ($length < 2 || $value[0] !== "'" || $value[$length - 1] !== "'")
        return false;
    for ($i = 1; $i < $length - 1; $i++) {
        if ($value[$i] === '\\') {
            $i++;
            if ($i >= $length - 1)
                return false;
            continue;
        }
        if ($value[$i] === "'") {
            if (($value[$i + 1] ?? '') === "'" && $i + 1 < $length - 1) {
                $i++;
                continue;
            }
            return false;
        }
    }
    return true;
}

function avero_normalize_create_statement(string $statement): string
{
    $statement = preg_replace('/\sAUTO_INCREMENT\s*=\s*\d+/i', '', trim($statement));
    return preg_replace('/\s+/', ' ', $statement);
}

/** Restore an uploaded dump generated by this application, without executing arbitrary SQL. */
function avero_restore_sql(mysqli $db, string $sql): void
{
    $marker = "-- AVERO_APP_BACKUP_V1\n";
    if (substr($sql, 0, strlen($marker)) !== $marker)
        throw new RuntimeException('File bukan backup SQL Avero yang didukung.');

    $tables = avero_backup_tables($db);
    $tableSet = array_fill_keys($tables, true);
    $drops = [];
    $creates = [];
    $inserts = [];
    $foreignKeysDisabled = false;
    foreach (avero_split_sql($sql) as $statement) {
        if (preg_match('/^SET\s+NAMES\s+utf8mb4$/i', $statement))
            continue;
        if (preg_match('/^SET\s+FOREIGN_KEY_CHECKS\s*=\s*([01])$/i', $statement, $match)) {
            if ($match[1] === '0')
                $foreignKeysDisabled = true;
            continue;
        }
        if (preg_match('/^DROP TABLE IF EXISTS `([A-Za-z0-9_]+)`$/i', $statement, $match)) {
            $table = $match[1];
            if (!isset($tableSet[$table]) || isset($drops[$table]))
                throw new RuntimeException('Daftar tabel dalam backup tidak valid.');
            $drops[$table] = true;
            continue;
        }
        if (preg_match('/^CREATE TABLE `([A-Za-z0-9_]+)`\s*\(/i', $statement, $match)) {
            $table = $match[1];
            if (!isset($tableSet[$table]) || isset($creates[$table]))
                throw new RuntimeException('Struktur tabel dalam backup tidak valid.');
            $current = $db->query('SHOW CREATE TABLE `' . $db->real_escape_string($table) . '`')->fetch_row();
            if (!$current || avero_normalize_create_statement($current[1]) !== avero_normalize_create_statement($statement)) {
                throw new RuntimeException('Struktur backup berbeda dari database aplikasi. Gunakan backup dari versi database yang sama.');
            }
            $creates[$table] = true;
            continue;
        }
        if (preg_match('/^INSERT INTO `([A-Za-z0-9_]+)`\s*\((.*?)\)\s*VALUES\s*\((.*)\)$/is', $statement, $match)) {
            $table = $match[1];
            if (!isset($tableSet[$table]) || !isset($creates[$table]))
                throw new RuntimeException('Data backup merujuk ke tabel yang tidak valid.');
            $columns = avero_split_sql_values($match[2]);
            $values = avero_split_sql_values($match[3]);
            $columnNames = [];
            foreach ($columns as $column) {
                if (!preg_match('/^`([A-Za-z0-9_]+)`$/D', $column, $columnMatch))
                    throw new RuntimeException('Nama kolom backup tidak valid.');
                $columnNames[] = $columnMatch[1];
            }
            if (count($columnNames) !== count($values))
                throw new RuntimeException('Jumlah kolom dan nilai pada backup tidak sesuai.');
            $columnResult = $db->query('SHOW COLUMNS FROM `' . $db->real_escape_string($table) . '`');
            $validColumns = [];
            while ($columnRow = $columnResult->fetch_assoc())
                $validColumns[$columnRow['Field']] = true;
            foreach ($columnNames as $column)
                if (!isset($validColumns[$column]))
                    throw new RuntimeException('Backup berisi kolom yang tidak dikenal.');
            foreach ($values as $value)
                if (!avero_is_sql_string_literal($value))
                    throw new RuntimeException('Backup berisi nilai SQL yang tidak didukung.');
            $inserts[] = $statement;
            continue;
        }
        throw new RuntimeException('File SQL berisi perintah di luar format backup Avero.');
    }

    if (!$foreignKeysDisabled || count($drops) !== count($tables) || count($creates) !== count($tables)) {
        throw new RuntimeException('Backup tidak lengkap atau struktur tabelnya tidak cocok.');
    }
    foreach ($tables as $table)
        if (!isset($drops[$table], $creates[$table]))
            throw new RuntimeException('Backup tidak memuat semua tabel aplikasi.');

    $db->query('SET FOREIGN_KEY_CHECKS=0');
    $db->begin_transaction();
    try {
        foreach ($tables as $table) {
            $safeTable = '`' . str_replace('`', '``', $table) . '`';
            if (!$db->query("DELETE FROM $safeTable"))
                throw new RuntimeException($db->error);
        }
        foreach ($inserts as $insert)
            if (!$db->query($insert))
                throw new RuntimeException($db->error);
        $db->commit();
    } catch (Throwable $error) {
        $db->rollback();
        throw $error;
    } finally {
        $db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}
