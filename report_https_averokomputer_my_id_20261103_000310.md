# Scanning-Web — Vulnerability Report

| | |
|---|---|
| Target | `https://averokomputer.my.id` |
| Waktu | 2026-11-03 00:03:10 |
| Durasi | 416.08 s |
| Request | 677 · pagu TANPA BATAS |
| Tech | PHP/8.1.34 |
| **Risk score** | **63.3/100 (grade C)** |

**Temuan:** 0 critical · 8 high · 8 medium · 36 low · 78 info

## CSRF Protection (form audit)
### [HIGH] Form POST tanpa token CSRF: https://averokomputer.my.id
*Tidak ditemukan field hidden bertipe csrf/nonce. Penyerang bisa memaksa user yang sudah login mengeksekusi aksi ini dari situs luar.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  method=post action=https://averokomputer.my.id
inputs=username, password
  ```
- **Remediation**: Gunakan CSRF token per-session/per-request (Double Submit Cookie / synchronizer token) atau SameSite=Lax cookie + Origin check. Framework modern (Django, Laravel, Rails) punya ini bawaan — pastikan middleware aktif.
- **OWASP**: Broken Access Control · **CWE-352** · confidence: needs-manual-review · CVSS≈7.5

## Information Disclosure
### [HIGH] Kredensial terlihat di halaman: Generic Secret
*Pola yang cocok dengan format kredensial muncul di respons publik. Nilai aslinya disamarkan di laporan, tapi potongan sumber di kiri-kanannya ditampilkan APA ADANYA supaya bisa langsung dicek manual.*

- **URL**: `https://averokomputer.my.id/login.php`
- **Evidence**:
  ```
  Lokasi  : https://averokomputer.my.id/login.php
Pola  : Generic Secret
Nilai  : token"…fc3"  (disamarkan di laporan)
Sebelum  : 'xTscNWEc1OdAv5/BikHawapSCaiD2XCmp+Bw==" data-cf-beacon=\'{"version":"2024.11.0","'
Sesudah  : ',"r":1,"spa":2}\' crossorigin="anonymous"></script>'
Verifikasi: buka https://averokomputer.my.id/login.php , lalu cari potongan "Sebelum" di atas di view-source (Ctrl+U).
  ```
- **Remediation**: Bila benar kredensial produksi: rotasi/revoke SEKARANG, pindahkan ke secret manager / header server-side, jangan embed di frontend.
- **OWASP**: Broken Access Control · **CWE-200** · confidence: needs-manual-review · CVSS≈7.5

### [HIGH] Pola kredensial di halaman publik — perlu tinjauan manual
*Pola yang tercocok adalah tipe yang BISA saja publik by-design (API key klien, JWT sesi). Temuan per-URL di bawah menunjuk lokasi persisnya; tinjau apakah nilainya benar-benar sensitif sebelum panik — kalau ya, naikkan jadi CRITICAL dan revoke.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  Daftar temuan (nilai disamarkan, lokasi persis):
· Generic Secret @ https://averokomputer.my.id/login.php → token"…fc3"
  ```
- **OWASP**: Broken Access Control · **CWE-200** · confidence: needs-manual-review · CVSS≈7.5

### [LOW] 1 path file internal terlihat
*Path file (mis. /app/controller.php) memberi tahu struktur aplikasi & teknologi.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  · //static.cloudflareinsights.com/beacon.min.js
  ```
- **Remediation**: Jangan render path absolut; pakai asset pipeline/CDN.
- **OWASP**: Broken Access Control · **CWE-200** · confidence: possible · CVSS≈3.1

### [LOW] Fingerprint: server
*Versi web server terekspos attacker tinggal cocokkan dengan CVE publik.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  server: cloudflare
  ```
- **Remediation**: Hapus header / samarkan versi (server_tokens off, expose_php Off, custom Server header).
- **OWASP**: Broken Access Control · **CWE-200** · confidence: possible · CVSS≈3.1

### [LOW] Fingerprint: x-powered-by
*Teknologi backend terekspos attacker tinggal cocokkan dengan CVE publik.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  x-powered-by: PHP/8.1.34
  ```
- **Remediation**: Hapus header / samarkan versi (server_tokens off, expose_php Off, custom Server header).
- **OWASP**: Broken Access Control · **CWE-200** · confidence: possible · CVSS≈3.1

### [LOW] Fingerprint: x-turbo-charged-by
*Versi software terdeteksi. attacker tinggal cocokkan dengan CVE publik.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  x-turbo-charged-by: LiteSpeed
  ```
- **Remediation**: Hapus header / samarkan versi (server_tokens off, expose_php Off, custom Server header).
- **OWASP**: Broken Access Control · **CWE-200** · confidence: possible · CVSS≈3.1

### [LOW] Teknologi/CMS terdeteksi: PHP/8.1.34
*Identifikasi CMS memudahkan attacker menargetkan vulnerability spesifik versi + plugin.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  x-powered-by=PHP/8.1.34
  ```
- **Remediation**: Update core CMS + seluruh plugin secara rutin, hapus plugin tidak aktif, dan nonaktifkan xmlrpc/meta generator.
- **OWASP**: Broken Access Control · **CWE-200** · confidence: possible · CVSS≈3.1

## Rate Limiting / Brute-force Guard
### [HIGH] TIDAK ada proteksi setelah 5 percobaan login gagal
*`https://averokomputer.my.id/login.php` membalas 5× HTTP 200, 200, 200, 200, 200 tanpa 429, `Retry-After`, CAPTCHA, atau pesan throttle. Brute-force/credential stuffing bisa jalan tanpa hambatan berarti — ini jalur kompromi akun nomor satu di dunia nyata.*

- **URL**: `https://averokomputer.my.id/login.php`
- **Evidence**:
  ```
  statuses=[200, 200, 200, 200, 200] · retry-after=-
respons akhir: 
<!DOCTYPE html>
<html lang="id">

<head>

  <meta charset="UTF-8">

  <meta name="viewport"
  content="width=device-width, initial-scale=1.0">

  <title>Login - Avero Kompute
  ```
- **Remediation**: Pasang pembatasan berlapis: rate limit per IP+endpoint (nginx `limit_req`/Redis token bucket), lockout progresif per akun, CAPTCHA bertahap, deteksi anomali, dan wajibkan 2FA. Gunakan juga deny-list password bocor.
- **OWASP**: Insecure Design · **CWE-307** · confidence: needs-manual-review · CVSS≈7.5

## HTTP Security Headers
### [HIGH] Header hilang: HSTS
*Memaksa browser memakai HTTPS, mencegah SSL-stripping & MITM. Respons target tidak memuat header ini.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  Request  : GET https://averokomputer.my.id/login.php
Respons  : HTTP 200
Hilang  : header 'strict-transport-security' TIDAK ada pada respons ini
Yang ada : alt-svc, cache-control, cf-cache-status, cf-ray, connection, content-encoding, content-type, date, expires, nel, pragma, report-to, server, server-timing, transfer-encoding, vary
  ```
- **Remediation**: Set: Strict-Transport-Security: max-age=31536000; includeSubDomains
- **OWASP**: Security Misconfiguration · **CWE-693** · confidence: possible · CVSS≈7.5

### [HIGH] Header hilang: CSP
*Garis pertahanan utama melawan XSS dan data injection. Respons target tidak memuat header ini.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  Request  : GET https://averokomputer.my.id/login.php
Respons  : HTTP 200
Hilang  : header 'content-security-policy' TIDAK ada pada respons ini
Yang ada : alt-svc, cache-control, cf-cache-status, cf-ray, connection, content-encoding, content-type, date, expires, nel, pragma, report-to, server, server-timing, transfer-encoding, vary
  ```
- **Remediation**: Set: Content-Security-Policy: default-src 'self'; object-src 'none'; frame-ancestors 'none'
- **OWASP**: Security Misconfiguration · **CWE-693** · confidence: possible · CVSS≈7.5

