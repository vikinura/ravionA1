// add-product.js
const STORAGE_KEY = 'ravion_store_db_v1';

function readDB(){
  const raw = localStorage.getItem(STORAGE_KEY);
  if(!raw){
    const seed = { products: [], orders: [] };
    localStorage.setItem(STORAGE_KEY, JSON.stringify(seed));
    return seed;
  }
  try { return JSON.parse(raw); } catch(e){ return {products:[],orders:[]}; }
}
function writeDB(db){ localStorage.setItem(STORAGE_KEY, JSON.stringify(db)); }
function uid(){ return 'id'+Math.random().toString(36).slice(2,9); }
function numberWithSeparators(x){ return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."); }

document.addEventListener('DOMContentLoaded', ()=>{
  const photosEl = document.getElementById('photos');
  const input = document.getElementById('photoInput');
  const saveBtn = document.getElementById('saveBtn');

  let staged = []; // dataURLs

  function renderPhotos() {
    photosEl.innerHTML = "";
    for (let i = 0; i < 5; i++) {
        const div = document.createElement("div");
        div.className = "photo-placeholder";

        if (staged[i]) {
            const img = document.createElement("img");
            img.src = URL.createObjectURL(staged[i]);   // ⬅ pakai file asli
            img.style.width = "100%";
            img.style.height = "100%";
            img.style.objectFit = "cover";
            div.appendChild(img);
        } else {
            div.textContent = (i === 0 ? "Cover" : "+");
        }
        photosEl.appendChild(div);
    }
}

  renderPhotos();

 input.addEventListener("change", (e) => {
    const selected = Array.from(e.target.files).slice(0, 5);
    staged = selected;     // ⬅ simpan file asli
    renderPhotos();
});


  function fileToDataURL(file){
    return new Promise((res,rej)=>{
      const fr = new FileReader();
      fr.onload = ()=>res(fr.result);
      fr.onerror = rej;
      fr.readAsDataURL(file);
    });
  }

  document.querySelector("#formAddProduct").addEventListener("submit", async function(e) {
  e.preventDefault();

  const formData = new FormData(this);

if (formData.has("rating")) {
    let r = formData.get("rating") + "";
    r = r.replace(",", ".");
    formData.set("rating", r);
}

  const response = await fetch("add_product.php", {
    method: "POST",
    body: formData
});

  const result = await response.text();
  alert(result);
  });
});