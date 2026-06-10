<?php
    // Database configuration — ALL credentials via environment variables
    // Copy .env.example to .env and fill in real values
    $is_local = ($_SERVER['SERVER_NAME'] ?? '') === 'localhost' || ($_SERVER['SERVER_NAME'] ?? '') === '127.0.0.1';

    if ($is_local) {
        define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
        define('DB_USER', getenv('DB_USER') ?: 'root');
        define('DB_PASS', getenv('DB_PASS') ?: '');
        define('DB_NAME', getenv('DB_NAME') ?: 'jarialjabar_local');
    } else {
        define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
        define('DB_USER', getenv('DB_USER'));
        define('DB_PASS', getenv('DB_PASS'));
        define('DB_NAME', getenv('DB_NAME'));
    }

    // Application settings
    define('MAX_LOGIN_ATTEMPTS', 5);
    define('LOGIN_TIMEOUT', 300);
    define('ALLOWED_LES_PROGRAMS', ['program1', 'program2', 'program3']);

    // Security settings
    define('CSRF_TOKEN_SECRET', getenv('CSRF_TOKEN_SECRET') ?: 'change-me-in-.env');
