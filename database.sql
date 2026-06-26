-- Script Inisialisasi Database Sistem Cuti
-- Siap di-import ke MySQL / MariaDB

CREATE DATABASE IF NOT EXISTS `sistem_cuti` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `sistem_cuti`;

-- --------------------------------------------------------
-- Struktur tabel untuk `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `nama` VARCHAR(255) NOT NULL,
    `role` VARCHAR(50) NOT NULL,
    `jabatan` VARCHAR(100) DEFAULT NULL,
    `kuota_cuti` INT DEFAULT 12,
    `foto` VARCHAR(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Struktur tabel untuk `cuti`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cuti` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `jenis_cuti` VARCHAR(100) NOT NULL,
    `tanggal_mulai` DATE NOT NULL,
    `tanggal_selesai` DATE NOT NULL,
    `jumlah_hari` INT NOT NULL,
    `alasan` TEXT NOT NULL,
    `lampiran` VARCHAR(255) DEFAULT '',
    `status` VARCHAR(50) DEFAULT 'Pending',
    `catatan_admin` VARCHAR(255) DEFAULT '',
    `tanggal_pengajuan` DATE NOT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Data Dummy untuk `users`
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `email`, `password`, `nama`, `role`, `jabatan`, `kuota_cuti`, `foto`) VALUES 
(1, 'admin@perusahaan.com', '$2y$10$UygYGQ3ogWWDKVjLBrqmmuIh6RuB32PpidlwIFX8T20R2H.q5a4KK', 'Praditya Admin Utama', 'Admin', 'HR Manager', 0, ''),
(2, 'budi@perusahaan.com', '$2y$10$OlbOyV0bjqs3NZOxKl6gReTKtqae7meiGnwcRdw3A7eUGK6CaBYC2', 'Budi Setiawan', 'Karyawan', 'Software Engineer', 12, ''),
(3, 'siti@perusahaan.com', '$2y$10$OlbOyV0bjqs3NZOxKl6gReTKtqae7meiGnwcRdw3A7eUGK6CaBYC2', 'Siti Rahma', 'Karyawan', 'UI/UX Designer', 12, '')
ON DUPLICATE KEY UPDATE `email` = `email`;

-- --------------------------------------------------------
-- Data Dummy untuk `cuti`
-- --------------------------------------------------------
INSERT INTO `cuti` (`id`, `user_id`, `jenis_cuti`, `tanggal_mulai`, `tanggal_selesai`, `jumlah_hari`, `alasan`, `lampiran`, `status`, `catatan_admin`, `tanggal_pengajuan`) VALUES 
(1, 2, 'Cuti Tahunan', '2026-07-01', '2026-07-03', 3, 'Acara keluarga di luar kota', '', 'Pending', '', '2026-06-24'),
(2, 3, 'Sakit', '2026-06-10', '2026-06-11', 2, 'Demam tinggi disertai flu', 'surat_sakit_siti.pdf', 'Disetujui', 'Lekas sembuh', '2026-06-09'),
(3, 2, 'Izin Penting', '2026-06-15', '2026-06-15', 1, 'Mengurus administrasi kependudukan', '', 'Ditolak', 'Mohon diajukan di hari lain karena rilis penting', '2026-06-12')
ON DUPLICATE KEY UPDATE `id` = `id`;
