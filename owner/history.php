<?php
require_once '../includes/db.php';
if (!isset($_SESSION['owner'])) {
    header("Location: ../auth/login.php"); exit;
}

$owner = $_SESSION['owner'];
$sid = $owner['id'];
$role = $_SESSION['role'];

if ($role === 'mechanic_owner') {
    // Mechanic history
    $history = $conn->query("SELECT mb.*, c.name as customer_name, w.name as worker_name 
                            FROM mechanic_bookings mb 
                            LEFT JOIN customers c ON mb.customer_id = c.id 
                            LEFT JOIN workers w ON mb.worker_id = w.id 
                            WHERE mb.shop_id = $sid AND mb.status IN ('completed', 'cancelled')
                            ORDER BY mb.updated_at DESC");
} else {
    // Spare parts history
    $history = $conn->query("SELECT spo.*, GROUP_CONCAT(CONCAT(sp.name,' x ',spoi.quantity) SEPARATOR ', ') as items, w.name as worker_name
                            FROM spare_parts_orders spo
                            LEFT JOIN spare_parts_order_items spoi ON spoi.order_id = spo.id
                            LEFT JOIN spare_parts sp ON sp.id = spoi.part_id
                            LEFT JOIN workers w ON w.id = spo.worker_id
                            WHERE spo.shop_id = $sid AND spo.status IN ('delivered', 'cancelled')
                            GROUP BY spo.id
                            ORDER BY spo.updated_at DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service History - Road Mithra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/web.css">
</head>
<body>
<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-logo"><img src="../assets/images/logo.png" alt="Logo"><span>Road Mithra</span></div>
        <div class="sidebar-section-label"><?php echo $role === 'mechanic_owner' ? 'Mechanic Shop' : 'Parts Shop'; ?></div>
        
        <?php if($role === 'mechanic_owner'): ?>
            <a href="mechanic_dashboard.php" class="nav-link"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="service_bookings.php" class="nav-link"><i class="fa-solid fa-wrench"></i> Service Bookings</a>
            <a href="manage_workers.php" class="nav-link"><i class="fa-solid fa-users"></i> Mechanics</a>
        <?php else: ?>
            <a href="parts_dashboard.php" class="nav-link"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="customer_orders.php" class="nav-link"><i class="fa-solid fa-cart-shopping"></i> Orders</a>
            <a href="inventory.php" class="nav-link"><i class="fa-solid fa-boxes-stacked"></i> Inventory</a>
            <a href="manage_delivery_workers.php" class="nav-link"><i class="fa-solid fa-truck-ramp-box"></i> Delivery Team</a>
        <?php endif; ?>
        
        <a href="history.php" class="nav-link active"><i class="fa-solid fa-clock-rotate-left"></i> History</a>
        <div class="sidebar-footer"><a href="../auth/logout.php" class="nav-link danger"><i class="fa-solid fa-power-off"></i> Logout</a></div>
    </aside>

    <div class="main-area">
        <header class="top-bar">
            <div class="top-bar-title"><h1>Completed History</h1><p>View all past request completions</p></div>
        </header>

        <div class="page-content">
            <div class="card">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Ref ID</th>
                                <th>Customer</th>
                                <th>Details</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Assigned To</th>
                                <th>Completed On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($history && $history->num_rows > 0): while($h = $history->fetch_assoc()): ?>
                            <tr>
                                <td style="font-weight:700;">#<?php echo $role === 'mechanic_owner' ? 'MB'.$h['id'] : 'SPO'.$h['id']; ?></td>
                                <td>
                                    <div style="font-weight:600;"><?php echo htmlspecialchars($h['customer_name'] ?: 'Customer'); ?></div>
                                    <div style="font-size:12px; color:#AAA;"><?php echo $h['customer_phone']; ?></div>
                                </td>
                                <td>
                                    <div style="font-size:13px; color:var(--text-dark); max-width:250px;">
                                        <?php echo $role === 'mechanic_owner' ? $h['problem_type'] : $h['items']; ?>
                                    </div>
                                </td>
                                <td style="font-weight:700;">₹<?php echo number_format($role === 'mechanic_owner' ? $h['amount'] : $h['total_amount'], 2); ?></td>
                                <td><span class="badge <?php echo $h['status'] === 'cancelled' ? 'badge-red' : 'badge-green'; ?>"><?php echo strtoupper($h['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($h['worker_name'] ?: '—'); ?></td>
                                <td style="font-size:13px; color:var(--text-light);">
                                    <?php echo date('d M Y, h:i A', strtotime($h['updated_at'])); ?>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="7" style="text-align:center; padding:50px; color:#AAA;">No completed records found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
