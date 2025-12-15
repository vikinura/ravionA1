async function renderCartPage(){
  const container = document.getElementById('cart-items');
  const summary = document.getElementById('cart-summary');

  if(!container || !summary) return;
  const cart = await getCart();
  if(cart.length === 0){
    container.innerHTML = '<p>Keranjang kosong — <a href="products.php">Belanja sekarang</a></p>'; 
    summary.innerHTML = '';
    return;
  }

  const products = await fetchProducts();
  let html = '';
  let total = 0;

  for(const item of cart){
    const p = products.find(x=>String(x.id)===String(item.product_id || item.id));
    if(!p) continue;
    const qty = item.qty || 0;
    const subtotal = p.price * qty;
    total += subtotal;
    html += `
      <div class="cart-item">
        <img src="${escapeHtml(p.image||'')}" alt="${escapeHtml(p.title||'')}" />
        <div style="flex:1">
          <div style="font-weight:700">${escapeHtml(p.title||'')}</div>
          <div style="font-size:13px;color:gray">Ukuran: ${escapeHtml(item.size || '-')}</div>
          <div style="color:var(--muted)">${escapeHtml(p.brand||'')}</div>
          <div style="margin-top:8px">
            Rp ${formatRupiah(p.price)} x ${qty} = <strong>Rp ${formatRupiah(subtotal)}</strong>
          </div>
          <div style="margin-top:8px">
            <button onclick="updateQty('${p.id}', ${qty - 1}, '${item.size || ''}')">-</button>
            <span style="padding:0 10px">${qty}</span>
            <button onclick="updateQty('${p.id}', ${qty + 1}, '${item.size || ''}')">+</button>
            <button style="margin-left:12px" onclick="removeFromCart('${p.id}', '${item.size || ''}')">Hapus</button>
          </div>
        </div>
      </div>
    `;
  }

  container.innerHTML = html;
  summary.innerHTML = `
    <div style="font-weight:700">Ringkasan Pesanan</div>
    <div style="margin-top:8px">Total: <strong>Rp ${formatRupiah(total)}</strong></div>
    <div class="cart-actions">
      <a class="btn ghost" href="products.php">Lanjut Belanja</a> 
      <a class="btn" href="checkout.php">Checkout</a> 
    </div>
  `;
}

// expose (already in cart.js)
window.renderCartPage = renderCartPage;