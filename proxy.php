<?php

declare(strict_types=1);

require_once __DIR__ . '/app/secure_config.php';
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

/*
|--------------------------------------------------------------------------
| CONFIG
|--------------------------------------------------------------------------
| IMPORTANT:
| 1) Put your REAL Winston API key here or load it from a secure config file.
| 2) Because your Cloudflare secret was shared in chat, rotate it first,
|    then paste the NEW secret here.
*/

const MAX_TEXT_LENGTH = 120000;
const MIN_TEXT_LENGTH = 100;
const MAX_FILE_SIZE   = 10485760; // 10 MB

$storageDir = __DIR__ . '/storage/plagiascope_tmp/';
$publicDir  = __DIR__ . '/uploads/plagiascope_public/';

if (!is_dir($storageDir) && !mkdir($storageDir, 0755, true) && !is_dir($storageDir)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create storage directory']);
    exit;
}

if (!is_dir($publicDir) && !mkdir($publicDir, 0755, true) && !is_dir($publicDir)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create public upload directory']);
    exit;
}

/*
|--------------------------------------------------------------------------
| SIMPLE RATE LIMIT
|--------------------------------------------------------------------------
*/
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$now = time();

if (!isset($_SESSION['ps_rate'])) {
    $_SESSION['ps_rate'] = [];
}

$_SESSION['ps_rate'] = array_values(array_filter(
    $_SESSION['ps_rate'],
    static fn($ts) => ($now - (int)$ts) < 300
));

if (count($_SESSION['ps_rate']) >= 10) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many scan requests. Please wait a few minutes and try again.']);
    exit;
}

$_SESSION['ps_rate'][] = $now;

/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/
function json_error(int $code, string $message): void
{
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

function get_request_payload(): array
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    $isMultipart = stripos($contentType, 'multipart/form-data') !== false;

    if ($isMultipart) {
        return [
            'isMultipart' => true,
            'text'        => $_POST['text'] ?? '',
            'language'    => $_POST['language'] ?? 'en',
            'country'     => $_POST['country'] ?? 'us',
            'turnstile'   => $_POST['cf-turnstile-response'] ?? '',
        ];
    }

    $raw = json_decode(file_get_contents('php://input'), true);
    if (!is_array($raw)) {
        json_error(400, 'Invalid request body');
    }

    return [
        'isMultipart' => false,
        'text'        => $raw['text'] ?? '',
        'language'    => $raw['language'] ?? 'en',
        'country'     => $raw['country'] ?? 'us',
        'turnstile'   => $raw['cf-turnstile-response'] ?? '',
    ];
}

function verify_turnstile(string $token, string $secret, string $ip): void
{
    if ($token === '') {
        json_error(400, 'Human verification token is missing.');
    }

    $postData = http_build_query([
        'secret'   => $secret,
        'response' => $token,
        'remoteip' => $ip,
    ]);

    $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postData,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        json_error(502, 'Failed to verify human check.');
    }

    $decoded = json_decode((string)$response, true);
    if (!is_array($decoded) || empty($decoded['success'])) {
        json_error(403, 'Human verification failed. Please try again.');
    }
}

function safe_unlink(?string $path): void
{
    if ($path && is_file($path)) {
        @unlink($path);
    }
}

function build_public_file_url(string $publicFileName): string
{
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $https ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base   = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');

    return $scheme . '://' . $host . $base . '/uploads/plagiascope_public/' . rawurlencode($publicFileName);
}

/*
|--------------------------------------------------------------------------
| REQUEST PARSE + TURNSTILE VERIFY
|--------------------------------------------------------------------------
*/
$payloadData = get_request_payload();

$text      = trim((string)$payloadData['text']);
$language  = trim((string)$payloadData['language']) ?: 'en';
$country   = trim((string)$payloadData['country']) ?: 'us';
$turnstile = trim((string)$payloadData['turnstile']);

verify_turnstile($turnstile, TURNSTILE_SECRET_KEY, $ip);

/*
|--------------------------------------------------------------------------
| BUILD WINSTON PAYLOAD
|--------------------------------------------------------------------------
*/
$payload = [
    'language' => $language,
    'country'  => $country,
];

$storedPrivatePath = null;
$storedPublicPath  = null;

if ($payloadData['isMultipart'] && !empty($_FILES['file']['tmp_name'])) {
    if (!isset($_FILES['file']['error']) || is_array($_FILES['file']['error'])) {
        json_error(400, 'Invalid uploaded file.');
    }

    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        json_error(400, 'File upload failed.');
    }

    if ((int)$_FILES['file']['size'] > MAX_FILE_SIZE) {
        json_error(400, 'File size must be under 10 MB.');
    }

    $origName = (string)($_FILES['file']['name'] ?? 'file');
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

    $allowedExt = ['pdf', 'doc', 'docx'];
    if (!in_array($ext, $allowedExt, true)) {
        json_error(400, 'Only PDF, DOC, and DOCX files are supported.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $_FILES['file']['tmp_name']);
    finfo_close($finfo);

    $allowedMime = [
        'pdf'  => ['application/pdf'],
        'doc'  => ['application/msword', 'application/octet-stream'],
        'docx' => [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',
            'application/octet-stream'
        ],
    ];

    if (!in_array($mime, $allowedMime[$ext], true)) {
        json_error(400, 'Invalid file type or MIME type mismatch.');
    }

    $safeBase = bin2hex(random_bytes(16));
    $privateName = $safeBase . '.' . $ext;
    $publicName  = $safeBase . '.' . $ext;

    $storedPrivatePath = $storageDir . $privateName;
    $storedPublicPath  = $publicDir . $publicName;

    if (!move_uploaded_file($_FILES['file']['tmp_name'], $storedPrivatePath)) {
        json_error(500, 'Failed to store uploaded file.');
    }

    if (!copy($storedPrivatePath, $storedPublicPath)) {
        safe_unlink($storedPrivatePath);
        json_error(500, 'Failed to prepare public scan file.');
    }

    $payload['file'] = build_public_file_url($publicName);
} else {
    if ($text === '') {
        json_error(400, 'Please provide text or upload a file.');
    }
    if (strlen($text) < MIN_TEXT_LENGTH) {
        json_error(400, 'Text must be at least 100 characters.');
    }
    if (strlen($text) > MAX_TEXT_LENGTH) {
        json_error(400, 'Text exceeds the maximum allowed length.');
    }
    $payload['text'] = $text;
}

/*
|--------------------------------------------------------------------------
| FORWARD TO WINSTON
|--------------------------------------------------------------------------
*/
$postData = json_encode($payload);

$ch = curl_init('https://api.gowinston.ai/v2/plagiarism');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $postData,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . WINSTON_API_KEY,
        'Content-Type: application/json',
        'Content-Length: ' . strlen((string)$postData),
    ],
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_TIMEOUT        => 120,
]);

$response = curl_exec($ch);
$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

/*
|--------------------------------------------------------------------------
| CLEANUP
|--------------------------------------------------------------------------
*/
safe_unlink($storedPrivatePath);
safe_unlink($storedPublicPath);

if ($curlErr) {
    json_error(502, 'Failed to reach Winston AI: ' . $curlErr);
}

http_response_code($httpCode > 0 ? $httpCode : 500);
echo $response ?: json_encode(['error' => 'Empty response from Winston AI']);
