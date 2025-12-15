<?php

?>
  </main> <footer class="site-footer">
  <div class="footer-container">
    <div class="footer-left">
      <img src="img/LOGo2.png" alt="Ravion Store" class="footer-logo">
      <p>Ravion Store — sepatu original pilihan terbaik untuk gaya kamu.</p>
    </div>

    <div class="footer-right">
      <h4>Kontak Kami</h4>
      <p>Email: <a href="mailto:support@ravionstore.com">support@ravionstore.com</a></p>
      <p>Instagram: <a href="https://instagram.com/ravionstore" target="_blank">@ravionstore</a></p>
      <p>WhatsApp: <a href="https://wa.me/6281234567890" target="_blank">+62 812-3456-7890</a></p>
    </div>
  </div>

  <div class="footer-bottom">
    <p>&copy; <span id="year"></span> Ravion Store — All Rights Reserved.</p>
  </div>
</footer>

<script src="js/ui-helpers.js"></script> 
<script src="js/main-init.js"></script> 
<script>
    document.getElementById('year').textContent = new Date().getFullYear();
    window.addEventListener('DOMContentLoaded', () => {
      if(window.updateCartCountElements) updateCartCountElements();
    });
</script>

</body>
</html>