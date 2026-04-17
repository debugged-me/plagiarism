<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/session.php';
start_app_session();

/**
 * Logout user
 */

// Clear user session data
unset($_SESSION['user_id']);
unset($_SESSION['user_email']);
unset($_SESSION['user_name']);
unset($_SESSION['user_avatar']);
unset($_SESSION['is_logged_in']);
session_write_close();

// Redirect to home
header('Location: ' . app_path('/'));
exit;