### [MEDIUM] Header hilang: X-Frame-Options
*Mencegah clickjacking (halaman lu di-embed iframe jahat). Respons target tidak memuat header ini.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  Request  : GET https://averokomputer.my.id/login.php
Respons  : HTTP 200
Hilang  : header 'x-frame-options' TIDAK ada pada respons ini
Yang ada : alt-svc, cache-control, cf-cache-status, cf-ray, connection, content-encoding, content-type, date, expires, nel, pragma, report-to, server, server-timing, transfer-encoding, vary
  ```
- **Remediation**: Set: X-Frame-Options: DENY  (atau pakai CSP frame-ancestors 'none')
- **OWASP**: Security Misconfiguration · **CWE-693** · confidence: possible · CVSS≈5.3

### [MEDIUM] Header hilang: X-Content-Type-Options
*Mencegah browser menebak MIME (sniffing) yang bisa berujung XSS. Respons target tidak memuat header ini.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  Request  : GET https://averokomputer.my.id/login.php
Respons  : HTTP 200
Hilang  : header 'x-content-type-options' TIDAK ada pada respons ini
Yang ada : alt-svc, cache-control, cf-cache-status, cf-ray, connection, content-encoding, content-type, date, expires, nel, pragma, report-to, server, server-timing, transfer-encoding, vary
  ```
- **Remediation**: Set: X-Content-Type-Options: nosniff
- **OWASP**: Security Misconfiguration · **CWE-693** · confidence: possible · CVSS≈5.3

### [LOW] Header hilang: Referrer-Policy
*Membatasi bocornya URL sensitif ke situs pihak ketiga. Respons target tidak memuat header ini.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  Request  : GET https://averokomputer.my.id/login.php
Respons  : HTTP 200
Hilang  : header 'referrer-policy' TIDAK ada pada respons ini
Yang ada : alt-svc, cache-control, cf-cache-status, cf-ray, connection, content-encoding, content-type, date, expires, nel, pragma, report-to, server, server-timing, transfer-encoding, vary
  ```
- **Remediation**: Set: Referrer-Policy: strict-origin-when-cross-origin
- **OWASP**: Security Misconfiguration · **CWE-693** · confidence: possible · CVSS≈3.1

### [LOW] Header hilang: Permissions-Policy
*Membatasi akses fitur browser (kamera, mikrofon, geolocation). Respons target tidak memuat header ini.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  Request  : GET https://averokomputer.my.id/login.php
Respons  : HTTP 200
Hilang  : header 'permissions-policy' TIDAK ada pada respons ini
Yang ada : alt-svc, cache-control, cf-cache-status, cf-ray, connection, content-encoding, content-type, date, expires, nel, pragma, report-to, server, server-timing, transfer-encoding, vary
  ```
- **Remediation**: Set: Permissions-Policy: geolocation=(), camera=(), microphone=()
- **OWASP**: Security Misconfiguration · **CWE-693** · confidence: possible · CVSS≈3.1

### [LOW] Header pengungkapan teknologi: x-powered-by
*'x-powered-by: PHP/8.1.34' memberitahu attacker persis versi software yang jalan, memudahkan pencocokan CVE yang sudah diketahui.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  x-powered-by: PHP/8.1.34
  ```
- **Remediation**: Sembunyikan version string (nginx: `server_tokens off;`, PHP: `expose_php = Off`, Apache: `ServerTokens Prod`).
- **OWASP**: Security Misconfiguration · **CWE-693** · confidence: possible · CVSS≈3.1

### [INFO] Header pengungkapan teknologi: server
*'server: cloudflare' memberitahu attacker persis versi software yang jalan, memudahkan pencocokan CVE yang sudah diketahui.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  server: cloudflare
  ```
- **Remediation**: Sembunyikan version string (nginx: `server_tokens off;`, PHP: `expose_php = Off`, Apache: `ServerTokens Prod`).
- **OWASP**: Security Misconfiguration · **CWE-693** · confidence: possible · CVSS≈0.0

### [INFO] Ringkasan header keamanan
*hadir 0/7 · hilang 6*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  hadir 0/7 · hilang 6
  ```
- **OWASP**: Security Misconfiguration · **CWE-693** · confidence: confirmed · CVSS≈0.0

## TLS Protocol & Cipher
### [HIGH] Protokol legacy diterima: TLS 1.0
*Server masih menegosiasi TLS 1.0 (Deprecated RFC 8996 — BEAST/Lucky13). Klien yang dipaksa turun versi (downgrade/BEAST/POODLE) bisa diintersep.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  TLSv1 · cipher=ECDHE-ECDSA-AES128-SHA · bits=128
  ```
- **Remediation**: Batasi protokol: hanya TLS 1.2 dan TLS 1.3 (nginx: `ssl_protocols TLSv1.2 TLSv1.3;`).
- **OWASP**: Cryptographic Failures · **CWE-326** · confidence: possible · CVSS≈7.5

### [HIGH] Protokol legacy diterima: TLS 1.1
*Server masih menegosiasi TLS 1.1 (Deprecated RFC 8996). Klien yang dipaksa turun versi (downgrade/BEAST/POODLE) bisa diintersep.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  TLSv1.1 · cipher=ECDHE-ECDSA-AES128-SHA · bits=128
  ```
- **Remediation**: Batasi protokol: hanya TLS 1.2 dan TLS 1.3 (nginx: `ssl_protocols TLSv1.2 TLSv1.3;`).
- **OWASP**: Cryptographic Failures · **CWE-326** · confidence: possible · CVSS≈7.5

### [INFO] TLS 1.2 didukung
*TLSv1.2 · cipher=ECDHE-ECDSA-AES128-GCM-SHA256 · bits=128*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  TLSv1.2 · cipher=ECDHE-ECDSA-AES128-GCM-SHA256 · bits=128
  ```
- **OWASP**: Cryptographic Failures · **CWE-326** · confidence: confirmed · CVSS≈0.0

### [INFO] TLS 1.3 didukung
*TLSv1.3 · cipher=TLS_AES_256_GCM_SHA384 · bits=256*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  TLSv1.3 · cipher=TLS_AES_256_GCM_SHA384 · bits=256
  ```
- **OWASP**: Cryptographic Failures · **CWE-326** · confidence: confirmed · CVSS≈0.0

### [INFO] Protokol tertinggi yang didukung
*TLS 1.3 · diterima: TLS 1.0, TLS 1.1, TLS 1.2, TLS 1.3*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  TLS 1.3 · diterima: TLS 1.0, TLS 1.1, TLS 1.2, TLS 1.3
  ```
- **OWASP**: Cryptographic Failures · **CWE-326** · confidence: confirmed · CVSS≈0.0

## Email Security (SPF/DMARC/DKIM)
### [MEDIUM] Record SPF tidak ditemukan
*Domain `averokomputer.my.id` tidak punya TXT `v=spf1` → email dari domain ini mudah dipalsukan pihak lain.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  TXT averokomputer.my.id: (tidak ada)
  ```
- **Remediation**: Tambahkan TXT `v=spf1 ip4:<IP-mu> -all` di DNS.
- **OWASP**: Security Misconfiguration · **CWE-290** · confidence: confirmed · CVSS≈5.3

### [MEDIUM] Record DMARC tidak ditemukan
*TXT `_dmarc.averokomputer.my.id` tidak ada → domain tanpa kebijakan anti-spoofing; email phishing atas nama kamu akan lolos pemeriksaan DMARC di banyak penerima.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  TXT _dmarc.averokomputer.my.id: tidak ditemukan
  ```
- **Remediation**: Tambahkan TXT `_dmarc.averokomputer.my.id` dengan `v=DMARC1; p=quarantine; rua=mailto:security@{host}`.
- **OWASP**: Security Misconfiguration · **CWE-290** · confidence: confirmed · CVSS≈5.3

### [LOW] Record DKIM tidak ditemukan pada selector umum
*Tidak ada `s1._domainkey.averokomputer.my.id` untuk selector yang umum dipakai. Bisa jadi DKIM memang belum ada, atau pakai selector khusus — konfirmasi ke panel email kamu.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  default, google, selector1, selector2, k1, s1
  ```
- **Remediation**: Aktifkan DKIM di penyedia email (Google Workspace: selector `google`; cPanel: `default`; Microsoft: `selector1`/`selector2`).
- **OWASP**: Security Misconfiguration · **CWE-290** · confidence: needs-manual-review · CVSS≈3.1

## Open Ports (host target)
### [MEDIUM] Port TERBUKA: 8080/tcp — HTTP-alt
*Sering admin panel / dev server tanpa proteksi.
Connect TCP berhasil dari sisi scanner terhadap IP publik target → siapa pun yang bisa merute IP ini dapat berinteraksi langsung dengan layanan.*

- **URL**: `tcp://104.21.87.204:8080`
- **Evidence**:
  ```
  socket.connect_ex(("104.21.87.204", 8080)) → 0 (SYN→SYN/ACK diterima dari mesin scanner)
  ```
