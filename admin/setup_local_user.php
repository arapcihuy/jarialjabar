<?php
// Force local bypass for initial setup if needed, or rely on config detection
$_SERVER['SERVER_NAME'] = 'localhost'; 
require_once __DIR__ . '/../config.php';

echo "Attempting to connect to MySQL locally...\n";
echo "Host: " . DB_HOST . "\n";
echo "User: " . DB_USER . "\n";

// Try to connect without DB selected first to create it
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n\nCRITICAL: Please edit config.php and set the correct DB_PASS for your local root user.\n");
}

echo "Connected to MySQL.\n";

// Create DB
$dbName = DB_NAME;
$sql = "CREATE DATABASE IF NOT EXISTS $dbName";
if ($conn->query($sql) === TRUE) {
    echo "Database '$dbName' checked/created.\n";
} else {
    die("Error creating database: " . $conn->error . "\n");
}

$conn->select_db($dbName);

// Create Users Table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'users' checked/created.\n";
} else {
    die("Error creating table users: " . $conn->error . "\n");
}

// Ensure login_attempts table exists
$sql = "CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    attempts INT DEFAULT 0,
    last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    blocked_until TIMESTAMP NULL,
    UNIQUE KEY unique_ip (ip_address)
)";
$conn->query($sql);

// Insert/Update Wiwik
$username = 'wiwik';
$passwordraw = 'wiwik123';
$fullname = 'Wiwik Admin';
$email = 'wiwik@local.test';
$role = 'admin';

$hash = password_hash($passwordraw, PASSWORD_DEFAULT);

// Check if exists
$check = $conn->query("SELECT id FROM users WHERE username = '$username'");
if ($check->num_rows > 0) {
    $stmt = $conn->prepare("UPDATE users SET password = ?, is_active = 1 WHERE username = ?");
    $stmt->bind_param("ss", $hash, $username);
    echo "Updating password for existing user '$username'...\n";
} else {
    $stmt = $conn->prepare("INSERT INTO users (username, password, fullname, email, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $hash, $fullname, $email, $role);
    echo "Creating new user '$username'...\n";
}

if ($stmt->execute()) {
    echo "SUCCESS: User '$username' is ready. You can login with password '$passwordraw'.\n";
} else {
    echo "Error setting up user: " . $stmt->error . "\n";
}

$conn->close();
?>
