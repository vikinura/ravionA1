const CART_KEY = 'current_cart'; 

function copyToClipboard(text) {
  navigator.clipboard.writeText(text).then(() => {
    alert("Nomor berhasil disalin!");
  }, (err) => {
    console.error('Could not copy text: ', err);
  });
}

function formatRupiah(number) {
    if (typeof number !== 'number') return number;
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number);
}


function hookupCheckoutForm(){
  const formCheckout = document.getElementById('checkoutForm');
  const modal = document.getElementById('payment-modal'); 
  const modalBody = document.getElementById('payment-info');

  if (!formCheckout || !modal || !modalBody) {
    console.error('Checkout form atau modal tidak ditemukan.');
    return;
  }

  const closeModalBtn = modal.querySelector('.close');
  if(closeModalBtn) {
      closeModalBtn.onclick = () => modal.style.display = 'none';
  }
  
  formCheckout.addEventListener('submit', (e) => {
    e.preventDefault(); 

    const metode = formCheckout.elements['metode']?.value;
    
    if (!metode) {
      alert('Pilih metode pembayaran terlebih dahulu!');
      return;
    }

    let html = '';

    if (metode === 'qris') {
      html = `
        <h3>Pembayaran QRIS</h3>
        <p>Scan kode QR berikut untuk menyelesaikan pembayaran:</p>
        <img src="img/QRISS.jpg" alt="QRIS" style="width:200px;height:auto;border-radius:8px;margin-top:10px;">
        <p style="margin-top:15px; font-weight:bold;">Pastikan Anda sudah membayar sebelum konfirmasi!</p>
        <button id="confirm-payment" class="btn btn-bayar" style="width:100%;margin-top:10px;">Konfirmasi & Proses Pesanan</button>
      `;
    } else if (metode === 'va-bca') {
      html = `
        <h3>Virtual Account BCA</h3>
        <p>Nomor Virtual Account:</p>
        <h2>1234567890</h2>
        <button class="copy-btn" onclick="copyToClipboard('1234567890')">Salin Nomor</button>
        <p style="margin-top:15px; font-weight:bold;">Pastikan Anda sudah membayar sebelum konfirmasi!</p>
        <button id="confirm-payment" class="btn btn-bayar" style="width:100%;margin-top:10px;">Konfirmasi & Proses Pesanan</button>
      `;
    } else if (metode === 'tf-bca') {
      html = `
        <h3>Transfer Bank BCA</h3>
        <p>Silakan transfer ke rekening berikut:</p>
        <h2>0987654321</h2>
        <p>a.n. Ravion Store</p>
        <button class="copy-btn" onclick="copyToClipboard('0987654321')">Salin Rekening</button>
        <p style="margin-top:15px; font-weight:bold;">Pastikan Anda sudah membayar sebelum konfirmasi!</p>
        <button id="confirm-payment" class="btn btn-bayar" style="width:100%;margin-top:10px;">Konfirmasi & Proses Pesanan</button>
      `;
    }

    modalBody.innerHTML = html;
    modal.style.display = 'flex';

    setTimeout(() => {
      const confirmBtn = document.getElementById('confirm-payment');
      if (confirmBtn) {
        confirmBtn.addEventListener('click', () => {
          if(modal) modal.style.display = 'none';
          
          // 3. Meneruskan submit form ke server
          // Ini akan mengirim data ke checkout_process.php
          formCheckout.submit(); 
        });
      }
    }, 100);
  });
}


document.addEventListener('DOMContentLoaded', hookupCheckoutForm);