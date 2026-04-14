<?php
// Ultra-simple OAuth test - shows everything
echo "<pre>";
echo "=== OAuth Debug ===\n\n";
echo "1. Check GET params:\n";
print_r($_GET);

echo "\n2. Check if code exists:\n";
echo isset($_GET['code']) ? "YES - Code: " . substr($_GET['code'], 0, 30) . "...\n" : "NO CODE\n";

if (isset($_GET['code'])) {
    echo "\n3. About to load callback.php...\n";
    
    // Capture any output/errors from callback
    ob_start();
    try {
        require_once __DIR__ . '/auth/callback.php';
        echo "callback.php loaded successfully\n";
    } catch (Throwable $e) {
        echo "ERROR in callback: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    $output = ob_get_clean();
    
    echo "\n4. Output from callback:\n";
    echo $output ?: "(no output)\n";
    
    echo "\n5. Session after callback:\n";
    session_start();
    print_r($_SESSION);
}

echo "\n6. Check error log:\n";
if (file_exists(__DIR__ . '/callback_test.log')) {
    echo file_get_contents(__DIR__ . '/callback_test.log');
} else {
    echo "(no log file yet)\n";
}
