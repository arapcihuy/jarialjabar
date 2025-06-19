<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../config.php';
require_once 'auth.php';

// Cek apakah user sudah login
checkAuth();

// Koneksi ke database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Filter dan pencarian
$search = $_GET['search'] ?? '';
$program = $_GET['program'] ?? '';
$status = $_GET['status'] ?? '';

// Query dasar
$query = "SELECT * FROM registrations WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $query .= " AND (fullname LIKE ? OR email LIKE ? OR whatsapp LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

if ($program) {
    $query .= " AND les_program = ?";
    $params[] = $program;
    $types .= "s";
}

if ($status) {
    $query .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
}

$query .= " ORDER BY created_at DESC";

// Prepare dan execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Ambil daftar program untuk filter
$programs = $conn->query("SELECT DISTINCT les_program FROM registrations ORDER BY les_program");

// Proses tambah pendaftar manual
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_pendaftar'])) {
    $nama = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);
    $wa = trim($_POST['whatsapp']);
    $program = trim($_POST['program']);
    $status = trim($_POST['status']);
    $notes = trim($_POST['catatan']);
    if ($nama && $email && $wa && $program && $status) {
        $stmt = $conn->prepare("INSERT INTO registrations (fullname, email, whatsapp, les_program, status, notes) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            $error = 'Prepare failed: ' . $conn->error;
        } else {
            $stmt->bind_param("ssssss", $nama, $email, $wa, $program, $status, $notes);
            if ($stmt->execute()) {
                header('Location: pendaftar.php?success=1');
                exit();
            } else {
                $error = 'Gagal menambah pendaftar. ' . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $error = 'Semua field wajib diisi!';
    }
}
if (isset($_GET['success'])) {
    $success = 'Pendaftar berhasil ditambahkan!';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pendaftar - Jari Aljabar</title>
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
                            <a class="nav-link active" href="pendaftar.php">
                                <i class="bi bi-person-plus"></i> Pendaftar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="programs.php">
                                <i class="bi bi-book"></i> Program
                            </a>
                        </li>
                        <?php if ($_SESSION['role'] === 'admin' && file_exists('users.php')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="bi bi-people"></i> Users
                            </a>
                        </li>
                        <?php endif; ?>
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
                    <h2>Manajemen Pendaftar</h2>
                    <div>
                        Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?>
                    </div>
                </div>

                <!-- Filter dan Pencarian -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" placeholder="Cari nama/email/whatsapp" value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="program">
                                    <option value="">Semua Program</option>
                                    <?php while ($p = $programs->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($p['les_program']); ?>" <?php echo $program === $p['les_program'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($p['les_program']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">Semua Status</option>
                                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                                    <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Disetujui</option>
                                    <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Ditolak</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tambah pendaftar manual -->
                <div class="card mb-4">
                    <div class="card-header">Tambah Pendaftar Manual</div>
                    <div class="card-body">
                        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
                        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                        <form method="post">
                            <div class="row g-2">
                                <div class="col-md-3"><input type="text" name="nama_lengkap" class="form-control" placeholder="Nama Lengkap" required></div>
                                <div class="col-md-2"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                                <div class="col-md-2"><input type="text" name="whatsapp" class="form-control" placeholder="No. WhatsApp" required></div>
                                <div class="col-md-3"><input type="text" name="program" class="form-control" placeholder="Program" required></div>
                                <div class="col-md-2">
                                    <select name="status" class="form-control" required>
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row g-2 mt-2">
                                <div class="col-md-12"><textarea name="catatan" class="form-control" placeholder="Catatan tambahan (opsional)"></textarea></div>
                            </div>
                            <div class="mt-2"><button type="submit" name="add_pendaftar" class="btn btn-primary">Tambah</button></div>
                        </form>
                    </div>
                </div>

                <!-- Tabel Pendaftar -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Lengkap</th>
                                        <th>Email</th>
                                        <th>WhatsApp</th>
                                        <th>Program</th>
                                        <th>Tanggal Daftar</th>
                                        <th>Status</th>
                                        <th>Catatan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    while ($row = $result->fetch_assoc()): 
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['whatsapp']); ?></td>
                                        <td><?php echo htmlspecialchars($row['les_program']); ?></td>
                                        <td><?php echo date('d-m-Y H:i', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $row['status'] === 'approved' ? 'success' : 
                                                    ($row['status'] === 'rejected' ? 'danger' : 'warning'); 
                                            ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['notes'] ?? '-'); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>">
                                                Edit
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal Edit -->
                                    <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Status Pendaftar</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="update_status.php" method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Status</label>
                                                            <select class="form-select" name="status" required>
                                                                <option value="pending" <?php echo $row['status'] === 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                                                                <option value="approved" <?php echo $row['status'] === 'approved' ? 'selected' : ''; ?>>Disetujui</option>
                                                                <option value="rejected" <?php echo $row['status'] === 'rejected' ? 'selected' : ''; ?>>Ditolak</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Catatan</label>
                                                            <textarea class="form-control" name="notes" rows="3"><?php echo htmlspecialchars($row['notes'] ?? ''); ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
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