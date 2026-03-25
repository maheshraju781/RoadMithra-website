<?php
require_once '../includes/db.php';
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../auth/login.php?role=customer"); exit;
}
$user = $_SESSION['user'];
$oid = isset($_GET['order_id']) ? (int)$_GET['order_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

// Fetch order details
$order_query = $conn->query("SELECT o.*, so.shop_name, so.phone as shop_phone 
                       FROM spare_parts_orders o 
                       JOIN shop_owners so ON o.shop_id = so.id 
                       WHERE o.id = $oid AND o.customer_id = " . $user['id']);

if (!$order_query || $order_query->num_rows == 0) {
    die("Order not found or you don't have permission to view it.");
}
$order = $order_query->fetch_assoc();

$items = $conn->query("SELECT * FROM spare_parts_order_items WHERE order_id = $oid");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Road Mithra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/web.css">
</head>
<body>

    <div class="app-layout">
        <aside class="sidebar">
            <div class="sidebar-logo"><img src="../assets/images/logo.png" alt="Logo"><span>Road Mithra</span></div>
            <div class="sidebar-section-label">Main Menu</div>
            <a href="dashboard.php" class="nav-link"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="buy_spares.php" class="nav-link active"><i class="fa-solid fa-cart-shopping"></i> Shop Parts</a>
            <a href="bookings.php" class="nav-link"><i class="fa-solid fa-calendar-check"></i> My Orders</a>
            <div class="sidebar-footer"><a href="../auth/logout.php" class="nav-link danger"><i class="fa-solid fa-power-off"></i> Logout</a></div>
        </aside>

        <main class="main-area">
            <header class="top-bar">
                <div class="top-bar-title">
                    <a href="bookings.php" style="font-size:12px; font-weight:700; color:var(--text-light); text-transform:uppercase; display:block; margin-bottom:5px;">
                        <i class="fa-solid fa-arrow-left"></i> Back to Orders
                    </a>
                    <h1>Order #<?php echo $oid; ?></h1>
                </div>
                <div class="top-bar-right">
                    <span class="badge <?php 
                        echo in_array($order['status'], ['completed', 'delivered']) ? 'badge-green' : (in_array($order['status'], ['pending', 'assigned']) ? 'badge-orange' : 'badge-blue'); 
                    ?>" style="padding:10px 20px; font-size:13px;">
                        <?php echo strtoupper($order['status']); ?>
                    </span>
                </div>
            </header>

            <div class="page-content">
                <div class="two-col">
                    <div>
                        <div class="card" style="margin-bottom:25px;">
                            <h3 style="margin-bottom:20px; font-size:18px;">Items Ordered</h3>
                            <div class="table-container">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Item Details</th>
                                            <th style="text-align:center;">Quantity</th>
                                            <th style="text-align:right;">Price</th>
                                            <th style="text-align:right;">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($item = $items->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <div style="font-weight:700; color:var(--text-dark);"><?php echo htmlspecialchars($item['part_name']); ?></div>
                                                <div style="font-size:11px; color:var(--text-light);">ID: #<?php echo $item['part_id']; ?></div>
                                            </td>
                                            <td style="text-align:center; font-weight:600;"><?php echo $item['quantity']; ?></td>
                                            <td style="text-align:right;">₹<?php echo number_format($item['price'], 2); ?></td>
                                            <td style="text-align:right; font-weight:800; color:var(--text-dark);">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div style="margin-top:30px; padding:20px; background:var(--bg); border-radius:var(--radius-md); display:flex; justify-content:flex-end;">
                                <div style="text-align:right;">
                                    <span style="display:block; font-size:12px; color:var(--text-light); text-transform:uppercase; font-weight:700;">Total Amount (incl. fees)</span>
                                    <span style="font-size:32px; font-weight:900; color:var(--primary);">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info" style="border:none; box-shadow:var(--shadow);">
                            <div class="stat-icon-box" style="background:var(--primary); color:white; width:40px; height:40px; border-radius:10px; font-size:16px;">
                                <i class="fa-solid fa-truck"></i>
                            </div>
                            <div>
                                <strong style="display:block; font-size:15px;">Tracking Delivery</strong>
                                <p style="font-size:13px; opacity:0.8;">Our partner shop is preparing your order. You will receive a call once the delivery agent is near your location.</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="card" style="margin-bottom:25px;">
                            <h3 style="margin-bottom:20px; font-size:16px;"><i class="fa-solid fa-location-arrow" style="color:var(--primary); margin-right:8px;"></i> Delivery Location</h3>
                            <div style="font-size:14px; color:var(--text-mid); line-height:1.6;">
                                <strong style="color:var(--text-dark); display:block; margin-bottom:5px;"><?php echo htmlspecialchars($order['customer_name']); ?></strong>
                                <?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?>
                                <div style="margin-top:10px; font-weight:700;">
                                    <i class="fa-solid fa-phone" style="font-size:12px; margin-right:5px;"></i> <?php echo $order['customer_phone']; ?>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <h3 style="margin-bottom:20px; font-size:16px;"><i class="fa-solid fa-shop" style="color:var(--secondary); margin-right:8px;"></i> Shop Information</h3>
                            <div style="font-size:14px; color:var(--text-mid);">
                                <strong style="color:var(--text-dark); display:block; margin-bottom:5px; font-size:15px;"><?php echo htmlspecialchars($order['shop_name']); ?></strong>
                                <p style="font-size:13px; margin-bottom:15px;">Verified Road Mithra Partner</p>
                                <a href="tel:<?php echo $order['shop_phone']; ?>" class="btn btn-outline btn-sm" style="width:100%; justify-content:center;">
                                    <i class="fa-solid fa-phone"></i> Call Shop
                                </a>
                                
                                <div style="margin-top:20px; padding-top:20px; border-top:1px solid var(--border);">
                                    <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                                        <span style="font-size:12px; color:var(--text-light);">Payment Method:</span>
                                        <span style="font-size:12px; font-weight:700; color:var(--text-dark);"><?php echo strtoupper(str_replace('_', ' ', $order['payment_method'])); ?></span>
                                    </div>
                                    <div style="display:flex; justify-content:space-between;">
                                        <span style="font-size:12px; color:var(--text-light);">Payment Status:</span>
                                        <span class="badge badge-green" style="font-size:9px;">SUCCESS</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>
