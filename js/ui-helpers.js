
function formatRupiah(num){
  return Number(num).toLocaleString('id-ID');
}
function escapeHtml(s){ return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

function changeMainImage(src, el) {
  const main = document.getElementById('main-product-image');
  if(main) main.src = src;
  document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
  if(el) el.classList.add('active');
}

function selectSize(btn) {
  document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('selected'));
  btn.classList.add('selected');
}

function changeQty(delta){
  const el = document.getElementById('product-qty');
  if(!el) return;
  let v = parseInt(el.value) || 1;
  v += delta;
  if(v < 1) v = 1;
  el.value = v;
}

function copyToClipboard(text) {
  if (!navigator.clipboard) {
    // fallback
    const ta = document.createElement('textarea');
    ta.value = text;
    document.body.appendChild(ta);
    ta.select();
    try { document.execCommand('copy'); alert('Nomor berhasil disalin!'); } catch(e) {}
    ta.remove();
    return;
  }
  navigator.clipboard.writeText(text).then(()=> {
    alert('Nomor berhasil disalin!');
  });
}

// expose
window.formatRupiah = formatRupiah;
window.escapeHtml = escapeHtml;
window.changeMainImage = changeMainImage;
window.selectSize = selectSize;
window.changeQty = changeQty;
window.copyToClipboard = copyToClipboard;
