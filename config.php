<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sistem_cuti');

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Cek migrasi - jika tabel users masih menggunakan kolom 'username', hapus tabel lama dan buat ulang.
    $table_exists = $db->query("SHOW TABLES LIKE 'users'")->fetch();
    if ($table_exists) {
        $schema_check = $db->query("DESCRIBE users")->fetchAll();
        $has_email = false;
        foreach ($schema_check as $col) {
            if ($col['Field'] === 'email') {
                $has_email = true;
                break;
            }
        }
        if (!$has_email) {
            $db->exec("DROP TABLE IF EXISTS cuti");
            $db->exec("DROP TABLE IF EXISTS users");
        }
    }

    // Buat Tabel Pengguna jika belum ada
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        nama VARCHAR(255) NOT NULL,
        role VARCHAR(50) NOT NULL,
        jabatan VARCHAR(100) DEFAULT NULL,
        kuota_cuti INT DEFAULT 12,
        foto VARCHAR(255) DEFAULT ''
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Buat Tabel Cuti jika belum ada
    $db->exec("CREATE TABLE IF NOT EXISTS cuti (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        jenis_cuti VARCHAR(100) NOT NULL,
        tanggal_mulai DATE NOT NULL,
        tanggal_selesai DATE NOT NULL,
        jumlah_hari INT NOT NULL,
        alasan TEXT NOT NULL,
        lampiran VARCHAR(255) DEFAULT '',
        status VARCHAR(50) DEFAULT 'Pending',
        catatan_admin VARCHAR(255) DEFAULT '',
        tanggal_pengajuan DATE NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Cek apakah data dummy sudah ada, jika kosong maka isi
    $check = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($check == 0) {
        // Password di-hash menggunakan PASSWORD_DEFAULT
        $passAdmin = password_hash('admin123', PASSWORD_DEFAULT);
        $passKaryawan = password_hash('karyawan123', PASSWORD_DEFAULT);

        // Tambah User Dummy dengan avatar inisial
        $db->exec("INSERT INTO users (email, password, nama, role, jabatan, kuota_cuti, foto) VALUES 
            ('admin@perusahaan.com', '$passAdmin', 'Praditya Admin Utama', 'Admin', 'HR Manager', 0, ''),
            ('budi@perusahaan.com', '$passKaryawan', 'Budi Setiawan', 'Karyawan', 'Software Engineer', 12, ''),
            ('siti@perusahaan.com', '$passKaryawan', 'Siti Rahma', 'Karyawan', 'UI/UX Designer', 12, '')");
            
        // Data dummy pengajuan cuti dengan format baru
        $db->exec("INSERT INTO cuti (user_id, jenis_cuti, tanggal_mulai, tanggal_selesai, jumlah_hari, alasan, lampiran, status, catatan_admin, tanggal_pengajuan) VALUES 
            (2, 'Cuti Tahunan', '2026-07-01', '2026-07-03', 3, 'Acara keluarga di luar kota', '', 'Pending', '', '2026-06-24'),
            (3, 'Sakit', '2026-06-10', '2026-06-11', 2, 'Demam tinggi disertai flu', 'surat_sakit_siti.pdf', 'Disetujui', 'Lekas sembuh', '2026-06-09'),
            (2, 'Izin Penting', '2026-06-15', '2026-06-15', 1, 'Mengurus administrasi kependudukan', '', 'Ditolak', 'Mohon diajukan di hari lain karena rilis penting', '2026-06-12')");
    }

} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

function cekLogin($role_wajib) {
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit;
    }
    if ($_SESSION['user']['role'] !== $role_wajib) {
        die("Akses Ditolak: Anda tidak memiliki otoritas ke halaman ini.");
    }
}
?>