# Sistem Informasi Pengajuan Cuti Karyawan

> Tugas Akhir (UAS) / Proyek Mata Kuliah Konsep dan Perancangan E-Business (Pak Gagah)

**Disusun Oleh**

**Vindy Chresika**  
NIM: 14022400041
Kelas: 4A-INF
Program Studi: Sistem Informasi

---

## Deskripsi Sistem

Sistem Informasi Pengajuan Cuti Karyawan adalah aplikasi berbasis web yang digunakan untuk mengelola proses pengajuan cuti karyawan secara digital. Sistem ini memungkinkan karyawan mengajukan cuti, melihat riwayat pengajuan, serta memantau status persetujuan cuti. Sementara itu, administrator dapat mengelola data pengajuan cuti, memberikan persetujuan atau penolakan, serta melihat laporan cuti karyawan.

Aplikasi ini dikembangkan menggunakan PHP, MySQL.

---

## Tujuan Sistem

Sistem ini dibuat untuk:

- Mempermudah proses pengajuan cuti karyawan.
- Mengurangi penggunaan formulir manual.
- Mempercepat proses persetujuan cuti.
- Menyediakan dokumentasi dan riwayat cuti secara terstruktur.
- Membantu admin dalam mengelola data cuti karyawan.

---

## Fitur Sistem

### Fitur Admin

- Login sebagai admin
- Melihat dashboard admin
- Melihat seluruh pengajuan cuti
- Menyetujui pengajuan cuti
- Menolak pengajuan cuti
- Memberikan catatan pada pengajuan cuti
- Melihat laporan cuti karyawan
- Mengelola pengaturan sistem

### Fitur Karyawan

- Login sebagai karyawan
- Melihat dashboard karyawan
- Mengajukan cuti
- Upload lampiran pendukung
- Melihat status pengajuan cuti
- Melihat riwayat cuti
- Melihat laporan cuti pribadi

---

## Teknologi yang Digunakan

| Teknologi | Keterangan |
|------------|------------|
| PHP | Backend |
| MySQL | Database |
| HTML | Struktur halaman |
| CSS | Tampilan antarmuka |
| JavaScript | Interaksi halaman |
| Bootstrap | Framework UI |
| XAMPP | Local Server |

---

## Struktur Folder

```text
sistemcutikaryawan-main/
│
├── admin_dashboard.php
├── admin_laporan.php
├── config.php
├── database.sql
├── detail_pengajuan.php
├── index.php
├── karyawan_ajukan.php
├── karyawan_dashboard.php
├── karyawan_laporan.php
├── karyawan_riwayat.php
├── layout_footer.php
├── layout_header.php
├── layout_sidebar.php
├── login.php
├── logout.php
├── pengaturan.php
├── uploads/
└── database.db
```

---

## Kebutuhan Sistem

### Software

- Windows 10/11
- XAMPP
- Web Browser (Google Chrome, Microsoft Edge, Mozilla Firefox)
- Git (Opsional)

### Minimum Requirement

- PHP 8.0 atau lebih baru
- MySQL/MariaDB
- Apache Web Server

---

## Cara Menjalankan Program

### 1. Download atau Clone Repository

```bash
git clone https://github.com/USERNAME/NAMA_REPOSITORY.git
```

Atau download ZIP dari GitHub kemudian ekstrak.

---

### 2. Pindahkan Folder Project

Salin folder project ke:

```text
C:\xampp\htdocs\
```

Contoh:

```text
C:\xampp\htdocs\sistemcutikaryawan
```

---

### 3. Jalankan XAMPP

Aktifkan:

- Apache
- MySQL

---

### 4. Import Database

Buka phpMyAdmin:

```text
http://localhost/phpmyadmin
```

Langkah:

1. Klik New
2. Buat database dengan nama:

```text
sistem_cuti
```

3. Klik database yang telah dibuat.
4. Pilih menu Import.
5. Pilih file:

```text
database.sql
```

6. Klik Import.

---

### 5. Konfigurasi Database

File konfigurasi:

```text
config.php
```

Konfigurasi default:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sistem_cuti');
```

Sesuaikan jika menggunakan konfigurasi database yang berbeda.

---

### 6. Jalankan Aplikasi

Buka browser:

```text
http://localhost/sistemcutikaryawan
```

atau

```text
http://localhost/sistemcutikaryawan/login.php
```

---

## Data Akun Pengujian

### Admin

Email:

```text
admin@perusahaan.com
```

Role:

```text
Admin
```

### Karyawan 1

Email:

```text
budi@perusahaan.com
```

Role:

```text
Karyawan
```

### Karyawan 2

Email:

```text
siti@perusahaan.com
```

Role:

```text
Karyawan
```

> Password mengikuti data yang telah disimpan pada database aplikasi.

---

## Alur Penggunaan Sistem

### Karyawan

1. Login ke sistem.
2. Membuka menu pengajuan cuti.
3. Mengisi formulir cuti.
4. Mengunggah lampiran jika diperlukan.
5. Mengirim pengajuan.
6. Menunggu persetujuan admin.
7. Melihat status pengajuan pada riwayat cuti.

### Admin

1. Login ke sistem.
2. Membuka daftar pengajuan cuti.
3. Memeriksa data pengajuan.
4. Memberikan keputusan:
   - Disetujui
   - Ditolak
5. Menambahkan catatan jika diperlukan.
6. Melihat laporan cuti.

---

## Database

Sistem menggunakan dua tabel utama:

### users

Menyimpan data pengguna.

Field utama:

- id
- nama
- email
- password
- role
- jabatan
- kuota_cuti
- foto

### cuti

Menyimpan data pengajuan cuti.

Field utama:

- id
- user_id
- jenis_cuti
- tanggal_mulai
- tanggal_selesai
- jumlah_hari
- alasan
- lampiran
- status
- catatan_admin
- tanggal_pengajuan
