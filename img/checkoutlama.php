<?php

$page_title = 'Daftar Produk'; 
require 'header.php'; 

?>

  <main class="checkout-container">
    <section class="checkout-form">
      <h2>Data Pembeli</h2>
      <form id="checkoutForm">
        <label>Nama Lengkap</label>
        <input type="text" id="nama" required />

        <label>Email</label>
        <input type="email" id="email" required />

        <label>Nomor Telepon</label>
        <input type="tel" id="telepon" required />

        <label>Alamat Lengkap</label>
        <textarea id="alamat" required></textarea>

        <label>Metode Pembayaran</label>
        <select id="metode" required>
          <option value="">Pilih metode pembayaran</option>
          <option value="qris">QRIS</option>
          <option value="va-bca">Virtual Account BCA</option>
          <option value="tf-bca">Transfer BCA</option>
        </select>

        <button type="submit" class="btn-bayar">Bayar Sekarang</button>
      </form>
    </section>

    <section class="checkout-summary">
      <h2>Ringkasan Belanja</h2>
      <div id="checkout-items"></div>
      <div id="checkout-total"></div>
    </section>
  </main>


<div id="payment-modal" class="modal">
  <div class="modal-content">
    <span class="close" id="close-modal">&times;</span>
    <div id="payment-info"></div>
  </div>
</div>


<div id="payment-success-modal" class="modal">
  <div class="modal-content">
    <span class="close" id="close-success">&times;</span>
    <h3>Pembayaran Berhasil!</h3>
    <p>Terima kasih telah berbelanja di Ravion Store 🎉</p>
    <p>Nomor Pesanan Anda:</p>
    <p id="payment-receipt" class="receipt-code"></p>
    <div style="margin-top:15px;">
      <button id="track-order-btn" class="btn">Lacak Pesanan</button>
      <button id="back-to-home" class="btn ghost">Kembali ke Beranda</button>
    </div>
  </div>
</div>

<?php

require 'footer.php'; 

?>


<script src="js/ui-helpers.js"></script>
<script src="js/cart.js"></script>
<script src="js/checkout.js"></script>
<script src="js/main-init.js"></script>

<script>
  window.addEventListener('DOMContentLoaded', () => {
    renderCheckoutSummary();
    updateCartCountElements();
  });
    const modal = document.getElementById('payment-modal');
  const closeModal = document.getElementById('close-modal');
  
    closeModal.addEventListener('click', () => {
    modal.style.display = 'none';
  });
</script>
</body>
</html>
