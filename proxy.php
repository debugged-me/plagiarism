<?php

declare(strict_types=1);

require_once __DIR__ . '/app/secure_config.php';
require_once __DIR__ . '/app/database.php';
require_once __DIR__ . '/app/session.php';
start_app_session();

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



const MAX_TEXT_LENGTH = 120000;
const MIN_TEXT_LENGTH = 100;
const MAX_FILE_SIZE   = 10485760; // 10 MB

$storageDir = __DIR__ . '/storage/plagiascope_tmp/';
$publicDir  = __DIR__ . '/uploads/plagiascope_public/';

// Create directories with more permissive permissions for cPanel
if (!is_dir($storageDir)) {
    if (!mkdir($storageDir, 0777, true) && !is_dir($storageDir)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create storage directory. Please create it manually and set permissions to 755 or 777.']);
        exit;
    }
    chmod($storageDir, 0777);
}

if (!is_dir($publicDir)) {
    if (!mkdir($publicDir, 0777, true) && !is_dir($publicDir)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create public upload directory. Please create it manually and set permissions to 755 or 777.']);
        exit;
    }
    chmod($publicDir, 0777);
}

// Verify directories are writable
if (!is_writable($storageDir)) {
    chmod($storageDir, 0777);
    if (!is_writable($storageDir)) {
        http_response_code(500);
        echo json_encode(['error' => 'Storage directory is not writable. Path: ' . $storageDir]);
        exit;
    }
}

if (!is_writable($publicDir)) {
    chmod($publicDir, 0777);
    if (!is_writable($publicDir)) {
        http_response_code(500);
        echo json_encode(['error' => 'Public upload directory is not writable. Path: ' . $publicDir]);
        exit;
    }
}


$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$now = time();
$isLoggedIn = !empty($_SESSION['is_logged_in']) && !empty($_SESSION['user_id']);
$userId = $_SESSION['user_id'] ?? null;

// Rate limiting based on user type
if ($isLoggedIn && $userId) {
    // Logged-in users: 10 scans per day (free tier)
    try {
        $db = PlagiaDatabase::getInstance();
        $todayScans = $db->countUserScansToday((int)$userId);

        // Premium users get unlimited, regular users get 10/day
        $limit = !empty($_SESSION['is_premium']) ? PHP_INT_MAX : 10;

        if ($todayScans >= $limit) {
            http_response_code(429);
            echo json_encode([
                'error' => 'Daily scan limit reached. Free users can scan up to 10 times per day. Upgrade to premium for unlimited scans.',
                'limit_reached' => true,
                'scans_today' => $todayScans,
                'show_premium' => true
            ]);
            exit;
        }
    } catch (Throwable $e) {
        // Database error - fall back to session-based limiting
        error_log('Database error in rate limiting: ' . $e->getMessage());
    }
} else {
    // Guests: 1 scan per session (tracked by IP + session)
    if (!isset($_SESSION['guest_scan_used'])) {
        $_SESSION['guest_scan_used'] = false;
    }

    if ($_SESSION['guest_scan_used'] === true) {
        http_response_code(429);
        echo json_encode([
            'error' => 'Guests are limited to 1 scan. Please sign in with Google to get unlimited scans and save your history.',
            'require_login' => true,
            'login_url' => app_path('auth/google')
        ]);
        exit;
    }

    $_SESSION['guest_scan_used'] = true;
}


