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
    $p_program = trim($_POST['program']);
    $p_status = trim($_POST['status']);
    $notes = trim($_POST['catatan']);
    if ($nama && $email && $wa && $p_program && $p_status) {
        $stmt = $conn->prepare("INSERT INTO registrations (fullname, email, whatsapp, les_program, status, notes) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            $error = 'Prepare failed: ' . $conn->error;
        } else {
            $stmt->bind_param("ssssss", $nama, $email, $wa, $p_program, $p_status, $notes);
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                    <h1 class="h3 fw-bold text-dark mb-1">Manajemen Pendaftar</h1>
                    <p class="text-muted mb-0">Kelola data pendaftaran siswa baru.</p>
                </div>
                <button type="button" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addManualModal">
                    <i class="fas fa-plus"></i> Tambah Manual
                </button>
            </div>

            <!-- Filter dan Pencarian -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small text-uppercase text-muted fw-bold">Pencarian</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" name="search" placeholder="Nama, Email, atau WhatsApp..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-uppercase text-muted fw-bold">Program</label>
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
                            <label class="form-label small text-uppercase text-muted fw-bold">Status</label>
                            <select class="form-select" name="status">
                                <option value="">Semua Status</option>
                                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                                <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Disetujui</option>
                                <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-secondary w-100 h-100 d-flex align-items-center justify-content-center gap-2">
                                <i class="fas fa-filter"></i> Terapkan
                            </button>
                        </div>
                    </form>
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

            <!-- Tabel Pendaftar -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Informasi Pendaftar</th>
                                    <th>Kontak</th>
                                    <th>Program</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                if ($result->num_rows === 0): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-folder-open fa-2x mb-3 opacity-25"></i>
                                            <p class="mb-0">Tidak ada data pendaftar ditemukan.</p>
                                        </td>
                                    </tr>
                                <?php else:
                                while ($row = $result->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td class="ps-4 text-muted"><?php echo $no++; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-initial rounded-circle bg-soft-primary text-primary fw-bold d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 40px; height: 40px;">
                                                <?php echo strtoupper(substr($row['fullname'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['fullname']); ?></div>
                                                <div class="small text-muted">
                                                    <i class="far fa-clock me-1"></i> <?php echo date('d M Y, H:i', strtotime($row['created_at'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>" class="text-decoration-none text-muted small hover:text-primary">
                                                <i class="far fa-envelope me-2 w-4"></i><?php echo htmlspecialchars($row['email']); ?>
                                            </a>
                                            <a href="https://wa.me/<?php echo htmlspecialchars($row['whatsapp']); ?>" target="_blank" class="text-decoration-none text-muted small hover:text-success">
                                                <i class="fab fa-whatsapp me-2 w-4"></i><?php echo htmlspecialchars($row['whatsapp']); ?>
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border fw-normal px-3 py-2">
                                            <?php echo htmlspecialchars($row['les_program']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                            // Fallback for older PHP versions that don't support match
                                            $statusClass = 'warning';
                                            $statusLabel = 'Menunggu';
                                            
                                            if ($row['status'] === 'approved') {
                                                $statusClass = 'success';
                                                $statusLabel = 'Disetujui';
                                            } elseif ($row['status'] === 'rejected') {
                                                $statusClass = 'danger';
                                                $statusLabel = 'Ditolak';
                                            }
                                        ?>
                                        <span class="badge bg-soft-<?php echo $statusClass; ?> text-<?php echo $statusClass; ?> px-3 py-2 rounded-pill">
                                            <?php echo $statusLabel; ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-light border text-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>">
                                            <i class="fas fa-edit me-1"></i> Kelola
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal Edit -->
                                <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg">
                                            <div class="modal-header border-bottom-0 pb-0">
                                                <h5 class="modal-title fw-bold">Update Status Pendaftar</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body pt-4">
                                                <form action="update_status.php" method="POST">
                                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                    
                                                    <div class="d-flex align-items-center mb-4 p-3 bg-light rounded-3">
                                                        <div class="avatar-initial rounded-circle bg-white text-primary fw-bold d-flex align-items-center justify-content-center me-3 shadow-sm border" style="width: 48px; height: 48px; font-size: 1.2rem;">
                                                            <?php echo strtoupper(substr($row['fullname'], 0, 1)); ?>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($row['fullname']); ?></h6>
                                                            <small class="text-muted"><?php echo htmlspecialchars($row['les_program']); ?></small>
                                                        </div>
                                                    </div>

                                                    <div class="mb-4">
                                                        <label class="form-label fw-bold small text-muted text-uppercase">Status Pendaftaran</label>
                                                        <select class="form-select form-select-lg" name="status" required>
                                                            <option value="pending" <?php echo $row['status'] === 'pending' ? 'selected' : ''; ?>>⏳ Menunggu Konfirmasi</option>
                                                            <option value="approved" <?php echo $row['status'] === 'approved' ? 'selected' : ''; ?>>✅ Disetujui</option>
                                                            <option value="rejected" <?php echo $row['status'] === 'rejected' ? 'selected' : ''; ?>>❌ Ditolak</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-4">
                                                        <label class="form-label fw-bold small text-muted text-uppercase">Catatan Admin</label>
                                                        <textarea class="form-control" name="notes" rows="3" placeholder="Tambahkan catatan internal (opsional)..."><?php echo htmlspecialchars($row['notes'] ?? ''); ?></textarea>
                                                    </div>

                                                    <div class="d-grid gap-2">
                                                        <button type="submit" class="btn btn-primary py-2 fw-bold">Simpan Perubahan</button>
                                                        <button type="button" class="btn btn-light py-2 text-muted" data-bs-dismiss="modal">Batal</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Manual -->
    <div class="modal fade" id="addManualModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i> Tambah Pendaftar Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" placeholder="Contoh: Budi Santoso" required>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="email@contoh.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">No. WhatsApp</label>
                                <input type="text" name="whatsapp" class="form-control" placeholder="0812..." required>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Program</label>
                                <input type="text" name="program" class="form-control" placeholder="Nama program" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status Awal</label>
                                <select name="status" class="form-select" required>
                                    <option value="pending">Menunggu</option>
                                    <option value="approved">Disetujui</option>
                                    <option value="rejected">Ditolak</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Catatan</label>
                            <textarea name="catatan" class="form-control" rows="2" placeholder="opsional"></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="add_pendaftar" class="btn btn-primary py-2 fw-bold">Simpan Pendaftar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 