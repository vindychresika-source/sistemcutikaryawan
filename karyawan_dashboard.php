<?php
require_once 'config.php';
cekLogin('Karyawan');

$user_id = $_SESSION['user']['id'];
$user_nama = $_SESSION['user']['nama'];

// Ambil info kuota cuti terupdate
$stmt_u = $db->prepare("SELECT kuota_cuti, jabatan FROM users WHERE id = ?");
$stmt_u->execute([$user_id]);
$user_data = $stmt_u->fetch();
$kuota = $user_data['kuota_cuti'];

// Hitung statistik cuti karyawan
$total_diajukan = $db->query("SELECT COUNT(*) FROM cuti WHERE user_id = $user_id")->fetchColumn();
$disetujui = $db->query("SELECT COUNT(*) FROM cuti WHERE user_id = $user_id AND status = 'Disetujui'")->fetchColumn();
$ditolak = $db->query("SELECT COUNT(*) FROM cuti WHERE user_id = $user_id AND status = 'Ditolak'")->fetchColumn();
$pending = $db->query("SELECT COUNT(*) FROM cuti WHERE user_id = $user_id AND status = 'Pending'")->fetchColumn();

// Ambil 5 pengajuan terbaru
$stmt_recent = $db->prepare("SELECT * FROM cuti WHERE user_id = ? ORDER BY id DESC LIMIT 5");
$stmt_recent->execute([$user_id]);
$recent_leaves = $stmt_recent->fetchAll();

// Data untuk Chart.js: Jumlah hari cuti disetujui per bulan di tahun ini
$current_year = date('Y');
$monthly_data = array_fill(1, 12, 0);

$stmt_chart = $db->prepare("SELECT tanggal_mulai, jumlah_hari FROM cuti WHERE user_id = ? AND status = 'Disetujui' AND YEAR(tanggal_mulai) = ?");
$stmt_chart->execute([$user_id, $current_year]);
$chart_leaves = $stmt_chart->fetchAll();

foreach ($chart_leaves as $l) {
    $month = (int)date('m', strtotime($l['tanggal_mulai']));
    if ($month >= 1 && $month <= 12) {
        $monthly_data[$month] += $l['jumlah_hari'];
    }
}

// Convert monthly data to simple JS array representation
$chart_js_values = implode(',', array_values($monthly_data));

// Set page title and active sidebar menu
$page_title = 'Dashboard Karyawan';
$active_menu = 'dashboard';

require_once 'layout_header.php';
require_once 'layout_sidebar.php';
?>

