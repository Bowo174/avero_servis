<?php
$nomor = $_GET['nomor'] ?? '';
header('Location: tanda_terima_servis.php?nomor=' . urlencode($nomor));
exit;
?>
