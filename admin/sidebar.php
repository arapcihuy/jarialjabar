<?php
// Get current page filename
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<div class="sidebar d-flex flex-column">
    <div class="sidebar-header">
        <div class="d-flex align-items-center justify-content-center gap-3">
            <?php if (file_exists($_SERVER['DOCUMENT_ROOT'].'/logo-jari-aljabar.jpeg')): ?>
                <img src="/logo-jari-aljabar.jpeg" alt="Logo" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
            <?php elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/images/logo-jari-aljabar.jpeg')): ?>
                <img src="/images/logo-jari-aljabar.jpeg" alt="Logo" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
            <?php else: ?>
                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 40px; height: 40px;">JA</div>
            <?php endif; ?>
            <h5 class="mb-0 fw-bold">Jari Aljabar</h5>
        </div>
    </div>
    
    <div class="sidebar-menu flex-grow-1">
        <p class="text-uppercase text-muted small fw-bold mb-3 px-3 mt-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">Menu Utama</p>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'pendaftar.php' ? 'active' : ''; ?>" href="pendaftar.php">
                    <i class="bi bi-people-fill"></i> Pendaftar
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'programs.php' ? 'active' : ''; ?>" href="programs.php">
                    <i class="bi bi-journal-bookmark-fill"></i> Program
                </a>
            </li>
        </ul>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <p class="text-uppercase text-muted small fw-bold mb-3 px-3 mt-4" style="font-size: 0.75rem; letter-spacing: 0.05em;">Administrator</p>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'users.php' ? 'active' : ''; ?>" href="users.php">
                    <i class="bi bi-shield-lock-fill"></i> Users Management
                </a>
            </li>
        </ul>
        <?php endif; ?>
    </div>

    <div class="p-4 border-top border-secondary border-opacity-25">
        <a href="logout.php" class="nav-link text-danger bg-danger bg-opacity-10 justify-content-center">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</div>
