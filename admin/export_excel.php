<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config.php'; // Pastikan path config.php sudah benar

// Koneksi database (samakan dengan dashboard.php)
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=pendaftar.csv');

$output = fopen('php://output', 'w');
fputcsv($output, array('Nama', 'Email', 'Program', 'Status', 'Tanggal'), ';');

$query = $conn->query("SELECT fullname AS nama, email, les_program AS program, status, created_at AS tanggal FROM registrations ORDER BY created_at DESC");
while ($row = $query->fetch_assoc()) {
    // Format tanggal ke d/m/Y H:i:s agar mudah dibaca Excel
    $row['tanggal'] = date('d/m/Y H:i:s', strtotime($row['tanggal']));
    fputcsv($output, $row, ';');
}
fclose($output);
exit; 