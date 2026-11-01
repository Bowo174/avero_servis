<?php
$nomor = $_GET['nomor'] ?? '';
header('Location: nota_servis.php?nomor=' . urlencode($nomor));
exit;
?>
