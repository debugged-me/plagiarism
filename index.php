<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Check if this is an OAuth callback (has code parameter)
if (isset($_GET['code'])) {
    // OAuth callback - include the auth callback handler
    require_once __DIR__ . '/auth/callback.php';
    exit;
}

// Normal request - redirect to landing page
header('Location: landing.php');
exit;
