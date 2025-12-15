<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role2'] != 'admin') {
    header("Location: ../login.php");
    exit;
}
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Ravion Store — Admin Page</title>
  <link rel="stylesheet" href="style_admin.css">
</head>
<body>
  <div class="app">
    <aside class="sidebar">
      <div class="brand">
        <img src="../img/LOGo2.png" alt="Ravion Logo" class="logo-img">
        <h1>Ravion Store</h1>
      </div>

      <div class="nav-title">Navigations</div>
      <nav class="menu">
        <div class="menu-parent">
          <button class="parent-btn" data-target="home-sub">
            <span class="icon">🏠</span><span class="label">Home</span><span class="chev">›</span>
          </button>
          <div id="home-sub" class="submenu">
            <a href="../index.php">Main Page</a>
          </div>
        </div>

        <div class="menu-parent">
          <button class="parent-btn" data-target="product-sub">
            <span class="icon">🛍️</span><span class="label">Product</span><span class="chev">›</span>
          </button>
          <div id="product-sub" class="submenu">
            <a href="view_product.php">View</a>
            <a href="add_product.html">Add Product</a>

          </div>
        </div>

        <a href="ordering.php" class="single-link"><span class="icon">📦</span><span class="label">Ordering</span><span class="chev">›</span></a>
      </nav>
    </aside>

    <div class="container">
      <div class="page-card">
        <div class="big-card">
          <div class="welcome">
            <h2>Halo, <?php echo $_SESSION['username']; ?>!</h2>
            <p>Selamat datang di,</p>
            <img src="../img/LOGo2.png" class="logo-big" alt="logo">
            <h3>Ravion Store</h3>
            <p class="small">Admin area — tempat pengaturan.</p>
          </div>
        </div>
      </div>
    </div>

    <a href="../logout.php" style="margin-left:20px;">Logout</a>
  </div>

  <script src="main.js"></script>
</body>
</html>
