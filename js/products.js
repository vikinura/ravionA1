const PRODUCTS_URL = 'product_api.php'; 
const PRODUCT_DETAIL_URL = 'detail_api.php';

async function fetchProducts(search = '', sort = 'default'){
  try{
  const url = `${PRODUCTS_URL}?search=${encodeURIComponent(search)}&sort=${encodeURIComponent(sort)}`; 
    const res = await fetch(url);
    const data = await res.json();
    return data;
  } catch(err){

    console.error('Gagal load produk dari API', err); 
    return [];
  }
}

  async function fetchProductDetail(id){  //untuk detail produk
    try{
    const url = `${PRODUCT_DETAIL_URL}?id=${encodeURIComponent(id)}`;
    const res = await fetch(url);
    const data = await res.json();
    if(data.error) throw new Error(data.error);
    return data;
  } catch(err){
    console.error('Gagal load detail produk dari API', err);
    return null;
  }
}

window.PRODUCTS_URL = PRODUCTS_URL;
window.fetchProducts = fetchProducts;
window.fetchProductDetail = fetchProductDetail;
