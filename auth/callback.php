<?php

declare(strict_types=1);

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../app/secure_config.php';
require_once __DIR__ . '/../app/database.php';
require_once __DIR__ . '/../app/session.php';
start_app_session();

/**
 * Handles Google OAuth callback
 */

// Check if this is a callback from Google (has code parameter)
if (!isset($_GET['code'])) {
    // Not a callback, redirect to home
    header('Location: ' . app_path('/'));
    exit;
}

// Verify state parameter
if (!isset($_GET['state']) || !isset($_SESSION['oauth_state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    error_log('OAuth state mismatch. GET: ' . ($_GET['state'] ?? 'NONE') . ' SESSION: ' . ($_SESSION['oauth_state'] ?? 'NONE'));
    die('Invalid state parameter. Possible CSRF attack.');
}

unset($_SESSION['oauth_state']);

$code = $_GET['code'];
$redirectUri = resolve_google_redirect_uri();
error_log('OAuth callback: code received, redirect_uri=' . $redirectUri);

// Exchange code for tokens
$tokenUrl = 'https://oauth2.googleapis.com/token';
// Use the exact same redirect URI that was used in the initial request.

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

if ($httpCode !== 200 || !$response) {
    error_log('OAuth token exchange failed: HTTP ' . $httpCode . ', response=' . $response);
    die('Failed to exchange authorization code for tokens.');
}
error_log('OAuth token exchange successful');

$tokenData = json_decode($response, true);

if (!isset($tokenData['id_token'])) {
    error_log('OAuth: No id_token in response: ' . json_encode($tokenData));
    die('ID token not received from Google.');
}

// Decode ID token (simplified - in production, verify signature)
$idToken = $tokenData['id_token'];
$tokenParts = explode('.', $idToken);

if (count($tokenParts) !== 3) {
    die('Invalid ID token format.');
}

$payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1])), true);

if (!$payload || !isset($payload['sub'])) {
    die('Failed to decode ID token payload.');
}

// Get or create user
error_log('OAuth: Looking up user with sub=' . $payload['sub']);
$db = PlagiaDatabase::getInstance();
$user = $db->findOrCreateUser([
    'id' => $payload['sub'],
    'email' => $payload['email'] ?? '',
    'name' => $payload['name'] ?? $payload['email'] ?? 'Unknown',
    'picture' => $payload['picture'] ?? null
]);

error_log('OAuth: User data from DB: ' . json_encode($user));

// Set session - use database id (integer), not google_id
$_SESSION['user_id'] = (int)$user['id'];  // Cast to int to ensure it's the DB ID
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_avatar'] = $user['avatar'] ?? null;
$_SESSION['is_logged_in'] = true;
session_write_close();

error_log('OAuth: Session set with user_id=' . $_SESSION['user_id']);

error_log('OAuth: Login complete, redirecting to ' . app_path('user'));
// Redirect to user dashboard.
header('Location: ' . app_path('user'));
exit;