function json_error(int $code, string $message): void
{
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

function extract_text_from_file(string $filePath, string $ext): ?string
{
    $ext = strtolower($ext);

    if ($ext === 'pdf') {
        return extract_text_from_pdf($filePath);
    } elseif ($ext === 'docx') {
        return extract_text_from_docx($filePath);
    } elseif ($ext === 'doc') {
        return extract_text_from_doc($filePath);
    }

    return null;
}

function extract_text_from_pdf(string $filePath): ?string
{
    // Try to use pdftotext if available
    $pdftotext = shell_exec('which pdftotext 2>/dev/null');
    if ($pdftotext) {
        $output = shell_exec('pdftotext "' . escapeshellarg($filePath) . '" - 2>/dev/null');
        if ($output && strlen($output) > 50) {
            return clean_extracted_text($output);
        }
    }

    // Fallback: extract text from PDF using basic parsing
    $content = file_get_contents($filePath);
    if (!$content) return null;

    // Look for text streams in PDF
    preg_match_all('/stream\s*(.*?)\s*endstream/s', $content, $matches);
    $text = '';
    foreach ($matches[1] as $stream) {
        // Try to decode flate-compressed streams
        $decoded = @gzuncompress($stream);
        if ($decoded) {
            // Extract text from decoded content
            preg_match_all('/\[\s*\(([^)]+)\)\s*\]/', $decoded, $textMatches);
            foreach ($textMatches[1] as $t) {
                $text .= $t . ' ';
            }
        }
    }

    // Alternative: look for BT...ET blocks (Begin/End Text)
    preg_match_all('/BT\s*(.*?)\s*ET/s', $content, $btMatches);
    foreach ($btMatches[1] as $bt) {
        preg_match_all('/\(([^)]+)\)/', $bt, $parenMatches);
        foreach ($parenMatches[1] as $t) {
            $text .= $t . ' ';
        }
    }

    if (strlen($text) > 50) {
        return clean_extracted_text($text);
    }

    return null;
}

function extract_text_from_docx(string $filePath): ?string
{
    // DOCX is a ZIP file containing XML
    $zip = new ZipArchive();
    if ($zip->open($filePath) !== true) {
        return null;
    }

    // Read the main document content
    $xmlContent = $zip->getFromName('word/document.xml');
    $zip->close();

    if (!$xmlContent) {
        return null;
    }

    // Extract text from XML
    $xml = new SimpleXMLElement($xmlContent);
    $xml->registerXPathNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

    $text = '';
    $paragraphs = $xml->xpath('//w:p');
    foreach ($paragraphs as $p) {
        $runs = $p->xpath('.//w:t');
        foreach ($runs as $t) {
            $text .= (string)$t . ' ';
        }
        $text .= "\n";
    }

    if (strlen($text) > 50) {
        return clean_extracted_text($text);
    }

    return null;
}

function extract_text_from_doc(string $filePath): ?string
{
    // Try to use antiword or catdoc if available
    $antiword = shell_exec('which antiword 2>/dev/null');
    if ($antiword) {
        $output = shell_exec('antiword "' . escapeshellarg($filePath) . '" 2>/dev/null');
        if ($output && strlen($output) > 50) {
            return clean_extracted_text($output);
        }
    }

    $catdoc = shell_exec('which catdoc 2>/dev/null');
    if ($catdoc) {
        $output = shell_exec('catdoc "' . escapeshellarg($filePath) . '" 2>/dev/null');
        if ($output && strlen($output) > 50) {
            return clean_extracted_text($output);
        }
    }

    // Fallback: try to extract raw text from the binary
    $content = file_get_contents($filePath);
    if (!$content) return null;

    // Look for readable text in the binary
    preg_match_all('/[\x20-\x7E\x0A\x0D]{10,}/', $content, $matches);
    $text = implode(' ', $matches[0]);

    if (strlen($text) > 100) {
        return clean_extracted_text($text);
    }

    return null;
}

function clean_extracted_text(string $text): string
{
    // Clean up extracted text
    $text = preg_replace('/\s+/', ' ', $text); // Normalize whitespace
    $text = preg_replace('/[^\x20-\x7E\x0A\x0D\xA0-\xFF]/', '', $text); // Remove non-printable
    $text = trim($text);

    // Ensure valid UTF-8 encoding
    if (!mb_check_encoding($text, 'UTF-8')) {
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
    }

    return $text;
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


$payloadData = get_request_payload();

$text      = trim((string)$payloadData['text']);
$language  = trim((string)$payloadData['language']) ?: 'en';
$country   = trim((string)$payloadData['country']) ?: 'us';
$turnstile = trim((string)$payloadData['turnstile']);

verify_turnstile($turnstile, TURNSTILE_SECRET_KEY, $ip);

$payload = [
    'language' => $language,
    'country'  => $country,
];

$storedPrivatePath = null;

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
        $error = error_get_last();
        $errorMsg = $error ? $error['message'] : 'Unknown error';
        json_error(500, 'Failed to store uploaded file. Error: ' . $errorMsg . ' | Path: ' . $storedPrivatePath . ' | Writable: ' . (is_writable($storageDir) ? 'yes' : 'no'));
    }

    // Extract text from the uploaded file instead of sending URL
    // (Winston API cannot access localhost URLs)
    $extractedText = extract_text_from_file($storedPrivatePath, $ext);

    if ($extractedText === null || strlen($extractedText) < MIN_TEXT_LENGTH) {
        safe_unlink($storedPrivatePath);
        json_error(400, 'Could not extract readable text from the uploaded file. Please try uploading a different file or paste the text directly.');
    }

    if (strlen($extractedText) > MAX_TEXT_LENGTH) {
        $extractedText = substr($extractedText, 0, MAX_TEXT_LENGTH);
    }

    $payload['text'] = $extractedText;
    $payload['file_name'] = $origName;

    // Clean up the temporary file
    safe_unlink($storedPrivatePath);
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

// Ensure text is valid UTF-8 before JSON encoding
if (isset($payload['text']) && !mb_check_encoding($payload['text'], 'UTF-8')) {
    $payload['text'] = mb_convert_encoding($payload['text'], 'UTF-8', 'UTF-8');
}

$postData = json_encode($payload);

if ($postData === false) {
    json_error(500, 'Failed to encode request data. Please try with different text or file.');
}

function call_winston_api(string $postData, string $apiKey): ?array
{
    $ch = curl_init('https://api.gowinston.ai/v2/plagiarism');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postData,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $apiKey,
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

    if ($curlErr) {
        return ['error' => 'curl', 'message' => $curlErr];
    }

    $decoded = json_decode((string)$response, true);

    $isQuotaError = $httpCode === 429 ||
        (is_array($decoded) && isset($decoded['error']) &&
            stripos($decoded['error'], 'quota') !== false);

    if ($isQuotaError) {
        return ['error' => 'quota', 'httpCode' => $httpCode, 'response' => $response];
    }

    // Check for auth errors (403) or other non-2xx responses
    if ($httpCode < 200 || $httpCode >= 300) {
        $errorMsg = $decoded['error'] ?? $decoded['message'] ?? 'API returned error ' . $httpCode;
        return ['error' => 'api', 'httpCode' => $httpCode, 'response' => json_encode(['error' => $errorMsg])];
    }

    return ['success' => true, 'httpCode' => $httpCode, 'response' => $response];
}

function get_working_api_key(array $keys): ?string
{
    $exhausted = $_SESSION['winston_exhausted'] ?? [];
    $now = time();

    $exhausted = array_filter($exhausted, fn($ts) => ($now - $ts) < 3600);
    $_SESSION['winston_exhausted'] = $exhausted;

    foreach ($keys as $key) {
        $keyHash = substr(md5($key), 0, 8);
        if (!isset($exhausted[$keyHash])) {
            return $key;
        }
    }
    return null;
}

function mark_key_exhausted(string $key): void
{
    $keyHash = substr(md5($key), 0, 8);
    $_SESSION['winston_exhausted'][$keyHash] = time();
}

$apiKeys = is_array(WINSTON_API_KEY) ? WINSTON_API_KEY : [WINSTON_API_KEY];
$finalResponse = null;
$finalHttpCode = 500;

foreach ($apiKeys as $apiKey) {
    $result = call_winston_api($postData, $apiKey);

    if (isset($result['success'])) {
        $finalResponse = $result['response'];
        $finalHttpCode = $result['httpCode'];
        break;
    }

    if (isset($result['error']) && $result['error'] === 'quota') {
        mark_key_exhausted($apiKey);
        continue;
    }

    if (isset($result['error']) && $result['error'] === 'curl') {
        $finalResponse = json_encode(['error' => 'Failed to reach Winston AI: ' . $result['message']]);
        $finalHttpCode = 502;
        break;
    }

    if (isset($result['error']) && $result['error'] === 'api') {
        $finalResponse = $result['response'];
        $finalHttpCode = $result['httpCode'];
        break;
    }
}

// Note: File is already cleaned up after text extraction
// Only text content is sent to Winston API, not file URLs

if ($finalResponse === null) {
    json_error(429, 'All API keys exhausted. Please try again later.');
}

// Save scan to history
if ($finalHttpCode >= 200 && $finalHttpCode < 300) {
    try {
        $db = PlagiaDatabase::getInstance();
        $resultData = json_decode($finalResponse, true);
        $resultSummary = is_array($resultData['result'] ?? null) ? $resultData['result'] : $resultData;

        $scanData = [
            'text' => $payload['text'] ?? '',
            'file_name' => $payload['file_name'] ?? null,
            'score' => $resultSummary['score'] ?? 0,
            'sources' => count($resultData['sources'] ?? []),
            'result' => $resultData
        ];

        $db->saveScan(
            $isLoggedIn ? (int)$userId : null,
            session_id(),
            $scanData
        );
    } catch (Throwable $e) {
        error_log('Failed to save scan history: ' . $e->getMessage());
    }
}

http_response_code($finalHttpCode > 0 ? $finalHttpCode : 500);
echo $finalResponse ?: json_encode(['error' => 'Empty response from Winston AI']);
