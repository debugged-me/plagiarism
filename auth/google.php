<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/secure_config.php';
session_start();

/**
 * Initiates Google OAuth flow
 */

if (!defined('GOOGLE_CLIENT_ID') || empty(constant('GOOGLE_CLIENT_ID'))) {
    die('Google OAuth not configured. Please set GOOGLE_CLIENT_ID in secure_config.php');
}

// Generate state parameter for security
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

// Build OAuth URL - must match exactly what's in Google Console
$redirectUri = defined('GOOGLE_REDIRECT_URI') ? constant('GOOGLE_REDIRECT_URI') : 'https://' . $_SERVER['HTTP_HOST'] . '/';

$params = [
    'client_id' => constant('GOOGLE_CLIENT_ID'),
    'redirect_uri' => $redirectUri,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $state,
    'access_type' => 'online',
    'prompt' => 'select_account'
];

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);

header('Location: ' . $authUrl);
exit;
