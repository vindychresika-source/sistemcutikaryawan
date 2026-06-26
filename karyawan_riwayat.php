<?php
require_once 'config.php';

// Halaman ini diakses oleh user yang sudah login (baik Admin maupun Karyawan dapat melihat riwayat mereka sendiri)
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$role = $_SESSION['user']['role'];

// Ambil riwayat pengajuan cuti milik user yang login
$stmt = $db->prepare("SELECT * FROM cuti WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$riwayat = $stmt->fetchAll();

// Set metadata layout
$page_title = 'Riwayat Pengajuan Cuti';
$active_menu = 'riwayat';

require_once 'layout_header.php';
require_once 'layout_sidebar.php';
?>

<div class="container-fluid p-0">
    <!-- Header Halaman -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="m-0 fw-bold text-main">Riwayat Pengajuan Cuti</h3>
            <p class="text-muted small m-0">Menampilkan daftar seluruh permohonan cuti yang telah Anda ajukan.</p>
        </div>
        <a href="karyawan_ajukan.php" class="btn btn-purple"><i class="fa-solid fa-plus me-2"></i>Buat Pengajuan Baru</a>
    </div>

    <!-- Riwayat Table Card -->
    <div class="card custom-card">
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="table custom-table align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 80px;">No</th>
                            <th>Jenis Cuti</th>
                            <th>Tanggal Pelaksanaan</th>
                            <th class="text-center">Durasi</th>
                            <th>Alasan Pengajuan</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($riwayat)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-regular fa-folder-open d-block fs-1 mb-3 text-light-purple"></i>
                                    Belum ada riwayat pengajuan cuti yang tercatat.
                                </td>
                            </tr>
                        <?php else: 
                            $no = 1;
                            foreach ($riwayat as $r): 
                                $statusClass = ($r['status'] == 'Disetujui') ? 'status-disetujui' : (($r['status'] == 'Ditolak') ? 'status-ditolak' : 'status-pending');
                                $statusIcon = ($r['status'] == 'Disetujui') ? 'fa-circle-check' : (($r['status'] == 'Ditolak') ? 'fa-circle-xmark' : 'fa-clock');
                                $statusBadgeIconColor = ($r['status'] == 'Disetujui') ? 'text-success' : (($r['status'] == 'Ditolak') ? 'text-danger' : 'text-warning');
                        ?>
                            <tr>
                                <td class="text-center fw-bold text-muted"><?= $no++ ?></td>
                                <td>
                                    <span class="fw-bold text-main"><?= htmlspecialchars($r['jenis_cuti']) ?></span>
                                    <br>
                                    <small class="text-muted" style="font-size: 0.75rem;">Diajukan: <?= date('d/m/Y', strtotime($r['tanggal_pengajuan'])) ?></small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fa-regular fa-calendar text-muted me-2" style="font-size: 0.85rem;"></i>
                                        <span>
                                            <?= date('d M Y', strtotime($r['tanggal_mulai'])) ?> 
                                            <i class="fa-solid fa-arrow-right mx-2 text-muted" style="font-size: 0.75rem;"></i>
                                            <?= date('d M Y', strtotime($r['tanggal_selesai'])) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center fw-semibold text-main"><?= $r['jumlah_hari'] ?> Hari</td>
                                <td>
                                    <div class="text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($r['alasan']) ?>">
                                        <?= htmlspecialchars($r['alasan']) ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="status-badge <?= $statusClass ?>">
                                        <i class="fa-solid <?= $statusIcon ?> <?= $statusBadgeIconColor ?>"></i><?= htmlspecialchars($r['status']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="detail_pengajuan.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary px-3 rounded-pill" style="font-size: 0.8rem; border-color: var(--primary-purple); color: var(--dark-purple);">
                                        <i class="fa-solid fa-eye me-1"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'layout_footer.php';
?>
