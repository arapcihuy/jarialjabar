<?php
    // Database configuration
    $is_local = ($_SERVER['SERVER_NAME'] ?? '') === 'localhost' || ($_SERVER['SERVER_NAME'] ?? '') === '127.0.0.1';

    if ($is_local) {
        define('DB_HOST', '127.0.0.1');
        define('DB_USER', 'root');
        define('DB_PASS', 'root'); // Try 'root' as common default
        define('DB_NAME', 'jarialjabar_local');
    } else {
        define('DB_HOST', 'localhost');
        define('DB_USER', 'u800708762_jariuser');
        define('DB_PASS', 'Rasyid1oke');
        define('DB_NAME', 'u800708762_jarialjabar');
    }

    // Application settings
    define('MAX_LOGIN_ATTEMPTS', 5);
    define('LOGIN_TIMEOUT', 300); // 5 minutes in seconds
    define('ALLOWED_LES_PROGRAMS', ['program1', 'program2', 'program3']); // Add your actual program names here

    // Security settings
    define('CSRF_TOKEN_SECRET', 'your-secret-key-here'); // Change this to a random string