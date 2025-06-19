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
    $password = $_POST['password'];
    $role = $_POST['role'];
    if ($username && $fullname && $password && $role) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (username, password, role, fullname) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $username, $hash, $role, $fullname);
        if ($stmt->execute()) {
            $success = 'User berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambah user: ' . $stmt->error;
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
            color: white;
        }
        .nav-link {
            color: rgba(255,255,255,.8);
        }
        .nav-link:hover {
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-3">
                    <div class="text-center mb-3">
                        <?php if (file_exists($_SERVER['DOCUMENT_ROOT'].'/logo-jari-aljabar.jpeg')): ?>
                            <img src="/logo-jari-aljabar.jpeg" alt="Logo Jari Aljabar" style="max-width:180px;max-height:180px;">
                        <?php elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/images/logo-jari-aljabar.jpeg')): ?>
                            <img src="/images/logo-jari-aljabar.jpeg" alt="Logo Jari Aljabar" style="max-width:180px;max-height:180px;">
                        <?php else: ?>
                            <div style="color:red;font-size:12px;">Logo tidak ditemukan!</div>
                        <?php endif; ?>
                    </div>
                    <h4>Admin Panel</h4>
                    <hr>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="pendaftar.php">
                                <i class="bi bi-person-plus"></i> Pendaftar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="programs.php">
                                <i class="bi bi-book"></i> Program
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="users.php">
                                <i class="bi bi-people"></i> Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Manajemen Users</h2>
                    <div>
                        Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?>
                    </div>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <!-- Tambah User -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Tambah User Baru</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <input type="hidden" name="add_user" value="1">
                            <div class="col-md-3">
                                <input type="text" name="username" class="form-control" placeholder="Username" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="fullname" class="form-control" placeholder="Nama Lengkap" required>
                            </div>
                            <div class="col-md-3">
                                <input type="password" name="password" class="form-control" placeholder="Password" required>
                            </div>
                            <div class="col-md-2">
                                <select name="role" class="form-select" required>
                                    <option value="">Pilih Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="operator">Operator</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-grid">
                                <button type="submit" class="btn btn-primary">Tambah</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Daftar Users -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Daftar Users</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Nama Lengkap</th>
                                        <th>Role</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($result->num_rows === 0): ?>
                                    <tr><td colspan="4" class="text-center">Belum ada user terdaftar.</td></tr>
                                <?php else: while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['id']) ?></td>
                                        <td><?= htmlspecialchars($row['username']) ?></td>
                                        <td><?= htmlspecialchars($row['fullname']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $row['role'] === 'admin' ? 'danger' : 'primary' ?>">
                                                <?= htmlspecialchars($row['role']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                                                <input type="hidden" name="delete_user" value="1">
                                                <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Hapus</button>
                                            </form>
                                            <?php else: ?>
                                            <span class="text-muted">(Login)</span>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 