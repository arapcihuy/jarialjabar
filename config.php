<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'u800708762_jariuser');
define('DB_PASS', 'Rasyid1oke');
define('DB_NAME', 'u800708762_jarialjabar');

// Application settings
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 300); // 5 minutes in seconds
define('ALLOWED_LES_PROGRAMS', ['program1', 'program2', 'program3']); // Add your actual program names here

// Security settings
define('CSRF_TOKEN_SECRET', 'your-secret-key-here'); // Change this to a random string
?> 