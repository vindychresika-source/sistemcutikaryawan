<?php
require_once 'config.php';
cekLogin('Admin');

$tgl_awal = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : '';
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : '';
$jenis_cuti = isset($_GET['jenis_cuti']) ? $_GET['jenis_cuti'] : '';
$cetak = isset($_GET['cetak']) ? true : false;

// Query pembentukan data laporan
$query_str = "SELECT cuti.*, users.nama, users.jabatan, users.email 
              FROM cuti 
              JOIN users ON cuti.user_id = users.id 
              WHERE 1=1";
$params = [];

if ($tgl_awal != '' && $tgl_akhir != '') {
    $query_str .= " AND cuti.tanggal_pengajuan BETWEEN ? AND ?";
    $params[] = $tgl_awal;
    $params[] = $tgl_akhir;
}
if ($jenis_cuti != '') {
    $query_str .= " AND cuti.jenis_cuti = ?";
    $params[] = $jenis_cuti;
}

$query_str .= " ORDER BY cuti.id ASC";
$stmt = $db->prepare($query_str);
$stmt->execute($params);
$laporan = $stmt->fetchAll();

// Hitung rekapitulasi data terpilih
$rekap_total = count($laporan);
$rekap_setuju = 0;
$rekap_tolak = 0;
$rekap_pending = 0;

foreach ($laporan as $l) {
    if ($l['status'] === 'Disetujui') $rekap_setuju++;
    elseif ($l['status'] === 'Ditolak') $rekap_tolak++;
    elseif ($l['status'] === 'Pending') $rekap_pending++;
}

