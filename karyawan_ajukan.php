<?php
require_once 'config.php';
cekLogin('Karyawan');

$user_id = $_SESSION['user']['id'];

// Ambil sisa kuota cuti karyawan
$stmt = $db->prepare("SELECT kuota_cuti FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$kuota = $stmt->fetchColumn();

$status_submit = '';
$err_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis = $_POST['jenis_cuti'];
    $mulai = $_POST['tanggal_mulai'];
    $selesai = $_POST['tanggal_selesai'];
    $alasan = trim($_POST['alasan']);
    $tgl_pengajuan = date('Y-m-d');

    // Validasi Tanggal
    $hari_mulai = new DateTime($mulai);
    $hari_selesai = new DateTime($selesai);
    
    if ($hari_selesai < $hari_mulai) {
        $status_submit = 'error_tanggal';
        $err_message = 'Tanggal selesai tidak boleh mendahului tanggal mulai!';
    } else {
        $interval = $hari_mulai->diff($hari_selesai);
        $jumlah_hari = $interval->days + 1;

        if ($jenis == 'Cuti Tahunan' && $jumlah_hari > $kuota) {
            $status_submit = 'error_kuota';
            $err_message = "Jumlah hari cuti tahunan yang diajukan ($jumlah_hari hari) melebihi sisa kuota Anda ($kuota hari).";
        } else {
            // Proses Upload Lampiran jika ada
            $file_name_db = '';
            if (isset($_FILES['lampiran']) && $_FILES['lampiran']['error'] == UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['lampiran']['tmp_name'];
                $file_orig = $_FILES['lampiran']['name'];
                $file_ext = pathinfo($file_orig, PATHINFO_EXTENSION);
                
                // Sanitasi dan generate nama unik
                $new_file_name = time() . '_' . preg_replace("/[^a-zA-Z0-9]/", "_", pathinfo($file_orig, PATHINFO_FILENAME)) . '.' . $file_ext;
                $target_dir = __DIR__ . '/uploads/';
                
                // Pastikan folder uploads ada
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                if (move_uploaded_file($file_tmp, $target_dir . $new_file_name)) {
                    $file_name_db = $new_file_name;
                }
            }

            // Insert ke database
            $stmt_ins = $db->prepare("INSERT INTO cuti (user_id, jenis_cuti, tanggal_mulai, tanggal_selesai, jumlah_hari, alasan, lampiran, status, catatan_admin, tanggal_pengajuan) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', '', ?)");
            $stmt_ins->execute([$user_id, $jenis, $mulai, $selesai, $jumlah_hari, $alasan, $file_name_db, $tgl_pengajuan]);
            $status_submit = 'success';
        }
    }
}

// Data untuk header layout
$page_title = 'Form Pengajuan Cuti';
$active_menu = 'ajukan';

require_once 'layout_header.php';
require_once 'layout_sidebar.php';
?>

