<?php
// Temporary debug version of callback - rename to callback.php after fixing
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../app/secure_config.php';
require_once __DIR__ . '/../app/database.php';
session_start();

$log = [];
$log[] = "=== OAuth Callback Debug ===";
$log[] = "Time: " . date('Y-m-d H:i:s');
$log[] = "GET: " . json_encode($_GET);
$log[] = "Session: " . json_encode($_SESSION);

// Check if this is a callback from Google
if (!isset($_GET['code'])) {
    $log[] = "ERROR: No code parameter";
    file_put_contents(__DIR__ . '/../oauth_error.log', implode("\n", $log) . "\n\n", FILE_APPEND);
    header('Location: /');
    exit;
}

// Verify state parameter
if (!isset($_GET['state']) || !isset($_SESSION['oauth_state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    $log[] = "ERROR: Invalid state";
    $log[] = "GET state: " . ($_GET['state'] ?? 'none');
    $log[] = "Session state: " . ($_SESSION['oauth_state'] ?? 'none');
    file_put_contents(__DIR__ . '/../oauth_error.log', implode("\n", $log) . "\n\n", FILE_APPEND);
    die('Invalid state parameter. Possible CSRF attack.');
}

unset($_SESSION['oauth_state']);
$code = $_GET['code'];
$log[] = "Code received: " . substr($code, 0, 20) . "...";

// Exchange code for tokens
$tokenUrl = 'https://oauth2.googleapis.com/token';
$redirectUri = defined('GOOGLE_REDIRECT_URI') ? constant('GOOGLE_REDIRECT_URI') : 'https://' . $_SERVER['HTTP_HOST'] . '/';

$postData = [
    'code' => $code,
    'client_id' => constant('GOOGLE_CLIENT_ID'),
    'client_secret' => constant('GOOGLE_CLIENT_SECRET'),
    'redirect_uri' => $redirectUri,
    'grant_type' => 'authorization_code'
];

$log[] = "Token URL: $tokenUrl";
$log[] = "Redirect URI: $redirectUri";

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

$log[] = "HTTP Code: $httpCode";
if ($curlErr) {
    $log[] = "cURL Error: $curlErr";
}

if ($httpCode !== 200 || !$response) {
    $log[] = "ERROR: Token exchange failed";
    $log[] = "Response: " . substr($response, 0, 500);
    file_put_contents(__DIR__ . '/../oauth_error.log', implode("\n", $log) . "\n\n", FILE_APPEND);
    die('Failed to exchange authorization code for tokens.');
}

$tokens = json_decode($response, true);
if (isset($tokens['error'])) {
    $log[] = "ERROR: Google returned error: " . $tokens['error'];
    file_put_contents(__DIR__ . '/../oauth_error.log', implode("\n", $log) . "\n\n", FILE_APPEND);
    die('OAuth error: ' . $tokens['error']);
}

$log[] = "✓ Got access token";

// Get user info from Google
$userCh = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
curl_setopt($userCh, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tokens['access_token']]);
curl_setopt($userCh, CURLOPT_RETURNTRANSFER, true);
$userResponse = curl_exec($userCh);
curl_close($userCh);

$userInfo = json_decode($userResponse, true);
if (!isset($userInfo['id'])) {
    $log[] = "ERROR: Failed to get user info";
    $log[] = "Response: " . substr($userResponse, 0, 500);
    file_put_contents(__DIR__ . '/../oauth_error.log', implode("\n", $log) . "\n\n", FILE_APPEND);
    die('Failed to get user info from Google.');
}

$log[] = "✓ User info: " . ($userInfo['email'] ?? 'no email');

// Save to database
try {
    $db = PlagiaDatabase::getInstance();
    $log[] = "✓ Database connected";
    
    $user = $db->findOrCreateUser([
        'id' => $userInfo['id'],
        'email' => $userInfo['email'] ?? '',
        'name' => $userInfo['name'] ?? ($userInfo['email'] ?? 'Unknown'),
        'picture' => $userInfo['picture'] ?? null
    ]);
    
    $log[] = "✓ User saved, ID: " . $user['id'];
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_avatar'] = $user['avatar'];
    $_SESSION['is_logged_in'] = true;
    
    $log[] = "✓ Session set";
    $log[] = "=== SUCCESS - Redirecting to user.php ===";
    file_put_contents(__DIR__ . '/../oauth_error.log', implode("\n", $log) . "\n\n", FILE_APPEND);
    
    // Redirect to user dashboard
    header('Location: /user.php');
    exit;
    
} catch (Exception $e) {
    $log[] = "ERROR Database: " . $e->getMessage();
    $log[] = "File: " . $e->getFile() . ":" . $e->getLine();
    file_put_contents(__DIR__ . '/../oauth_error.log', implode("\n", $log) . "\n\n", FILE_APPEND);
    die('Database error: ' . $e->getMessage());
}