<!-- Halaman Dashboard Konten Utama -->
<div class="container-fluid p-0">
    <!-- Kartu Sambutan -->
    <div class="welcome-card">
        <div>
            <h2>Selamat Datang Kembali, <?= htmlspecialchars($user_nama) ?>!</h2>
            <p>Anda saat ini menjabat sebagai <strong><?= htmlspecialchars($user_data['jabatan']) ?></strong>. Anda memiliki sisa kuota cuti tahunan sebanyak <strong><?= $kuota ?> Hari</strong> untuk tahun ini. Gunakan hak cuti Anda dengan bijak!</p>
        </div>
        <!-- SVG Vector Karyawan Kantor Minimalis (Inline) -->
        <svg class="welcome-img" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="100" cy="100" r="80" fill="#ffffff" fill-opacity="0.12"/>
            <rect x="70" y="80" width="60" height="70" rx="10" fill="#EBE6FF"/>
            <circle cx="100" cy="55" r="22" fill="#EBE6FF"/>
            <path d="M60 150C60 120 80 110 100 110C120 110 140 120 140 150" fill="#6A50E5"/>
            <rect x="85" y="90" width="30" height="20" rx="3" fill="#B9A7FF"/>
            <path d="M90 100L97 105L110 95" stroke="#ffffff" stroke-width="3" stroke-linecap="round"/>
        </svg>
    </div>

    <!-- Statistik Kartu -->
    <div class="row g-4 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card bg-gradient-total">
                <div>
                    <h5 class="stat-label">Cuti Diajukan</h5>
                    <h2 class="stat-value"><?= $total_diajukan ?></h2>
                </div>
                <div class="stat-icon"><i class="fa-solid fa-paper-plane"></i></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card bg-gradient-disetujui">
                <div>
                    <h5 class="stat-label">Cuti Disetujui</h5>
                    <h2 class="stat-value"><?= $disetujui ?></h2>
                </div>
                <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card bg-gradient-ditolak">
                <div>
                    <h5 class="stat-label">Cuti Ditolak</h5>
                    <h2 class="stat-value"><?= $ditolak ?></h2>
                </div>
                <div class="stat-icon"><i class="fa-solid fa-circle-xmark"></i></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card bg-gradient-pending">
                <div>
                    <h5 class="stat-label">Cuti Pending</h5>
                    <h2 class="stat-value"><?= $pending ?></h2>
                </div>
                <div class="stat-icon"><i class="fa-solid fa-clock"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Grafik Penggunaan Cuti Tahunan -->
        <div class="col-12 col-xl-6 mb-4">
            <div class="card custom-card h-100">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold"><i class="fa-solid fa-chart-bar text-purple me-2"></i>Penggunaan Hari Cuti disetujui (<?= $current_year ?>)</h5>
                </div>
                <div class="card-body-custom">
                    <canvas id="chartTahunan" style="min-height: 250px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Daftar Pengajuan Terbaru -->
        <div class="col-12 col-xl-6 mb-4">
            <div class="card custom-card h-100">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold"><i class="fa-solid fa-list-check text-purple me-2"></i>Daftar Pengajuan Terbaru</h5>
                    <a href="karyawan_riwayat.php" class="btn btn-sm btn-outline-primary rounded-pill px-3" style="font-size: 0.8rem; border-color: var(--primary-purple); color: var(--dark-purple);">Lihat Semua</a>
                </div>
                <div class="card-body-custom p-0 table-responsive" style="max-height: 320px; overflow-y: auto;">
                    <table class="table align-middle table-hover mb-0">
                        <thead class="sticky-top bg-light" style="z-index: 1;">
                            <tr>
                                <th class="border-0 px-4 py-3 text-muted fw-bold" style="font-size: 0.75rem;">JENIS</th>
                                <th class="border-0 px-4 py-3 text-muted fw-bold" style="font-size: 0.75rem;">TANGGAL</th>
                                <th class="border-0 px-4 py-3 text-muted fw-bold text-center" style="font-size: 0.75rem;">DURASI</th>
                                <th class="border-0 px-4 py-3 text-muted fw-bold text-end" style="font-size: 0.75rem;">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_leaves)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Belum ada pengajuan cuti.</td>
                                </tr>
                            <?php else: foreach($recent_leaves as $r): 
                                $statusClass = ($r['status'] == 'Disetujui') ? 'status-disetujui' : (($r['status'] == 'Ditolak') ? 'status-ditolak' : 'status-pending');
                                $statusIcon = ($r['status'] == 'Disetujui') ? 'fa-circle-check' : (($r['status'] == 'Ditolak') ? 'fa-circle-xmark' : 'fa-clock');
                            ?>
                                <tr onclick="window.location='detail_pengajuan.php?id=<?= $r['id'] ?>'" style="cursor: pointer;">
                                    <td class="px-4 py-3">
                                        <span class="fw-bold text-main" style="font-size: 0.9rem;"><?= htmlspecialchars($r['jenis_cuti']) ?></span>
                                    </td>
                                    <td class="px-4 py-3 text-muted" style="font-size: 0.85rem;">
                                        <?= date('d M Y', strtotime($r['tanggal_mulai'])) ?>
                                    </td>
                                    <td class="px-4 py-3 text-center fw-semibold text-main" style="font-size: 0.85rem;">
                                        <?= $r['jumlah_hari'] ?> Hari
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <span class="status-badge <?= $statusClass ?>">
                                            <i class="fa-solid <?= $statusIcon ?>"></i><?= htmlspecialchars($r['status']) ?>
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
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('chartTahunan').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [{
                label: 'Hari Cuti',
                data: [<?= $chart_js_values ?>],
                backgroundColor: 'rgba(106, 80, 229, 0.7)',
                borderColor: 'rgba(106, 80, 229, 1)',
                borderWidth: 1.5,
                borderRadius: 6,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        color: '#7E7C8C',
                        font: {
                            family: 'Poppins',
                            size: 11
                        }
                    },
                    grid: {
                        color: '#EFEFFA'
                    }
                },
                x: {
                    ticks: {
                        color: '#7E7C8C',
                        font: {
                            family: 'Poppins',
                            size: 11
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>

<?php
require_once 'layout_footer.php';
?>