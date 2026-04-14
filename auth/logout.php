<?php
declare(strict_types=1);

session_start();

/**
 * Logout user
 */

// Clear user session data
unset($_SESSION['user_id']);
unset($_SESSION['user_email']);
unset($_SESSION['user_name']);
unset($_SESSION['user_avatar']);
unset($_SESSION['is_logged_in']);

// Redirect to home
header('Location: /');
exit;
