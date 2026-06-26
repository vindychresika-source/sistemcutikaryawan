<?php
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$status_submit = '';
$err_message = '';

// Re-fetch data user terupdate dari DB
$stmt_user = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$current_user = $stmt_user->fetch();

// Sinkronisasi session terupdate
$_SESSION['user'] = $current_user;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $nama = trim($_POST['nama']);
        $email = trim($_POST['email']);
        
        // Validasi Email Unik (kecuali email sendiri)
        $stmt_check = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
        $stmt_check->execute([$email, $user_id]);
        $email_count = $stmt_check->fetchColumn();

        if ($email_count > 0) {
            $status_submit = 'error';
            $err_message = 'Alamat email sudah digunakan oleh pengguna lain!';
        } else {
            // Upload Foto jika ada
            $foto_name = $current_user['foto'];
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['foto']['tmp_name'];
                $file_orig = $_FILES['foto']['name'];
                $file_ext = pathinfo($file_orig, PATHINFO_EXTENSION);
                
                // Sanitasi dan generate nama unik
                $new_foto_name = 'avatar_' . $user_id . '_' . time() . '.' . $file_ext;
                $target_dir = __DIR__ . '/uploads/';
                
                if (move_uploaded_file($file_tmp, $target_dir . $new_foto_name)) {
                    $foto_name = 'uploads/' . $new_foto_name;
                }
            }

            // Update DB
            $stmt_up = $db->prepare("UPDATE users SET nama = ?, email = ?, foto = ? WHERE id = ?");
            $stmt_up->execute([$nama, $email, $foto_name, $user_id]);
            
            $_SESSION['flash_msg'] = [
                'type' => 'success',
                'title' => 'Profil Diperbarui!',
                'text' => 'Informasi profil Anda berhasil diperbarui.'
            ];
            header("Location: pengaturan.php");
            exit;
        }
    } 
    
    elseif (isset($_POST['update_password'])) {
        $old_pass = $_POST['old_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        if ($new_pass !== $confirm_pass) {
            $status_submit = 'error';
            $err_message = 'Password baru dan konfirmasi password tidak cocok!';
        } elseif (!password_verify($old_pass, $current_user['password'])) {
            $status_submit = 'error';
            $err_message = 'Password lama Anda salah!';
        } else {
            // Hash password baru dan update
            $new_pass_hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt_up = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt_up->execute([$new_pass_hash, $user_id]);
            
            $_SESSION['flash_msg'] = [
                'type' => 'success',
                'title' => 'Password Diubah!',
                'text' => 'Password akun Anda berhasil diperbarui.'
            ];
            header("Location: pengaturan.php");
            exit;
        }
    }
}

// Meta layout
$page_title = 'Pengaturan Akun';
$active_menu = 'pengaturan';

require_once 'layout_header.php';
require_once 'layout_sidebar.php';

$user_avatar = !empty($current_user['foto']) ? htmlspecialchars($current_user['foto']) : 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($current_user['nama']);
?>

<div class="container-fluid p-0" style="max-width: 900px; margin: 0 auto;">
    <!-- Header Halaman -->
    <div class="mb-4">
        <h3 class="m-0 fw-bold text-main">Pengaturan Akun</h3>
        <p class="text-muted small m-0">Sesuaikan data profil pribadi Anda atau ubah kata sandi secara aman.</p>
    </div>

    <div class="row">
        <!-- Form Kiri: Ubah Profil -->
        <div class="col-12 col-md-6 mb-4">
            <div class="card custom-card h-100">
                <div class="card-header-custom border-bottom pb-3">
                    <h5 class="m-0 fw-bold"><i class="fa-solid fa-user-pen text-purple me-2"></i>Edit Profil Saya</h5>
                </div>
                <div class="card-body-custom py-4">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <!-- Preview Foto Profil -->
                        <div class="text-center mb-4">
                            <img src="<?= $user_avatar ?>" class="rounded-circle mb-3 border border-3 border-white shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                            <div class="small text-muted">Akan langsung diperbarui di menu sidebar</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control rounded-3" value="<?= htmlspecialchars($current_user['nama']) ?>" required style="border-color: #E2E0EC;">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat Email</label>
                            <input type="email" name="email" class="form-control rounded-3" value="<?= htmlspecialchars($current_user['email']) ?>" required style="border-color: #E2E0EC;">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Unggah Foto Profil Baru</label>
                            <input type="file" name="foto" class="form-control rounded-3" accept=".jpg,.jpeg,.png" style="border-color: #E2E0EC;">
                            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Menerima format JPG/PNG, ukuran maks 1MB.</small>
                        </div>

                        <button type="submit" name="update_profile" class="btn btn-purple w-100"><i class="fa-regular fa-floppy-disk me-2"></i>Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Form Kanan: Ubah Password -->
        <div class="col-12 col-md-6 mb-4">
            <div class="card custom-card h-100">
                <div class="card-header-custom border-bottom pb-3">
                    <h5 class="m-0 fw-bold"><i class="fa-solid fa-lock text-purple me-2"></i>Ubah Password</h5>
                </div>
                <div class="card-body-custom py-4">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Password Saat Ini</label>
                            <input type="password" name="old_password" class="form-control rounded-3" required placeholder="Masukkan password lama" style="border-color: #E2E0EC;">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="new_password" class="form-control rounded-3" required placeholder="Minimal 6 karakter" style="border-color: #E2E0EC;">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" class="form-control rounded-3" required placeholder="Ulangi password baru" style="border-color: #E2E0EC;">
                        </div>

                        <button type="submit" name="update_password" class="btn btn-purple w-100" style="background-color: var(--text-main);"><i class="fa-solid fa-key me-2"></i>Perbarui Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

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
<?php elseif ($status_submit === 'error'): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '<?= addslashes($err_message) ?>',
            confirmButtonColor: '#6A50E5'
        });
    </script>
<?php endif; ?>

<?php
require_once 'layout_footer.php';
?>
