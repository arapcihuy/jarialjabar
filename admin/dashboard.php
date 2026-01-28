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
                    <h1 class="h3 fw-bold text-dark mb-1">Dashboard Overview</h1>
                    <p class="text-muted mb-0">Selamat datang kembali, <span class="fw-bold text-primary"><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>! 👋</p>
                </div>
                <div class="d-flex gap-2">
                    <span class="px-3 py-2 bg-white rounded-pill shadow-sm border text-sm text-muted">
                        <i class="bi bi-calendar-event me-2"></i> <?php echo date('d M Y'); ?>
                    </span>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-value text-primary"><?php echo $stats['total']; ?></div>
                        <div class="stat-label">Total Pendaftar</div>
                        <div class="stat-icon bg-soft-primary">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-value text-warning"><?php echo $stats['pending']; ?></div>
                        <div class="stat-label">Menunggu Persetujuan</div>
                        <div class="stat-icon bg-soft-warning">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-value text-success"><?php echo $stats['approved']; ?></div>
                        <div class="stat-label">Pendaftar Diterima</div>
                        <div class="stat-icon bg-soft-success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-value text-danger"><?php echo $stats['rejected']; ?></div>
                        <div class="stat-label">Pendaftar Ditolak</div>
                        <div class="stat-icon bg-soft-danger">
                            <i class="bi bi-x-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Registrations -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Pendaftaran Terbaru</h5>
                        <small class="text-muted">10 pendaftar terakhir yang masuk sistem.</small>
                    </div>
                    <a href="export_excel.php" class="btn btn-success btn-sm px-3 rounded-pill" target="_blank">
                        <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Nama Lengkap</th>
                                    <th>Program Pilihan</th>
                                    <th>Tanggal Daftar</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reg = $recent_registrations->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-initial rounded-circle bg-soft-primary text-primary fw-bold d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px; font-size: 0.9rem;">
                                                <?php echo strtoupper(substr($reg['fullname'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($reg['fullname']); ?></div>
                                                <div class="small text-muted"><?php echo htmlspecialchars($reg['email']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-medium text-dark"><?php echo htmlspecialchars($reg['les_program']); ?></span>
                                    </td>
                                    <td class="text-muted">
                                        <i class="bi bi-calendar3 me-2 text-muted opacity-50"></i>
                                        <?php echo date('d M Y', strtotime($reg['created_at'])); ?>
                                        <small class="d-block text-muted" style="font-size: 0.75rem;"><?php echo date('H:i', strtotime($reg['created_at'])); ?> WIB</small>
                                    </td>
                                    <td>
                                        <?php 
                                            $statusClass = match($reg['status']) {
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                                default => 'warning'
                                            };
                                            $statusIcon = match($reg['status']) {
                                                'approved' => 'check-circle-fill',
                                                'rejected' => 'x-circle-fill',
                                                default => 'clock-fill'
                                            };
                                        ?>
                                        <span class="badge bg-soft-<?php echo $statusClass; ?> text-<?php echo $statusClass; ?> px-3 py-2 rounded-pill">
                                            <i class="bi bi-<?php echo $statusIcon; ?> me-1"></i>
                                            <?php echo ucfirst($reg['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="pendaftar.php" class="btn btn-sm btn-light border text-muted">Detail</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <?php if ($recent_registrations->num_rows === 0): ?>
                            <div class="text-center py-5 text-muted">
                                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486776.png" width="60" class="opacity-25 mb-3" alt="Empty">
                                <p>Belum ada data pendaftaran.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 