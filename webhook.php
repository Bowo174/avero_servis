<?php

// Token rahasia untuk verifikasi webhook Meta
$verify_token = "avero_webhook_2026";

// Verifikasi webhook dari Meta
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $mode = $_GET['hub_mode'] ?? '';
    $token = $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? '';

    if ($mode === 'subscribe' && $token === $verify_token) {
        echo $challenge;
        exit;
    }

    http_response_code(403);
    echo "VERIFICATION_FAILED";
    exit;
}

// Menerima data webhook dari Meta
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $input = file_get_contents("php://input");

    // Simpan data webhook untuk sementara agar mudah diperiksa
    file_put_contents(
        _DIR_ . "/webhook_log.txt",
        date("Y-m-d H:i:s") . "\n" . $input . "\n\n",
        FILE_APPEND
    );

    http_response_code(200);
    echo "EVENT_RECEIVED";
    exit;
}

http_response_code(405);
echo "METHOD_NOT_ALLOWED";
?>