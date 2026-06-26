<?php
require_once 'config.php';

if (isset($_SESSION['user'])) {
    if ($_SESSION['user']['role'] == 'Admin') header("Location: admin_dashboard.php");
    else header("Location: karyawan_dashboard.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        if ($user['role'] == 'Admin') header("Location: admin_dashboard.php");
        else header("Location: karyawan_dashboard.php");
        exit;
    } else {
        $error = 'Email atau Password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SI Cuti Karyawan</title>
    
    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root {
            --primary-purple: #B9A7FF;
            --dark-purple: #6A50E5;
            --hover-purple: #553BBF;
            --bg-gray: #F4F3F8;
            --text-main: #2C1D54;
        }
        
        .developer-credit{
   			 margin-top: 25px;
  			 font-size: 0.95rem;
    		opacity: 0.9;
   			 font-weight: 500;
		}

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-gray);
            min-height: 100vh;
            display: flex;
            align-items: center;
            margin: 0;
            padding: 0;
        }

        .login-wrapper {
            width: 100%;
            min-height: 100vh;
            display: flex;
        }

        /* Sisi Kiri: Ilustrasi & Judul */
        .login-left {
            flex: 1.2;
            background: linear-gradient(135deg, var(--dark-purple), var(--primary-purple));
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 50px;
            color: #ffffff;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            top: -100px;
            left: -100px;
        }

        .login-left::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
            bottom: -150px;
            right: -150px;
        }

        .login-illustration {
            max-width: 380px;
            margin-bottom: 40px;
            z-index: 2;
            filter: drop-shadow(0 15px 25px rgba(44, 29, 84, 0.2));
        }

        .login-title-box {
            text-align: center;
            max-width: 550px;
            z-index: 2;
        }

        .login-title-box h1 {
            font-weight: 800;
            font-size: 2.2rem;
            line-height: 1.3;
            margin-bottom: 15px;
            text-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .login-title-box p {
            font-size: 1.05rem;
            opacity: 0.9;
            font-weight: 300;
        }

        /* Sisi Kanan: Card Login */
        .login-right {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 50px;
            background-color: var(--bg-gray);
        }

        .login-card {
            background-color: #ffffff;
            width: 100%;
            max-width: 450px;
            border-radius: 24px;
            padding: 45px;
            box-shadow: 0 12px 40px rgba(185, 167, 255, 0.2);
            border: none;
            position: relative;
        }

        .login-card-header {
            margin-bottom: 35px;
            text-align: center;
        }

        .login-card-header h2 {
            font-weight: 700;
            font-size: 1.8rem;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .login-card-header p {
            color: #8C8A97;
            font-size: 0.9rem;
            margin: 0;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 22px;
        }

        .input-group-custom i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #A3A1B0;
            font-size: 1rem;
            transition: color 0.3s ease;
            z-index: 10;
        }

        .form-control-custom {
            width: 100%;
            padding: 14px 20px 14px 50px;
            font-size: 0.95rem;
            background-color: #F8F7FC;
            border: 1.5px solid #F1EFF7;
            border-radius: 14px;
            color: var(--text-main);
            transition: all 0.3s ease;
        }

        .form-control-custom:focus {
            background-color: #ffffff;
            border-color: var(--primary-purple);
            outline: none;
            box-shadow: 0 0 0 4px rgba(185, 167, 255, 0.25);
        }

        .form-control-custom:focus + i {
            color: var(--dark-purple);
        }

        .login-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            margin-bottom: 30px;
        }

        .form-check-input:checked {
            background-color: var(--dark-purple);
            border-color: var(--dark-purple);
        }

        .form-check-label {
            color: #6C6A7B;
            font-weight: 500;
        }

        .forgot-link {
            color: var(--dark-purple);
            text-decoration: none;
            font-weight: 600;
        }

        .forgot-link:hover {
            color: var(--hover-purple);
            text-decoration: underline;
        }

        .btn-login {
            background-color: var(--dark-purple);
            color: #ffffff;
            font-weight: 700;
            padding: 14px;
            border-radius: 14px;
            border: none;
            font-size: 1rem;
            width: 100%;
            box-shadow: 0 6px 20px rgba(106, 80, 229, 0.25);
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background-color: var(--hover-purple);
            box-shadow: 0 8px 25px rgba(106, 80, 229, 0.35);
        }

        .demo-credentials {
            margin-top: 30px;
            padding: 15px;
            background-color: #F1EDFF;
            border-radius: 12px;
            font-size: 0.8rem;
            border: 1px dashed var(--primary-purple);
        }

        .demo-credentials code {
            color: var(--dark-purple);
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .login-wrapper {
                flex-direction: column;
            }
            .login-left {
                padding: 40px 20px;
                min-height: 350px;
                flex: none;
            }
            .login-illustration {
                max-width: 180px;
                margin-bottom: 20px;
            }
            .login-title-box h1 {
                font-size: 1.6rem;
            }
            .login-title-box p {
                font-size: 0.9rem;
            }
            .login-right {
                padding: 30px 15px;
            }
            .login-card {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <!-- Sisi Kiri: Ilustrasi & Judul -->
    <div class="login-left">
        <!-- SVG Ilustrasi Calendar Elegant -->
        <svg class="login-illustration" viewBox="0 0 500 500" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="75" y="100" width="350" height="320" rx="40" fill="#ffffff" fill-opacity="0.15" stroke="#ffffff" stroke-width="8"/>
            <path d="M75 180H425" stroke="#ffffff" stroke-width="8" stroke-linecap="round"/>
            <circle cx="150" cy="140" r="15" fill="#ffffff"/>
            <circle cx="350" cy="140" r="15" fill="#ffffff"/>
            <!-- Lembaran kertas cuti / checklist -->
            <rect x="130" y="220" width="240" height="150" rx="16" fill="#ffffff"/>
            <rect x="170" y="260" width="160" height="12" rx="6" fill="#B9A7FF"/>
            <rect x="170" y="290" width="120" height="12" rx="6" fill="#EBE6FF"/>
            <circle cx="180" cy="335" r="12" fill="#6A50E5"/>
            <!-- Centang -->
            <path d="M175 335L179 339L186 331" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
            <rect x="205" y="330" width="80" height="10" rx="5" fill="#6A50E5"/>
        </svg>

        <div class="login-title-box">
    		<h1>Sistem Informasi<br>Pengajuan Cuti Karyawan</h1>
   			 <p>Kelola pengajuan cuti tahunan, sakit, dan izin khusus secara mudah, transparan, dan efisien dalam satu platform terintegrasi.</p>

  		 	 <div class="developer-credit">
       			 Developed by <strong>Vindy Chresika (14022400041) 4A-INF UAS Pak Gagah</strong>
   		 	</div>
		</div>
    </div>

    <!-- Sisi Kanan: Card Login -->
    <div class="login-right">
        <div class="login-card">
            <div class="login-card-header">
                <h2>Selamat Datang</h2>
                <p>Silakan masuk menggunakan akun Anda</p>
            </div>
            
            <form method="POST" action="">
                <div class="mb-1">
                    <label class="form-label">Alamat Email</label>
                </div>
                <div class="input-group-custom">
                    <input type="email" name="email" class="form-control-custom" required placeholder="nama@perusahaan.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    <i class="fa-regular fa-envelope"></i>
                </div>

                <div class="mb-1">
                    <label class="form-label">Password</label>
                </div>
                <div class="input-group-custom">
                    <input type="password" name="password" class="form-control-custom" required placeholder="••••••••">
                    <i class="fa-solid fa-lock"></i>
                </div>

                <div class="login-options">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">Ingat Saya</label>
                    </div>
                    <a href="#" onclick="Swal.fire('Fitur Lupa Password', 'Silakan hubungi administrator HR di admin@perusahaan.com untuk mengatur ulang password Anda.', 'info')" class="forgot-link">Lupa Password?</a>
                </div>

                <button type="submit" class="btn-login">Masuk Aplikasi</button>
            </form>

            <div class="demo-credentials mt-4">
                <div class="fw-bold mb-1 text-main"><i class="fa-solid fa-circle-info me-1"></i> Akun Demo:</div>
                <div class="d-flex justify-content-between mb-1">
                    <span>Admin:</span>
                    <span><code>admin@perusahaan.com</code> / <code>admin123</code></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Karyawan:</span>
                    <span><code>budi@perusahaan.com</code> / <code>karyawan123</code></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($error): ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Login Gagal',
        text: '<?= $error ?>',
        confirmButtonColor: '#6A50E5'
    });
</script>
<?php endif; ?>
</body>
</html>