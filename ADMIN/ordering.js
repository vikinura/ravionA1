// ordering.js
const STORAGE_KEY = 'ravion_store_db_v1';
function readDB(){ const raw = localStorage.getItem(STORAGE_KEY); if(!raw){ const seed={products:[],orders:[]}; localStorage.setItem(STORAGE_KEY,JSON.stringify(seed)); return seed;} try{return JSON.parse(raw);}catch(e){return {products:[],orders:[]}} }
function writeDB(db){ localStorage.setItem(STORAGE_KEY, JSON.stringify(db)); }
function rupiah(v){ return v.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."); }
function uid(){ return 'id'+Math.random().toString(36).slice(2,9); }

document.addEventListener('DOMContentLoaded', ()=>{
  const monthInput = document.getElementById('analyticMonth');
  const today = new Date();
  const defaultMonth = `${today.getFullYear()}-${String(today.getMonth()+1).padStart(2,'0')}`;
  monthInput.value = defaultMonth;

  const totalRevenueEl = document.getElementById('totalRevenue');
  const totalOrdersEl = document.getElementById('totalOrders');
  const incomeEl = document.getElementById('income');
  const expensesEl = document.getElementById('expenses');
  const balanceEl = document.getElementById('balance');
  const genBtn = document.getElementById('genOrder');
  const topGrid = document.getElementById('topGrid');

  // Chart.js
  let chart = null;
  function buildChart(labels, data){
    const ctx = document.getElementById('ordersChart').getContext('2d');
    if(chart) chart.destroy();
    chart = new Chart(ctx, {
      type: 'line',
      data: { labels, datasets: [{ label:'Revenue', data, fill:true, borderColor:'#7EE787', backgroundColor:'rgba(126,231,135,0.08)', tension:0.35 }] },
      options: { plugins:{legend:{display:false}}, scales:{y:{ticks:{color:'#9ca3a6'}, beginAtZero:true}, x:{ticks:{color:'#9ca3a6'}}}, responsive:true, maintainAspectRatio:false }
    });
  }

  function refreshAll(){
    const db = readDB();
    const sel = monthInput.value; // "YYYY-MM"
    const monthly = db.orders.filter(o => o.date && o.date.startsWith(sel));
    const totalRev = monthly.reduce((s,o)=>s + (o.revenue||0), 0);
    const totalOrders = monthly.length;
    const expenses = Math.round(totalRev * 0.05);
    const balance = totalRev - expenses;

    totalRevenueEl.textContent = 'Rp ' + rupiah(totalRev);
    totalOrdersEl.textContent = totalOrders;
    incomeEl.textContent = 'Rp ' + rupiah(totalRev);
    expensesEl.textContent = 'Rp ' + rupiah(expenses);
    balanceEl.textContent = 'Rp ' + rupiah(balance);

    // chart labels & data
    const sorted = monthly.slice().sort((a,b)=> a.date.localeCompare(b.date));
    const labels = sorted.map(o=> o.date.split('-')[2] );
    const data = sorted.map(o=> o.revenue );
    buildChart(labels, data);

    // top selling
    topGrid.innerHTML = '';
    const products = db.products.slice().sort((a,b)=> (b.sold||0) - (a.sold||0)).slice(0,8);
    if(products.length === 0) topGrid.innerHTML = '<div class="card">No products yet.</div>';
    products.forEach(p=>{
      const div = document.createElement('div'); div.className = 'product-card';
      const thumb = document.createElement('div'); thumb.className='photo-placeholder'; thumb.style.height='120px';
      if(p.imgs && p.imgs[0]) { const im = document.createElement('img'); im.src = p.imgs[0]; thumb.innerHTML=''; thumb.appendChild(im); } else { thumb.textContent='No Image'; }
      const title = document.createElement('div'); title.style.fontWeight='800'; title.style.marginTop='8px'; title.textContent = p.title;
      const sub = document.createElement('div'); sub.style.color='var(--muted)'; sub.textContent = `${p.sold||0} Pcs`;
      div.appendChild(thumb); div.appendChild(title); div.appendChild(sub);
      topGrid.appendChild(div);
    });
  }

  genBtn.addEventListener('click', ()=>{
    const db = readDB();
    const d = new Date();
    const date = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
    const rev = Math.floor(500000 + Math.random()*10000000);
    db.orders.push({ date, revenue: rev });
    if(db.products && db.products.length){
      const p = db.products[Math.floor(Math.random()*db.products.length)];
      p.sold = (p.sold||0) + Math.floor(1 + Math.random()*10);
    }
    writeDB(db);
    refreshAll();
  });

  monthInput.addEventListener('change', refreshAll);

  // initialize seed if empty
  const dbNow = readDB();
  if(!dbNow.orders.length && !dbNow.products.length){
    // small seed example
    dbNow.products = [
      {id: uid(), title:'Air Jordan', price:4000000, size:'42', stock:600, category:'Shoes', desc:'Popular', imgs:[], sold:600},
      {id: uid(), title:'Sneaker X', price:2500000, size:'40', stock:120, category:'Shoes', desc:'Casual', imgs:[], sold:350}
    ];
    dbNow.orders = [
      {date: defaultMonth() + '-01', revenue: 2000000},
      {date: defaultMonth() + '-03', revenue: 3500000},
      {date: defaultMonth() + '-07', revenue: 2500000}
    ];
    writeDB(dbNow);
  }

  function defaultMonth(){ const d=new Date(); return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`; }
  function uid(){ return 'id'+Math.random().toString(36).slice(2,9); }

  // initial render
  refreshAll();
});
