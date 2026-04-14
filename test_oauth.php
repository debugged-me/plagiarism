<?php
// Test the OAuth flow step by step
echo "<h2>OAuth Flow Test</h2>";
echo "<pre>";

// Step 1: Check if we can detect OAuth params
if (isset($_GET['code'])) {
    echo "✓ Step 1: Code detected in URL\n";
    echo "  Code: " . substr($_GET['code'], 0, 20) . "...\n";
} else {
    echo "✗ Step 1: No code in URL\n";
}

// Step 2: Check session
session_start();
echo "\n✓ Step 2: Session started\n";
echo "  Session ID: " . session_id() . "\n";
echo "  Session data: " . json_encode($_SESSION) . "\n";

// Step 3: Check config
echo "\n✓ Step 3: Loading config...\n";
try {
    require_once __DIR__ . '/app/secure_config.php';
    echo "  GOOGLE_CLIENT_ID: " . (defined('GOOGLE_CLIENT_ID') ? 'OK' : 'MISSING') . "\n";
    echo "  GOOGLE_CLIENT_SECRET: " . (defined('GOOGLE_CLIENT_SECRET') ? 'OK' : 'MISSING') . "\n";
    echo "  GOOGLE_REDIRECT_URI: " . (defined('GOOGLE_REDIRECT_URI') ? constant('GOOGLE_REDIRECT_URI') : 'MISSING') . "\n";
} catch (Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}

// Step 4: Check database
echo "\n✓ Step 4: Loading database...\n";
try {
    require_once __DIR__ . '/app/database.php';
    $db = PlagiaDatabase::getInstance();
    echo "  Database: OK (connected)\n";
    
    // Try a simple query
    $testUser = $db->getUserById(1);
    echo "  Test query: " . ($testUser ? 'Found user' : 'No user yet (expected)') . "\n";
} catch (Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}

// Step 5: Check user.php exists
echo "\n✓ Step 5: Check user.php...\n";
if (file_exists(__DIR__ . '/user.php')) {
    echo "  user.php: EXISTS\n";
} else {
    echo "  user.php: MISSING - Create this file!\n";
}

// Step 6: Test the full flow if we have a code
if (isset($_GET['code'])) {
    echo "\n=== SIMULATING FULL FLOW ===\n";
    
    // Verify state
    if (isset($_GET['state']) && isset($_SESSION['oauth_state']) && $_GET['state'] === $_SESSION['oauth_state']) {
        echo "✓ State verified\n";
    } else {
        echo "✗ State mismatch or missing\n";
    }
    
    echo "\nIf you see this, the flow should work.\n";
    echo "Try actually logging in now.\n";
}

echo "</pre>";

// Log file check
if (file_exists(__DIR__ . '/oauth_error.log')) {
    echo "<h3>Error Log</h3>";
    echo "<pre>" . file_get_contents(__DIR__ . '/oauth_error.log') . "</pre>";
}
