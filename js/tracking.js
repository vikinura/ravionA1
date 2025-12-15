/* tracking.js
 - fitur lacak pesanan
*/
function setupTracking() {
  const trackBtn = document.getElementById('track-btn');
  const input = document.getElementById('order-id-input');
  const result = document.getElementById('tracking-result');

  if (!trackBtn) return;
  trackBtn.addEventListener('click', () => {
    const id = input.value.trim().toUpperCase();
    if (!id) {
      result.innerHTML = '<p style="color:red;">Masukkan nomor pesanan terlebih dahulu.</p>';
      return;
    }

    const orders = JSON.parse(localStorage.getItem('orders') || '[]');
    const order = orders.find(o => o.id === id);

    if (!order) {
      result.innerHTML = '<p style="color:red;">Nomor pesanan tidak ditemukan.</p>';
      return;
    }

    const statuses = ['Diproses', 'Dikirim', 'Dalam Perjalanan', 'Selesai'];
    let currentIndex = statuses.indexOf(order.status);
    if (currentIndex < statuses.length - 1) currentIndex++;
    order.status = statuses[currentIndex];

    const updatedOrders = orders.map(o => (o.id === order.id ? order : o));
    localStorage.setItem('orders', JSON.stringify(updatedOrders));

    const steps = statuses.map((s, i) => `
      <div class="step ${i <= currentIndex ? 'active' : ''}" title="${s}">
        ${i + 1}
      </div>
    `).join('');

    result.innerHTML = `
      <div class="card">
        <p><strong>No. Pesanan:</strong> ${order.id}</p>
        <p><strong>Tanggal Pesanan:</strong> ${order.date}</p>
        <p><strong>Status Saat Ini:</strong> ${order.status}</p>
        <p><strong>Perkiraan Sampai:</strong> ${order.estimate}</p>
        <div class="tracking-steps">${steps}</div>
      </div>
    `;
  });
}

// expose
window.setupTracking = setupTracking;
