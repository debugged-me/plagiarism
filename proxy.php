<?php
// ─────────────────────────────────────────────
//  PlagiaScope — PHP Proxy for Winston AI
//  Supports: plain text + file uploads (PDF, DOC, DOCX)
// ─────────────────────────────────────────────

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// ── Parse body ──
// Multipart (file upload) comes as $_POST + $_FILES
// JSON (text only) comes as raw body
$isMultipart = isset($_SERVER['CONTENT_TYPE']) &&
    strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') !== false;

if ($isMultipart) {
    $apiKey   = $_POST['_apiKey']  ?? '';
    $cf_token = $_POST['cf_token'] ?? '';
    $language = $_POST['language'] ?? 'en';
    $country  = $_POST['country']  ?? 'us';
    $textBody = $_POST['text']     ?? '';
} else {
    $raw  = json_decode(file_get_contents('php://input'), true);
    if (!$raw) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request body']);
        exit;
    }
    $apiKey   = $raw['_apiKey']  ?? '';
    $cf_token = $raw['cf_token'] ?? '';
    $language = $raw['language'] ?? 'en';
    $country  = $raw['country']  ?? 'us';
    $textBody = $raw['text']     ?? '';
}

// ── Cloudflare Turnstile validation ──
$turnstile_secret = '0x4AAAAAACu4yZ4M7OXpeGy_lgbLVY5n08A';
if (empty($cf_token)) {
    http_response_code(400);
    echo json_encode(['error' => 'Security token missing']);
    exit;
}
$verify_data = http_build_query([
    'secret'   => $turnstile_secret,
    'response' => $cf_token,
    'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
]);
$verify_context = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $verify_data
    ]
]);
$verify_resp = file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, $verify_context);
$verify_json = json_decode($verify_resp, true);
if (!$verify_json['success']) {
    http_response_code(403);
    echo json_encode(['error' => 'Security check failed']);
    exit;
}


if (!$apiKey) {
    http_response_code(401);
    echo json_encode(['error' => 'No API key provided']);
    exit;
}

// ── Build Winston AI payload ──
$payload = [
    'language' => $language,
    'country'  => $country,
];

// ── Handle file upload ──
$uploadedFilePath = null;

if ($isMultipart && !empty($_FILES['file']['tmp_name'])) {
    $allowed  = ['pdf', 'doc', 'docx'];
    $origName = $_FILES['file']['name'];
    $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        http_response_code(400);
        echo json_encode(['error' => 'Only PDF, DOC, and DOCX files are supported.']);
        exit;
    }

    if ($_FILES['file']['size'] > 10 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['error' => 'File size must be under 10 MB.']);
        exit;
    }

    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = uniqid('ps_', true) . '.' . $ext;
    $filePath = $uploadDir . $fileName;

    if (!move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save uploaded file.']);
        exit;
    }

    $uploadedFilePath = $filePath;

    // Build public URL for the uploaded file
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'];
    $dir      = rtrim(dirname($_SERVER['REQUEST_URI']), '/');
    $fileUrl  = $protocol . '://' . $host . $dir . '/uploads/' . $fileName;

    $payload['file'] = $fileUrl;
} elseif (!empty($textBody)) {
    if (strlen($textBody) < 100) {
        http_response_code(400);
        echo json_encode(['error' => 'Text must be at least 100 characters.']);
        exit;
    }
    $payload['text'] = $textBody;
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Provide either text or a file to scan.']);
    exit;
}

// ── Forward to Winston AI ──
$postData = json_encode($payload);

$ch = curl_init('https://api.gowinston.ai/v2/plagiarism');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $postData,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
        'Content-Length: ' . strlen($postData),
    ],
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_TIMEOUT        => 120,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

// Clean up uploaded file after forwarding
if ($uploadedFilePath && file_exists($uploadedFilePath)) {
    unlink($uploadedFilePath);
}

if ($curlErr) {
    http_response_code(502);
    echo json_encode(['error' => 'Failed to reach Winston AI: ' . $curlErr]);
    exit;
}

http_response_code($httpCode);
echo $response;
