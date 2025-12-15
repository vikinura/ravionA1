<?php
// ================= DATABASE CONNECT =================
$conn = new mysqli("localhost", "root", "", "web_login.");
if ($conn->connect_error) {
    die("DB Error: " . $conn->connect_error);
}

// =============== HANDLE AJAX REQUESTS ===============
if (isset($_GET["action"])) {

    // ---- 1. Ambil Dashboard Data ----
    if ($_GET["action"] == "dashboard") {
        $month = $_GET["month"];

        $q1 = $conn->query("
            SELECT SUM(total_harga) AS total 
            FROM orders 
            WHERE DATE_FORMAT(order_date, '%Y-%m') = '$month'
        ");
        $revenue = $q1->fetch_assoc()["total"] ?? 0;

        $q2 = $conn->query("
            SELECT COUNT(*) AS total 
            FROM orders 
            WHERE DATE_FORMAT(order_date, '%Y-%m') = '$month'
        ");
        $orders = $q2->fetch_assoc()["total"];

        $income = $revenue;
        $expenses = $revenue * 0.05;
        $balance = $income - $expenses;

        $g = $conn->query("
            SELECT DATE(order_date) AS d, SUM(total_harga) AS rev
            FROM orders
            WHERE DATE_FORMAT(order_date, '%Y-%m') = '$month'
            GROUP BY DATE(order_date)
            ORDER BY d
        ");

        $labels = [];
        $data = [];

        while ($r = $g->fetch_assoc()) {
            $labels[] = substr($r["d"], 8, 2);
            $data[] = (int)$r["rev"];
        }

        $top = $conn->query("
            SELECT p.name, SUM(oi.quantity) AS sold
            FROM order_items oi
            JOIN products p ON p.id_product = oi.product_id
            GROUP BY p.id_product
            ORDER BY sold DESC
            LIMIT 8
        ");

        $topData = [];
        while ($row = $top->fetch_assoc()) {
            $topData[] = $row;
        }

        echo json_encode([
            "revenue"     => (int)$revenue,
            "totalOrders" => (int)$orders,
            "income"      => (int)$income,
            "expenses"    => (int)$expenses,
            "balance"     => (int)$balance,
            "labels"      => $labels,
            "graph"       => $data,
            "top"         => $topData
        ]);
        exit;
    }

    // ---- 2. Ambil semua orders ----
    if ($_GET["action"] == "payments") {

        $orders = [];
        $q = $conn->query("
        SELECT * FROM orders
        WHERE status != 'confirmed'
        ORDER BY order_id DESC
        ");


        while ($o = $q->fetch_assoc()) {
            $oid = $o["order_id"];

            $itemsQ = $conn->query("
                SELECT oi.quantity, oi.price_at_purchase, p.name, p.image1
                FROM order_items oi
                JOIN products p ON p.id_product = oi.product_id
                WHERE oi.order_id = '$oid'
            ");

            $items = [];
            while ($i = $itemsQ->fetch_assoc()) {
                $items[] = $i;
            }

            $o["items"] = $items;
            $orders[] = $o;
        }

        echo json_encode($orders);
        exit;
    }

    // ---- 3. Konfirmasi pembayaran ----
    if ($_GET["action"] == "confirm") {
        $id = $_POST["order_id"];
        $conn->query("UPDATE orders SET status='confirmed' WHERE order_id='$id'");
        echo "OK";
        exit;
    }
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Ravion Store — Ordering</title>
    <link rel="stylesheet" href="style_admin.css">
</head>

<body>

    <div class="app">
        <aside class="sidebar">
            <div class="brand">
                <img src="../img/LOGo2.png" alt="Ravion Logo" class="logo-img">
                <h1>Ravion Store</h1>
            </div>

            <div class="nav-title">Navigations</div>
            <nav class="menu">
                <div class="menu-parent">
                    <button class="parent-btn" data-target="home-sub">
                        <span class="icon">🏠</span><span class="label">Home</span><span class="chev">›</span>
                    </button>
                    <div id="home-sub" class="submenu">
                        <a href="../index.php">Main Page</a>
                    </div>
                </div>

                <div class="menu-parent">
                    <button class="parent-btn" data-target="product-sub">
                        <span class="icon">🛍️</span><span class="label">Product</span><span class="chev">›</span>
                    </button>
                    <div id="product-sub" class="submenu">
                        <a href="view_product.php">View</a>
                        <a href="add_product.html">Add Product</a>
                    </div>
                </div>

                <a href="ordering.php" class="single-link active"><span class="icon">📦</span><span class="label">Ordering</span><span class="chev">›</span></a>
            </nav>
        </aside>

        <main class="container">

            <h2>Ordering</h2>

            <!-- PAYMENT LIST -->
            <section class="card">
                <h3>Konfirmasi Pembayaran</h3>
                <div id="paymentList"></div>
            </section>

            <!-- KPI -->
            <!-- ANALYTIC -->
            <section class="card">
                <h3>Analitik</h3>
                Bulan: <input type="month" id="analyticMonth">

                <div class="kpi-row">
                    <div class="kpi card">
                        <h4>Total Penghasilan</h4>
                        <div id="totalRevenue">Rp 0</div>
                    </div>
                    <div class="kpi card">
                        <h4>Total Pesanan</h4>
                        <div id="totalOrders">0</div>
                    </div>
                </div>
            </section>

            <canvas id="ordersChart" height="120"></canvas>
        </main>

    </div>

    <script>
        function rupiah(x) {
            return Number(x).toLocaleString("id-ID");
        }

        let chart = null;

        const monthInput = document.getElementById("analyticMonth");
        const paymentList = document.getElementById("paymentList");

        async function loadDashboard() {
            const m = monthInput.value;
            const r = await fetch("ordering.php?action=dashboard&month=" + m);
            const j = await r.json();

            document.getElementById("totalRevenue").textContent = "Rp " + rupiah(j.revenue);
            document.getElementById("totalOrders").textContent = j.totalOrders;
            document.getElementById("income").textContent = "Rp " + rupiah(j.income);
            document.getElementById("expenses").textContent = "Rp " + rupiah(j.expenses);
            document.getElementById("balance").textContent = "Rp " + rupiah(j.balance);

            const ctx = document.getElementById("ordersChart");
            if (chart) chart.destroy();
            chart = new Chart(ctx, {
                type: "line",
                data: {
                    labels: j.labels,
                    datasets: [{
                        data: j.graph,
                        borderColor: "green",
                        fill: true
                    }]
                }
            });
        }

        async function loadPayments() {
            const r = await fetch("ordering.php?action=payments");
            const j = await r.json();

            paymentList.innerHTML = "";

            j.forEach(o => {
                let itemsHTML = "";

                o.items.forEach(it => {
                    itemsHTML += `
                <div class="item-box">
                    <img src="../upload/${it.image1}" class="prod-img">
                    <div class="item-info">
                        <b>${it.name}</b><br>
                        Qty: ${it.quantity}<br>
                        Harga: Rp ${rupiah(it.price_at_purchase)}
                    </div>
                </div>`;
                });

                paymentList.innerHTML += `
            <div class="pay-card" id="order-card-${o.order_id}">
                <div class="pay-header">
                    <h3>Order #${o.order_id}</h3>
                    <p>${o.nama_lengkap}</p>
                    <p>Total: Rp ${rupiah(o.total_harga)}</p>
                    <p>Status: ${o.status}</p>
                    <button onclick="confirmPay('${o.order_id}')">Confirm</button>
                </div>
                <div class="pay-items">${itemsHTML}</div>
            </div>`;
            });
        }

        function confirmPay(id) {
            fetch("ordering.php?action=confirm", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "order_id=" + id
            }).then(() => {
                const card = document.getElementById("order-card-" + id);
                if (card) card.remove();
                alert("Pembayaran dikonfirmasi!");
            });
        }

        monthInput.addEventListener("change", loadDashboard);

        const d = new Date();
        monthInput.value = d.getFullYear() + "-" + String(d.getMonth() + 1).padStart(2, '0');

        loadDashboard();
        loadPayments();
    </script>

    <script src="ordering.js"></script>
    <script src="main.js"></script>

</body>

</html>