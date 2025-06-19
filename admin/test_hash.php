<?php
$password_from_db = '$2y$10$imeLCM16oLEq/MjV07KdE.BzqWKk8YQfg8YtgdkgpCAyvoVuJ20SO'; // Ini hash dari database
$password_input = 'admin123'; // Ini password yang Anda coba masukkan

if (password_verify($password_input, $password_from_db)) {
    echo "Password cocok!";
} else {
    echo "Password tidak cocok.";
}
?>
