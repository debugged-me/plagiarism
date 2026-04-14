<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/database.php';
session_start();

header('Content-Type: application/json');

// Check if user is logged in
if (empty($_SESSION['is_logged_in']) || empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated', 'require_login' => true]);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 100) : 50;

try {
    $db = PlagiaDatabase::getInstance();
    $scans = $db->getUserScans($userId, $limit);
    
    // Decode result_data JSON for each scan
    foreach ($scans as &$scan) {
        if (!empty($scan['result_data'])) {
            $scan['result'] = json_decode($scan['result_data'], true);
        }
        unset($scan['result_data']); // Remove raw JSON from response
    }
    
    echo json_encode([
        'success' => true,
        'scans' => $scans,
        'user' => [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'avatar' => $_SESSION['user_avatar'] ?? null
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch history: ' . $e->getMessage()]);
}
