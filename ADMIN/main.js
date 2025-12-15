
document.addEventListener('DOMContentLoaded', ()=>{
  
  document.querySelectorAll('.parent-btn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const target = btn.dataset.target;
      const el = document.getElementById(target);
      if(!el) return;
      const visible = el.style.display === 'flex';
      document.querySelectorAll('.submenu').forEach(s=> s.style.display = 'none');
      if(!visible) el.style.display = 'flex';
    });
  });

  const path = location.pathname.split('/').pop();
  if(path === '' || path === 'index.html' || path === 'admin.html'){
    const el = document.getElementById('home-sub');
    if(el) el.style.display = 'flex';
  }
  if(path.startsWith('product') || path === 'add-product.html' || path === 'product.html'){
    const el = document.getElementById('product-sub');
    if(el) el.style.display = 'flex';
  }
});