- **Remediation**: Cek apa ini admin/dev panel — kalau ya, kunci atau hapus dari publik.
- **OWASP**: Security Misconfiguration · **CWE-284** · confidence: confirmed · CVSS≈5.3

### [MEDIUM] Port TERBUKA: 8443/tcp — HTTPS-alt
*Cek apakah sama kuatnya dengan 443.
Connect TCP berhasil dari sisi scanner terhadap IP publik target → siapa pun yang bisa merute IP ini dapat berinteraksi langsung dengan layanan.*

- **URL**: `tcp://104.21.87.204:8443`
- **Evidence**:
  ```
  socket.connect_ex(("104.21.87.204", 8443)) → 0 (SYN→SYN/ACK diterima dari mesin scanner)
  ```
- **Remediation**: Pastikan config TLS sama ketatnya dengan 443.
- **OWASP**: Security Misconfiguration · **CWE-284** · confidence: confirmed · CVSS≈5.3

### [LOW] Port 21/FTP: filtered
*Koneksi di-drop (timeout) — kemungkinan ada firewall drop, lebih baik daripada terbuka.*

- **URL**: `tcp://104.21.87.204:21`
- **Evidence**:
  ```
  104.21.87.204:21 → timeout
  ```
- **OWASP**: Security Misconfiguration · **CWE-284** · confidence: possible · CVSS≈3.1

### [LOW] Port 22/SSH: filtered
*Koneksi di-drop (timeout) — kemungkinan ada firewall drop, lebih baik daripada terbuka.*

- **URL**: `tcp://104.21.87.204:22`
- **Evidence**:
  ```
  104.21.87.204:22 → timeout
  ```
- **OWASP**: Security Misconfiguration · **CWE-284** · confidence: possible · CVSS≈3.1

### [LOW] Port 23/Telnet: filtered
*Koneksi di-drop (timeout) — kemungkinan ada firewall drop, lebih baik daripada terbuka.*

- **URL**: `tcp://104.21.87.204:23`
- **Evidence**:
  ```
  104.21.87.204:23 → timeout
  ```
- **OWASP**: Security Misconfiguration · **CWE-284** · confidence: possible · CVSS≈3.1

### [LOW] Port 25/SMTP: filtered
*Koneksi di-drop (timeout) — kemungkinan ada firewall drop, lebih baik daripada terbuka.*

- **URL**: `tcp://104.21.87.204:25`
- **Evidence**:
  ```
  104.21.87.204:25 → timeout
  ```
- **OWASP**: Security Misconfiguration · **CWE-284** · confidence: possible · CVSS≈3.1

### [LOW] Port 53/DNS: filtered
*Koneksi di-drop (timeout) — kemungkinan ada firewall drop, lebih baik daripada terbuka.*

- **URL**: `tcp://104.21.87.204:53`
- **Evidence**:
  ```
  104.21.87.204:53 → timeout
  ```
- **OWASP**: Security Misconfiguration · **CWE-284** · confidence: possible · CVSS≈3.1

### [LOW] Port 110/POP3: filtered
*Koneksi di-drop (timeout) — kemungkinan ada firewall drop, lebih baik daripada terbuka.*

- **URL**: `tcp://104.21.87.204:110`
- **Evidence**:
  ```
  104.21.87.204:110 → timeout
  ```
- **OWASP**: Security Misconfiguration · **CWE-284** · confidence: possible · CVSS≈3.1

### [LOW] Port 143/IMAP: filtered
*Koneksi di-drop (timeout) — kemungkinan ada firewall drop, lebih baik daripada terbuka.*

- **URL**: `tcp://104.21.87.204:143`
- **Evidence**:
  ```
  104.21.87.204:143 → timeout
  ```
- **OWASP**: Security Misconfiguration · **CWE-284** · confidence: possible · CVSS≈3.1

### [LOW] Port 445/SMB: filtered
*Koneksi di-drop (timeout) — kemungkinan ada firewall drop, lebih baik daripada terbuka.*

- **URL**: `tcp://104.21.87.204:445`
- **Evidence**:
  ```
  104.21.87.204:445 → timeout
  ```
- **OWASP**: Security Misconfiguration · **CWE-284** · confidence: possible · CVSS≈3.1

### [LOW] Port 1433/MSSQL: filtered
*Koneksi di-drop (timeout) — kemungkinan ada firewall drop, lebih baik daripada terbuka.*

- **URL**: `tcp://104.21.87.204:1433`
- **Evidence**:
  ```
  104.21.87.204:1433 → timeout
  ```
- **OWASP**: Security Misconfiguration · **CWE-284** · confidence: possible · CVSS≈3.1

### [LOW] Port 2375/Docker API: filtered
*Koneksi di-drop (timeout) — kemungkinan ada firewall drop, lebih baik daripada terbuka.*

- **URL**: `tcp://104.21.87.204:2375`
- **Evidence**:
  ```
  104.21.87.204:2375 → timeout
  ```
- **OWASP**: Security Misconfiguration · **CWE-284** · confidence: possible · CVSS≈3.1

### [LOW] Port 3306/MySQL: filtered
*Koneksi di-drop (timeout) — kemungkinan ada firewall drop, lebih baik daripada terbuka.*

- **URL**: `tcp://104.21.87.204:3306`
- **Evidence**:
  ```
  104.21.87.204:3306 → timeout
  ```
- **OWASP**: Security Misconfiguration · **CWE-284** · confidence: possible · CVSS≈3.1

### [LOW] Port 3389/RDP: filtered
*Koneksi di-drop (timeout) — kemungkinan ada firewall drop, lebih baik daripada terbuka.*

- **URL**: `tcp://104.21.87.204:3389`
- **Evidence**:
  ```
  104.21.87.204:3389 → timeout
  ```
- **OWASP**: Security Misconfiguration · **CWE-284** · confidence: possible · CVSS≈3.1

### [LOW] Port 5432/PostgreSQL: filtered
*Koneksi di-drop (timeout) — kemungkinan ada firewall drop, lebih baik daripada terbuka.*

- **URL**: `tcp://104.21.87.204:5432`
- **Evidence**:
  ```
  104.21.87.204:5432 → timeout
  ```
- **OWASP**: Security Misconfiguration · **CWE-284** · confidence: possible · CVSS≈3.1

### [LOW] Port 5984/CouchDB: filtered
*Koneksi di-drop (timeout) — kemungkinan ada firewall drop, lebih baik daripada terbuka.*

- **URL**: `tcp://104.21.87.204:5984`
- **Evidence**:
  ```
  104.21.87.204:5984 → timeout
  ```
- **OWASP**: Security Misconfiguration · **CWE-284** · confidence: possible · CVSS≈3.1

### [LOW] Port 6379/Redis: filtered
*Koneksi di-drop (timeout) — kemungkinan ada firewall drop, lebih baik daripada terbuka.*

- **URL**: `tcp://104.21.87.204:6379`
- **Evidence**:
  ```
  104.21.87.204:6379 → timeout
  ```
- **OWASP**: Security Misconfiguration · **CWE-284** · confidence: possible · CVSS≈3.1

### [LOW] Port 7001/Cassandra JMX: filtered
*Koneksi di-drop (timeout) — kemungkinan ada firewall drop, lebih baik daripada terbuka.*

- **URL**: `tcp://104.21.87.204:7001`
- **Evidence**:
  ```
  104.21.87.204:7001 → timeout
  ```
- **OWASP**: Security Misconfiguration · **CWE-284** · confidence: possible · CVSS≈3.1

### [LOW] Port 8888/MAMP/dev: filtered
*Koneksi di-drop (timeout) — kemungkinan ada firewall drop, lebih baik daripada terbuka.*

- **URL**: `tcp://104.21.87.204:8888`
- **Evidence**:
  ```
  104.21.87.204:8888 → timeout
  ```
- **OWASP**: Security Misconfiguration · **CWE-284** · confidence: possible · CVSS≈3.1

### [LOW] Port 9200/Elasticsearch: filtered
*Koneksi di-drop (timeout) — kemungkinan ada firewall drop, lebih baik daripada terbuka.*

- **URL**: `tcp://104.21.87.204:9200`
- **Evidence**:
  ```
  104.21.87.204:9200 → timeout
  ```
