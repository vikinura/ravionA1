// main.js — sidebar toggles & active links
document.addEventListener('DOMContentLoaded', ()=>{
  // submenu toggle
  document.querySelectorAll('.parent-btn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const target = btn.dataset.target;
      const el = document.getElementById(target);
      if(!el) return;
      // toggle
      const visible = el.style.display === 'flex';
      // close all submenus first
      document.querySelectorAll('.submenu').forEach(s=> s.style.display = 'none');
      if(!visible) el.style.display = 'flex';
    });
  });

  // auto-open submenu if current page belongs to it
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
