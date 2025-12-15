<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role2'] != 'user') {
    header("Location: header  .php");
    exit;
}
$username = $_SESSION['username'];
?>


<?php
$display_username = $username ?? null; 
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>ShoeShop — <?php echo $page_title ?? 'Beranda'; ?></title>
  <link href="style.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<header class="navbar">
  <div class="logo">
    <img src="img/LOGo2.png" alt="Ravion Logo" />
    <h1>Ravion Store</h1>
  </div>

  <nav class="nav-links">
    <a href="homeuser.php">Home</a>
    <a href="products.php">Products</a>
    <a href="cart.php" class="cart-link">
      <img src="img/Ikon keranjang.jpeg" alt="Keranjang" class="cart-icon">
      <span id="cart-count" class="cart-count">0</span>
    </a>
    <a href="history_pemesanan.php" class="track-link">
      <span>History</span>
    </a>
    <?php if ($display_username): ?>
        <span style="margin-left:12px; font-weight:600;">Halo, <?= htmlspecialchars($display_username) ?></span>
        <a href="logout.php" style="margin-left:8px; color:#FFFFFF;">Logout</a>
    <?php endif; ?>
  </nav>
</header>