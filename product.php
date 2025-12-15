<?php
require 'db_connect.php';

$page_title = 'Detail Produk';
require 'header.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;
$images = [];

if ($product_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id_product = ?");
    $stmt->bind_param("i", $product_id); 
    $stmt->execute();
    $result_detail = $stmt->get_result();

    if ($result_detail && $result_detail->num_rows == 1) {
        $product = $result_detail->fetch_assoc();
        
        for ($i = 1; $i <= 5; $i++) {
            if (!empty($product['image'.$i])) {
                $images[] = 'upload/' . $product['image'.$i];
            }
        }
        $sizes = explode(',', $product['size']);
    }
    $stmt->close();
}

if (!$product) {
    header("Location: products.php");
    exit();
}

$formattedPrice = number_format($product['price'], 0, ',', '.');
$formattedOldPrice = $product['old_price'] ? number_format($product['old_price'], 0, ',', '.') : null;
$mainImageSource = $images[0] ?? 'upload/no-image.png'; 
?>

<style>
    #toast-notification {
        visibility: hidden;
        min-width: 250px;
        background-color: #333; 
        color: #fff;
        text-align: center;
        border-radius: 50px;
        padding: 16px;
        position: fixed;
        z-index: 9999;
        left: 50%;
        bottom: 30px;
        transform: translateX(-50%);
        font-size: 15px;
        box-shadow: 0px 4px 15px rgba(0,0,0,0.2);
        opacity: 0;
        transition: opacity 0.5s, bottom 0.5s;
    }

    #toast-notification.show {
        visibility: visible;
        opacity: 1;
        bottom: 50px; 
    }

    @keyframes cartBump {
        0% { transform: scale(1); }
        50% { transform: scale(1.4); color: #ff4757; } 
        100% { transform: scale(1); color: inherit; }
    }

    .cart-bump-anim {
        animation: cartBump 0.3s ease-out;
    }
</style>

<main class="container product-detail-page">
    
    <div id="product-detail" class="product-detail"> 
        
        <div class="product-gallery">
            <div class="thumbnail-list" id="thumb-list">
                <?php foreach ($images as $i => $img): ?>
                    <img src="<?php echo $img; ?>" class="thumb <?php echo ($i === 0) ? 'active' : ''; ?>" 
                         onclick="changeMainImage('<?php echo $img; ?>', this)">
                <?php endforeach; ?>
            </div>
            <div class="main-image">
                <img id="main-product-image" src="<?php echo htmlspecialchars($mainImageSource); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
        </div>

        <div class="product-info">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <div style="color:var(--muted);margin-bottom:8px"><?php echo htmlspecialchars($product['brand']); ?> • Rating <?php echo $product['rating']; ?></div>
            <div class="price" style="font-size:22px">Rp <?php echo $formattedPrice; ?></div>
            <?php if ($formattedOldPrice): ?>
                <div style="text-decoration:line-through;color:var(--muted)">Rp <?php echo $formattedOldPrice; ?></div>
            <?php endif; ?>
            
            <p style="margin-top:12px;color:var(--muted)"><?php echo htmlspecialchars($product['description']); ?></p>

            <div class="size-selector">
                <label>Pilih Ukuran:</label>
                <div class="sizes" id="size-options">
                    <?php foreach ($sizes as $size): ?>
                        <button type="button" class="size-btn" data-size="<?php echo trim($size); ?>"><?php echo trim($size); ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="qty-row">
                <label>Qty:
                <button type="button" class="qty-btn-minus">-</button>
                <input id="product-qty" type="number" value="1" min="1" max="<?php echo $product['stock']; ?>" style="width:54px;text-align:center;margin:0 6px" /> 
                <button type="button" class="qty-btn-plus">+</button>
                </label>
            </div>

            <div style="display:flex;gap:12px;margin-top:12px">
                <form method="POST" action="cart_action.php" id="add-cart-form" style="display:inline-block">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id" value="<?php echo $product['id_product']; ?>">
                    <input type="hidden" name="size" id="selected-size" value="">
                    <input type="hidden" name="qty" id="form-qty" value="1">
                    <button class="btn" id="add-to-cart-btn" type="submit">Tambah ke Keranjang</button> 
                </form>

                <form method="POST" action="cart_action.php" id="buy-now-form" style="display:inline-block">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id" value="<?php echo $product['id_product']; ?>">
                    <input type="hidden" name="goto" value="checkout.php">
                    <input type="hidden" name="size" id="selected-size-2" value="">
                    <input type="hidden" name="qty" id="form-qty-2" value="1">
                    <button class="btn ghost" id="buy-now-btn" type="submit">Beli Sekarang</button>
                </form>
            </div>
        </div>
        
    </div>
    
    <div id="toast-notification">Produk berhasil ditambahkan!</div>

</main>


<?php require 'footer.php'; ?>

<script src="js/ui-helpers.js"></script> 
<script src="js/main-init.js"></script> 

<script>
  function changeMainImage(src, element) {
      document.getElementById('main-product-image').src = src;
      document.querySelectorAll('#thumb-list img').forEach(img => img.classList.remove('active'));
      element.classList.add('active');
  }

  document.addEventListener('DOMContentLoaded', () => {
    const qtyInput = document.getElementById('product-qty');
    const formAdd = document.getElementById('add-cart-form');
    const formBuy = document.getElementById('buy-now-form');
    const btnMinus = document.querySelector('.qty-btn-minus');
    const btnPlus = document.querySelector('.qty-btn-plus');
    const toast = document.getElementById('toast-notification'); 

    function showToast(message) {
        if(!toast) return;
        toast.innerText = message;
        toast.className = "show"; 
        setTimeout(function(){ toast.className = toast.className.replace("show", ""); }, 3000);
    }

    function syncHiddenQty(val) {
        if (document.getElementById('form-qty')) document.getElementById('form-qty').value = val;
        if (document.getElementById('form-qty-2')) document.getElementById('form-qty-2').value = val;
    }

    function updateQty(delta) {
        let current = parseInt(qtyInput.value) || 1;
        let next = current + delta;
        if (next < 1) next = 1;
        const maxStock = parseInt(qtyInput.getAttribute('max')) || 999;
        if (next > maxStock) next = maxStock;
        qtyInput.value = next;
        syncHiddenQty(next);
    }

    if (btnMinus) btnMinus.addEventListener('click', () => updateQty(-1));
    if (btnPlus) btnPlus.addEventListener('click', () => updateQty(1));
    if (qtyInput) {
        qtyInput.addEventListener('input', () => syncHiddenQty(parseInt(qtyInput.value) || 1));
    }

    document.querySelectorAll('.size-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('selected'));
            e.target.classList.add('selected');
            const s = e.target.getAttribute('data-size');
            const inp1 = document.getElementById('selected-size');
            if(inp1) inp1.value = s;
            const inp2 = document.getElementById('selected-size-2');
            if(inp2) inp2.value = s;
        });
    });

    function checkSizeSelected() {
        const selectedSizeEl = document.querySelector('.size-selector .size-btn.selected');
        if (!selectedSizeEl) {
            showToast('⚠️ Harap pilih ukuran sepatu!');
            return false;
        }
        return true;
    }

    if (formAdd) {
        formAdd.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!checkSizeSelected()) return;

            syncHiddenQty(qtyInput.value);

            const formData = new FormData(formAdd);
            formData.append('ajax', '1'); 

            const btn = document.getElementById('add-to-cart-btn');
            const originalText = btn.innerText;
            btn.innerText = 'Menambahkan...';
            btn.disabled = true;

            try {
                const response = await fetch('cart_action.php', { 
                    method: 'POST',
                    body: formData
                });

                const textResult = await response.text();
                let result;
                try {
                    result = JSON.parse(textResult);
                } catch (err) {
                    console.error("Server Error (Not JSON):", textResult);
                    throw new Error("Respon server bermasalah.");
                }

                if (result.status === 'success') {
                    const cartCountEl = document.getElementById('cart-count'); 
                    if (cartCountEl) cartCountEl.innerText = result.total_qty;
                    
                    showToast('✅ Berhasil masuk keranjang!');
                } else {
                    showToast('❌ Gagal: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('❌ Terjadi kesalahan sistem.');
            } finally {
                btn.innerText = originalText;
                btn.disabled = false;
            }
        });
    }

    if (formBuy) {
        formBuy.addEventListener('submit', (e) => {
            if (!checkSizeSelected()) {
                e.preventDefault(); 
            }
            syncHiddenQty(qtyInput.value);
        });
    }
  });
</script>

</body>
</html>