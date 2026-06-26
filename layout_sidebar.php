<?php
$role = $_SESSION['user']['role'];
$user_name_display = htmlspecialchars($_SESSION['user']['nama']);
$user_role_display = htmlspecialchars($_SESSION['user']['role'] . ' - ' . $_SESSION['user']['jabatan']);
$user_avatar = !empty($_SESSION['user']['foto']) ? htmlspecialchars($_SESSION['user']['foto']) : 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($_SESSION['user']['nama']);

// Routing dinamis menu
$dashboard_link = ($role === 'Admin') ? 'admin_dashboard.php' : 'karyawan_dashboard.php';
$laporan_link = ($role === 'Admin') ? 'admin_laporan.php' : 'karyawan_laporan.php';
?>

<!-- Sidebar -->
<div id="sidebar">
    <div>
        <!-- Logo Area -->
        <div class="logo-area">
            <div class="logo-icon">
                <i class="fa-solid fa-plane-departure"></i>
            </div>
            <h1 class="app-name">SI CUTI KARYAWAN</h1>
        </div>

        <!-- Navigation Menu -->
        <ul class="menu-list">
            <li class="menu-item <?= ($active_menu === 'dashboard') ? 'active' : '' ?>">
                <a href="<?= $dashboard_link ?>" class="menu-link">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="menu-item <?= ($active_menu === 'ajukan') ? 'active' : '' ?>">
                <a href="karyawan_ajukan.php" class="menu-link">
                    <i class="fa-solid fa-file-signature"></i>
                    <span>Pengajuan Cuti</span>
                </a>
            </li>
            <li class="menu-item <?= ($active_menu === 'riwayat') ? 'active' : '' ?>">
                <a href="karyawan_riwayat.php" class="menu-link">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Riwayat Cuti</span>
                </a>
            </li>
            <li class="menu-item <?= ($active_menu === 'laporan') ? 'active' : '' ?>">
                <a href="<?= $laporan_link ?>" class="menu-link">
                    <i class="fa-solid fa-file-invoice"></i>
                    <span>Laporan</span>
                </a>
            </li>
            <li class="menu-item <?= ($active_menu === 'pengaturan') ? 'active' : '' ?>">
                <a href="pengaturan.php" class="menu-link">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Pengaturan</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="logout.php" class="menu-link text-danger">
                    <i class="fa-solid fa-right-from-bracket text-danger"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- User Profile Area in Sidebar -->
    <div class="user-preview">
        <img src="<?= $user_avatar ?>" alt="Avatar">
        <div class="user-info">
            <h6 class="user-name"><?= $user_name_display ?></h6>
            <p class="user-role"><?= $user_role_display ?></p>
        </div>
    </div>
</div>

<!-- Main content wraps around the sidebar -->
<div id="content-wrapper">
