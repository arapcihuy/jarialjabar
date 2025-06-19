<?php
session_start();
require_once '../config.php';
require_once 'auth.php';

echo "<h2>Test Session</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Status: " . session_status() . "</p>";

if (isset($_SESSION['user_id'])) {
    echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
    echo "<p>Username: " . ($_SESSION['username'] ?? 'N/A') . "</p>";
    echo "<p>Fullname: " . ($_SESSION['fullname'] ?? 'N/A') . "</p>";
    echo "<p>Role: " . ($_SESSION['role'] ?? 'N/A') . "</p>";
    echo "<p><a href='dashboard.php'>Go to Dashboard</a></p>";
    echo "<p><a href='logout.php'>Logout</a></p>";
} else {
    echo "<p>No session found. <a href='login.php'>Login</a></p>";
}

echo "<h3>All Session Variables:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?> 