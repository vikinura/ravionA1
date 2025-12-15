<?php
require 'db_connect.php';

// Ambil semua produk
$sql  = "SELECT * FROM products ORDER BY created_at DESC";
$res  = $conn->query($sql);

// Pesan notifikasi
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Product - Ravion Admin</title>
    <link rel="stylesheet" href="style_admin.css">

    <style>
        /* Paksa Modal agar di atas segalanya */
        #modalOverlay {
            display: none;
            /* Default sembunyi */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 9999 !important;
            /* Angka z-index sangat tinggi */
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(2px);
        }

        .modal-content {
            background: #fff;
            padding: 30px;
            width: 400px;
            max-width: 90%;
            border-radius: 4px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            text-align: center;
            border: 1px solid #000;
        }
    </style>
</head>

<body>
    <div class="app">

        <aside class="sidebar">
            <div class="brand">
                <img src="../img/LOGo2.png" alt="Logo" class="logo-img">
                <h1>Ravion Store</h1>
            </div>

            <div class="nav-title">Navigations</div>
            <nav class="menu">
                <div class="menu-parent">
                    <button class="parent-btn" data-target="home-sub">
                        <span class="icon">🏠</span><span class="label">Home</span><span class="chev">›</span>
                    </button>
                    <div id="home-sub" class="submenu">
                        <a href="../homeuser.php">Main Page</a>
                    </div>
                </div>

                <div class="menu-parent">
                    <button class="parent-btn" data-target="product-sub">
                        <span class="icon">🛍️</span><span class="label">Product</span><span class="chev">›</span>
                    </button>
                    <div id="product-sub" class="submenu" style="display:flex;">
                        <a href="view_product.php" style="color: #fff;">View</a>
                        <a href="add_product.html">Add Product</a>
                    </div>
                </div>

                <a href="ordering.php" class="single-link">
                    <span class="icon">📦</span><span class="label">Ordering</span><span class="chev">›</span>
                </a>
            </nav>
        </aside>

        <main class="container">

            <div class="flex-between">
                <h2>Product List</h2>
                <a href="add_product.html" class="btn">Add New Product</a>
            </div>

            <?php if ($msg): ?>
                <div style="background: #333; color: #fff; padding: 15px; margin-bottom: 20px; border-left: 5px solid #27ae60;">
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <div class="page-card">
                <div style="overflow-x:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Brand</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Category</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($res && $res->num_rows > 0): ?>
                                <?php $no = 1;
                                while ($row = $res->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <?php
                                            $img = !empty($row['image1']) ? "../upload/" . $row['image1'] : "https://via.placeholder.com/60";
                                            ?>
                                            <img src="<?= htmlspecialchars($img) ?>" alt="img">
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($row['name']) ?></strong>
                                            <div style="font-size:11px; color:#888;">Size: <?= htmlspecialchars($row['size']) ?></div>
                                        </td>
                                        <td><?= htmlspecialchars($row['brand']) ?></td>
                                        <td>Rp <?= number_format($row['price'], 0, ',', '.') ?></td>
                                        <td>
                                            <?= $row['stock'] ?>
                                            <?php if ($row['stock'] < 5) echo '<span style="color:red; font-size:10px;">(Low)</span>'; ?>
                                        </td>
                                        <td><?= htmlspecialchars($row['categories']) ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="edit_product.php?id=<?= $row['id_product'] ?>" class="btn-action btn-edit">Edit</a>

                                                <button type="button"
                                                    class="btn-action btn-delete js-delete-btn"
                                                    data-id="<?php echo $row['id_product']; ?>"
                                                    data-name="<?php echo htmlspecialchars($row['name']); ?>">
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align:center; padding: 30px;">
                                        Belum ada data.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="modalOverlay">
        <div class="modal-content">
            <h3>Konfirmasi Hapus</h3>
            <p id="modalText">Apakah anda yakin?</p>

            <form id="deleteForm">
                <input type="hidden" name="id_product" id="deleteId" value="">

                <div class="modal-btns">
                    <button type="button" class="btn-ghost" id="btnCancelModal">Batal</button>
                    <button type="submit" class="btn-delete" style="border:none; padding:10px 20px;">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // 1. Sidebar Logic
            document.querySelectorAll('.parent-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const target = btn.dataset.target;
                    const el = document.getElementById(target);
                    if (el) el.style.display = (el.style.display === 'flex') ? 'none' : 'flex';
                });
            });

            // 2. MODAL LOGIC (Menggunakan Event Listener Otomatis)
            const modal = document.getElementById('modalOverlay');
            const inputId = document.getElementById('deleteId');
            const modalText = document.getElementById('modalText');
            const btnCancel = document.getElementById('btnCancelModal');

            // Cari semua tombol dengan class 'js-delete-btn'
            document.querySelectorAll('.js-delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    // Ambil data dari atribut tombol
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');

                    console.log("Tombol diklik. ID:", id, "Nama:", name); // Cek Console (F12) jika macet

                    // Isi form modal
                    inputId.value = id;
                    modalText.innerText = 'Hapus "' + name + '" secara permanen?';

                    // Tampilkan modal
                    modal.style.display = 'flex';
                });
            });

            // Fungsi Tutup Modal
            function closeModal() {
                modal.style.display = 'none';
            }

            // Event Listener Tutup Modal
            if (btnCancel) btnCancel.addEventListener('click', closeModal);

            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) closeModal();
                });
            }

        });
        //hapus produk
        const deleteForm = document.getElementById('deleteForm');

        deleteForm.addEventListener('submit', function(e) {
            e.preventDefault(); // ⛔ stop reload halaman

            const formData = new FormData(deleteForm);

            fetch('delete_product.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message); // 🔔 notifikasi

                    if (data.status === 'success') {
                        // tutup modal
                        document.getElementById('modalOverlay').style.display = 'none';

                        // reload halaman biar data update
                        location.reload();
                    }
                })
                .catch(err => {
                    alert('Terjadi kesalahan sistem');
                    console.error(err);
                });
        });
    </script>
</body>

</html>