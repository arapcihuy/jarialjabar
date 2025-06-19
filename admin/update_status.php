<?php
session_start();
require_once '../config.php';
require_once 'auth.php';

// Cek apakah user sudah login
checkAuth();

// Cek apakah request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pendaftar.php");
    exit();
}

// Ambil data dari form
$id = $_POST['id'] ?? '';
$status = $_POST['status'] ?? '';
$notes = $_POST['notes'] ?? '';

// Validasi input
if (!$id || !$status) {
    $_SESSION['error'] = "Data tidak lengkap!";
    header("Location: pendaftar.php");
    exit();
}

// Koneksi ke database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update status
$stmt = $conn->prepare("UPDATE registrations SET status = ?, notes = ? WHERE id = ?");
$stmt->bind_param("ssi", $status, $notes, $id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Status berhasil diperbarui!";
} else {
    $_SESSION['error'] = "Gagal memperbarui status!";
}

$stmt->close();
$conn->close();

header("Location: dashboard.php");
exit(); 