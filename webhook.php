<?php
$configPath = __DIR__ . '/config.local.php';
if (!is_file($configPath)) {
    http_response_code(503);
    exit('Webhook belum dikonfigurasi.');
}
$config = require $configPath;
$verifyToken = (string) ($config['whatsapp_webhook_verify_token'] ?? '');
$appSecret = (string) ($config['whatsapp_app_secret'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $mode = $_GET['hub_mode'] ?? '';
    $token = $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? '';
    if ($verifyToken !== '' && $mode === 'subscribe' && hash_equals($verifyToken, (string) $token)) {
        header('Content-Type: text/plain; charset=utf-8');
        echo $challenge;
        exit;
    }
    http_response_code(403);
    exit('VERIFICATION_FAILED');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($appSecret === '') {
        http_response_code(503);
        exit('Webhook belum dikonfigurasi.');
    }
    $input = file_get_contents('php://input');
    $signature = (string) ($_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '');
    $expected = 'sha256=' . hash_hmac('sha256', $input, $appSecret);
    if ($signature === '' || !hash_equals($expected, $signature)) {
        http_response_code(403);
        exit('INVALID_SIGNATURE');
    }
    http_response_code(200);
    exit('EVENT_RECEIVED');
}

http_response_code(405);
header('Allow: GET, POST');
exit('METHOD_NOT_ALLOWED');
