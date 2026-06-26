<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title : 'Sistem Informasi Cuti' ?> - SI Cuti Karyawan</title>
    
    <!-- Google Fonts: Poppins & Playfair Display -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom CSS Styling -->
    <style>
        :root {
            --primary-purple: #B9A7FF;
            --dark-purple: #6A50E5;
            --hover-purple: #553BBF;
            --light-purple: #EBE6FF;
            --bg-gray: #F4F3F8;
            --text-main: #2C1D54;
            --text-dark: #333333;
            --text-muted: #7E7C8C;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-gray);
            color: var(--text-dark);
            min-height: 100vh;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6, .display-font {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            color: var(--text-main);
        }
        
        .playfair-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
        }

        /* Sidebar Styling */
        #sidebar {
            width: 280px;
            background-color: var(--primary-purple);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1000;
            padding: 30px 20px;
            transition: all 0.3s ease;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        #sidebar .logo-area {
            text-align: center;
            margin-bottom: 40px;
        }

        #sidebar .logo-icon {
            font-size: 2.2rem;
            color: var(--dark-purple);
            margin-bottom: 10px;
        }

        #sidebar .app-name {
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: var(--text-main);
            margin: 0;
        }

        #sidebar .menu-list {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
        }

        #sidebar .menu-item {
            margin-bottom: 8px;
        }

        #sidebar .menu-link {
            display: flex;
            align-items: center;
            padding: 12px 18px;
            color: var(--text-main);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            border-radius: 12px;
            transition: all 0.2s ease;
        }

        #sidebar .menu-link i {
            margin-right: 15px;
            font-size: 1.15rem;
            width: 20px;
            text-align: center;
            color: var(--dark-purple);
            transition: all 0.2s ease;
        }

        #sidebar .menu-link:hover {
            background-color: rgba(255, 255, 255, 0.4);
            color: var(--dark-purple);
        }

        #sidebar .menu-item.active .menu-link {
            background-color: var(--dark-purple);
            color: #ffffff;
        }

        #sidebar .menu-item.active .menu-link i {
            color: #ffffff;
        }

        #sidebar .user-preview {
            background-color: rgba(255, 255, 255, 0.5);
            padding: 15px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            margin-top: auto;
        }

        #sidebar .user-preview img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--dark-purple);
            margin-right: 12px;
        }

        #sidebar .user-preview .user-info {
            line-height: 1.2;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #sidebar .user-preview .user-name {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--text-main);
            margin: 0 0 2px 0;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }

        #sidebar .user-preview .user-role {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 500;
            margin: 0;
        }

        /* Main Content Styling */
        #content-wrapper {
            margin-left: 280px;
            padding: 40px;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        /* Mobile navbar toggle */
        #mobile-header {
            display: none;
            background-color: #ffffff;
            padding: 15px 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 999;
            align-items: center;
            justify-content: space-between;
        }

        /* Card and Elements Design */
        .custom-card {
            background: #ffffff;
            border-radius: 18px;
            border: none;
            box-shadow: 0 5px 25px rgba(185, 167, 255, 0.15);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-bottom: 24px;
        }

        .custom-card.hover-effect:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(185, 167, 255, 0.25);
        }

        .custom-card .card-header-custom {
            padding: 24px 24px 0 24px;
            background: none;
            border: none;
        }

        .custom-card .card-body-custom {
            padding: 24px;
        }

        /* Buttons Styling */
        .btn-purple {
            background-color: var(--dark-purple);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 10px 24px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-purple:hover {
            background-color: var(--hover-purple);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(106, 80, 229, 0.3);
        }

        .btn-cancel {
            background-color: #ffffff;
            color: var(--text-muted);
            border: 1px solid #E2E0EC;
            border-radius: 12px;
            padding: 10px 24px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-cancel:hover {
            background-color: var(--bg-gray);
            color: var(--text-dark);
        }

        /* Stats Cards */
        .stat-card {
            border-radius: 18px;
            border: none;
            box-shadow: 0 5px 25px rgba(185, 167, 255, 0.12);
            transition: all 0.3s ease;
            color: #ffffff;
            padding: 24px;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 130px;
        }

        .stat-card .stat-icon {
            position: absolute;
            right: 20px;
            bottom: 10px;
            font-size: 4rem;
            opacity: 0.15;
        }

        .stat-card .stat-value {
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1;
            margin: 10px 0 0 0;
        }

        .stat-card .stat-label {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
            margin: 0;
        }

        /* Stat Card Colors */
        .bg-gradient-total {
            background: linear-gradient(135deg, #8C76FF, #A694FF);
        }

        .bg-gradient-disetujui {
            background: linear-gradient(135deg, #10B981, #34D399);
        }

        .bg-gradient-ditolak {
            background: linear-gradient(135deg, #EF4444, #F87171);
        }

        .bg-gradient-pending {
            background: linear-gradient(135deg, #F59E0B, #FBBF24);
        }

        /* Table Custom Styling */
        .table-container {
            background: #ffffff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(185, 167, 255, 0.12);
        }

        .custom-table {
            margin-bottom: 0;
        }

        .custom-table thead {
            background-color: #F1EFF7;
        }

        .custom-table th {
            color: var(--text-main);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            padding: 18px 24px;
            border-bottom: 1px solid #EBE9F3;
        }

        .custom-table td {
            padding: 16px 24px;
            color: var(--text-dark);
            vertical-align: middle;
            border-bottom: 1px solid #F1EFF7;
            font-size: 0.9rem;
        }

        .custom-table tr:last-child td {
            border-bottom: none;
        }

        /* Status Badge Styling */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .status-badge i {
            margin-right: 6px;
            font-size: 0.85rem;
        }

        .status-pending {
            background-color: #FFFBEB;
            color: #D97706;
        }

        .status-disetujui {
            background-color: #ECFDF5;
            color: #059669;
        }

        .status-ditolak {
            background-color: #FEF2F2;
            color: #DC2626;
        }

        /* Profile Welcome Card */
        .welcome-card {
            background: linear-gradient(135deg, #6A50E5, #9B86FF);
            color: #ffffff;
            border-radius: 20px;
            padding: 30px;
            position: relative;
            overflow: hidden;
            margin-bottom: 30px;
        }

        .welcome-card h2 {
            color: #ffffff;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .welcome-card p {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 0;
            max-width: 60%;
        }

        .welcome-card .welcome-img {
            position: absolute;
            right: 40px;
            bottom: -20px;
            height: 160px;
            opacity: 0.85;
            display: block;
        }

        /* Responsive Breakpoints */
        @media (max-width: 991.98px) {
            #sidebar {
                left: -280px;
            }
            #sidebar.active {
                left: 0;
            }
            #content-wrapper {
                margin-left: 0;
                padding: 20px;
            }
            #mobile-header {
                display: flex;
            }
            .welcome-card p {
                max-width: 100%;
            }
            .welcome-card .welcome-img {
                display: none;
            }
        }
    </style>
</head>
<body>

    <!-- Mobile Header -->
    <div id="mobile-header">
        <div class="d-flex align-items-center">
            <button id="sidebarToggle" class="btn btn-outline-secondary me-3" style="border-color: #E2E0EC;">
                <i class="fa-solid fa-bars"></i>
            </button>
            <span class="fw-bold text-main fs-5"><i class="fa-solid fa-plane-departure text-purple me-2"></i>SI Cuti</span>
        </div>
        <div class="d-flex align-items-center">
            <span class="small fw-semibold text-muted me-2"><?= htmlspecialchars($user['nama']) ?></span>
            <img src="<?= !empty($user['foto']) ? htmlspecialchars($user['foto']) : 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($user['nama']) ?>" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
        </div>
    </div>
