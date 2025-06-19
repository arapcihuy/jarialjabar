<?php
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

// Ambil statistik
$stats = [
    'total' => 0,
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0
];

$result = $conn->query("SELECT status, COUNT(*) as count FROM registrations GROUP BY status");
while ($row = $result->fetch_assoc()) {
    $stats[$row['status']] = $row['count'];
    $stats['total'] += $row['count'];
}

// Ambil pendaftaran terbaru
$recent_registrations = $conn->query("
    SELECT * FROM registrations 
    ORDER BY created_at DESC 
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Jari Aljabar</title>
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
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
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
                            <a class="nav-link active" href="dashboard.php">
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
                        <?php if ($_SESSION['role'] === 'admin'): ?>
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
                    <h2>Dashboard</h2>
                    <div>
                        Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card bg-primary text-white">
                            <h3><?php echo $stats['total']; ?></h3>
                            <p class="mb-0">Total Pendaftar</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card bg-warning text-white">
                            <h3><?php echo $stats['pending']; ?></h3>
                            <p class="mb-0">Menunggu</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card bg-success text-white">
                            <h3><?php echo $stats['approved']; ?></h3>
                            <p class="mb-0">Disetujui</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card bg-danger text-white">
                            <h3><?php echo $stats['rejected']; ?></h3>
                            <p class="mb-0">Ditolak</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Registrations -->
                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Pendaftar Terbaru</h5>
                        <a href="export_excel.php" class="btn btn-success" target="_blank"><i class="bi bi-download"></i> Export ke Excel</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Program</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($reg = $recent_registrations->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($reg['fullname']); ?></td>
                                        <td><?php echo htmlspecialchars($reg['email']); ?></td>
                                        <td><?php echo htmlspecialchars($reg['les_program']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $reg['status'] === 'approved' ? 'success' : 
                                                    ($reg['status'] === 'rejected' ? 'danger' : 'warning'); 
                                            ?>">
                                                <?php echo ucfirst($reg['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d M Y H:i', strtotime($reg['created_at'])); ?></td>
                                    </tr>
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