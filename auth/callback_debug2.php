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
// ... rest of callback logic
