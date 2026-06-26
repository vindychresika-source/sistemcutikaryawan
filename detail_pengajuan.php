<?php
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$id_cuti = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_cuti <= 0) {
    die("ID Pengajuan tidak valid.");
}

// Fetch detail pengajuan beserta data pengaju
$stmt = $db->prepare("SELECT cuti.*, users.nama, users.email, users.jabatan, users.kuota_cuti, users.foto 
                      FROM cuti 
                      JOIN users ON cuti.user_id = users.id 
                      WHERE cuti.id = ?");
$stmt->execute([$id_cuti]);
$detail = $stmt->fetch();

if (!$detail) {
    die("Pengajuan cuti tidak ditemukan.");
}

// Pastikan karyawan biasa tidak bisa melihat detail pengajuan milik orang lain
if ($_SESSION['user']['role'] === 'Karyawan' && $_SESSION['user']['id'] !== $detail['user_id']) {
    die("Akses Ditolak: Anda tidak memiliki wewenang melihat detail pengajuan ini.");
}

// Handle Aksi dari Halaman Detail ini
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['user']['role'] === 'Admin') {
    $action = $_POST['action']; // 'Setujui' atau 'Tolak'
    $catatan = isset($_POST['catatan_admin']) ? trim($_POST['catatan_admin']) : '';
    
    if ($detail['status'] === 'Pending') {
        $db->beginTransaction();
        try {
            $status_baru = ($action === 'Setujui') ? 'Disetujui' : 'Ditolak';
            
            $stmt_u = $db->prepare("UPDATE cuti SET status = ?, catatan_admin = ? WHERE id = ?");
            $stmt_u->execute([$status_baru, $catatan, $id_cuti]);
            
            if ($status_baru === 'Disetujui' && $detail['jenis_cuti'] === 'Cuti Tahunan') {
                $stmt_k = $db->prepare("UPDATE users SET kuota_cuti = GREATEST(0, kuota_cuti - ?) WHERE id = ?");
                $stmt_k->execute([$detail['jumlah_hari'], $detail['user_id']]);
            }
            
            $db->commit();
            $_SESSION['flash_msg'] = [
                'type' => 'success',
                'title' => 'Berhasil!',
                'text' => 'Status pengajuan cuti berhasil diperbarui.'
            ];
            header("Location: detail_pengajuan.php?id=" . $id_cuti);
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['flash_msg'] = [
                'type' => 'error',
                'title' => 'Gagal!',
                'text' => 'Gagal memproses pengajuan: ' . $e->getMessage()
            ];
        }
    }
}

// Meta layout
$page_title = 'Detail Pengajuan Cuti';
$active_menu = ($_SESSION['user']['role'] === 'Admin') ? 'dashboard' : 'riwayat';

require_once 'layout_header.php';
require_once 'layout_sidebar.php';

$emp_avatar = !empty($detail['foto']) ? htmlspecialchars($detail['foto']) : 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($detail['nama']);
$statusClass = ($detail['status'] == 'Disetujui') ? 'status-disetujui' : (($detail['status'] == 'Ditolak') ? 'status-ditolak' : 'status-pending');
$statusIcon = ($detail['status'] == 'Disetujui') ? 'fa-circle-check' : (($detail['status'] == 'Ditolak') ? 'fa-circle-xmark' : 'fa-clock');
$statusColor = ($detail['status'] == 'Disetujui') ? 'text-success' : (($detail['status'] == 'Ditolak') ? 'text-danger' : 'text-warning');
?>

