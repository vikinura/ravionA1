<?php
session_start();
require 'db_connect.php'; 

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query  = "SELECT * FROM users2 WHERE username='$username' OR email='$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);

        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id']  = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role2']    = $row['role2'];

            if ($row['role2'] == 'admin') {
                header("Location: ADMIN/adminpage.php");
            } else {
                header("Location: homeuser.php");
            }
            exit;
        } else {
            $error = "Password yang Anda masukkan salah.";
        }
    } else {
        $error = "Username atau Email tidak ditemukan.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ravion Store</title>
    
    <style>

        :root {
            --primary: #0a0a0a;   
            --accent: #d32f2f;    
            --bg: #f8f9fa;
            --text: #111;
            --gray: #666;
            --border: #e5e5e5;
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
            max-width: 400px; 
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
            text-align: center;
            border: 1px solid rgba(0,0,0,0.04);
            animation: fadeIn 0.5s ease-out;
        }
        .brand-logo {
            height: 80px;
            margin-bottom: 20px;
            display: block;
            margin-left: auto;
            margin-right: auto;
            background-color: #000; 
            padding: 12px;          
            border-radius: 12px;    
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--primary);
            letter-spacing: -0.5px;
        }

        p.subtitle {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--primary);
        }

        .form-control {
            width: 100%;
            padding: 14px 16px; 
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.2s ease;
            background-color: #fafafa;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(0,0,0,0.05);
        }

        .btn-block {
            width: 100%;
            padding: 14px;
            border: none;
            background-color: var(--primary);
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }

        .btn-block:hover {
            background-color: var(--accent);
        }

        .alert-error {
            background: #ffebee;
            border: 1px solid #ffcdd2;
            color: #b71c1c;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .auth-footer {
            margin-top: 30px;
            font-size: 13px;
            color: var(--gray);
        }

        .auth-footer a {
            color: var(--primary);
            font-weight: 700;
            text-decoration: none;
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="auth-card">
        <img src="img/LOGo2.png" alt="Ravion Logo" class="brand-logo">
        
        <h2>Welcome Back</h2>
        <p class="subtitle">Masuk ke akun Anda</p>

        <?php if(isset($error)): ?>
            <div class="alert-error">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                </svg>
                <span><?= $error ?></span>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="username">Username / Email</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
            </div>

            <button type="submit" name="login" class="btn-block">Sign In</button>
        </form>

        <div class="auth-footer">
            Belum punya akun? <a href="index.php">Daftar Sekarang</a>
        </div>
    </div>

</body>
</html>