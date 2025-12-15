<?php

$page_title = 'Daftar Produk'; 
require 'header.php'; 

?>

<main class="container cart-section">
    <h2>Keranjang Belanja</h2>
     <div id="cart-items"></div>
    <div id="cart-summary" class="cart-summary"></div>
  </main>

<script src="js/ui-helpers.js"></script>
<script src="js/cart.js"></script>
<script src="js/cart-page.js"></script>
<script src="js/main-init.js"></script>

<?php

$page_title = 'Daftar Produk'; 
require 'footer.php'; 

?>

  <script>
    window.addEventListener('DOMContentLoaded', () => {
      renderCartPage();
      updateCartCountElements();
    });
  </script>
</body>
</html>
