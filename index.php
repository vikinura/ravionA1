<?php
require 'db_connect.php'; 

$message = "";       
$msg_type = "";      
$success = false;    

if (isset($_POST['register'])) {
    
    $fullname = htmlspecialchars($_POST['fullname']);
    $username = htmlspecialchars($_POST['username']);
    $email    = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT * FROM users2 WHERE email='$email' OR username='$username'");
    
    if (mysqli_num_rows($check) > 0) {
        $message = "Username atau Email sudah terdaftar!";
        $msg_type = "error";
    } else {
        $query = mysqli_query($conn, 
            "INSERT INTO users2 (fullname, username, email, password, role2)
            VALUES('$fullname', '$username', '$email', '$password', 'user')");

        if ($query) {
            $success = true;
        } else {
            $message = "Terjadi kesalahan sistem. Silakan coba lagi.";
            $msg_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Ravion Store</title>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        :root {
            --primary: #0a0a0a;
            --accent: #d32f2f;
            --bg: #f8f9fa;
            --text: #111;
            --border: #e5e5e5;
            --error-bg: #ffebee;
            --error-text: #b71c1c;
            --success-color: #1b5e20; 
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--text);
        }

        .auth-card {
            background: #fff;
            width: 100%;
            max-width: 420px;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
            text-align: center;
            border: 1px solid rgba(0,0,0,0.04);
        }

        .brand-logo {
            width: 80px; 
            height: auto; 
            margin-bottom: 20px; 
            display: block;      
            margin-left: auto;   
            margin-right: auto;  
            object-fit: contain;
            background-color: #000; 
            padding: 12px;
            border-radius: 12px;
        }

        h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--primary);
        }

        p.subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 25px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 20px;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert.error {
            background-color: var(--error-bg);
            color: var(--error-text);
            border: 1px solid #ffcdd2;
        }

        .success-box {
            padding: 20px 0;
            animation: fadeIn 0.5s ease;
        }
        
        .success-title {
            color: var(--success-color);
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .success-desc {
            color: #555;
            font-size: 15px;
            margin-bottom: 30px;
        }

        .btn-login-now {
            display: block;
            width: 100%;
            padding: 15px;
            background-color: var(--primary);
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            border-radius: 8px;
            transition: 0.3s;
        }

        .btn-login-now:hover {
            background-color: var(--accent);
        }
        
        .form-group { margin-bottom: 15px; text-align: left; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--primary); }
        .form-control { width: 100%; padding: 12px 16px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px; transition: all 0.2s ease; background-color: #fafafa; }
        .form-control:focus { outline: none; border-color: var(--primary); background: #fff; }

        .btn-block { width: 100%; padding: 14px; border: none; background-color: var(--primary); color: #fff; font-size: 15px; font-weight: 600; border-radius: 8px; cursor: pointer; transition: background 0.3s; margin-top: 10px; }
        .btn-block:hover { background-color: var(--accent); }

        .auth-footer { margin-top: 25px; padding-top: 20px; border-top: 1px solid #f0f0f0; font-size: 13px; color: #666; }
        .auth-footer a { color: var(--primary); font-weight: 700; text-decoration: none; }
        
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <div class="auth-card">
        <img src="img/LOGo2.png" alt="Ravion Logo" class="brand-logo">
        
        <?php if ($success): ?>

            <div class="success-box">
                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#1b5e20" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:15px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                
                <h3 class="success-title">Registrasi Berhasil!</h3>
                <p class="success-desc">Akun Anda telah dibuat. Silakan Login.</p>
                
                <a href="login.php" class="btn-login-now">Login Disini</a>
            </div>

        <?php else: ?> 
            
            <h2>Create Account</h2>
            <p class="subtitle">Buat akun member baru</p>

            <?php if ($message != ""): ?>
                <div class="alert <?= $msg_type ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span><?= $message ?></span>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="fullname" class="form-control" placeholder="Contoh: Budi Santoso" value="<?= isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : '' ?>" required>
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Buat username unik" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="nama@email.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                </div>

                <button type="submit" name="register" class="btn-block">Daftar Member</button>
            </form>

            <div class="auth-footer">
                Sudah punya akun? <a href="login.php">Login Disini</a>
            </div>

        <?php endif; ?> </div>

</body>
</html>