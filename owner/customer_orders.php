<?php
require_once '../includes/db.php';
if (!isset($_SESSION['owner'])) { header("Location: ../auth/login.php"); exit; }
$sid = $_SESSION['owner']['id'];

// Correct table: spare_parts_orders with shop_id join
$orders = $conn->query("
    SELECT spo.*,
        GROUP_CONCAT(CONCAT(sp.name,' x ',spoi.quantity) SEPARATOR ', ') as items,
        w.name as worker_name
    FROM spare_parts_orders spo
    LEFT JOIN spare_parts_order_items spoi ON spoi.order_id = spo.id
    LEFT JOIN spare_parts sp ON sp.id = spoi.part_id
    LEFT JOIN workers w ON w.id = spo.worker_id
    WHERE spo.shop_id = $sid
    GROUP BY spo.id
    ORDER BY spo.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Orders - Road Mithra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/web.css">
    <script src="../assets/js/config.js"></script>
</head>
<body>
<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-logo"><img src="../assets/images/logo.png" alt="Logo"><span>Road Mithra</span></div>
        <div class="sidebar-section-label">Parts Shop</div>
        <a href="parts_dashboard.php" class="nav-link"><i class="fa-solid fa-house"></i> Dashboard</a>
        <a href="customer_orders.php" class="nav-link"><i class="fa-solid fa-cart-shopping"></i> Customer Orders</a>
        <a href="inventory.php" class="nav-link active"><i class="fa-solid fa-boxes-stacked"></i> Inventory</a>
        <a href="manage_delivery_workers.php" class="nav-link"><i class="fa-solid fa-truck-ramp-box"></i> Delivery Team</a>
        <a href="history.php" class="nav-link"><i class="fa-solid fa-clock-rotate-left"></i> History</a>
        <div class="sidebar-footer"><a href="../auth/logout.php" class="nav-link danger"><i class="fa-solid fa-power-off"></i> Logout</a></div>
    </aside>
    <div class="main-area">
        <header class="top-bar">
            <div class="top-bar-title"><h1>Customer Orders</h1><p>All incoming spare parts orders for your shop</p></div>
            <div class="top-bar-right">
                <div style="display:flex;gap:8px;">
                    <button class="btn btn-sm btn-outline filter-btn active" data-status="all">All</button>
                    <button class="btn btn-sm btn-outline filter-btn" data-status="pending">Pending</button>
                    <button class="btn btn-sm btn-outline filter-btn" data-status="assigned">Assigned</button>
                    <button class="btn btn-sm btn-outline filter-btn" data-status="delivered">Delivered</button>
                </div>
            </div>
        </header>
        <div class="page-content">
            <div class="card">
                <div class="table-container">
                    <table class="data-table" id="ordersTable">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Delivery Worker</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if($orders && $orders->num_rows > 0): while($o=$orders->fetch_assoc()): ?>
                        <tr data-status="<?php echo $o['status']; ?>">
                            <td style="font-weight:700;">#SPO<?php echo $o['id']; ?></td>
                            <td>
                                <div style="font-weight:600;"><?php echo htmlspecialchars($o['customer_name'] ?: 'Customer'); ?></div>
                                <div style="font-size:12px;color:var(--text-light);"><?php echo htmlspecialchars($o['customer_phone'] ?: ''); ?></div>
                            </td>
                            <td style="max-width:200px;font-size:13px;color:var(--text-light);"><?php echo htmlspecialchars($o['items'] ?: '—'); ?></td>
                            <td style="font-weight:700;">₹<?php echo number_format($o['total_amount'], 2); ?></td>
                            <td><span class="badge <?php echo $o['payment_method']=='cod'?'badge-orange':'badge-blue'; ?>"><?php echo strtoupper($o['payment_method']); ?></span></td>
                            <td><span class="badge <?php
                                switch($o['status']) {
                                    case 'delivered': echo 'badge-green'; break;
                                    case 'pending': echo 'badge-orange'; break;
                                    case 'cancelled': echo 'badge-red'; break;
                                    default: echo 'badge-blue'; break;
                                }?>"><?php echo strtoupper($o['status']); ?></span></td>
                            <td><?php echo htmlspecialchars($o['worker_name'] ?: '—'); ?></td>
                            <td style="font-size:13px;color:var(--text-light);"><?php echo date('d M, h:i A', strtotime($o['created_at'])); ?></td>
                            <td>
                                <?php if($o['status'] == 'pending'): ?>
                                <div style="display:flex; gap:5px;">
                                    <a href="assign_delivery.php?id=<?php echo $o['id']; ?>" class="btn btn-sm btn-primary">Assign</a>
                                    <button onclick="rejectOrder(<?php echo $o['id']; ?>)" class="btn btn-sm btn-outline danger">Reject</button>
                                </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="9" style="text-align:center;padding:50px;color:var(--text-light);">No orders found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function(){
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const st = this.dataset.status;
        document.querySelectorAll('#ordersTable tbody tr').forEach(row => {
            row.style.display = (st === 'all' || row.dataset.status === st) ? '' : 'none';
        });
    });
});

async function rejectOrder(orderId) {
    if(!confirm("Are you sure you want to reject this order?")) return;
    
    const fd = new FormData();
    fd.append('action', 'reject_order');
    fd.append('order_id', orderId);

    try {
        const res = await fetch(apiUrl('api/owner_api.php'), {
            method: 'POST', body: fd
        });
        const result = await res.json();
        if(result.success) {
            alert("Order rejected successfully.");
            location.reload();
        } else {
            alert("Error: " + result.message);
        }
    } catch(e) { alert("Server error"); }
}
</script>
</body>
</html>
