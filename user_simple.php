<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h1>User Dashboard (Debug Mode)</h1>";
echo "<pre>";
echo "Session Data:\n";
print_r($_SESSION);
echo "\n\n";

if (empty($_SESSION['is_logged_in'])) {
    echo "⚠️ Not logged in. <a href='/auth/google'>Sign in</a>\n";
    echo "</pre>";
    exit;
}

echo "✓ Logged in as: " . ($_SESSION['user_name'] ?? 'Unknown') . "\n";
echo "✓ User ID: " . ($_SESSION['user_id'] ?? 'None') . "\n";
echo "✓ Email: " . ($_SESSION['user_email'] ?? 'None') . "\n";

try {
    require_once __DIR__ . '/app/database.php';
    $db = PlagiaDatabase::getInstance();
    echo "\n✓ Database connected\n";
    
    $scans = $db->getUserScans((int)$_SESSION['user_id'], 10);
    echo "✓ Found " . count($scans) . " scan(s)\n";
} catch (Exception $e) {
    echo "\n✗ Database error: " . $e->getMessage() . "\n";
}

echo "\n<a href='/chat.php'>Go to Chat</a> | <a href='/auth/logout.php'>Logout</a>";
echo "</pre>";
