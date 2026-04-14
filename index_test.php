<?php
// Test version of index.php with full debug output
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h1>OAuth Flow Trace</h1>";
echo "<pre>";
echo "Step 1: Script started\n";
echo "URL: " . $_SERVER['REQUEST_URI'] . "\n";
echo "Query String: " . ($_SERVER['QUERY_STRING'] ?? 'none') . "\n\n";

echo "Step 2: Check GET parameters\n";
echo "GET: " . print_r($_GET, true) . "\n";

echo "Step 3: Check for 'code' parameter\n";
if (isset($_GET['code'])) {
    echo "✓ Code IS set: " . substr($_GET['code'], 0, 30) . "...\n\n";
    
    echo "Step 4: About to include callback.php\n";
    $callbackFile = __DIR__ . '/auth/callback.php';
    echo "Callback file: $callbackFile\n";
    echo "File exists: " . (file_exists($callbackFile) ? 'YES' : 'NO') . "\n";
    echo "File readable: " . (is_readable($callbackFile) ? 'YES' : 'NO') . "\n\n";
    
    if (file_exists($callbackFile) && is_readable($callbackFile)) {
        echo "Step 5: Including callback.php...\n";
        include $callbackFile;
        echo "\n✓ Callback completed\n";
    } else {
        echo "✗ Cannot read callback.php\n";
    }
} else {
    echo "✗ Code is NOT set\n";
    echo "Redirecting to landing.php...\n";
    header('Location: landing.php');
}

echo "</pre>";
