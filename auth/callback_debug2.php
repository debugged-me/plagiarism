<?php
// Debug version of callback to trace state mismatch
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h2>OAuth Callback Debug</h2>";
echo "<pre>";

require_once __DIR__ . '/../app/secure_config.php';
require_once __DIR__ . '/../app/database.php';
session_start();

echo "Step 1: Session started\n";
echo "Session ID: " . session_id() . "\n";
echo "\nStep 2: Check parameters\n";
echo "GET state: " . ($_GET['state'] ?? 'NOT SET') . "\n";
echo "Session oauth_state: " . ($_SESSION['oauth_state'] ?? 'NOT SET') . "\n";

echo "\nStep 3: Compare\n";
if (!isset($_GET['state'])) {
    echo "✗ GET state is missing\n";
} elseif (!isset($_SESSION['oauth_state'])) {
    echo "✗ Session oauth_state is missing\n";
    echo "\nAll session data:\n";
    print_r($_SESSION);
} elseif ($_GET['state'] !== $_SESSION['oauth_state']) {
    echo "✗ States don't match!\n";
    echo "GET: " . $_GET['state'] . "\n";
    echo "Session: " . $_SESSION['oauth_state'] . "\n";
} else {
    echo "✓ States match!\n";
}

// Continue with the flow
echo "\nStep 4: Process the code...\n";

if (!isset($_GET['code'])) {
    die("No code parameter\n");
}

$code = $_GET['code'];
echo "Code received: " . substr($code, 0, 30) . "...\n";

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

$ch = curl_init($tokenUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($postData),
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";

if ($httpCode !== 200 || !$response) {
    die("Token exchange failed\n");
}

$tokens = json_decode($response, true);
if (isset($tokens['error'])) {
    die("Google error: " . $tokens['error'] . "\n");
}

echo "✓ Got access token\n";

// Get user info
$userCh = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
curl_setopt($userCh, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tokens['access_token']]);
curl_setopt($userCh, CURLOPT_RETURNTRANSFER, true);
$userResponse = curl_exec($userCh);
curl_close($userCh);

$userInfo = json_decode($userResponse, true);
echo "User: " . ($userInfo['email'] ?? 'no email') . "\n";

// Save to database
$db = PlagiaDatabase::getInstance();
$user = $db->findOrCreateUser([
    'id' => $userInfo['id'],
    'email' => $userInfo['email'] ?? '',
    'name' => $userInfo['name'] ?? ($userInfo['email'] ?? 'Unknown'),
    'picture' => $userInfo['picture'] ?? null
]);

echo "✓ User saved, ID: " . $user['id'] . "\n";

// Set session
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_avatar'] = $user['avatar'];
$_SESSION['is_logged_in'] = true;

echo "✓ Session set\n";
echo "\n<a href='/user_simple.php'>Go to User Dashboard</a>\n";
echo "</pre>";
