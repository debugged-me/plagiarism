<?php
// TEST version of callback with full logging
error_reporting(E_ALL);
ini_set('display_errors', '1');

$log = [];
function log_msg($msg) {
    global $log;
    $log[] = date('H:i:s') . ' - ' . $msg;
}

log_msg("Callback started");
log_msg("URL: " . $_SERVER['REQUEST_URI']);

// Capture all errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    log_msg("ERROR [$errno]: $errstr in $errfile:$errline");
    return true;
});

set_exception_handler(function($e) {
    log_msg("EXCEPTION: " . $e->getMessage());
    log_msg("Stack: " . $e->getTraceAsString());
});

try {
    log_msg("Loading config...");
    require_once __DIR__ . '/../app/secure_config.php';
    log_msg("Config loaded OK");
    
    log_msg("Loading database...");
    require_once __DIR__ . '/../app/database.php';
    log_msg("Database loaded OK");
    
    log_msg("Starting session...");
    session_start();
    log_msg("Session ID: " . session_id());
    log_msg("Session data: " . json_encode($_SESSION));
} catch (Throwable $e) {
    log_msg("FATAL in includes: " . $e->getMessage());
    save_log();
    die("Failed to initialize: " . $e->getMessage());
}

// Check code
if (!isset($_GET['code'])) {
    log_msg("No code parameter, redirecting to /");
    save_log();
    header('Location: /');
    exit;
}
log_msg("Code received: " . substr($_GET['code'], 0, 20) . "...");

// Check state
if (!isset($_GET['state']) || !isset($_SESSION['oauth_state'])) {
    log_msg("Missing state - GET: " . json_encode($_GET) . " SESSION: " . json_encode($_SESSION));
    save_log();
    die('Missing state');
}

if ($_GET['state'] !== $_SESSION['oauth_state']) {
    log_msg("State mismatch! GET=" . $_GET['state'] . " SESSION=" . $_SESSION['oauth_state']);
    save_log();
    die('State mismatch');
}

log_msg("State verified");
unset($_SESSION['oauth_state']);

// Exchange tokens
try {
    $tokenUrl = 'https://oauth2.googleapis.com/token';
    $redirectUri = defined('GOOGLE_REDIRECT_URI') ? constant('GOOGLE_REDIRECT_URI') : 'https://' . $_SERVER['HTTP_HOST'] . '/';
    
    $postData = [
        'code' => $_GET['code'],
        'client_id' => constant('GOOGLE_CLIENT_ID'),
        'client_secret' => constant('GOOGLE_CLIENT_SECRET'),
        'redirect_uri' => $redirectUri,
        'grant_type' => 'authorization_code'
    ];
    
    log_msg("Requesting token from Google...");
    $ch = curl_init($tokenUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);
    
    log_msg("HTTP Code: $httpCode");
    if ($curlErr) {
        log_msg("cURL Error: $curlErr");
    }
    
    if ($httpCode !== 200 || !$response) {
        log_msg("Token exchange failed. Response: " . substr($response, 0, 500));
        save_log();
        die('Token exchange failed');
    }
    
    $tokens = json_decode($response, true);
    if (isset($tokens['error'])) {
        log_msg("Google error: " . $tokens['error']);
        save_log();
        die('Google error: ' . $tokens['error']);
    }
    
    log_msg("✓ Got access token");
    
    // Get user info
    $userCh = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
    curl_setopt($userCh, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tokens['access_token']]);
    curl_setopt($userCh, CURLOPT_RETURNTRANSFER, true);
    $userResponse = curl_exec($userCh);
    curl_close($userCh);
    
    $userInfo = json_decode($userResponse, true);
    if (!isset($userInfo['id'])) {
        log_msg("Failed to get user info: " . $userResponse);
        save_log();
        die('Failed to get user info');
    }
    
    log_msg("✓ User: " . ($userInfo['email'] ?? 'no email'));
    
    // Database save
    log_msg("Connecting to database...");
    $db = PlagiaDatabase::getInstance();
    log_msg("✓ Database connected");
    
    $user = $db->findOrCreateUser([
        'id' => $userInfo['id'],
        'email' => $userInfo['email'] ?? '',
        'name' => $userInfo['name'] ?? ($userInfo['email'] ?? 'Unknown'),
        'picture' => $userInfo['picture'] ?? null
    ]);
    
    log_msg("✓ User saved, ID: " . $user['id']);
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_avatar'] = $user['avatar'];
    $_SESSION['is_logged_in'] = true;
    
    log_msg("✓ Session set");
    log_msg("Final session: " . json_encode($_SESSION));
    save_log();
    
    header('Location: /user_simple.php');
    exit;
    
} catch (Throwable $e) {
    log_msg("EXCEPTION: " . $e->getMessage());
    log_msg("File: " . $e->getFile() . ":" . $e->getLine());
    save_log();
    die("Error: " . $e->getMessage());
}

function save_log() {
    global $log;
    file_put_contents(__DIR__ . '/../callback_test.log', implode("\n", $log) . "\n\n===\n\n", FILE_APPEND);
}