// Fitur Cetak
if ($cetak) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Laporan Rekapitulasi Cuti Karyawan</title>
        <style>
            body { font-family: 'Times New Roman', Times, serif; padding: 40px; color: #000000; line-height: 1.4; }
            .header { text-align: center; margin-bottom: 30px; border-bottom: 3px double #000000; padding-bottom: 15px; }
            .header h1 { margin: 0 0 5px 0; font-size: 20pt; font-weight: bold; }
            .header p { margin: 0; font-size: 11pt; font-style: italic; }
            .title { text-align: center; margin-bottom: 25px; }
            .title h2 { margin: 0 0 5px 0; font-size: 14pt; text-transform: uppercase; }
            .title p { margin: 0; font-size: 10pt; }
            .rekap-box { display: flex; justify-content: space-around; border: 1px solid #000; padding: 12px; margin-bottom: 25px; font-weight: bold; background-color: #f5f5f5; }
            table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 10pt; }
            th, td { border: 1px solid #000000; padding: 8px 10px; text-align: left; }
            th { background-color: #e5e5e5; text-align: center; text-transform: uppercase; font-weight: bold; }
            .text-center { text-align: center; }
            .footer-sign { margin-top: 50px; display: flex; justify-content: flex-end; }
            .sign-box { text-align: center; width: 250px; }
            .sign-space { height: 75px; }
            @media print {
                body { padding: 0; }
                .no-print { display: none; }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>PT MULTI DIGITAL NUSANTARA</h1>
            <p>Gedung Technopreneur Lt. 4, Jl. Kebon Jeruk No. 12, Jakarta Barat | Telp: (021) 555-8799</p>
        </div>

        <div class="title">
            <h2>Laporan Rekapitulasi Pengajuan Cuti Karyawan</h2>
            <?php if($tgl_awal && $tgl_akhir): ?>
                <p>Periode Pengajuan: <strong><?= date('d M Y', strtotime($tgl_awal)) ?></strong> s.d. <strong><?= date('d M Y', strtotime($tgl_akhir)) ?></strong></p>
            <?php else: ?>
                <p>Periode Pengajuan: <strong>Semua Periode</strong></p>
            <?php endif; ?>
            <?php if($jenis_cuti): ?>
                <p>Kategori Cuti: <strong><?= htmlspecialchars($jenis_cuti) ?></strong></p>
            <?php endif; ?>
        </div>

        <div class="rekap-box">
            <span>TOTAL PENGAJUAN: <?= $rekap_total ?></span>
            <span>DISETUJUI: <?= $rekap_setuju ?></span>
            <span>DITOLAK: <?= $rekap_tolak ?></span>
            <span>PENDING: <?= $rekap_pending ?></span>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">No</th>
                    <th>Nama Karyawan</th>
                    <th>Jabatan</th>
                    <th>Jenis Cuti</th>
                    <th style="width: 180px;">Tanggal Cuti</th>
                    <th style="width: 70px;">Durasi</th>
                    <th style="width: 100px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($laporan)): ?>
                    <tr><td colspan="7" class="text-center">Tidak ada data laporan yang sesuai filter.</td></tr>
                <?php else: 
                    $no = 1;
                    foreach($laporan as $l): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td><?= htmlspecialchars($l['nama']) ?></td>
                        <td><?= htmlspecialchars($l['jabatan']) ?></td>
                        <td><?= htmlspecialchars($l['jenis_cuti']) ?></td>
                        <td class="text-center"><?= date('d/m/y', strtotime($l['tanggal_mulai'])) ?> - <?= date('d/m/y', strtotime($l['tanggal_selesai'])) ?></td>
                        <td class="text-center"><?= $l['jumlah_hari'] ?> Hari</td>
                        <td class="text-center" style="font-weight: bold;"><?= $l['status'] ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>

        <div class="footer-sign">
            <div class="sign-box">
                <p>Jakarta, <?= date('d F Y') ?></p>
                <p>HR Department</p>
                <div class="sign-space"></div>
                <p style="text-decoration: underline; font-weight: bold;">Praditya Admin Utama</p>
                <p>HR Manager</p>
            </div>
        </div>

        <script>window.print();</script>
    </body>
    </html>
    <?php
    exit;
}

// Meta layout
$page_title = 'Laporan Rekapitulasi Cuti';
$active_menu = 'laporan';

require_once 'layout_header.php';
require_once 'layout_sidebar.php';
?>

<div class="container-fluid p-0">
    <!-- Header Halaman -->
    <div class="mb-4">
        <h3 class="m-0 fw-bold text-main">Laporan Pengajuan Cuti</h3>
        <p class="text-muted small m-0">Menyaring rekapitulasi data cuti karyawan dan mencetak laporan dalam format fisik/PDF.</p>
    </div>

    <!-- Filter & Cetak Card -->
    <div class="card custom-card">
        <div class="card-body-custom">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Jenis Cuti</label>
                    <select name="jenis_cuti" class="form-select py-2 rounded-3" style="border-color: #E2E0EC;">
                        <option value="">-- Semua Jenis Cuti --</option>
                        <option value="Cuti Tahunan" <?= $jenis_cuti == 'Cuti Tahunan' ? 'selected' : '' ?>>Cuti Tahunan</option>
                        <option value="Sakit" <?= $jenis_cuti == 'Sakit' ? 'selected' : '' ?>>Sakit</option>
                        <option value="Izin Penting" <?= $jenis_cuti == 'Izin Penting' ? 'selected' : '' ?>>Izin Penting</option>
                        <option value="Cuti Melahirkan" <?= $jenis_cuti == 'Cuti Melahirkan' ? 'selected' : '' ?>>Cuti Melahirkan</option>
                        <option value="Cuti Khusus" <?= $jenis_cuti == 'Cuti Khusus' ? 'selected' : '' ?>>Cuti Khusus / Dispensasi</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="tgl_awal" class="form-control py-2 rounded-3" value="<?= htmlspecialchars($tgl_awal) ?>" style="border-color: #E2E0EC;">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="tgl_akhir" class="form-control py-2 rounded-3" value="<?= htmlspecialchars($tgl_akhir) ?>" style="border-color: #E2E0EC;">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-purple w-50 py-2"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                    <a href="admin_laporan.php?tgl_awal=<?= urlencode($tgl_awal) ?>&tgl_akhir=<?= urlencode($tgl_akhir) ?>&jenis_cuti=<?= urlencode($jenis_cuti) ?>&cetak=true" target="_blank" class="btn btn-cancel w-50 py-2" style="border-color: var(--primary-purple); color: var(--dark-purple);">
                        <i class="fa-solid fa-print me-1"></i> Cetak PDF
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Rekapitulasi Statistik Box -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="bg-white p-3 rounded-4 shadow-sm text-center border-start border-4 border-primary">
                <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Total Cuti Diajukan</small>
                <h4 class="m-0 fw-bold text-main"><?= $rekap_total ?> Pengajuan</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="bg-white p-3 rounded-4 shadow-sm text-center border-start border-4 border-success">
                <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Cuti Disetujui</small>
                <h4 class="m-0 fw-bold text-success"><?= $rekap_setuju ?> Disetujui</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="bg-white p-3 rounded-4 shadow-sm text-center border-start border-4 border-danger">
                <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Cuti Ditolak</small>
                <h4 class="m-0 fw-bold text-danger"><?= $rekap_tolak ?> Ditolak</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="bg-white p-3 rounded-4 shadow-sm text-center border-start border-4 border-warning">
                <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Cuti Pending</small>
                <h4 class="m-0 fw-bold text-warning"><?= $rekap_pending ?> Pending</h4>
            </div>
        </div>
    </div>

    <!-- Data Laporan Table -->
    <div class="card custom-card">
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="table custom-table align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 70px;">No</th>
                            <th>Karyawan</th>
                            <th>Jenis Cuti</th>
                            <th>Tanggal Pelaksanaan</th>
                            <th class="text-center">Durasi</th>
                            <th>Alasan</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($laporan)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">Belum ada data pengajuan dalam laporan. Silakan atur filter pencarian.</td>
                            </tr>
                        <?php else: 
                            $no = 1;
                            foreach ($laporan as $l): 
                                $statusClass = ($l['status'] == 'Disetujui') ? 'status-disetujui' : (($l['status'] == 'Ditolak') ? 'status-ditolak' : 'status-pending');
                                $statusIcon = ($l['status'] == 'Disetujui') ? 'fa-circle-check' : (($l['status'] == 'Ditolak') ? 'fa-circle-xmark' : 'fa-clock');
                        ?>
                            <tr>
                                <td class="text-center fw-bold text-muted"><?= $no++ ?></td>
                                <td>
                                    <div class="fw-bold text-main" style="font-size: 0.9rem;"><?= htmlspecialchars($l['nama']) ?></div>
                                    <small class="text-muted text-uppercase" style="font-size: 0.7rem;"><?= htmlspecialchars($l['jabatan']) ?></small>
                                </td>
                                <td>
                                    <span class="fw-semibold text-main" style="font-size: 0.85rem;"><?= htmlspecialchars($l['jenis_cuti']) ?></span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d M Y', strtotime($l['tanggal_mulai'])) ?> - <?= date('d M Y', strtotime($l['tanggal_selesai'])) ?>
                                    </small>
                                </td>
                                <td class="text-center fw-bold text-main" style="font-size: 0.85rem;"><?= $l['jumlah_hari'] ?> Hari</td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($l['alasan']) ?>">
                                        <?= htmlspecialchars($l['alasan']) ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="status-badge <?= $statusClass ?>" style="padding: 4px 10px; font-size: 0.75rem;">
                                        <i class="fa-solid <?= $statusIcon ?>"></i><?= htmlspecialchars($l['status']) ?>
                                    </span>
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