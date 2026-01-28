<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../config.php';
require_once 'auth.php';

// Cek apakah user sudah login dan adalah admin
checkAuth();
if (!isAdmin()) {
    header('Location: dashboard.php');
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('Koneksi gagal: ' . $conn->connect_error);
}

// Handle tambah user
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    if ($username && $fullname && $email && $password && $role) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (username, password, role, fullname, email) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $username, $hash, $role, $fullname, $email);
        try {
            if ($stmt->execute()) {
                $success = 'User berhasil ditambahkan!';
            }
        } catch (mysqli_sql_exception $e) {
            // Check specific error code for Duplicate Entry
            if ($e->getCode() == 1062) {
                $errorMsg = $e->getMessage();
                // Check if the duplicate error is actually for the username column
                if (strpos($errorMsg, "'username'") !== false) {
                    $error = "Username '$username' sudah terdaftar. Gunakan username lain.";
                } elseif (strpos($errorMsg, "'email'") !== false) {
                    $error = "Email '$email' sudah terdaftar. Gunakan email lain.";    
                } else {
                    // Likely a duplicate PRIMARY key (ID) issue due to missing AUTO_INCREMENT
                    $error = "Gagal menambah user (Error Duplikat Sistem): " . $errorMsg . ". Kemungkinan database 'id' tidak Auto Increment.";
                }
            } else {
                $error = 'Gagal menambah user: ' . $e->getMessage();
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    } else {
        $error = 'Semua field wajib diisi!';
    }
}

// Handle hapus user
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user']) && isset($_POST['user_id'])
) {
    $user_id = intval($_POST['user_id']);
    // Cegah user menghapus dirinya sendiri
    if ($user_id == $_SESSION['user_id']) {
        $error = 'Anda tidak dapat menghapus user yang sedang login!';
    } else {
        $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
        $stmt->bind_param('i', $user_id);
        if ($stmt->execute()) {
            $success = 'User berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus user: ' . $stmt->error;
        }
    }
}

$result = $conn->query('SELECT id, username, fullname, role FROM users ORDER BY id ASC');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Users - Jari Aljabar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="modern.css" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h1 class="h3 fw-bold text-dark mb-1">Manajemen Users</h1>
                    <p class="text-muted mb-0">Kelola akun administrator dan operator.</p>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success border-0 shadow-sm bg-green-50 text-green-700 mb-4 fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?= $success ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger border-0 shadow-sm bg-red-50 text-red-700 mb-4 fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Tambah User Form -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white py-3 border-bottom-0">
                            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-user-plus me-2 text-primary"></i> Tambah User Baru</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="add_user" value="1">
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-muted text-uppercase">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-user"></i></span>
                                        <input type="text" name="username" class="form-control border-start-0 ps-2 bg-light" placeholder="Username" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-muted text-uppercase">Nama Lengkap</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-id-card"></i></span>
                                        <input type="text" name="fullname" class="form-control border-start-0 ps-2 bg-light" placeholder="Nama Lengkap" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-muted text-uppercase">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-envelope"></i></span>
                                        <input type="email" name="email" class="form-control border-start-0 ps-2 bg-light" placeholder="Email" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-muted text-uppercase">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-lock"></i></span>
                                        <input type="password" name="password" class="form-control border-start-0 ps-2 bg-light" placeholder="Password" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold small text-muted text-uppercase">Role User</label>
                                    <select name="role" class="form-select bg-light" required>
                                        <option value="">Pilih Role...</option>
                                        <option value="admin">Admin (Akses Penuh)</option>
                                        <option value="operator">Operator (Terbatas)</option>
                                    </select>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary py-2 fw-bold">
                                        <i class="fas fa-plus-circle me-1"></i> Tambah User
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Daftar Users Table -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-users me-2 text-primary"></i> Daftar Pengguna</h5>
                            <span class="badge bg-soft-primary text-primary rounded-pill"><?= $result->num_rows ?> User Terdaftar</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4">User</th>
                                            <th>Role</th>
                                            <th class="text-end pe-4">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php 
                                    // Re-query to sort by newest first
                                    $result = $conn->query('SELECT id, username, fullname, role FROM users ORDER BY id DESC');
                                    
                                    if ($result->num_rows === 0): ?>
                                        <tr><td colspan="3" class="text-center py-5 text-muted">Belum ada user terdaftar.</td></tr>
                                    <?php else: while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-initial rounded-circle bg-soft-primary text-primary fw-bold d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                        <?= strtoupper(substr($row['fullname'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['fullname']) ?></div>
                                                        <div class="small text-muted">@<?= htmlspecialchars($row['username']) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php 
                                                    $roleClass = $row['role'] === 'admin' ? 'danger' : 'primary';
                                                    $roleIcon = $row['role'] === 'admin' ? 'shield-alt' : 'user-cog';
                                                ?>
                                                <span class="badge bg-soft-<?= $roleClass ?> text-<?= $roleClass ?> px-3 py-2 rounded-pill">
                                                    <i class="fas fa-<?= $roleIcon ?> me-1"></i> <?= ucfirst($row['role']) ?>
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                                                    <input type="hidden" name="delete_user" value="1">
                                                    <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-light border text-danger hover:bg-red-50" title="Hapus User">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                                <?php else: ?>
                                                <span class="badge bg-light text-muted border fw-normal">Sedang Login</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 