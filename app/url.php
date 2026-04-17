<?php

declare(strict_types=1);

function app_base_path(): string
{
    static $basePath = null;

    if ($basePath !== null) {
        return $basePath;
    }

    $projectRoot = str_replace('\\', '/', realpath(dirname(__DIR__)) ?: dirname(__DIR__));
    $documentRootRaw = $_SERVER['DOCUMENT_ROOT'] ?? '';
    $documentRoot = $documentRootRaw !== ''
        ? str_replace('\\', '/', realpath($documentRootRaw) ?: $documentRootRaw)
        : '';

    if ($documentRoot !== '' && str_starts_with($projectRoot, $documentRoot)) {
        $relative = trim(substr($projectRoot, strlen($documentRoot)), '/');
        $basePath = $relative === '' ? '' : '/' . $relative;
        return $basePath;
    }

    $scriptFilenameRaw = $_SERVER['SCRIPT_FILENAME'] ?? '';
    $scriptFilename = $scriptFilenameRaw !== ''
        ? str_replace('\\', '/', realpath($scriptFilenameRaw) ?: $scriptFilenameRaw)
        : '';
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');

    if ($scriptFilename !== '' && $scriptName !== '' && str_starts_with($scriptFilename, $projectRoot)) {
        $relativeScript = str_replace('\\', '/', substr($scriptFilename, strlen($projectRoot)));
        if ($relativeScript !== '' && str_ends_with($scriptName, $relativeScript)) {
            $basePath = rtrim(substr($scriptName, 0, -strlen($relativeScript)), '/');
            return $basePath;
        }
    }

    $basePath = '';
    return $basePath;
}

function app_path(string $path = ''): string
{
    $basePath = app_base_path();
    $trimmedPath = ltrim($path, '/');

    if ($trimmedPath === '') {
        return ($basePath !== '' ? $basePath : '') . '/';
    }

    return ($basePath !== '' ? $basePath : '') . '/' . $trimmedPath;
}

function app_origin(): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return $scheme . '://' . $host;
}

function app_url(string $path = ''): string
{
    return app_origin() . app_path($path);
}

function resolve_google_redirect_uri(): string
{
    // If a redirect URI is explicitly configured, always use it.
    // This ensures the URI matches exactly what's registered in Google Console.
    $configuredUrl = defined('GOOGLE_REDIRECT_URI') ? trim((string) constant('GOOGLE_REDIRECT_URI')) : '';
    if ($configuredUrl !== '') {
        return $configuredUrl;
    }

    // Fallback to dynamically generated URL only if no config is set.
    return app_url('auth/callback.php');
}