- **OWASP**: Security Misconfiguration · **CWE-284** · confidence: possible · CVSS≈3.1

### [LOW] Port 27017/MongoDB: filtered
*Koneksi di-drop (timeout) — kemungkinan ada firewall drop, lebih baik daripada terbuka.*

- **URL**: `tcp://104.21.87.204:27017`
- **Evidence**:
  ```
  104.21.87.204:27017 → timeout
  ```
- **OWASP**: Security Misconfiguration · **CWE-284** · confidence: possible · CVSS≈3.1

### [INFO] Host averokomputer.my.id → 104.21.87.204
*23 port akan diuji (connect-only)*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  23 port akan diuji (connect-only)
  ```
- **OWASP**: Security Misconfiguration · **CWE-284** · confidence: confirmed · CVSS≈0.0

### [INFO] Port TERBUKA: 80/tcp — HTTP
*Harus redirect ke HTTPS.
Connect TCP berhasil dari sisi scanner terhadap IP publik target → siapa pun yang bisa merute IP ini dapat berinteraksi langsung dengan layanan.*

- **URL**: `tcp://104.21.87.204:80`
- **Evidence**:
  ```
  socket.connect_ex(("104.21.87.204", 80)) → 0 (SYN→SYN/ACK diterima dari mesin scanner)
  ```
- **Remediation**: Batasi akses dengan firewall/VPN dan audit layanan yang menjalankannya.
- **OWASP**: Security Misconfiguration · **CWE-284** · confidence: confirmed · CVSS≈0.0

### [INFO] Port TERBUKA: 443/tcp — HTTPS
*Layanan web utama.
Connect TCP berhasil dari sisi scanner terhadap IP publik target → siapa pun yang bisa merute IP ini dapat berinteraksi langsung dengan layanan.*

- **URL**: `tcp://104.21.87.204:443`
- **Evidence**:
  ```
  socket.connect_ex(("104.21.87.204", 443)) → 0 (SYN→SYN/ACK diterima dari mesin scanner)
  ```
- **Remediation**: Batasi akses dengan firewall/VPN dan audit layanan yang menjalankannya.
- **OWASP**: Security Misconfiguration · **CWE-284** · confidence: confirmed · CVSS≈0.0

## Stack Version Intelligence
### [MEDIUM] PHP 8.1 mendekati/lewat security support
*Software stack: **PHP** versi `8.1.34` — PHP 8.1 security fixes berakhir Des 2025 — segera 8.3/8.4.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  cocok pada pola: 'PHP/8.1.34'
  ```
- **Remediation**: Upgrade ke rilis support terbaru. Kalau upgrade terhalang: compensating control (WAF virtual patch, nonaktifkan modul berisiko, network policy) + jadwalkan upgrade sebagai debt resmi.
- **OWASP**: Vulnerable and Outdated Components · **CWE-1104** · confidence: needs-manual-review · CVSS≈5.3

### [LOW] Header `x-powered-by` mengekspos versi: PHP/8.1.34
*Walau versi ini mungkin masih disupport, mengekspos versi lengkap = memudahkan attacker memilih CVE yang cocok sebelum menyentuh sistem.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  x-powered-by: PHP/8.1.34
  ```
- **Remediation**: `ServerTokens Prod` (Apache), `server_tokens off;` (nginx), `expose_php=Off` (php.ini), `httpProtocol.customHeaders` IIS — sisakan hanya yang benar-benar perlu.
- **OWASP**: Vulnerable and Outdated Components · **CWE-1104** · confidence: confirmed · CVSS≈3.1

## Transport Security (HTTP/HTTPS)
### [MEDIUM] Redirect HTTP tidak menuju HTTPS
*Port 80 dialihkan, tetapi tujuan redirect bukan HTTPS ke host yang sama. Ini membuka peluang SSL-stripping.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  302 → login.php
  ```
- **Remediation**: Selalu redirect 301 ke skema https:// host yang sama.
- **OWASP**: Cryptographic Failures · **CWE-319** · confidence: possible · CVSS≈5.3

### [INFO] Server merespons
*Target aktif dan mengembalikan respons.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  HTTP 200 · 2906 bytes · 0.09s
  ```
- **OWASP**: Cryptographic Failures · **CWE-319** · confidence: confirmed · CVSS≈0.0

## Cookie Security Flags
### [LOW] Cookie 'PHPSESSID' discop untuk semua subdomain
*Satu subdomain yang compromized (mis. staging lama) bisa membaca cookie production.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  Domain=averokomputer.my.id
  ```
- **Remediation**: Batasi cookie ke host spesifik (jangan `.example.com`).
- **OWASP**: Security Misconfiguration · **CWE-1004** · confidence: possible · CVSS≈3.1

### [INFO] Cookie 'PHPSESSID' sudah benar
*Secure, HttpOnly, SameSite=*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  Secure, HttpOnly, SameSite=
  ```
- **OWASP**: Security Misconfiguration · **CWE-1004** · confidence: confirmed · CVSS≈0.0

## DNS Security (CAA/MTA-STS/TLS-RPT)
### [LOW] Record CAA tidak ditemukan
*Domain tidak punya CAA → semua CA yang dipercaya browser bisa menerbitkan sertifikat untuk domainmu. Kebanyakan insiden sertifikat salahi terjadi karena tidak ada pembatasan ini.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  CAA averokomputer.my.id: kosong
  ```
- **Remediation**: Tambahkan CAA, contoh: `0 issue "letsencrypt.org"` (+ `0 issuewild` bila pakai wildcard, `0 iodef` untuk notifikasi).
- **OWASP**: Security Misconfiguration · **CWE-290** · confidence: confirmed · CVSS≈3.1

### [INFO] Tidak ada record MX
*Domain tidak menerima email — tidak ada MTA-STS/TLS-RPT yang perlu dipasang (kecuali dipakai untuk phishing domain).*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  MX averokomputer.my.id: kosong
  ```
- **OWASP**: Security Misconfiguration · **CWE-290** · confidence: confirmed · CVSS≈0.0

## DNSSEC (signed zone)
### [LOW] DNSSEC belum dipasang untuk averokomputer.my.id
*Zona tidak punya DNSKEY/RRSIG. Serangan praktis: DNS spoofing/NXDOMAIN injection di jalur yang tidak dipercaya (hotspot publik, ISP kompromi) bisa mengarahkan pengguna ke server palsu. TLS tetap melindungi isi, tapi DNSSEC menutup peluang pemalsuan sebelum TLS dimulai.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  DNSKEY=0 RRSIG=0 rcode=0
  ```
- **Remediation**: Aktifkan DNSSEC di penyedia DNS (Cloudflare/Route53/cPanel punya saklar satu klik), lalu pasang DS record di registrar. Pakai algoritma RS256/13 dan siapkan key rollover.
- **OWASP**: Security Misconfiguration · **CWE-353** · confidence: confirmed · CVSS≈3.1

## Subdomain Enumeration (crt.sh)
### [LOW] 0 subdomain teregistrasi di CT log
*Semua subdomain ini pernah diterbitkan sertificate-nya dan kini tercatat publik. Subdomain staging/dev sering tidak di-patch, tidak diaudit, dan tetap online — ini titik masuk favorit.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  ·
  ```
- **Remediation**: Dekomisioner subdomain yang tidak dipakai, pastikan semuanya ter-patch & terpantau, dan hapus DNS record yatim (subdomain hijacking risk).
- **OWASP**: Security Misconfiguration · **RECON** · confidence: confirmed · CVSS≈3.1

## Third-party JS & SRI
### [LOW] 1 aset dimuat dari 1 host eksternal
*Setiap script pihak ketiga = akses penuh attacker ke DOM, bisa membaca form/token dan mengirim data keluar. Rincian host: static.cloudflareinsights.com(1).*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  [script] https://static.cloudflareinsights.com/beacon.min.js/vce701823e78c4301a35b7e07294d87301792088814092
  ```
- **Remediation**: Audit & hapus script yang tidak dipakai, pin versi konkret (bukan `latest`), dan tambahkan `integrity` + `crossorigin`.
- **OWASP**: Software and Data Integrity Failures · **CWE-829** · confidence: confirmed · CVSS≈3.1

## WebSocket Security
### [LOW] Socket.IO terdeteksi
*Socket.io membolehkan namespace publik; kalau server mencampahi event tanpa auth, user asing bisa subscribe ke room/data user lain. Periksa `io.use()` auth middleware.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  wss://averokomputer.my.id/socket.io/socket.io.js
  ```
