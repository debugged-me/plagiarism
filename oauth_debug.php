<?php
// OAuth Debug Script - Run this to diagnose OAuth issues
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h1>OAuth Debug</h1>";
echo "<pre>";

// Check if we have OAuth params
echo "GET params:\n";
print_r($_GET);

echo "\n\nSession:\n";
session_start();
print_r($_SESSION);

echo "\n\nConfig check:\n";
require_once __DIR__ . '/app/secure_config.php';
echo "GOOGLE_CLIENT_ID: " . (defined('GOOGLE_CLIENT_ID') ? substr(GOOGLE_CLIENT_ID, 0, 20) . '...' : 'NOT DEFINED') . "\n";
echo "GOOGLE_CLIENT_SECRET: " . (defined('GOOGLE_CLIENT_SECRET') ? 'DEFINED' : 'NOT DEFINED') . "\n";
echo "GOOGLE_REDIRECT_URI: " . (defined('GOOGLE_REDIRECT_URI') ? GOOGLE_REDIRECT_URI : 'NOT DEFINED') . "\n";

// If we have a code, try to process it
if (isset($_GET['code'])) {
    echo "\n\nProcessing OAuth code...\n";
    
    $code = $_GET['code'];
    $tokenUrl = 'https://oauth2.googleapis.com/token';
    $redirectUri = defined('GOOGLE_REDIRECT_URI') ? constant('GOOGLE_REDIRECT_URI') : 'https://' . $_SERVER['HTTP_HOST'] . '/';
    
    $postData = [
        'code' => $code,
        'client_id' => constant('GOOGLE_CLIENT_ID'),
        'client_secret' => constant('GOOGLE_CLIENT_SECRET'),
        'redirect_uri' => $redirectUri,
        'grant_type' => 'authorization_code'
    ];
    
    echo "\nToken request to: $tokenUrl\n";
    echo "Redirect URI: $redirectUri\n";
    
    $ch = curl_init($tokenUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);
    
    echo "\nHTTP Code: $httpCode\n";
    if ($curlErr) {
        echo "cURL Error: $curlErr\n";
    }
    
    $tokens = json_decode($response, true);
    if (isset($tokens['error'])) {
        echo "\nGoogle Error: " . $tokens['error'] . "\n";
        echo "Description: " . ($tokens['error_description'] ?? 'N/A') . "\n";
    } else if (isset($tokens['access_token'])) {
        echo "\n✓ Got access token!\n";
        
        // Get user info
        $userCh = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
        curl_setopt($userCh, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tokens['access_token']]);
        curl_setopt($userCh, CURLOPT_RETURNTRANSFER, true);
        $userResponse = curl_exec($userCh);
        curl_close($userCh);
        
        $userInfo = json_decode($userResponse, true);
        echo "\nUser Info:\n";
        print_r($userInfo);
        
        // Try to save to database
        try {
            require_once __DIR__ . '/app/database.php';
            $db = PlagiaDatabase::getInstance();
            echo "\n✓ Database connected (type: " . (defined('DB_HOST') ? 'MySQL' : 'SQLite') . ")\n";
            
            $user = $db->findOrCreateUser([
                'id' => $userInfo['id'],
                'email' => $userInfo['email'],
                'name' => $userInfo['name'],
                'picture' => $userInfo['picture'] ?? null
            ]);
            
            echo "\n✓ User saved to database:\n";
            print_r($user);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_avatar'] = $user['avatar'];
            $_SESSION['is_logged_in'] = true;
            
            echo "\n✓ Session set!\n";
            echo "\n<a href='/user.php'>Go to User Dashboard</a>\n";
            
        } catch (Exception $e) {
            echo "\n✗ Database Error: " . $e->getMessage() . "\n";
            echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        }
    } else {
        echo "\nRaw response:\n";
        echo $response;
    }
}

echo "</pre>";
