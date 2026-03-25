<?php
require_once '../includes/db.php';
if (!isset($_SESSION['owner']) || $_SESSION['role'] !== 'parts_owner') {
    header("Location: ../auth/login.php?role=parts_owner"); exit;
}
$owner = $_SESSION['owner'];
$sid = $owner['id'];

// Using correct table: spare_parts_orders (not 'orders')
$orderCount   = $conn->query("SELECT COUNT(*) c FROM spare_parts_orders WHERE shop_id=$sid")->fetch_assoc()['c'];
$productCount = $conn->query("SELECT COUNT(*) c FROM spare_parts WHERE shop_id=$sid")->fetch_assoc()['c'];
$activeWorkers= $conn->query("SELECT COUNT(*) c FROM workers WHERE shop_id=$sid AND is_available=1")->fetch_assoc()['c'];
$totalRevenue = $conn->query("SELECT COALESCE(SUM(total_amount),0) t FROM spare_parts_orders WHERE shop_id=$sid AND status='delivered'")->fetch_assoc()['t'];
$lowStock     = $conn->query("SELECT * FROM spare_parts WHERE shop_id=$sid AND quantity < 10 LIMIT 3");

// Pending orders
$pendingOrders = $conn->query("SELECT spo.*, GROUP_CONCAT(CONCAT(sp.name,' x ',spoi.quantity) SEPARATOR ', ') as items
    FROM spare_parts_orders spo
    LEFT JOIN spare_parts_order_items spoi ON spoi.order_id = spo.id
    LEFT JOIN spare_parts sp ON sp.id = spoi.part_id
    WHERE spo.shop_id = $sid AND spo.status = 'pending'
    GROUP BY spo.id ORDER BY spo.created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Manager - <?php echo htmlspecialchars($owner['shop_name']); ?></title>
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
        <a href="parts_dashboard.php" class="nav-link active"><i class="fa-solid fa-house"></i> Store Overview</a>
        <a href="customer_orders.php" class="nav-link"><i class="fa-solid fa-cart-shopping"></i> Customer Orders</a>
        <a href="inventory.php" class="nav-link"><i class="fa-solid fa-boxes-stacked"></i> Inventory</a>
        <a href="manage_delivery_workers.php" class="nav-link"><i class="fa-solid fa-truck-ramp-box"></i> Delivery Team</a>
        <a href="history.php" class="nav-link"><i class="fa-solid fa-clock-rotate-left"></i> History</a>
        <div class="sidebar-footer"><a href="../auth/logout.php" class="nav-link danger"><i class="fa-solid fa-power-off"></i> Logout</a></div>
    </aside>

    <div class="main-area">
        <header class="top-bar">
            <div class="top-bar-title">
                <h1><?php echo htmlspecialchars($owner['shop_name']); ?></h1>
                <p>Owner: <?php echo htmlspecialchars($owner['owner_name']); ?> &bull; <?php echo htmlspecialchars($owner['phone']); ?></p>
            </div>
            <div class="top-bar-right">
                <div class="toggle-wrap">
                    <span style="font-size:13px;font-weight:600;color:var(--text-light);">Shop Open</span>
                    <label class="toggle">
                        <input type="checkbox" id="shopStatus" <?php echo $owner['is_open'] ? 'checked' : ''; ?> onchange="toggleShop()">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <div class="avatar"><?php echo strtoupper(substr($owner['shop_name'],0,1)); ?></div>
            </div>
        </header>

        <div class="page-content">
            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon-box" style="background:#D1FAE5;color:#059669;"><i class="fa-solid fa-indian-rupee-sign"></i></div>
                    <div class="stat-text"><h2>₹<?php echo number_format($totalRevenue); ?></h2><p>Total Sales</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-box" style="background:#DBEAFE;color:#2563EB;"><i class="fa-solid fa-cart-shopping"></i></div>
                    <div class="stat-text"><h2><?php echo $orderCount; ?></h2><p>Total Orders</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-box" style="background:#FEF3C7;color:#D97706;"><i class="fa-solid fa-box"></i></div>
                    <div class="stat-text"><h2><?php echo $productCount; ?></h2><p>Catalog Items</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-box" style="background:var(--primary-light);color:var(--primary);"><i class="fa-solid fa-truck"></i></div>
                    <div class="stat-text"><h2><?php echo $activeWorkers; ?></h2><p>Available Delivery</p></div>
                </div>
            </div>

            <div class="two-col">
                <!-- Pending Orders Table -->
                <div class="card">
                    <div class="card-header">
                        <h3>Pending Shipments</h3>
                        <a href="customer_orders.php" class="link-text">View All Orders</a>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead><tr><th>Order</th><th>Customer</th><th>Items</th><th>Amount</th><th>Action</th></tr></thead>
                            <tbody>
                            <?php if($pendingOrders && $pendingOrders->num_rows > 0): while($o=$pendingOrders->fetch_assoc()): ?>
                            <tr>
                                <td style="font-weight:700;">#SPO<?php echo $o['id']; ?></td>
                                <td><?php echo htmlspecialchars($o['customer_name'] ?: 'Customer'); ?></td>
                                <td style="font-size:12px;color:var(--text-light);max-width:200px;"><?php echo htmlspecialchars($o['items'] ?: '—'); ?></td>
                                <td style="font-weight:700;">₹<?php echo number_format($o['total_amount']); ?></td>
                                <td>
                                    <div style="display:flex; gap:5px;">
                                        <a href="assign_delivery.php?id=<?php echo $o['id']; ?>" class="btn btn-sm btn-primary">Assign</a>
                                        <button onclick="rejectOrder(<?php echo $o['id']; ?>)" class="btn btn-sm btn-outline danger">Reject</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text-light);">No pending orders</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Low Stock Alerts -->
                <div class="card">
                    <div class="card-header"><h3>⚠️ Low Stock Alert</h3><a href="inventory.php" class="link-text">Manage Inventory</a></div>
                    <div style="display:flex;flex-direction:column;gap:12px;">
                        <?php
                        $lowStock = $conn->query("SELECT * FROM spare_parts WHERE shop_id=$sid AND quantity < 10 LIMIT 5");
                        if($lowStock && $lowStock->num_rows > 0): while($item=$lowStock->fetch_assoc()): ?>
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px;background:#FFF9F9;border:1px solid #FFEBEB;border-radius:12px;">
                            <div>
                                <h4 style="font-size:14px;font-weight:700;"><?php echo htmlspecialchars($item['name']); ?></h4>
                                <p style="font-size:12px;color:var(--danger);">Only <?php echo $item['quantity']; ?> left in stock</p>
                            </div>
                            <a href="edit_product.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger">Restock</a>
                        </div>
                        <?php endwhile; else: ?>
                        <div style="text-align:center;padding:30px;color:var(--text-light);">
                            <i class="fa-solid fa-boxes-stacked" style="font-size:30px;margin-bottom:10px;display:block;color:#10B981;"></i>
                            All products are well stocked!
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function toggleShop(){
    const v=document.getElementById('shopStatus').checked?1:0;
    fetch(apiUrl(`api/owner_api.php?action=toggle_shop&status=${v}`)).then(r=>r.json());
}

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
