<?php
require_once '../config.php';

// Koneksi database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Ambil daftar program dan status unik untuk filter
$programs = [];
$status_list = [];
$prog_res = $conn->query("SELECT DISTINCT les_program FROM registrations ORDER BY les_program");
while ($row = $prog_res->fetch_assoc()) {
    if ($row['les_program']) $programs[] = $row['les_program'];
}
$stat_res = $conn->query("SELECT DISTINCT status FROM registrations ORDER BY status");
while ($row = $stat_res->fetch_assoc()) {
    if ($row['status']) $status_list[] = $row['status'];
}

// Handle export CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="data_pendaftar.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['No', 'Nama Lengkap', 'Email', 'WhatsApp', 'Program', 'Tanggal Daftar', 'Status', 'Catatan']);
    $sql = "SELECT * FROM registrations ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $no = 1;
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $no++, $row['fullname'], $row['email'], $row['whatsapp'], $row['les_program'], $row['created_at'], $row['status'], $row['notes']
        ]);
    }
    fclose($output);
    exit();
}

// Handle search
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$filter_program = isset($_GET['program']) ? $conn->real_escape_string($_GET['program']) : '';
$filter_status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';
$where = [];
if ($search) {
    $where[] = "(fullname LIKE '%$search%' OR email LIKE '%$search%' OR whatsapp LIKE '%$search%' OR les_program LIKE '%$search%')";
}
if ($filter_program) {
    $where[] = "les_program = '$filter_program'";
}
if ($filter_status) {
    $where[] = "status = '$filter_status'";
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$sql = "SELECT * FROM registrations $where_sql ORDER BY created_at DESC";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pendaftar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .container { max-width: 1100px; margin-top: 40px; }
        .table thead { background: #198754; color: #fff; }
        .export-btn { float: right; }
    </style>
</head>
<body>
<div class="container">
    <h2 class="mb-4">Daftar Pendaftar</h2>
    <form class="row mb-3" method="get">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Cari nama/email/WA/program..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-2">
            <select name="program" class="form-select">
                <option value="">Semua Program</option>
                <?php foreach ($programs as $prog): ?>
                    <option value="<?php echo htmlspecialchars($prog); ?>" <?php if ($filter_program === $prog) echo 'selected'; ?>><?php echo htmlspecialchars($prog); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">Semua Status</option>
                <?php foreach ($status_list as $stat): ?>
                    <option value="<?php echo htmlspecialchars($stat); ?>" <?php if ($filter_status === $stat) echo 'selected'; ?>><?php echo htmlspecialchars(ucfirst($stat)); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-success w-100">Cari/Filter</button>
        </div>
        <div class="col-md-3 text-end">
            <a href="?export=csv" class="btn btn-primary export-btn">Export ke CSV</a>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
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
                </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows > 0): $no = 1; while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['whatsapp']); ?></td>
                    <td><?php echo htmlspecialchars($row['les_program']); ?></td>
                    <td><?php echo date('d-m-Y H:i', strtotime($row['created_at'])); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td><?php echo htmlspecialchars($row['notes']); ?></td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="8" class="text-center">Belum ada data pendaftar.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
<?php $conn->close(); ?> 