- **Remediation**: Pasang middleware autentikasi per-namespace/room, jangan siarkan data user di event global.
- **OWASP**: Security Misconfiguration · **CWE-346** · confidence: needs-manual-review · CVSS≈3.1

## .well-known / security.txt
### [LOW] security.txt belum tersedia
*RFC 5785: `/.well-known/security.txt` memudahkan orang yang menemukan bug menghubungi kamu dengan benar. Tanpa kanal ini, penemu bug sering lapor ke media sosial atau langsung diekspos publik.*

- **URL**: `https://averokomputer.my.id/.well-known/security.txt`
- **Evidence**:
  ```
  GET /.well-known/security.txt → 404/tidak ada
  ```
- **Remediation**: Buat file berisi `Contact: https://.../security` (atau mailto + kunci), `Expires: <date>` maksimal 1 tahun, opsional `Encryption` & `Preferred-Languages: id, en`. Referensi: securitytxt.org
- **OWASP**: Security Misconfiguration · **CWE-200** · confidence: confirmed · CVSS≈3.1

## Exposed Admin / Management Panels
### [INFO] Tidak ada panel admin publik yang ketemu
*26 path diuji.*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Identification and Authentication Failures · **CWE-200** · confidence: confirmed · CVSS≈0.0

## Auth Bypass (path / header)
### [INFO] Tidak ada endpoint 401/403 yang ditemukan
*Probe ke path umum tidak menghasilkan satu pun respons 401/403. Entah tidak ada area privat, atau proteksinya di lapisan yang tidak terlihat dari luar.*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Broken Access Control · **CWE-288** · confidence: needs-manual-review · CVSS≈0.0

## Backup & Archive Files
### [INFO] Tidak ada arsip backup / file editor yang terbuka
*42 nama file backup & swap populer dicek (GET read-only) — tidak ada yang bisa diunduh publik.*

- **URL**: `https://averokomputer.my.id`
- **Remediation**: Kebiasaan aman: simpan backup di luar web root dengan nama acak + enkripsi, dan gunakan editor yang menyimpan swap di ~/.local, bukan di samping file.
- **OWASP**: Security Misconfiguration · **CWE-530** · confidence: possible · CVSS≈0.0

## Cache & CDN Control
### [INFO] Cache & CDN Control: tidak ditemukan bukti masalah ini
*Pemeriksaan selesai tanpa temuan. Ini berarti modul tidak melihat BUKTI masalah pada halaman/respons yang terjangkau scan — bukan jaminan absolut, karena cakupan terbatas pada yang terekspos publik (tanpa login).*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Security Misconfiguration · **CWE-524** · confidence: possible · CVSS≈0.0

## Cloud Storage Bucket
### [INFO] Tidak ada URL bucket cloud yang terekspos
*Halaman yang dipuat tidak memuat URL bucket S3/GCS/Azure. Ini normal — aset biasanya lewat CDN, bukan langsung ke bucket.*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Security Misconfiguration · **CWE-538** · confidence: confirmed · CVSS≈0.0

## CMS Checks (WP/Joomla/Drupal)
### [INFO] Tidak ada CMS populer yang teridentifikasi
*Jejak WordPress/Joomla/Drupal tidak ditemukan di HTML, header, maupun file bawaan yang dicoba (readme.html, CHANGELOG, xmlrpc, joomla.xml) — pemeriksaan khas-CMS dilewati.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  probe: "wp-content/", "wp-includes/", "wp-json", "/components/com_", "joomla", "drupal", "sites/default/files"
  ```
- **Remediation**: Tidak ada yang perlu diperbaiki bila memang tidak memakai CMS. Bila memakai, catat versi internalnya dan kunci file bawaannya.
- **OWASP**: Security Misconfiguration · **CWE-200** · confidence: possible · CVSS≈0.0

## OS Command Injection
### [INFO] OS Command Injection: tidak ditemukan bukti masalah ini
*Pemeriksaan selesai tanpa temuan. Ini berarti modul tidak melihat BUKTI masalah pada halaman/respons yang terjangkau scan — bukan jaminan absolut, karena cakupan terbatas pada yang terekspos publik (tanpa login).*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Injection · **CWE-78** · confidence: possible · CVSS≈0.0

## CORS Policy
### [INFO] Tidak ada header CORS
*Server tidak mendeklarasikan CORS → browser memblokir cross-origin read. Ini default yang AMAN.*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Broken Access Control · **CWE-942** · confidence: confirmed · CVSS≈0.0

## CRLF / Header Injection
### [INFO] Tidak ada indikasi CRLF injection
*2 titik uji × 7 encoding (14 request). Tidak ada token yang muncul di header mana pun, dan tidak ada CR/LF terbentuk di body.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  probe=14 · params=password, username
  ```
- **OWASP**: Injection · **CWE-113** · confidence: possible · CVSS≈0.0

## Debug & Diagnostic Endpoints
### [INFO] Tidak ada panel debug yang terbuka
*15 endpoint diagnostik + trigger Xdebug diuji — semuanya tertutup atau tidak dikenali. Permukaan info runtime bersih.*

- **URL**: `https://averokomputer.my.id`
- **Remediation**: Jaga begini: jalankan probe `actuator/heapdump` & `?XDEBUG_SESSION_START=1` di pipeline CI supaya konfigurasi debug tidak bisa lolos ke produksi.
- **OWASP**: Security Misconfiguration · **CWE-489** · confidence: possible · CVSS≈0.0

## Insecure Deserialization
### [INFO] Tidak ada indikasi data terserialisasi
*Tidak ditemukan pola serialize PHP/Java/Ruby/pickle di cookie, parameter, maupun body. ViewState .NET tetap perlu dicek manual apakah ter-MAC (machineKey).*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Software and Data Integrity Failures · **CWE-502** · confidence: confirmed · CVSS≈0.0

## DevOps & Pipeline Artifacts
### [INFO] Tidak ada artifact devops yang ter-deploy
*24 path pipeline/infra/build dicek — tidak ada yang terakses publik. Pipeline hygiene bagus.*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Security Misconfiguration · **CWE-540** · confidence: possible · CVSS≈0.0

## Directory Listing / Autoindex
### [INFO] Tidak ada autoindex yang terbuka
*1 path diuji tanpa tanda directory listing.*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Security Misconfiguration · **CWE-548** · confidence: confirmed · CVSS≈0.0

## DOM-based XSS (static JS audit)
### [INFO] Tidak ada JavaScript yang bisa diaudit
*Halaman target tidak memuat inline script maupun .js same-origin yang bisa diunduh. DOM XSS tetap mungkin di bundle pihak ketiga (tidak diaudit otomatis).*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Injection · **CWE-79** · confidence: needs-manual-review · CVSS≈0.0

## GraphQL Introspection
### [INFO] Tidak ada endpoint GraphQL yang terbukti
*Tidak ditemukan endpoint GraphQL yang merespons. Kalau aplikasi memakai GraphQL di belakang path acak, uji manual dengan akun milik sendiri.*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Broken Access Control · **CWE-200** · confidence: confirmed · CVSS≈0.0

## Host Header Injection
### [INFO] Tidak ada indikasi Host Header Injection
*Header host alternatif tidak mempan dipantulkan/dipakai untuk redirect. Poisoning pada alur email tetap butuh uji manual dengan akun milik sendiri.*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Security Misconfiguration · **CWE-644** · confidence: possible · CVSS≈0.0

## HTTP Methods
### [INFO] TRACE ditolak (HTTP 405)
*TRACE https://averokomputer.my.id → HTTP 405*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  TRACE https://averokomputer.my.id → HTTP 405
  ```
- **OWASP**: Security Misconfiguration · **CWE-650** · confidence: confirmed · CVSS≈0.0

### [INFO] Metode PUT dijawab HTTP 404
*PUT https://averokomputer.my.id/sw-1k5phxtibk-probe → HTTP 404 · body '<!DOCTYPE html> <html style="height:100%"> <head> <meta name="viewport" content="width=dev'*

- **URL**: `https://averokomputer.my.id/sw-1k5phxtibk-probe`
- **Evidence**:
  ```
  PUT https://averokomputer.my.id/sw-1k5phxtibk-probe → HTTP 404 · body '<!DOCTYPE html> <html style="height:100%"> <head> <meta name="viewport" content="width=dev'
  ```
