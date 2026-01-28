<?php
// Production Error Reporting (Hidden)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once '../config.php';
require_once 'security.php';
require_once 'auth.php'; // Use auth.php for session start

// Start secure session
startSession();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$security = new Security();
$ip_address = $_SERVER['REMOTE_ADDR'];

// Check Rate Limit
$rateLimit = $security->checkRateLimit($ip_address);
if (!$rateLimit['allowed']) {
    $error = $rateLimit['message'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($error)) {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
        $error = "Security Token Invalid (CSRF). Silakan refresh halaman.";
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            // Log error internally, show generic message
            error_log("Connection failed: " . $conn->connect_error);
            $error = "Terjadi kesalahan sistem. Silakan coba lagi nanti.";
        } else {
            $stmt = $conn->prepare("SELECT id, username, password, role, fullname FROM users WHERE username = ? AND is_active = 1");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    // Login Success
                    session_regenerate_id(true); // Prevent Session Fixation
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['fullname'] = $user['fullname'];
                    $_SESSION['LAST_ACTIVITY'] = time();
                    
                    // Clear failed attempts
                    $security->clearLoginAttempts($ip_address);
                    
                    // Update last login
                    $update = $conn->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                    $update->bind_param("i", $user['id']);
                    $update->execute();
                    
                    header('Location: dashboard.php');
                    exit();
                }
            }
            
            // Login Failed
            $security->recordFailedLogin($ip_address);
            $error = "Username atau password salah!";
            $stmt->close();
            $conn->close();
        }
    }
}

// Generate CSRF Token for the form
$csrf_token = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Jari Aljabar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="modern.css" rel="stylesheet">
</head>
<body>
    <div class="login-page">
        <div class="login-card">
            <div class="login-header">
                <?php if (file_exists($_SERVER['DOCUMENT_ROOT'].'/logo-jari-aljabar.jpeg')): ?>
                    <img src="/logo-jari-aljabar.jpeg" alt="Logo Jari Aljabar" style="width: 80px; height: 80px; object-fit: contain; border-radius: 50%; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <?php else: ?>
                    <div class="mb-3">
                        <i class="fas fa-user-shield fa-3x text-primary"></i>
                    </div>
                <?php endif; ?>
                <h2>Admin Portal</h2>
                <p class="text-muted">Silakan masuk untuk mengelola sistem</p>
            </div>

            <?php if (isset($_GET['timeout'])): ?>
                <div class="alert alert-warning mb-4 border-0 shadow-sm bg-orange-50 text-orange-700">
                    <i class="fas fa-clock me-2"></i> Sesi habis. Silakan login kembali.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['logout'])): ?>
                <div class="alert alert-success mb-4 border-0 shadow-sm bg-green-50 text-green-700">
                    <i class="fas fa-check-circle me-2"></i> Berhasil logout.
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger mb-4 border-0 shadow-sm bg-red-50 text-red-700">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                
                <div class="mb-4">
                    <label for="username" class="form-label fw-bold small text-uppercase text-muted">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted ps-3"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" id="username" name="username" placeholder="Masukkan username" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-bold small text-uppercase text-muted">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted ps-3"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" placeholder="Masukkan password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3 mt-2">
                    <i class="fas fa-sign-in-alt me-2"></i> Masuk Sekarang
                </button>
                
                <div class="text-center mt-4">
                    <a href="/" class="text-decoration-none text-muted small hover:text-primary">
                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Website
                    </a>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 