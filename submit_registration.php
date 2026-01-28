<?php
require_once 'config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle OPTIONS request for CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Koneksi database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal"]);
    exit();
}

// Ambil data POST (FormData)
$fullname = $conn->real_escape_string(trim($_POST['fullname'] ?? ''));
$email = $conn->real_escape_string(trim($_POST['email'] ?? ''));
$whatsapp = preg_replace('/[^0-9]/', '', $conn->real_escape_string(trim($_POST['whatsapp'] ?? '')));
$les_program = $conn->real_escape_string(trim($_POST['program'] ?? ''));
$notes = $conn->real_escape_string(trim($_POST['jadwal'] ?? ''));

// Validasi sederhana
if (!$fullname || !$email || !$whatsapp || !$les_program || !$notes) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Semua field wajib diisi!"]);
    exit();
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Format email tidak valid!"]);
    exit();
}
if (!preg_match('/^[0-9]{8,15}$/', $whatsapp)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Nomor WhatsApp tidak valid!"]);
    exit();
}

// Cek email duplikat
$cek = $conn->prepare("SELECT id FROM registrations WHERE email = ?");
$cek->bind_param("s", $email);
$cek->execute();
$cek->store_result();
if ($cek->num_rows > 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Email sudah terdaftar!"]);
    $cek->close();
    exit();
}
$cek->close();

// Simpan ke database
$stmt = $conn->prepare("INSERT INTO registrations (fullname, email, whatsapp, les_program, notes, created_at, status) VALUES (?, ?, ?, ?, ?, NOW(), 'pending')");
$stmt->bind_param("sssss", $fullname, $email, $whatsapp, $les_program, $notes);
if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Pendaftaran berhasil!"]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Gagal menyimpan data!"]);
}
$stmt->close();

$conn->close();
?>