- **OWASP**: Security Misconfiguration · **CWE-650** · confidence: needs-manual-review · CVSS≈0.0

### [INFO] Metode DELETE dijawab HTTP 404
*DELETE https://averokomputer.my.id/sw-1k5phxtibk-probe → HTTP 404 · body '<!DOCTYPE html> <html style="height:100%"> <head> <meta name="viewport" content="width=dev'*

- **URL**: `https://averokomputer.my.id/sw-1k5phxtibk-probe`
- **Evidence**:
  ```
  DELETE https://averokomputer.my.id/sw-1k5phxtibk-probe → HTTP 404 · body '<!DOCTYPE html> <html style="height:100%"> <head> <meta name="viewport" content="width=dev'
  ```
- **OWASP**: Security Misconfiguration · **CWE-650** · confidence: needs-manual-review · CVSS≈0.0

### [INFO] Metode PATCH dijawab HTTP 404
*PATCH https://averokomputer.my.id/sw-1k5phxtibk-probe → HTTP 404 · body '<!DOCTYPE html> <html style="height:100%"> <head> <meta name="viewport" content="width=dev'*

- **URL**: `https://averokomputer.my.id/sw-1k5phxtibk-probe`
- **Evidence**:
  ```
  PATCH https://averokomputer.my.id/sw-1k5phxtibk-probe → HTTP 404 · body '<!DOCTYPE html> <html style="height:100%"> <head> <meta name="viewport" content="width=dev'
  ```
- **OWASP**: Security Misconfiguration · **CWE-650** · confidence: needs-manual-review · CVSS≈0.0

### [INFO] Metode PROPFIND dijawab HTTP 404
*PROPFIND https://averokomputer.my.id/sw-1k5phxtibk-probe → HTTP 404 · body '<!DOCTYPE html> <html style="height:100%"> <head> <meta name="viewport" content="width=dev'*

- **URL**: `https://averokomputer.my.id/sw-1k5phxtibk-probe`
- **Evidence**:
  ```
  PROPFIND https://averokomputer.my.id/sw-1k5phxtibk-probe → HTTP 404 · body '<!DOCTYPE html> <html style="height:100%"> <head> <meta name="viewport" content="width=dev'
  ```
- **OWASP**: Security Misconfiguration · **CWE-650** · confidence: needs-manual-review · CVSS≈0.0

### [INFO] Metode COPY dijawab HTTP 404
*COPY https://averokomputer.my.id/sw-1k5phxtibk-probe → HTTP 404 · body '<!DOCTYPE html> <html style="height:100%"> <head> <meta name="viewport" content="width=dev'*

- **URL**: `https://averokomputer.my.id/sw-1k5phxtibk-probe`
- **Evidence**:
  ```
  COPY https://averokomputer.my.id/sw-1k5phxtibk-probe → HTTP 404 · body '<!DOCTYPE html> <html style="height:100%"> <head> <meta name="viewport" content="width=dev'
  ```
- **OWASP**: Security Misconfiguration · **CWE-650** · confidence: needs-manual-review · CVSS≈0.0

### [INFO] Metode MOVE dijawab HTTP 404
*MOVE https://averokomputer.my.id/sw-1k5phxtibk-probe → HTTP 404 · body '<!DOCTYPE html> <html style="height:100%"> <head> <meta name="viewport" content="width=dev'*

- **URL**: `https://averokomputer.my.id/sw-1k5phxtibk-probe`
- **Evidence**:
  ```
  MOVE https://averokomputer.my.id/sw-1k5phxtibk-probe → HTTP 404 · body '<!DOCTYPE html> <html style="height:100%"> <head> <meta name="viewport" content="width=dev'
  ```
- **OWASP**: Security Misconfiguration · **CWE-650** · confidence: needs-manual-review · CVSS≈0.0

### [INFO] Metode MKCOL dijawab HTTP 404
*MKCOL https://averokomputer.my.id/sw-1k5phxtibk-probe → HTTP 404 · body '<!DOCTYPE html> <html style="height:100%"> <head> <meta name="viewport" content="width=dev'*

- **URL**: `https://averokomputer.my.id/sw-1k5phxtibk-probe`
- **Evidence**:
  ```
  MKCOL https://averokomputer.my.id/sw-1k5phxtibk-probe → HTTP 404 · body '<!DOCTYPE html> <html style="height:100%"> <head> <meta name="viewport" content="width=dev'
  ```
- **OWASP**: Security Misconfiguration · **CWE-650** · confidence: needs-manual-review · CVSS≈0.0

### [INFO] Metode PROPPATCH dijawab HTTP 404
*PROPPATCH https://averokomputer.my.id/sw-1k5phxtibk-probe → HTTP 404 · body '<!DOCTYPE html> <html style="height:100%"> <head> <meta name="viewport" content="width=dev'*

- **URL**: `https://averokomputer.my.id/sw-1k5phxtibk-probe`
- **Evidence**:
  ```
  PROPPATCH https://averokomputer.my.id/sw-1k5phxtibk-probe → HTTP 404 · body '<!DOCTYPE html> <html style="height:100%"> <head> <meta name="viewport" content="width=dev'
  ```
- **OWASP**: Security Misconfiguration · **CWE-650** · confidence: needs-manual-review · CVSS≈0.0

### [INFO] Metode CONNECT dijawab HTTP 400
*CONNECT https://averokomputer.my.id/sw-1k5phxtibk-probe → HTTP 400 · body '<!doctype html> <!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en-US"> <![endif]-->'*

- **URL**: `https://averokomputer.my.id/sw-1k5phxtibk-probe`
- **Evidence**:
  ```
  CONNECT https://averokomputer.my.id/sw-1k5phxtibk-probe → HTTP 400 · body '<!doctype html> <!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en-US"> <![endif]-->'
  ```
- **OWASP**: Security Misconfiguration · **CWE-650** · confidence: needs-manual-review · CVSS≈0.0

## HTTP Request Smuggling (probe)
### [INFO] 1 probe smuggling tidak dapat disimpulkan
*Permintaan dibalas tanpa status yang jelas (koneksi ditutup / error transport). Ini BUKAN bukti aman dan BUKAN bukti desync — hanya menandakan perlu uji timing (CL.TE/TE.CL time-based) oleh penguji manual.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  TE-obfuscation: InvalidHeader: Invalid leading whitespace, reserved character(s), or return character(s) in header value: '\tchunked'
  ```
- **OWASP**: Security Misconfiguration · **CWE-444** · confidence: needs-manual-review · CVSS≈0.0

### [INFO] Server menolak permintaan panjang yang ambigu
*3 dari 4 probe (CL.TE, TE.CL, obfuscation, duplikasi) dibalas 4xx/5xx — perilaku yang benar untuk front-end.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  CL.TE → HTTP 400; TE.CL → HTTP 501; TE-duplicate → HTTP 400
(Tidak dapat disimpulkan: TE-obfuscation: InvalidHeader: Invalid leading whitespace, reserved character(s), or return character(s) in header value: '\tchunked')
  ```
- **Remediation**: Request smuggling tetap perlu diuji manual dengan timing probe (CL.TE/TE.CL time-based) karena penolakan status saja tidak membuktikan tidak ada desync.
- **OWASP**: Security Misconfiguration · **CWE-444** · confidence: possible · CVSS≈0.0

## IDOR / Broken Object Level Authorization
### [INFO] Tidak ada object reference yang terdeteksi
*URL & halaman yang discrawl tidak memuat param id/uid/order/dokumen yang polanya bisa ditebak. IDOR biasanya ada di endpoint API setelah login — uji manual dengan dua akun milik sendiri.*

- **URL**: `https://averokomputer.my.id`
- **Remediation**: Saat menambah API ber-id, pastikan otorisasi per-objek (cek pemilik) bukan hanya cek login, dan pakai UUID random sebagai ID publik.
- **OWASP**: Broken Access Control · **CWE-639** · confidence: needs-manual-review · CVSS≈0.0

## JNDI / Log4Shell Signature
### [INFO] Tidak ada jejak Log4Shell dari sisi respons
*2 param + header diuji dengan literal `${jndi:…invalid}` — tidak ada pesan lookup/refleksi/500 anomali. Catatan: blind Log4Shell TIDAK selalu berbunyi di respons; bukti definitif adalah cek versi library & log server.*

