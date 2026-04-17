<?php

declare(strict_types=1);

require_once __DIR__ . '/url.php';

function start_app_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name('PLAGIASCOPESESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => app_path('/'),
        'domain' => '',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}
