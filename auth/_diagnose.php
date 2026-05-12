<?php

declare(strict_types=1);

/**
 * Temporary diagnostic page — DELETE AFTER USE.
 * Tests outbound HTTPS to Google's OAuth token endpoint.
 */

header('Content-Type: text/plain; charset=utf-8');

echo "PHP version: " . PHP_VERSION . "\n";
echo "cURL version: " . (function_exists('curl_version') ? curl_version()['version'] : 'N/A') . "\n";
echo "OpenSSL: " . (defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : 'N/A') . "\n";
echo "openssl.cafile ini: " . (ini_get('openssl.cafile') ?: '(empty)') . "\n";
echo "curl.cainfo ini: " . (ini_get('curl.cainfo') ?: '(empty)') . "\n";
echo str_repeat('-', 60) . "\n";

$targets = [
    'https://oauth2.googleapis.com/token',
    'https://accounts.google.com/.well-known/openid-configuration',
    'https://www.google.com/',
];

$modes = [
    'default'    => null,
    'ipv4-only'  => CURL_IPRESOLVE_V4,
    'ipv6-only'  => CURL_IPRESOLVE_V6,
];

foreach ($targets as $url) {
    foreach ($modes as $modeName => $resolve) {
        echo "GET {$url} [{$modeName}]\n";
        $ch = curl_init($url);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_NOBODY => false,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => false,
        ];
        if ($resolve !== null) {
            $opts[CURLOPT_IPRESOLVE] = $resolve;
        }
        curl_setopt_array($ch, $opts);
        $start = microtime(true);
        $body = curl_exec($ch);
        $elapsed = round((microtime(true) - $start) * 1000);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $primaryIp = curl_getinfo($ch, CURLINFO_PRIMARY_IP);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        echo "  HTTP {$http}, errno={$errno}, time={$elapsed}ms, ip={$primaryIp}\n";
        if ($errno !== 0) {
            echo "  cURL error: {$error}\n";
        }
    }
    echo "\n";
}

echo str_repeat('-', 60) . "\n";
echo "DNS check (gethostbyname):\n";
foreach (['oauth2.googleapis.com', 'accounts.google.com', 'www.google.com'] as $host) {
    $ip = gethostbyname($host);
    echo "  {$host} -> " . ($ip === $host ? '(failed)' : $ip) . "\n";
}