- **URL**: `https://averokomputer.my.id`
- **Remediation**: Verifikasi `log4j-core` ≥2.17.1 di dependency lockfile/artifact (bukan dari luar) dan grep log produksi untuk '$'+'{jndi'.
- **OWASP**: Injection · **CWE-917** · confidence: possible · CVSS≈0.0

## Secrets in JavaScript
### [INFO] Tidak ada JS same-origin yang bisa dipindai
*Halaman target tidak merujuk inline script maupun berkas .js dari host yang sama.*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Cryptographic Failures · **CWE-798** · confidence: needs-manual-review · CVSS≈0.0

## JSONP Hijacking
### [INFO] Tidak ada endpoint JSONP yang ditemukan
*Tidak ditemukan callback param / pola API yang khas JSONP. API modern sebaiknya memakai CORS + auth, bukan JSONP.*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Broken Access Control · **CWE-346** · confidence: possible · CVSS≈0.0

## JWT & Token Analysis
### [INFO] Tidak ada JWT yang terdeteksi
*Tidak ditemukan token format JWT (header.payload.signature) di cookie, body, maupun respons. Kalau aplikasi memang memakai JWT, biasanya ada di header Authorization saat user login — uji dengan akun test milik sendiri.*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Identification and Authentication Failures · **CWE-347** · confidence: confirmed · CVSS≈0.0

## LDAP Injection
### [INFO] Tidak ada indikasi LDAP injection
*3 param diuji dengan wildcard & penutup filter. Tidak ada error directory maupun pelonggaran hasil.*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Injection · **CWE-90** · confidence: possible · CVSS≈0.0

## Local/Remote File Inclusion
### [INFO] Local/Remote File Inclusion: tidak ditemukan bukti masalah ini
*Pemeriksaan selesai tanpa temuan. Ini berarti modul tidak melihat BUKTI masalah pada halaman/respons yang terjangkau scan — bukan jaminan absolut, karena cakupan terbatas pada yang terekspos publik (tanpa login).*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Injection · **CWE-98** · confidence: possible · CVSS≈0.0

## Link & Navigation Hygiene
### [INFO] Higienitas tautan & navigasi bersih
*1 halaman diperiksa: tidak ada form plaintext, kredensial dalam URL, target=_blank tanpa noopener, atau <base> mencurigakan.*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Security Misconfiguration · **CWE-1022** · confidence: possible · CVSS≈0.0

## Mass Assignment / Auto-Binding
### [INFO] Tidak ada field otoritatif yang terekspos di sisi klien
*Form & JS yang dimuat tidak memuat nama field sensitif (role/balance/status/dll).*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Insecure Design · **CWE-915** · confidence: possible · CVSS≈0.0

## Mixed Content
### [INFO] Mixed Content: tidak ditemukan bukti masalah ini
*Pemeriksaan selesai tanpa temuan. Ini berarti modul tidak melihat BUKTI masalah pada halaman/respons yang terjangkau scan — bukan jaminan absolut, karena cakupan terbatas pada yang terekspos publik (tanpa login).*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Cryptographic Failures · **CWE-311** · confidence: possible · CVSS≈0.0

## NoSQL Injection
### [INFO] Tidak ada indikasi NoSQL injection
*2 parameter diuji dengan operator `$ne`/`$gt`/`$exists` + sintaks JSON. Tidak ada error DB maupun delta boolean yang muncul.*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Injection · **CWE-89** · confidence: possible · CVSS≈0.0

## OAuth / SSO Configuration
### [INFO] Tidak ada alur OAuth/SSO yang terlihat
*Halaman yang discrawl tidak memuat tautan/parameter OAuth. Kalau login SSO ada di balik tombol (JS), arahkan scanner ke halaman login tersebut.*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Cryptographic Failures · **CWE-290** · confidence: needs-manual-review · CVSS≈0.0

## Open / Unvalidated Redirect
### [INFO] Open / Unvalidated Redirect: tidak ditemukan bukti masalah ini
*Pemeriksaan selesai tanpa temuan. Ini berarti modul tidak melihat BUKTI masalah pada halaman/respons yang terjangkau scan — bukan jaminan absolut, karena cakupan terbatas pada yang terekspos publik (tanpa login).*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Broken Access Control · **CWE-601** · confidence: possible · CVSS≈0.0

## HTTP Parameter Pollution
### [INFO] Tidak ada ketidaksepakatan parameter terdeteksi
*5 param diuji dengan duplikasi, `;`, dan notasi array. Respons tetap konsisten.*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Insecure Design · **CWE-235** · confidence: possible · CVSS≈0.0

## Path Traversal / LFI
### [INFO] Path Traversal / LFI: tidak ditemukan bukti masalah ini
*Pemeriksaan selesai tanpa temuan. Ini berarti modul tidak melihat BUKTI masalah pada halaman/respons yang terjangkau scan — bukan jaminan absolut, karena cakupan terbatas pada yang terekspos publik (tanpa login).*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Broken Access Control · **CWE-22** · confidence: possible · CVSS≈0.0

## postMessage Security
### [INFO] Tidak ada JS klien untuk audit postMessage
*Tidak ditemukan inline script / .js same-origin yang bisa dianalisa.*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Broken Access Control · **CWE-346** · confidence: needs-manual-review · CVSS≈0.0

## Prototype Pollution (JS backend)
### [INFO] Tidak ada indikasi prototype pollution
*4 variasi key `__proto__`/`constructor` diuji — tidak ada refleksi di JSON keluaran maupun error khusus.*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Injection · **CWE-1321** · confidence: possible · CVSS≈0.0

## robots.txt / sitemap.xml
### [INFO] robots.txt tersedia (0 Disallow, 0 Allow)
*# As a condition of accessing this website, you agree to abide by the following · # content signals: ·  · # (a)  If a content-signal = yes, you may collect content for the corresponding · #  use. · # (b)  If a content-signal = no, you may not collect content for the · #  corresponding use. · # (c)  If the*

- **URL**: `https://averokomputer.my.id/robots.txt`
- **Evidence**:
  ```
  # As a condition of accessing this website, you agree to abide by the following · # content signals: ·  · # (a)  If a content-signal = yes, you may collect content for the corresponding · #  use. · # (b)  If a content-signal = no, you may not collect content for the · #  corresponding use. · # (c)  If the
  ```
- **OWASP**: Security Misconfiguration · **CWE-200** · confidence: confirmed · CVSS≈0.0

## Exposed Sensitive Files
### [INFO] Tidak ada file sensitif yang terbaca
*39 path umum diuji dan tidak ada yang membuka kontennya.*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Security Misconfiguration · **CWE-538** · confidence: confirmed · CVSS≈0.0

## Service Worker Security
### [INFO] Tidak ada service worker terdeteksi
*Registrasi `navigator.serviceWorker` maupun berkas sw.js umum tidak ditemukan. Tanpa SW, permukaan persisten lintas-sesi ini tidak ada.*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Security Misconfiguration · **CWE-346** · confidence: possible · CVSS≈0.0

## Session Fixation / URL Sessions
### [INFO] Session-id yang disuplai klien tidak diadopsi server
*Probe cookie `PHPSESSID, sessionid, ci_session, laravel_session` dikirim ke https://averokomputer.my.id, https://averokomputer.my.id/account/login, https://averokomputer.my.id/login.php, https://averokomputer.my.id/signin, https://averokomputer.my.id/users/sign_in. Server tidak pernah mengembalikan nilai acak itu lewat Set-Cookie, jadi jalur fiksasi lewat cookie tertutup pada titik uji ini. Catatan: ini BUKAN jaminan — form login yang tidak terlihat scanner (mis. di belakang SSO/2FA) wajib dites manual.*

- **URL**: `https://averokomputer.my.id/login.php`
- **Evidence**:
  ```
  https://averokomputer.my.id/login.php → HTTP 200
https://averokomputer.my.id/signin → HTTP 404
https://averokomputer.my.id/users/sign_in → HTTP 404
https://averokomputer.my.id/account/login → HTTP 404
https://averokomputer.my.id → HTTP 302
  ```
- **Remediation**: Pertahankan: regenerasi identifier setelah login, tolak ID tak dikenal, dan jangan bawa sesi di URL.
- **OWASP**: Security Misconfiguration · **CWE-384** · confidence: possible · CVSS≈0.0

