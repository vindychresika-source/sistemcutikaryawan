<?php
require_once 'config.php';
cekLogin('Admin');

// Handle Aksi Setujui/Tolak
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $id_cuti = (int)$_POST['id_cuti'];
    $action = $_POST['action']; // 'Setujui' atau 'Tolak'
    $catatan = isset($_POST['catatan_admin']) ? trim($_POST['catatan_admin']) : '';
    
    $stmt_c = $db->prepare("SELECT * FROM cuti WHERE id = ?");
    $stmt_c->execute([$id_cuti]);
    $cuti_info = $stmt_c->fetch();
    
    if ($cuti_info && $cuti_info['status'] === 'Pending') {
        $db->beginTransaction();
        try {
            $status_baru = ($action === 'Setujui') ? 'Disetujui' : 'Ditolak';
            
            // Update status cuti
            $stmt_u = $db->prepare("UPDATE cuti SET status = ?, catatan_admin = ? WHERE id = ?");
            $stmt_u->execute([$status_baru, $catatan, $id_cuti]);
            
            // Jika disetujui & berjenis 'Cuti Tahunan', kurangi kuota cuti karyawan
            if ($status_baru === 'Disetujui' && $cuti_info['jenis_cuti'] === 'Cuti Tahunan') {
                $stmt_k = $db->prepare("UPDATE users SET kuota_cuti = GREATEST(0, kuota_cuti - ?) WHERE id = ?");
                $stmt_k->execute([$cuti_info['jumlah_hari'], $cuti_info['user_id']]);
            }
            
            $db->commit();
            $_SESSION['flash_msg'] = [
                'type' => 'success',
                'title' => 'Berhasil!',
                'text' => 'Status pengajuan cuti berhasil diperbarui.'
            ];
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['flash_msg'] = [
                'type' => 'error',
                'title' => 'Gagal!',
                'text' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }
    header("Location: admin_dashboard.php");
    exit;
}

// Statistik Ringkasan
$total_pengajuan = $db->query("SELECT COUNT(*) FROM cuti")->fetchColumn();
$pending = $db->query("SELECT COUNT(*) FROM cuti WHERE status = 'Pending'")->fetchColumn();
$setuju = $db->query("SELECT COUNT(*) FROM cuti WHERE status = 'Disetujui'")->fetchColumn();
$tolak = $db->query("SELECT COUNT(*) FROM cuti WHERE status = 'Ditolak'")->fetchColumn();

// Data Pie/Doughnut Chart: Jumlah Cuti per Jenis
$stmt_chart = $db->query("SELECT jenis_cuti, COUNT(*) as jml FROM cuti GROUP BY jenis_cuti");
$chart_data = $stmt_chart->fetchAll();
$chart_labels = [];
$chart_values = [];
foreach ($chart_data as $cd) {
    $chart_labels[] = $cd['jenis_cuti'];
    $chart_values[] = $cd['jml'];
}
$js_labels = json_encode($chart_labels);
$js_values = json_encode($chart_values);

// Fitur Pencarian, Filter & Pagination
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';

$where_clause = " WHERE 1=1";
$params = [];

if ($search != '') {
    $where_clause .= " AND (users.nama LIKE ? OR users.jabatan LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($filter_status != '') {
    $where_clause .= " AND cuti.status = ?";
    $params[] = $filter_status;
}

// Pagination Setup
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Hitung total data
$count_query = "SELECT COUNT(*) FROM cuti JOIN users ON cuti.user_id = users.id" . $where_clause;
$stmt_count = $db->prepare($count_query);
$stmt_count->execute($params);
$total_items = $stmt_count->fetchColumn();
$total_pages = ceil($total_items / $limit);

// Query Data Terpilih
$query_str = "SELECT cuti.*, users.nama, users.jabatan, users.foto 
              FROM cuti 
              JOIN users ON cuti.user_id = users.id" 
              . $where_clause . 
              " ORDER BY cuti.id DESC LIMIT $limit OFFSET $offset";
$stmt_list = $db->prepare($query_str);
$stmt_list->execute($params);
$daftar_cuti = $stmt_list->fetchAll();

// Meta layout
$page_title = 'Panel Kontrol Admin';
$active_menu = 'dashboard';

require_once 'layout_header.php';
require_once 'layout_sidebar.php';
?>

<div class="container-fluid p-0">
    <!-- Header Admin -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="m-0 fw-bold text-main">Dashboard HR & Kelola Cuti</h3>
            <p class="text-muted small m-0">Pantau statistik pengajuan cuti perusahaan dan lakukan verifikasi izin karyawan.</p>
        </div>
        <div class="bg-white px-3 py-2 rounded-4 shadow-sm" style="border: 1px solid var(--primary-purple);">
            <i class="fa-solid fa-user-tie text-purple me-1"></i>
            <span class="small fw-bold text-main">Mode Admin</span>
        </div>
    </div>

    <!-- Statistik Kartu -->
    <div class="row g-4 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card bg-gradient-total">
                <div>
                    <h5 class="stat-label">Total Pengajuan</h5>
                    <h2 class="stat-value"><?= $total_pengajuan ?></h2>
                </div>
                <div class="stat-icon"><i class="fa-solid fa-folder-open"></i></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card bg-gradient-pending">
                <div>
                    <h5 class="stat-label">Pending Verifikasi</h5>
                    <h2 class="stat-value"><?= $pending ?></h2>
                </div>
                <div class="stat-icon"><i class="fa-solid fa-clock"></i></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card bg-gradient-disetujui">
                <div>
                    <h5 class="stat-label">Telah Disetujui</h5>
                    <h2 class="stat-value"><?= $setuju ?></h2>
                </div>
                <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card bg-gradient-ditolak">
                <div>
                    <h5 class="stat-label">Telah Ditolak</h5>
                    <h2 class="stat-value"><?= $tolak ?></h2>
                </div>
                <div class="stat-icon"><i class="fa-solid fa-circle-xmark"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Visualisasi Status / Pie Chart -->
        <div class="col-12 col-xl-4 mb-4">
            <div class="card custom-card h-100">
                <div class="card-header-custom">
                    <h5 class="m-0 fw-bold"><i class="fa-solid fa-chart-pie text-purple me-2"></i>Jenis Cuti Diajukan</h5>
                </div>
                <div class="card-body-custom d-flex flex-column align-items-center justify-content-center" style="min-height: 280px;">
                    <?php if (empty($chart_labels)): ?>
                        <div class="text-center text-muted">Belum ada visualisasi jenis cuti.</div>
                    <?php else: ?>
                        <div style="position: relative; height:240px; width:100%">
                            <canvas id="chartJenisCuti"></canvas>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tabel Kelola Cuti Karyawan -->
        <div class="col-12 col-xl-8 mb-4">
            <div class="card custom-card h-100">
                <div class="card-header-custom pb-3">
                    <h5 class="m-0 fw-bold mb-3"><i class="fa-solid fa-list-check text-purple me-2"></i>Daftar Pengajuan Cuti</h5>
                    
                    <!-- Search & Filter Form -->
                    <form method="GET" action="" class="row g-2">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 rounded-start-3" style="border-color: #E2E0EC;"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0 rounded-end-3" placeholder="Cari karyawan / jabatan..." value="<?= htmlspecialchars($search) ?>" style="border-color: #E2E0EC;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="status" class="form-select rounded-3" style="border-color: #E2E0EC;">
                                <option value="">-- Semua Status --</option>
                                <option value="Pending" <?= $filter_status == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Disetujui" <?= $filter_status == 'Disetujui' ? 'selected' : '' ?>>Disetujui</option>
                                <option value="Ditolak" <?= $filter_status == 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-purple w-100 rounded-3"><i class="fa-solid fa-filter"></i> Filter</button>
                        </div>
                    </form>
                </div>
                
                <div class="card-body-custom p-0 table-responsive">
                    <table class="table custom-table align-middle table-hover">
                        <thead>
                            <tr>
                                <th>Karyawan</th>
                                <th>Jenis</th>
                                <th>Tanggal Pelaksanaan</th>
                                <th class="text-center">Durasi</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" style="min-width: 180px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($daftar_cuti)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">Data pengajuan cuti tidak ditemukan.</td>
                                </tr>
                            <?php else: foreach ($daftar_cuti as $d): 
                                $statusClass = ($d['status'] == 'Disetujui') ? 'status-disetujui' : (($d['status'] == 'Ditolak') ? 'status-ditolak' : 'status-pending');
                                $statusIcon = ($d['status'] == 'Disetujui') ? 'fa-circle-check' : (($d['status'] == 'Ditolak') ? 'fa-circle-xmark' : 'fa-clock');
                                $emp_avatar = !empty($d['foto']) ? htmlspecialchars($d['foto']) : 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($d['nama']);
                            ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?= $emp_avatar ?>" class="rounded-circle me-3" style="width: 40px; height: 40px; object-fit: cover; border: 1.5px solid var(--primary-purple);">
                                            <div>
                                                <div class="fw-bold text-main" style="line-height:1.2; font-size: 0.9rem;"><?= htmlspecialchars($d['nama']) ?></div>
                                                <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;"><?= htmlspecialchars($d['jabatan']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-main" style="font-size: 0.85rem;"><?= htmlspecialchars($d['jenis_cuti']) ?></span>
                                    </td>
                                    <td>
                                        <small class="text-muted d-block" style="font-size: 0.8rem;">
                                            <?= date('d/m/y', strtotime($d['tanggal_mulai'])) ?> - <?= date('d/m/y', strtotime($d['tanggal_selesai'])) ?>
                                        </small>
                                    </td>
                                    <td class="text-center fw-bold text-main" style="font-size: 0.85rem;"><?= $d['jumlah_hari'] ?> Hari</td>
                                    <td class="text-center">
                                        <span class="status-badge <?= $statusClass ?>" style="padding: 4px 10px; font-size: 0.75rem;">
                                            <i class="fa-solid <?= $statusIcon ?>"></i><?= htmlspecialchars($d['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="detail_pengajuan.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 32px; height: 32px; padding: 0; line-height: 32px; display: inline-flex; justify-content: center; align-items: center;" title="Detail Pengajuan">
                                                <i class="fa-solid fa-eye" style="font-size: 0.8rem;"></i>
                                            </a>
                                            <?php if ($d['status'] === 'Pending'): ?>
                                                <!-- Form Setujui -->
                                                <form action="" method="POST" id="form-approve-<?= $d['id'] ?>" style="display:none;">
                                                    <input type="hidden" name="id_cuti" value="<?= $d['id'] ?>">
                                                    <input type="hidden" name="action" value="Setujui">
                                                </form>
                                                <button onclick="confirmApprove(<?= $d['id'] ?>)" class="btn btn-sm btn-success rounded-circle" style="width: 32px; height: 32px; padding: 0; line-height: 32px; display: inline-flex; justify-content: center; align-items: center;" title="Setujui">
                                                    <i class="fa-solid fa-check" style="font-size: 0.8rem;"></i>
                                                </button>
                                                
                                                <!-- Form Tolak -->
                                                <form action="" method="POST" id="form-reject-<?= $d['id'] ?>" style="display:none;">
                                                    <input type="hidden" name="id_cuti" value="<?= $d['id'] ?>">
                                                    <input type="hidden" name="action" value="Tolak">
                                                    <input type="hidden" name="catatan_admin" id="catatan-reject-<?= $d['id'] ?>" value="">
                                                </form>
                                                <button onclick="confirmReject(<?= $d['id'] ?>)" class="btn btn-sm btn-danger rounded-circle" style="width: 32px; height: 32px; padding: 0; line-height: 32px; display: inline-flex; justify-content: center; align-items: center;" title="Tolak">
                                                    <i class="fa-solid fa-xmark" style="font-size: 0.8rem;"></i>
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted small">Selesai</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <?php if ($total_pages > 1): ?>
                    <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <small class="text-muted">Menampilkan <?= count($daftar_cuti) ?> dari <?= $total_items ?> data</small>
                        <nav>
                            <ul class="pagination pagination-sm m-0">
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?search=<?= urlencode($search) ?>&status=<?= urlencode($filter_status) ?>&page=<?= $page - 1 ?>"><i class="fa-solid fa-chevron-left"></i></a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                        <a class="page-link" href="?search=<?= urlencode($search) ?>&status=<?= urlencode($filter_status) ?>&page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?search=<?= urlencode($search) ?>&status=<?= urlencode($filter_status) ?>&page=<?= $page + 1 ?>"><i class="fa-solid fa-chevron-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert Confirmation Handling -->
<script>
function confirmApprove(id) {
    Swal.fire({
        title: 'Setujui Pengajuan?',
        text: "Anda akan menyetujui pengajuan cuti ini dan memotong kuota tahunan karyawan (jika berjenis Cuti Tahunan).",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10B981',
        cancelButtonColor: '#7E7C8C',
        confirmButtonText: 'Ya, Setujui!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('form-approve-' + id).submit();
        }
    });
}

function confirmReject(id) {
    Swal.fire({
        title: 'Tolak Pengajuan?',
        text: 'Masukkan alasan atau catatan penolakan untuk karyawan:',
        input: 'textarea',
        inputPlaceholder: 'Tulis alasan penolakan di sini...',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#7E7C8C',
        confirmButtonText: 'Tolak Pengajuan',
        cancelButtonText: 'Batal',
        inputValidator: (value) => {
            if (!value) {
                return 'Alasan penolakan wajib diisi!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('catatan-reject-' + id).value = result.value;
            document.getElementById('form-reject-' + id).submit();
        }
    });
}
</script>

<!-- SweetAlert Toast Flash Message -->
<?php if (isset($_SESSION['flash_msg'])): ?>
    <script>
        Swal.fire({
            icon: '<?= $_SESSION['flash_msg']['type'] ?>',
            title: '<?= $_SESSION['flash_msg']['title'] ?>',
            text: '<?= $_SESSION['flash_msg']['text'] ?>',
            confirmButtonColor: '#6A50E5'
        });
    </script>
    <?php unset($_SESSION['flash_msg']); ?>
<?php endif; ?>

<!-- Chart.js Pie Chart Initialization -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    <?php if (!empty($chart_labels)): ?>
    const pieCtx = document.getElementById('chartJenisCuti').getContext('2d');
    new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: <?= $js_labels ?>,
            datasets: [{
                data: <?= $js_values ?>,
                backgroundColor: [
                    '#8C76FF', // Purple
                    '#10B981', // Green
                    '#EF4444', // Red
                    '#F59E0B', // Amber
                    '#3B82F6', // Blue
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            family: 'Poppins',
                            size: 11
                        },
                        boxWidth: 12
                    }
                }
            },
            cutout: '65%'
        }
    });
    <?php endif; ?>
});
</script>

<?php
require_once 'layout_footer.php';
?>