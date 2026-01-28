<?php
// Fungsi untuk memulai session dengan aman
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Set secure session parameters
        session_set_cookie_params([
            'lifetime' => 0, // Session cookie persists until browser closed
            'path' => '/',
            'domain' => '', // Default domain
            'secure' => true, // Only send over HTTPS
            'httponly' => true, // Prevent JS access to session cookie
            'samesite' => 'Strict' // Prevent CSRF
        ]);
        session_start();
    }
    
    // Add Security Headers
    header("X-Frame-Options: DENY");
    header("X-Content-Type-Options: nosniff");
    header("X-XSS-Protection: 1; mode=block");

    // Session timeout: 15 menit
    $timeout = 900; // 15*60 detik
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
        session_unset();
        session_destroy();
        header('Location: login.php?timeout=1');
            exit();
        }
    $_SESSION['LAST_ACTIVITY'] = time();
}

// Fungsi untuk mengecek apakah user sudah login
function checkAuth() {
    startSession();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

// Fungsi untuk mengecek apakah user adalah admin
function isAdmin() {
    startSession();
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Fungsi untuk mendapatkan nama user
function getUserName() {
    startSession();
    return $_SESSION['fullname'] ?? 'User';
}

// Fungsi untuk mendapatkan role user
function getUserRole() {
    startSession();
    return $_SESSION['role'] ?? 'user';
}

// Fungsi untuk mendapatkan user ID
function getUserId() {
    startSession();
    return $_SESSION['user_id'] ?? null;
}