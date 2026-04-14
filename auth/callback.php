<?php

declare(strict_types=1);

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../app/secure_config.php';
require_once __DIR__ . '/../app/database.php';
session_start();

/**
 * Handles Google OAuth callback
 */

// Check if this is a callback from Google (has code parameter)
if (!isset($_GET['code'])) {
    // Not a callback, redirect to home
    header('Location: /');
    exit;
}

// Verify state parameter
if (!isset($_GET['state']) || !isset($_SESSION['oauth_state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    die('Invalid state parameter. Possible CSRF attack.');
}

unset($_SESSION['oauth_state']);

$code = $_GET['code'];

// Exchange code for tokens
$tokenUrl = 'https://oauth2.googleapis.com/token';
// Use the exact same redirect URI that was used in the initial request
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

if ($httpCode !== 200 || !$response) {
    die('Failed to exchange authorization code for tokens.');
}

$tokenData = json_decode($response, true);

if (!isset($tokenData['id_token'])) {
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
$db = PlagiaDatabase::getInstance();
$user = $db->findOrCreateUser([
    'id' => $payload['sub'],
    'email' => $payload['email'] ?? '',
    'name' => $payload['name'] ?? $payload['email'] ?? 'Unknown',
    'picture' => $payload['picture'] ?? null
]);

// Set session
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_avatar'] = $user['avatar'];
$_SESSION['is_logged_in'] = true;

// Redirect to user dashboard
header('Location: /user.php');
exit;
