<?php

declare(strict_types=1);

const WINSTON_API_KEY = [
    'XvgLRXI7vzOBITna6JeyWJP4KOp5DFntdmwfpHgs42652442',
    'oag4QICS9kp8dNQdD6vMmtnd2sthhIb4WmeOWrdS608640d0',
];
const TURNSTILE_SECRET_KEY = '0x4AAAAAACu7HPXs3nrAwQsv5xTLRzMy2vo';
define('GOOGLE_CLIENT_ID', '126933193964-8oajgd78l4mfv1f161l0nme9dc1jl2vj.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    define('GOOGLE_REDIRECT_URI', 'http://localhost/plagiarism/auth/callback.php');
} else {
    define('GOOGLE_REDIRECT_URI', 'https://plagiascope.softtechco.biz/auth/callback.php');
}
// MySQL Database Credentials
const DB_HOST = 'localhost';
const DB_NAME = 'softtech_plagia';
const DB_USER = 'softtech_plagia';
const DB_PASS = 'moth34board';
