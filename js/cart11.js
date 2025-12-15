/* cart.js
 - util cart: communicate with server-side cart (no localStorage)
*/
async function httpGetJson(url) {
  const res = await fetch(url, { credentials: 'same-origin' });
  return res.json();
}

async function httpPostForm(url, dataObj) {
  const fd = new URLSearchParams();
  for (const k in dataObj) fd.append(k, dataObj[k]);
  const res = await fetch(url, {
    method: 'POST',
    body: fd,
    credentials: 'same-origin',
    headers: { 'Accept': 'application/json' }
  });
  return res.json();
}

async function getCart(){
  const r = await httpGetJson('cart_action.php?action=list&ajax=1');
  return r.items || [];
}

async function addToCart(productId, qty=1, size=null){
  // call add repeatedly qty times or use set if server supports qty parameter
  for (let i=0;i<qty;i++) {
    await httpGetJson(`cart_action.php?action=add&id=${encodeURIComponent(productId)}&size=${encodeURIComponent(size||'')}&ajax=1`);
  }
  if (typeof window.renderCartPage === 'function') window.renderCartPage();
}

async function removeFromCart(productId, size=null){
  await httpGetJson(`cart_action.php?action=rem&id=${encodeURIComponent(productId)}&size=${encodeURIComponent(size||'')}&ajax=1`);
  if (typeof window.renderCartPage === 'function') window.renderCartPage();
}

async function updateQty(productId, qty, size=null){
  await httpGetJson(`cart_action.php?action=set&id=${encodeURIComponent(productId)}&qty=${encodeURIComponent(qty)}&size=${encodeURIComponent(size||'')}&ajax=1`);
  if (typeof window.renderCartPage === 'function') window.renderCartPage();
}

async function cartTotalCount(){
  const items = await getCart();
  return items.reduce((s,i)=>s + (parseInt(i.qty)||0), 0);
}

async function updateCartCountElements(){
  const n = await cartTotalCount();
  ['cart-count','cart-count-2','cart-count-3','cart-count-4','cart-count-5'].forEach(id=>{
    const el = document.getElementById(id);
    if(el) el.textContent = n;
  });
}

// expose
window.getCart = getCart;
window.addToCart = addToCart;
window.removeFromCart = removeFromCart;
window.updateQty = updateQty;
window.cartTotalCount = cartTotalCount;
window.updateCartCountElements = updateCartCountElements;