## Source Map Exposure
### [INFO] Tidak ada source map yang terekspos
*0 kandidat path .map dicek — semua 404/tidak diumumkan. Konfigurasi produksi bersih.*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Broken Access Control · **CWE-540** · confidence: possible · CVSS≈0.0

## SQL Injection
### [INFO] SQL Injection: tidak ditemukan bukti masalah ini
*Pemeriksaan selesai tanpa temuan. Ini berarti modul tidak melihat BUKTI masalah pada halaman/respons yang terjangkau scan — bukan jaminan absolut, karena cakupan terbatas pada yang terekspos publik (tanpa login).*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Injection · **CWE-89** · confidence: possible · CVSS≈0.0

## TLS Certificate Analysis
### [INFO] Sertifikat valid
*berlaku s/d 2026-12-20 (47 hari lagi)*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  berlaku s/d 2026-12-20 (47 hari lagi)
  ```
- **OWASP**: Cryptographic Failures · **CWE-295** · confidence: confirmed · CVSS≈0.0

### [INFO] SAN mencakup nama host
*averokomputer.my.id ∈ *.averokomputer.my.id, averokomputer.my.id*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  averokomputer.my.id ∈ *.averokomputer.my.id, averokomputer.my.id
  ```
- **OWASP**: Cryptographic Failures · **CWE-295** · confidence: confirmed · CVSS≈0.0

### [INFO] Detail sertifikat
*CN=averokomputer.my.id · issuer=Let's Encrypt · SAN=2 · version=V3 · serial=0616DD80430832CFE74A9747*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  CN=averokomputer.my.id · issuer=Let's Encrypt · SAN=2 · version=V3 · serial=0616DD80430832CFE74A9747
  ```
- **OWASP**: Cryptographic Failures · **CWE-295** · confidence: confirmed · CVSS≈0.0

## SSRF (Server-Side Request Forgery)
### [INFO] Tidak ada indikasi SSRF pada parameter uji
*Parameter URL yang diuji tidak menunjukkan perilaku fetch server-side. Blind SSRF tanpa collaborator tidak bisa dibuktikan otomatis.*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Server-Side Request Forgery (SSRF) · **CWE-918** · confidence: needs-manual-review · CVSS≈0.0

## Server-Side Template Injection
### [INFO] Server-Side Template Injection: tidak ditemukan bukti masalah ini
*Pemeriksaan selesai tanpa temuan. Ini berarti modul tidak melihat BUKTI masalah pada halaman/respons yang terjangkau scan — bukan jaminan absolut, karena cakupan terbatas pada yang terekspos publik (tanpa login).*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Injection · **CWE-1336** · confidence: possible · CVSS≈0.0

## Subdomain Brute-Force (DNS)
### [INFO] 0 dari 108 kandidat subdomain yang resolve
*Tidak ada satu pun nama umum (dev, staging, admin, db, git, …) yang punya record A di DNS `{apex}`. Permukaan host tersembunyi minimal.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  contoh diuji: www, mail, webmail, ftp, admin, administrator, dev, develop, development, staging, stage, test …
  ```
- **Remediation**: Tetap pantau DNS berkala — host sementara (preview PR, event landing) sering muncul lalu terlupa dihapus.
- **OWASP**: Security Misconfiguration · **CWE-200** · confidence: possible · CVSS≈0.0

## Subdomain Takeover (dangling DNS)
### [INFO] 2 subdomain dicek — tidak ada CNAME menggantung ke layanan yang bisa diambil
*Cek mencakup provider umum (GitHub/Heroku/S3/Netlify/Azure/Vercel/Fastly/Shopify/dkk). Subdomain yang menunjuk VPS/CDN privat tidak terdeteksi di sini.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  my.id · www.my.id
  ```
- **OWASP**: Security Misconfiguration · **CWE-284** · confidence: possible · CVSS≈0.0

## File Upload Surface Audit
### [INFO] Permukaan upload ada tapi tidak mencurigakan
*0 form, 4 path API*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  0 form, 4 path API
  ```
- **OWASP**: Injection · **CWE-434** · confidence: possible · CVSS≈0.0

## WAF / CDN Detection
### [INFO] WAF/CDN terdeteksi: Cloudflare
*Cloudflare teridentifikasi dari header/cookie (kecocokan 3 signature).*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  header markers
  ```
- **Remediation**: Catat bahwa hasil scan 'bersih' bisa berarti payload dipotong WAF, bukan berarti code-nya aman.
- **OWASP**: Security Misconfiguration · **WAF** · confidence: possible · CVSS≈0.0

### [INFO] WAF memotong payload uji
*Beberapa payload diblokir di lapisan depan. Ini bagus sebagai defence-in-depth, TIDAK boleh dianggap aplikasi sudah tidak vulnerable — WAF bisa di-bypass (encoding, chunked, case variation, parameter pollution).*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  scanner-ua(HTTP 403)
  ```
- **Remediation**: Perbaiki di source (escape/parameterize/authorization), WAF hanya lapisan tambahan.
- **OWASP**: Security Misconfiguration · **WAF** · confidence: confirmed · CVSS≈0.0

### [INFO] Header indikatif
*server=cloudflare; cf-ray=a547131bfa8c0cd6-HKG*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  server=cloudflare; cf-ray=a547131bfa8c0cd6-HKG
  ```
- **OWASP**: Security Misconfiguration · **WAF** · confidence: possible · CVSS≈0.0

## Webshell / Backdoor Files
### [INFO] Tidak ada jejak webshell/backdoor
*31 path shell populer dicek (read-only GET). Tidak ada respons yang memuat fingerprint shell.*

- **URL**: `https://averokomputer.my.id`
- **Remediation**: Pemindaian file di server (malware-recom, rkhunter, LMD) tetap disarankan secara berkala.
- **OWASP**: Injection · **CWE-506** · confidence: possible · CVSS≈0.0

## XPath Injection
### [INFO] Tidak ada indikasi XPath injection
*5 param diuji dengan pemutus kutip + wildcard. Tidak ada error parser XPath maupun lonjakan node.*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Injection · **CWE-643** · confidence: possible · CVSS≈0.0

## Reflected Cross-Site Scripting (XSS)
### [INFO] Reflected Cross-Site Scripting (XSS): tidak ditemukan bukti masalah ini
*Pemeriksaan selesai tanpa temuan. Ini berarti modul tidak melihat BUKTI masalah pada halaman/respons yang terjangkau scan — bukan jaminan absolut, karena cakupan terbatas pada yang terekspos publik (tanpa login).*

- **URL**: `https://averokomputer.my.id`
- **OWASP**: Injection · **CWE-79** · confidence: possible · CVSS≈0.0

## XML External Entity (XXE)
### [INFO] XXE tidak terbukti di 7 endpoint XML
*SYSTEM entity `file:///etc/hosts` dikirim ke endpoint-endpoint berikut. Tidak ada yang memenuhi syarat bukti (≥2 baris bergaya hosts file, respons berbeda dari kontrol XML benign, bukan halaman custom-404). Yang muncul justru halaman 404/405/default milik aplikasi — persis kondisi yang dulu dijual sebagai 6 CRITICAL palsu.*

- **URL**: `https://averokomputer.my.id/xml`
- **Evidence**:
  ```
  averokomputer.my.id/xml (0 baris hosts (< 2)); averokomputer.my.id/api (0 baris hosts (< 2)); averokomputer.my.id/api/xml (0 baris hosts (< 2)); averokomputer.my.id/xmlrpc.php (0 baris hosts (< 2)); averokomputer.my.id/soap (0 baris hosts (< 2)); averokomputer.my.id/ws (0 baris hosts (< 2)); averokomputer.my.id/service (0 baris hosts (< 2))
  ```
- **OWASP**: Injection · **CWE-611** · confidence: needs-manual-review · CVSS≈0.0

## DNS Zone Transfer (AXFR)
### [INFO] 2 nameserver menolak AXFR (baik)
*`averokomputer.my.id` tidak melayani zone transfer ke pihak tak terdaftar — konfigurasi sudah benar.*

- **URL**: `https://averokomputer.my.id`
- **Evidence**:
  ```
  jaxson.ns.cloudflare.com, colette.ns.cloudflare.com
  ```
- **OWASP**: Security Misconfiguration · **CWE-541** · confidence: confirmed · CVSS≈0.0

---
_Laporan ini hasil pemindaian otomatis. Verifikasi manual tetap diperlukan sebelum tindak lanjut._