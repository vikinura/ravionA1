
window.addEventListener('DOMContentLoaded', () => {


  if(window.updateCartCountElements) updateCartCountElements();

 const productDetailContainer = document.getElementById('product-detail');
  if(productDetailContainer){
    const tambahKeranjangBtn = document.getElementById('tambah-keranjang');
    if (tambahKeranjangBtn) {
        tambahKeranjangBtn.addEventListener('click', (e) => {
            const productId = e.target.getAttribute('data-id');
            const qty = parseInt(document.getElementById('qty').value) || 1;
            const sizeElement = document.querySelector('.size-selector .size-btn.selected');
            const size = sizeElement ? sizeElement.textContent : null;

            if (!size) {
                return alert('Pilih ukuran terlebih dahulu!');
            }
            
           window.addToCart(productId, qty, size);
            alert(`Produk ${productId} (Size ${size}) berhasil ditambahkan ke keranjang`);
        });
    }
  }

  // Jika halaman cart -> render cart page (gunakan AJAX untuk fetch data produk jika diperlukan)
  if(document.getElementById('cart-items') && window.renderCartPage) {
    renderCartPage();
  }

  // jika checkout page -> hookup form & render summary
  if(document.getElementById('checkoutForm')) {
    if(window.hookupCheckoutForm) hookupCheckoutForm();
    if(window.renderCheckoutSummary) renderCheckoutSummary();
  }

  // --- LOGIKA MODAL UKURAN (disederhanakan) ---
  const sizeModal = document.getElementById('size-modal');
  if (sizeModal) {
      const closeBtn = document.getElementById('close-size-modal');
      const confirmBtn = document.getElementById('confirm-size-btn');
      
      // Tombol "Tambah" di Grid akan memicu modal
      document.querySelectorAll('.btn-tambah').forEach(btn => {
          btn.addEventListener('click', (e) => {
              window.tempProductId = e.target.getAttribute('data-id');
              const card = e.target.closest('.product-card');
              const productName = card ? card.querySelector('.name').textContent : 'Produk';
              
              const titleEl = document.getElementById('size-modal-product');
              if (titleEl) titleEl.textContent = productName;
              sizeModal.style.display = 'flex';
          });
      });

      // Logika menutup modal tetap sama
      if (closeBtn) closeBtn.onclick = () => { 
          sizeModal.style.display = 'none'; 
          window.selectedTempSize = null; 
          document.querySelectorAll('#size-options .size-btn').forEach(b => b.classList.remove('selected'));
      };
      
      window.onclick = (event) => { 
          if (event.target === sizeModal) { 
              sizeModal.style.display = 'none'; 
              window.selectedTempSize = null; 
              document.querySelectorAll('#size-options .size-btn').forEach(b => b.classList.remove('selected'));
          } 
      };

      // Logika memilih ukuran dalam modal
      document.querySelectorAll('#size-options .size-btn').forEach(btn => {
          btn.addEventListener('click', (e) => {
              document.querySelectorAll('#size-options .size-btn').forEach(b => b.classList.remove('selected'));
              e.target.classList.add('selected');
          });
      });


      // Logika Konfirmasi (Tombol di dalam Modal)
      if (confirmBtn) confirmBtn.addEventListener('click', () => {
        const selectedSizeElement = document.querySelector('#size-options .size-btn.selected');
        const selectedSize = selectedSizeElement ? selectedSizeElement.textContent : null;

        if (!selectedSize) {
          alert('Pilih ukuran terlebih dahulu!'); 
          return;
        }
          
        window.addToCart(window.tempProductId, 1, selectedSize);
        alert(`Produk berhasil ditambahkan (Size ${selectedSize})`);
        sizeModal.style.display = 'none';

        // Reset tampilan tombol setelah ditutup
        document.querySelectorAll('#size-options .size-btn').forEach(b => b.classList.remove('selected'));
      });
  }
});