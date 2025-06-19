<?php
require_once 'auth.php';

// Cek apakah user sudah login
checkAuth();

echo "<h2>Test Session Timeout</h2>";
echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
echo "<p>Username: " . $_SESSION['username'] . "</p>";
echo "<p>Last Activity: " . date('Y-m-d H:i:s', $_SESSION['LAST_ACTIVITY']) . "</p>";
echo "<p>Current Time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Session akan timeout setelah 15 menit tidak aktif</p>";
echo "<p><a href='dashboard.php'>Kembali ke Dashboard</a></p>";
echo "<p><a href='logout.php'>Logout</a></p>";
?> 