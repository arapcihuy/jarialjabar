<?php
session_start();
require_once '../config.php';
require_once 'auth.php';

// Cek apakah user sudah login
checkAuth();

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle program actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $duration = $_POST['duration'];
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            if ($_POST['action'] === 'add') {
                $stmt = $conn->prepare("INSERT INTO programs (name, description, price, duration, is_active) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssdsi", $name, $description, $price, $duration, $is_active);
            } else {
                $id = (int)$_POST['id'];
                $stmt = $conn->prepare("UPDATE programs SET name = ?, description = ?, price = ?, duration = ?, is_active = ? WHERE id = ?");
                $stmt->bind_param("ssdsii", $name, $description, $price, $duration, $is_active, $id);
            }
            
            if ($stmt->execute()) {
                $success = "Program " . ($_POST['action'] === 'add' ? 'added' : 'updated') . " successfully";
            } else {
                $error = "Error " . ($_POST['action'] === 'add' ? 'adding' : 'updating') . " program";
            }
            $stmt->close();
        }
    }
}

// Get all programs
$programs = $conn->query("SELECT * FROM programs ORDER BY name");


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Programs - Jari Aljabar</title>
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
                    <h1 class="h3 fw-bold text-dark mb-1">Manajemen Program</h1>
                    <p class="text-muted mb-0">Atur paket bimbingan belajar yang tersedia.</p>
                </div>
                <button type="button" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addProgramModal">
                    <i class="fas fa-plus"></i> Tambah Program
                </button>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success border-0 shadow-sm bg-green-50 text-green-700 mb-4 fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger border-0 shadow-sm bg-red-50 text-red-700 mb-4 fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Programs Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Nama Program</th>
                                    <th>Deskripsi</th>
                                    <th>Harga</th>
                                    <th>Durasi</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($programs->num_rows === 0): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">Belum ada program tersedia.</td>
                                    </tr>
                                <?php else: while ($program = $programs->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-dark"><?php echo htmlspecialchars($program['name']); ?></td>
                                    <td class="text-muted small" style="max-width: 300px;"><?php echo htmlspecialchars($program['description']); ?></td>
                                    <td class="fw-bold text-primary">Rp <?php echo number_format($program['price'], 0, ',', '.'); ?></td>
                                    <td><span class="badge bg-light text-dark border fw-normal"><?php echo htmlspecialchars($program['duration']); ?></span></td>
                                    <td>
                                        <span class="badge bg-soft-<?php echo $program['is_active'] ? 'success' : 'danger'; ?> text-<?php echo $program['is_active'] ? 'success' : 'danger'; ?> rounded-pill px-3">
                                            <?php echo $program['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-light border text-primary" data-bs-toggle="modal" data-bs-target="#editProgramModal<?php echo $program['id']; ?>">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </button>
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editProgramModal<?php echo $program['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg">
                                            <div class="modal-header border-bottom-0 pb-0">
                                                <h5 class="modal-title fw-bold">Edit Program</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body pt-4">
                                                <form method="POST">
                                                    <input type="hidden" name="action" value="edit">
                                                    <input type="hidden" name="id" value="<?php echo $program['id']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small text-muted text-uppercase">Nama Program</label>
                                                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($program['name']); ?>" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small text-muted text-uppercase">Deskripsi</label>
                                                        <textarea class="form-control" name="description" rows="3" required><?php echo htmlspecialchars($program['description']); ?></textarea>
                                                    </div>
                                                    
                                                    <div class="row g-3 mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold small text-muted text-uppercase">Harga (Rp)</label>
                                                            <input type="number" class="form-control" name="price" value="<?php echo $program['price']; ?>" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold small text-muted text-uppercase">Durasi</label>
                                                            <input type="text" class="form-control" name="duration" value="<?php echo htmlspecialchars($program['duration']); ?>" required placeholder="Contoh: 12 Pertemuan">
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-4 form-check form-switch">
                                                        <input type="checkbox" class="form-check-input" name="is_active" id="is_active<?php echo $program['id']; ?>" <?php echo $program['is_active'] ? 'checked' : ''; ?>>
                                                        <label class="form-check-label fw-bold" for="is_active<?php echo $program['id']; ?>">Status Aktif</label>
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

    <!-- Add Program Modal -->
    <div class="modal fade" id="addProgramModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i> Tambah Program Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Program</label>
                            <input type="text" class="form-control" name="name" placeholder="Contoh: Paket Intensif SD" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Jelaskan detail program..." required></textarea>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Harga (Rp)</label>
                                <input type="number" class="form-control" name="price" placeholder="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Durasi</label>
                                <input type="text" class="form-control" name="duration" placeholder="Contoh: 1 Bulan" required>
                            </div>
                        </div>
                        
                        <div class="mb-4 form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="is_active" id="is_active_new" checked>
                            <label class="form-check-label fw-bold" for="is_active_new">Status Aktif</label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-2 fw-bold">Tambah Program</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 