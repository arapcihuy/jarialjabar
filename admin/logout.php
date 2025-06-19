<?php
session_start();

// Hapus semua data session
session_unset();
session_destroy();

// Redirect ke halaman login dengan pesan logout berhasil
header("Location: login.php?logout=1");
exit();
?> 