<div class="container-fluid p-0" style="max-width: 800px; margin: 0 auto;">
    <!-- Judul Halaman -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="m-0 fw-bold text-main">Pengajuan Permohonan Cuti</h3>
            <p class="text-muted small m-0">Silakan lengkapi formulir di bawah ini untuk mengajukan cuti.</p>
        </div>
        <div class="bg-white px-3 py-2 rounded-4 shadow-sm text-end" style="border: 1px solid var(--primary-purple);">
            <small class="text-muted fw-bold d-block" style="font-size: 0.75rem;">SISA KUOTA CUTI TAHUNAN</small>
            <span class="fw-bold text-main" style="font-size: 1.1rem; color: var(--dark-purple) !important;"><?= $kuota ?> HARI</span>
        </div>
    </div>

    <!-- Formulir Card -->
    <div class="card custom-card">
        <div class="card-body-custom">
            <form method="POST" action="" enctype="multipart/form-data">
                <!-- Jenis Cuti -->
                <div class="mb-4">
                    <label class="form-label">Jenis Cuti</label>
                    <select name="jenis_cuti" class="form-select py-3 px-3 rounded-4" style="border-color: #E2E0EC;" required>
                        <option value="Cuti Tahunan">Cuti Tahunan (Mengurangi Kuota)</option>
                        <option value="Sakit">Sakit</option>
                        <option value="Izin Penting">Izin Penting</option>
                        <option value="Cuti Melahirkan">Cuti Melahirkan</option>
                        <option value="Cuti Khusus">Cuti Khusus / Dispensasi</option>
                    </select>
                </div>

                <!-- Tanggal Mulai & Selesai -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Mulai Cuti</label>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control py-3 px-3 rounded-4" style="border-color: #E2E0EC;" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Selesai Cuti</label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control py-3 px-3 rounded-4" style="border-color: #E2E0EC;" required>
                    </div>
                </div>

                <!-- Jumlah Hari Otomatis (Disabled/Readonly) -->
                <div class="mb-4">
                    <label class="form-label">Jumlah Hari Cuti</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-4 px-3" style="border-color: #E2E0EC;"><i class="fa-solid fa-calendar-day text-purple"></i></span>
                        <input type="text" id="jumlah_hari_tampil" class="form-control py-3 px-3 bg-light border-start-0 rounded-end-4" style="border-color: #E2E0EC;" readonly placeholder="Diisi otomatis berdasarkan tanggal" value="">
                    </div>
                </div>

                <!-- Alasan Cuti -->
                <div class="mb-4">
                    <label class="form-label">Alasan Pengajuan Cuti</label>
                    <textarea name="alasan" class="form-control px-3 py-3 rounded-4" rows="4" style="border-color: #E2E0EC;" placeholder="Tuliskan detail keperluan/alasan pengajuan cuti Anda..." required></textarea>
                </div>

                <!-- Upload Lampiran -->
                <div class="mb-5">
                    <label class="form-label">Upload Lampiran <span class="text-muted fw-normal">(Opsional - PDF/Gambar, Maks. 2MB)</span></label>
                    <input type="file" name="lampiran" class="form-control py-3 px-3 rounded-4" style="border-color: #E2E0EC;" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                </div>

                <!-- Tombol Action -->
                <div class="d-flex gap-3 justify-content-end">
                    <a href="karyawan_dashboard.php" class="btn btn-cancel px-4 py-3">Batal</a>
                    <button type="submit" class="btn btn-purple px-4 py-3"><i class="fa-regular fa-paper-plane me-2"></i>Ajukan Cuti</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript Auto-calculation -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const tglMulaiInput = document.getElementById('tanggal_mulai');
    const tglSelesaiInput = document.getElementById('tanggal_selesai');
    const jmlHariTampil = document.getElementById('jumlah_hari_tampil');

    // Membatasi tanggal minimal pengajuan mulai hari ini
    const today = new Date().toISOString().split('T')[0];
    tglMulaiInput.min = today;
    tglSelesaiInput.min = today;

    function hitungHari() {
        const valMulai = tglMulaiInput.value;
        const valSelesai = tglSelesaiInput.value;

        if (valMulai && valSelesai) {
            const dateMulai = new Date(valMulai);
            const dateSelesai = new Date(valSelesai);

            // Validasi jika selesai mendahului mulai
            if (dateSelesai < dateMulai) {
                jmlHariTampil.value = "Tanggal selesai tidak valid";
                jmlHariTampil.classList.add("text-danger");
                return;
            }

            jmlHariTampil.classList.remove("text-danger");
            // Hitung selisih hari
            const diffTime = Math.abs(dateSelesai - dateMulai);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            
            jmlHariTampil.value = diffDays + " Hari Kerja / Kalender";
        } else {
            jmlHariTampil.value = "";
        }
    }

    tglMulaiInput.addEventListener('change', function() {
        tglSelesaiInput.min = tglMulaiInput.value; // Minimal tanggal selesai adalah tanggal mulai
        hitungHari();
    });
    tglSelesaiInput.addEventListener('change', hitungHari);
});
</script>

<!-- SweetAlert Feedback -->
<?php if ($status_submit == 'success'): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Pengajuan Berhasil!',
            text: 'Permohonan cuti Anda telah berhasil dikirim dan menunggu persetujuan HR.',
            confirmButtonColor: '#6A50E5'
        }).then(() => {
            window.location = 'karyawan_riwayat.php';
        });
    </script>
<?php elseif ($status_submit != ''): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Pengajuan Gagal',
            text: '<?= addslashes($err_message) ?>',
            confirmButtonColor: '#6A50E5'
        });
    </script>
<?php endif; ?>

<?php
require_once 'layout_footer.php';
?>