<div class="container-fluid p-0">
    <!-- Header Halaman -->
    <div class="mb-4">
        <h3 class="m-0 fw-bold text-main">Rincian Pengajuan Cuti</h3>
        <p class="text-muted small m-0">Meninjau secara mendalam pengajuan permohonan izin/cuti karyawan.</p>
    </div>

    <!-- Dua Card Berdampingan -->
    <div class="row">
        <!-- Card Kiri: Informasi Karyawan -->
        <div class="col-12 col-lg-5 mb-4">
            <div class="card custom-card h-100">
                <div class="card-header-custom border-bottom pb-3">
                    <h5 class="m-0 fw-bold"><i class="fa-solid fa-address-card text-purple me-2"></i>Informasi Karyawan</h5>
                </div>
                <div class="card-body-custom text-center py-5">
                    <img src="<?= $emp_avatar ?>" class="rounded-circle mb-4 border border-4 border-white shadow-sm" style="width: 110px; height: 110px; object-fit: cover;">
                    <h4 class="fw-bold text-main mb-1"><?= htmlspecialchars($detail['nama']) ?></h4>
                    <p class="text-muted text-uppercase fw-semibold mb-4" style="font-size: 0.8rem; letter-spacing: 0.5px;"><?= htmlspecialchars($detail['jabatan']) ?></p>
                    
                    <hr class="my-4 text-light-purple" style="opacity: 0.2;">
                    
                    <div class="row text-start g-3 px-3">
                        <div class="col-12">
                            <small class="text-muted fw-bold d-block mb-1" style="font-size: 0.75rem;">ALAMAT EMAIL</small>
                            <span class="text-main fw-medium"><i class="fa-regular fa-envelope text-purple me-2"></i><?= htmlspecialchars($detail['email']) ?></span>
                        </div>
                        <div class="col-12">
                            <small class="text-muted fw-bold d-block mb-1" style="font-size: 0.75rem;">SISA KUOTA CUTI TAHUNAN</small>
                            <span class="text-main fw-bold"><i class="fa-solid fa-circle-info text-purple me-2"></i><?= $detail['kuota_cuti'] ?> Hari</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Kanan: Informasi Pengajuan -->
        <div class="col-12 col-lg-7 mb-4">
            <div class="card custom-card h-100">
                <div class="card-header-custom border-bottom pb-3">
                    <h5 class="m-0 fw-bold"><i class="fa-solid fa-file-invoice text-purple me-2"></i>Informasi Pengajuan Cuti</h5>
                </div>
                <div class="card-body-custom py-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <small class="text-muted fw-bold d-block mb-1" style="font-size: 0.75rem;">JENIS CUTI</small>
                            <span class="fs-5 fw-bold text-main"><?= htmlspecialchars($detail['jenis_cuti']) ?></span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted fw-bold d-block mb-1" style="font-size: 0.75rem;">STATUS PENGAJUAN</small>
                            <span class="status-badge <?= $statusClass ?> mt-1">
                                <i class="fa-solid <?= $statusIcon ?> <?= $statusColor ?>"></i><?= htmlspecialchars($detail['status']) ?>
                            </span>
                        </div>

                        <div class="col-12">
                            <small class="text-muted fw-bold d-block mb-1" style="font-size: 0.75rem;">TANGGAL PELAKSANAAN</small>
                            <div class="d-flex align-items-center">
                                <div class="bg-light px-3 py-2 rounded-3 border fw-semibold text-main">
                                    <i class="fa-regular fa-calendar text-purple me-2"></i><?= date('d M Y', strtotime($detail['tanggal_mulai'])) ?>
                                </div>
                                <div class="mx-3 text-muted fw-bold">s/d</div>
                                <div class="bg-light px-3 py-2 rounded-3 border fw-semibold text-main">
                                    <i class="fa-regular fa-calendar text-purple me-2"></i><?= date('d M Y', strtotime($detail['tanggal_selesai'])) ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <small class="text-muted fw-bold d-block mb-1" style="font-size: 0.75rem;">DURASI CUTI</small>
                            <span class="fs-5 fw-bold text-main"><?= $detail['jumlah_hari'] ?> Hari</span>
                        </div>

                        <div class="col-md-6">
                            <small class="text-muted fw-bold d-block mb-1" style="font-size: 0.75rem;">TANGGAL PENGAJUAN</small>
                            <span class="fw-semibold text-main"><?= date('d F Y', strtotime($detail['tanggal_pengajuan'])) ?></span>
                        </div>

                        <div class="col-12">
                            <small class="text-muted fw-bold d-block mb-1" style="font-size: 0.75rem;">ALASAN PENGAJUAN</small>
                            <div class="bg-light p-3 rounded-4 border text-main" style="min-height: 80px; font-size: 0.95rem; line-height: 1.5;">
                                <?= nl2br(htmlspecialchars($detail['alasan'])) ?>
                            </div>
                        </div>

                        <div class="col-12">
                            <small class="text-muted fw-bold d-block mb-1" style="font-size: 0.75rem;">LAMPIRAN DOKUMEN</small>
                            <?php if (empty($detail['lampiran'])): ?>
                                <span class="text-muted small"><i class="fa-solid fa-circle-exclamation me-1"></i> Tidak ada lampiran yang diupload.</span>
                            <?php else: 
                                $file_ext = strtolower(pathinfo($detail['lampiran'], PATHINFO_EXTENSION));
                                $is_img = in_array($file_ext, ['jpg', 'jpeg', 'png']);
                            ?>
                                <div class="d-flex align-items-center mt-2 p-3 bg-light rounded-4 border">
                                    <i class="fa-solid <?= $is_img ? 'fa-file-image text-success' : 'fa-file-pdf text-danger' ?> fs-3 me-3"></i>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="text-main fw-bold text-truncate" style="font-size: 0.85rem;"><?= htmlspecialchars($detail['lampiran']) ?></div>
                                        <small class="text-muted text-uppercase" style="font-size: 0.7rem;"><?= $file_ext ?> Dokumen</small>
                                    </div>
                                    <a href="uploads/<?= htmlspecialchars($detail['lampiran']) ?>" target="_blank" class="btn btn-sm btn-purple rounded-pill px-3 py-1" style="font-size: 0.8rem;">
                                        <i class="fa-solid fa-eye me-1"></i> Buka / Unduh
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Catatan Admin (Jika Status Disetujui/Ditolak) -->
                        <?php if ($detail['status'] !== 'Pending'): ?>
                            <div class="col-12">
                                <small class="text-muted fw-bold d-block mb-1" style="font-size: 0.75rem;">CATATAN DARI HR / ADMIN</small>
                                <div class="p-3 rounded-4 border text-main" style="background-color: #FAF9FF; border-color: var(--primary-purple) !important; font-size: 0.95rem;">
                                    <strong>Catatan:</strong> <em>"<?= htmlspecialchars($detail['catatan_admin'] ?: 'Tidak ada catatan.') ?>"</em>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tombol Tindakan di Bagian Bawah -->
    <div class="d-flex justify-content-between align-items-center mt-3 bg-white p-3 rounded-4 shadow-sm">
        <a href="javascript:history.back()" class="btn btn-cancel px-4"><i class="fa-solid fa-arrow-left me-2"></i>Kembali</a>
        
        <?php if ($detail['status'] === 'Pending' && $_SESSION['user']['role'] === 'Admin'): ?>
            <div class="d-flex gap-2">
                <!-- Form Setujui -->
                <form action="" method="POST" id="form-approve-detail" style="display:none;">
                    <input type="hidden" name="action" value="Setujui">
                </form>
                <button onclick="approveDetail()" class="btn btn-purple px-4" style="background-color:#10B981;"><i class="fa-solid fa-check me-2"></i>Setujui Pengajuan</button>

                <!-- Form Tolak -->
                <form action="" method="POST" id="form-reject-detail" style="display:none;">
                    <input type="hidden" name="action" value="Tolak">
                    <input type="hidden" name="catatan_admin" id="catatan-reject-detail" value="">
                </form>
                <button onclick="rejectDetail()" class="btn btn-purple px-4" style="background-color:#EF4444;"><i class="fa-solid fa-xmark me-2"></i>Tolak Pengajuan</button>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function approveDetail() {
    Swal.fire({
        title: 'Setujui Pengajuan ini?',
        text: "Anda akan menyetujui permohonan cuti <?= htmlspecialchars($detail['nama']) ?>.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10B981',
        cancelButtonColor: '#7E7C8C',
        confirmButtonText: 'Ya, Setujui!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('form-approve-detail').submit();
        }
    });
}

function rejectDetail() {
    Swal.fire({
        title: 'Tolak Pengajuan ini?',
        text: 'Tulis alasan penolakan untuk karyawan:',
        input: 'textarea',
        inputPlaceholder: 'Tulis alasan di sini...',
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
            document.getElementById('catatan-reject-detail').value = result.value;
            document.getElementById('form-reject-detail').submit();
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

<?php
require_once 'layout_footer.php';
?>
