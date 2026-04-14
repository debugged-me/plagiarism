<?php
declare(strict_types=1);

/**
 * Production Installation Script for PlagiaScope
 * Upload this file to your server and run it once to set up the database and directories.
 * DELETE this file after successful installation for security.
 */

// Security: only allow access with a secret key
$installKey = $_GET['key'] ?? '';
if ($installKey !== 'setup2024') {
    http_response_code(403);
    die('Access denied. Use: install.php?key=setup2024');
}

header('Content-Type: text/plain');
echo "=== PlagiaScope Production Installation ===\n\n";

// 1. Create data directory
echo "1. Creating data directory...\n";
$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    if (mkdir($dataDir, 0755, true)) {
        echo "   ✓ Created: data/\n";
    } else {
        echo "   ✗ Failed to create data/\n";
    }
} else {
    echo "   ✓ Already exists: data/\n";
}

// Ensure data directory is writable
if (!is_writable($dataDir)) {
    chmod($dataDir, 0755);
}
echo "   Writable: " . (is_writable($dataDir) ? 'Yes' : 'No - Fix permissions!') . "\n\n";

// 2. Create SQLite database
echo "2. Creating SQLite database...\n";
$dbPath = $dataDir . '/plagiascope.db';

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            google_id VARCHAR(255) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            name VARCHAR(255) NOT NULL,
            avatar_url TEXT,
            is_premium INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "   ✓ Users table created\n";
    
    // Create scans table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS scans (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            session_id VARCHAR(64),
            text_preview TEXT,
            plagiarism_score INTEGER,
            sources_found INTEGER,
            scan_result TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )
    ");
    echo "   ✓ Scans table created\n";
    
    // Create indexes
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_scans_user_id ON scans(user_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_scans_session_id ON scans(session_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_scans_created_at ON scans(created_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_users_google_id ON users(google_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)");
    echo "   ✓ Indexes created\n";
    
    // Set permissions
    chmod($dbPath, 0644);
    echo "   ✓ Database permissions set\n";
    
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// 3. Create storage directories
echo "\n3. Creating storage directories...\n";
$dirs = [
    'storage/plagiascope_tmp',
    'uploads/plagiascope_public'
];

foreach ($dirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (!is_dir($path)) {
        if (mkdir($path, 0755, true)) {
            echo "   ✓ Created: $dir/\n";
        } else {
            echo "   ✗ Failed: $dir/\n";
        }
    } else {
        echo "   ✓ Exists: $dir/\n";
    }
}

// 4. Check config
echo "\n4. Checking configuration...\n";
$configFile = __DIR__ . '/app/secure_config.php';
if (file_exists($configFile)) {
    require_once $configFile;
    
    $checks = [
        'GOOGLE_CLIENT_ID' => 'Google OAuth Client ID',
        'GOOGLE_CLIENT_SECRET' => 'Google OAuth Client Secret',
        'TURNSTILE_SECRET_KEY' => 'Cloudflare Turnstile Secret'
    ];
    
    foreach ($checks as $const => $desc) {
        if (defined($const) && !empty(constant($const))) {
            echo "   ✓ $desc is set\n";
        } else {
            echo "   ✗ $desc is MISSING - Add to app/secure_config.php\n";
        }
    }
    
    if (defined('WINSTON_API_KEY')) {
        echo "   ✓ WINSTON_API_KEY is set\n";
    } else {
        echo "   ✗ WINSTON_API_KEY is MISSING\n";
    }
} else {
    echo "   ✗ secure_config.php not found!\n";
}

// 5. Test database
echo "\n5. Testing database connection...\n";
try {
    require_once __DIR__ . '/app/database.php';
    $db = PlagiaDatabase::getInstance();
    
    // Test insert
    $testUser = $db->findOrCreateUser([
        'id' => 'test_install_' . time(),
        'email' => 'test@example.com',
        'name' => 'Install Test',
        'picture' => null
    ]);
    
    echo "   ✓ Database connection OK\n";
    echo "   ✓ Test user created (ID: {$testUser['id']})\n";
    
} catch (Exception $e) {
    echo "   ✗ Database error: " . $e->getMessage() . "\n";
}

echo "\n=== Installation Complete ===\n";
echo "Database path: $dbPath\n";
echo "\n⚠️ IMPORTANT: Delete install.php after installation for security!\n";
echo "\nNext steps:\n";
echo "1. Visit https://plagiascope.softtechco.biz/setup.php to verify\n";
echo "2. Test OAuth login at https://plagiascope.softtechco.biz/auth/google\n";
