<?php
declare(strict_types=1);

/**
 * Setup script to verify installation and create database
 */

echo "=== PlagiaScope Setup ===\n\n";

// Check PHP version
echo "✓ PHP Version: " . PHP_VERSION . "\n";

// Check required extensions
$required = ['pdo', 'pdo_sqlite', 'curl', 'json', 'session'];
foreach ($required as $ext) {
    if (extension_loaded($ext)) {
        echo "✓ Extension '$ext' loaded\n";
    } else {
        echo "✗ Extension '$ext' NOT loaded - REQUIRED\n";
    }
}

// Check directories
echo "\n=== Directories ===\n";
$dirs = [
    'app' => __DIR__ . '/app',
    'auth' => __DIR__ . '/auth',
    'api' => __DIR__ . '/api',
    'data' => __DIR__ . '/data',
    'storage' => __DIR__ . '/storage/plagiascope_tmp',
    'uploads' => __DIR__ . '/uploads/plagiascope_public'
];

foreach ($dirs as $name => $path) {
    if (!is_dir($path)) {
        echo "Creating $name directory...\n";
        mkdir($path, 0777, true);
    }
    $writable = is_writable($path) ? 'writable' : 'NOT WRITABLE';
    echo "✓ $name: $path ($writable)\n";
}

// Check config
echo "\n=== Configuration ===\n";
$configFile = __DIR__ . '/app/secure_config.php';
if (file_exists($configFile)) {
    require_once $configFile;
    require_once __DIR__ . '/app/url.php';
    
    if (defined('GOOGLE_CLIENT_ID') && !empty(GOOGLE_CLIENT_ID)) {
        echo "✓ GOOGLE_CLIENT_ID set\n";
    } else {
        echo "✗ GOOGLE_CLIENT_ID NOT set\n";
    }
    
    if (defined('GOOGLE_CLIENT_SECRET') && !empty(GOOGLE_CLIENT_SECRET)) {
        echo "✓ GOOGLE_CLIENT_SECRET set\n";
    } else {
        echo "✗ GOOGLE_CLIENT_SECRET NOT set\n";
    }
    
    if (defined('WINSTON_API_KEY')) {
        echo "✓ WINSTON_API_KEY set\n";
    } else {
        echo "✗ WINSTON_API_KEY NOT set\n";
    }

    echo "✓ Google redirect URI: " . resolve_google_redirect_uri() . "\n";
    echo "  Register this exact URI in Google Cloud Console > OAuth 2.0 Client IDs > Authorized redirect URIs\n";
} else {
    echo "✗ secure_config.php NOT FOUND\n";
}

// Test database
echo "\n=== Database Test ===\n";
try {
    require_once __DIR__ . '/app/database.php';
    $db = PlagiaDatabase::getInstance();
    
    // Test user creation
    $testUser = $db->findOrCreateUser([
        'id' => 'test_google_id',
        'email' => 'test@example.com',
        'name' => 'Test User',
        'picture' => null
    ]);
    echo "✓ Database connected\n";
    echo "✓ Test user created (ID: {$testUser['id']})\n";
    
    // Test scan save
    $scanId = $db->saveScan((int)$testUser['id'], session_id(), [
        'text' => 'Test scan',
        'score' => 25,
        'sources' => 2,
        'result' => ['test' => true]
    ]);
    echo "✓ Test scan saved (ID: $scanId)\n";
    
    // Get scans
    $scans = $db->getUserScans((int)$testUser['id']);
    echo "✓ Retrieved " . count($scans) . " scan(s) from history\n";
    
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}

// Check .htaccess
echo "\n=== URL Rewrite ===\n";
if (file_exists(__DIR__ . '/.htaccess')) {
    echo "✓ .htaccess exists\n";
    $content = file_get_contents(__DIR__ . '/.htaccess');
    if (strpos($content, 'auth/') !== false) {
        echo "✓ Auth rewrite rules present\n";
    } else {
        echo "✗ Auth rewrite rules MISSING\n";
    }
} else {
    echo "✗ .htaccess NOT FOUND\n";
}

echo "\n=== Setup Complete ===\n";
echo "Visit https://plagiascope.softtechco.biz/setup.php to run this check again.\n";
echo "Visit https://plagiascope.softtechco.biz/auth/google to test OAuth.\n";
