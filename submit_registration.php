<?php
require_once 'config.php';

// Start session for CSRF protection
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token");

// Handle OPTIONS request for CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Rate limiting
$ip = $_SERVER['REMOTE_ADDR'];
$rate_limit_file = sys_get_temp_dir() . '/rate_limit_' . md5($ip) . '.txt';

if (file_exists($rate_limit_file)) {
    $rate_data = json_decode(file_get_contents($rate_limit_file), true);
    if (time() - $rate_data['time'] < 60 && $rate_data['count'] >= 5) {
        http_response_code(429);
        echo json_encode(["status" => "error", "message" => "Too many requests. Please try again later."]);
        exit();
    }
}

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    // CSRF Protection
    if (!isset($data['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $data['csrf_token'])) {
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "Invalid security token"]);
        exit();
    }

    // Validate required fields
    if (empty($data['fullname']) || empty($data['email']) || empty($data['whatsapp']) || empty($data['les_program'])) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
        exit();
    }

    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid email format"]);
        exit();
    }

    // Validate WhatsApp number
    if (!preg_match('/^[0-9]{10,15}$/', $data['whatsapp'])) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid WhatsApp number format"]);
        exit();
    }

    // Validate les_program
    if (!in_array($data['les_program'], ALLOWED_LES_PROGRAMS)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid program selection"]);
        exit();
    }

    // Sanitize inputs
    $fullname = $conn->real_escape_string(trim($data['fullname']));
    $email = $conn->real_escape_string(trim($data['email']));
    $whatsapp = $conn->real_escape_string(trim($data['whatsapp']));
    $les_program = $conn->real_escape_string(trim($data['les_program']));

    // Validate input lengths
    if (strlen($fullname) > 100 || strlen($email) > 100 || strlen($whatsapp) > 15 || strlen($les_program) > 100) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Input length exceeds maximum allowed"]);
        exit();
    }

    // Check for duplicate email
    $check_stmt = $conn->prepare("SELECT id FROM registrations WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Email already registered"]);
        $check_stmt->close();
        exit();
    }
    $check_stmt->close();

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO registrations (fullname, email, whatsapp, les_program, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $fullname, $email, $whatsapp, $les_program);

    if ($stmt->execute()) {
        // Update rate limit
        $rate_data = [
            'time' => time(),
            'count' => ($rate_data['count'] ?? 0) + 1
        ];
        file_put_contents($rate_limit_file, json_encode($rate_data));

        // Log successful registration
        error_log("New registration successful: " . $email);

        echo json_encode(["status" => "success", "message" => "Registration successful"]);
    } else {
        error_log("Registration failed for email: " . $email . " - Error: " . $stmt->error);
        echo json_encode(["status" => "error", "message" => "Registration failed. Please try again later."]);
    }

    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
}

$conn->close();
?> 