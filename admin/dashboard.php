<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin']) || $_SESSION['role'] !== 'admin') { header("Location: ../auth/login.php?role=admin"); exit; }
$admin = $_SESSION['admin'];

// Correct counts using actual schema tables
$mechCount   = $conn->query("SELECT COUNT(*) c FROM shop_owners WHERE shop_type='mechanic'")->fetch_assoc()['c'];
$partsCount  = $conn->query("SELECT COUNT(*) c FROM shop_owners WHERE shop_type='spare_parts'")->fetch_assoc()['c'];
$pending     = $conn->query("SELECT COUNT(*) c FROM shop_owners WHERE is_approved=0")->fetch_assoc()['c'];
$custCount   = $conn->query("SELECT COUNT(*) c FROM customers")->fetch_assoc()['c'];
$orderCount  = $conn->query("SELECT COUNT(*) c FROM spare_parts_orders")->fetch_assoc()['c'];
$bookingCount= $conn->query("SELECT COUNT(*) c FROM mechanic_bookings")->fetch_assoc()['c'];
$commonShops = $conn->query("SELECT COUNT(*) c FROM shop_owners WHERE shop_type IN ('puncture','towing','battery_shop','fuel_station')")->fetch_assoc()['c'];
$workerCount = $conn->query("SELECT COUNT(*) c FROM workers")->fetch_assoc()['c'];
$totalRevenue= $conn->query("SELECT COALESCE(SUM(total_amount),0) t FROM spare_parts_orders WHERE status='delivered'")->fetch_assoc()['t'];
$mechRevenue = $conn->query("SELECT COALESCE(SUM(amount),0) t FROM mechanic_bookings WHERE status='completed'")->fetch_assoc()['t'];

// Recent shops
$shops = $conn->query("SELECT * FROM shop_owners ORDER BY created_at DESC LIMIT 8");
// Recent customers
$customers = $conn->query("SELECT * FROM customers ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - Road Mithra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/web.css">
</head>
<body>
<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-logo"><img src="../assets/images/logo.png" alt="Logo"><span>Road Mithra</span></div>
        <div class="sidebar-section-label">Administration</div>
        <a href="dashboard.php" class="nav-link active"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
        <a href="manage_mechanics.php" class="nav-link"><i class="fa-solid fa-wrench"></i> Mechanic Shops</a>
        <a href="manage_parts.php" class="nav-link"><i class="fa-solid fa-gears"></i> Spare Part Shops</a>
        <a href="manage_workers.php" class="nav-link"><i class="fa-solid fa-users"></i> All Workers</a>
        <a href="manage_customers.php" class="nav-link"><i class="fa-solid fa-user-group"></i> Customers</a>
        <a href="manage_common.php" class="nav-link"><i class="fa-solid fa-triangle-exclamation"></i> Common Shops</a>
        <a href="worker_requests.php" class="nav-link"><i class="fa-solid fa-clipboard-list"></i> Worker Requests</a>
        <div class="sidebar-footer"><a href="../auth/logout.php" class="nav-link danger"><i class="fa-solid fa-power-off"></i> Logout</a></div>
    </aside>

    <div class="main-area">
        <header class="top-bar">
            <div class="top-bar-title">
                <h1>System Dashboard</h1>
                <p>Welcome back, <strong><?php echo $admin['username']; ?></strong> — full platform overview</p>
            </div>
            <div class="top-bar-right">
                <?php if($pending > 0): ?>
                <a href="manage_mechanics.php" class="badge badge-orange" style="font-size:12px;text-decoration:none;"><?php echo $pending; ?> Pending Approvals</a>
                <?php endif; ?>
                <div class="avatar"><?php echo strtoupper(substr($admin['username'],0,1)); ?></div>
            </div>
        </header>

        <div class="page-content">
            <!-- 8 Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon-box" style="background:#D1FAE5;color:#059669;"><i class="fa-solid fa-indian-rupee-sign"></i></div>
                    <div class="stat-text"><h2>₹<?php echo number_format(($totalRevenue+$mechRevenue)/1000,1); ?>k</h2><p>Total Revenue</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-box" style="background:#DBEAFE;color:#2563EB;"><i class="fa-solid fa-wrench"></i></div>
                    <div class="stat-text"><h2><?php echo $mechCount; ?></h2><p>Mechanic Shops</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-box" style="background:#FEF3C7;color:#D97706;"><i class="fa-solid fa-gears"></i></div>
                    <div class="stat-text"><h2><?php echo $partsCount; ?></h2><p>Spare Part Shops</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-box" style="background:var(--primary-light);color:var(--primary);"><i class="fa-solid fa-user-group"></i></div>
                    <div class="stat-text"><h2><?php echo $custCount; ?></h2><p>Customers</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-box" style="background:#FEE2E2;color:#DC2626;"><i class="fa-solid fa-cart-shopping"></i></div>
                    <div class="stat-text"><h2><?php echo $orderCount; ?></h2><p>Parts Orders</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-box" style="background:#F0FDF4;color:#16A34A;"><i class="fa-solid fa-calendar-check"></i></div>
                    <div class="stat-text"><h2><?php echo $bookingCount; ?></h2><p>Mechanic Bookings</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-box" style="background:#FFF7ED;color:#EA580C;"><i class="fa-solid fa-users"></i></div>
                    <div class="stat-text"><h2><?php echo $workerCount; ?></h2><p>Total Workers</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-box" style="background:#F5F3FF;color:#7C3AED;"><i class="fa-solid fa-shop"></i></div>
                    <div class="stat-text"><h2><?php echo $commonShops; ?></h2><p>Common Shops</p></div>
                </div>
            </div>

            <?php if($pending > 0): ?>
            <div class="alert alert-warn" style="margin-bottom:25px;">
                <i class="fa-solid fa-triangle-exclamation" style="margin-top:2px;"></i>
                <div><strong><?php echo $pending; ?> shops</strong> are waiting for admin approval. <a href="manage_mechanics.php" style="color:inherit;font-weight:700;text-decoration:underline;">Review and approve now →</a></div>
            </div>
            <?php endif; ?>

            <div class="two-col">
                <!-- Shops Table -->
                <div class="card">
                    <div class="card-header">
                        <h3>Recent Shop Registrations</h3>
                        <a href="manage_mechanics.php" class="link-text">View All</a>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead><tr><th>Shop</th><th>Owner</th><th>Type</th><th>Phone</th><th>Status</th><th>Action</th></tr></thead>
                            <tbody>
                            <?php while($s=$shops->fetch_assoc()): ?>
                            <tr>
                                <td><span style="font-weight:600;"><?php echo htmlspecialchars($s['shop_name']); ?></span></td>
                                <td><?php echo htmlspecialchars($s['owner_name']); ?></td>
                                <td><span class="badge badge-purple"><?php echo str_replace('_',' ',ucfirst($s['shop_type'])); ?></span></td>
                                <td style="font-size:13px;"><?php echo $s['phone']; ?></td>
                                <td><span class="badge <?php echo $s['is_approved']?'badge-green':'badge-orange'; ?>"><?php echo $s['is_approved']?'Approved':'Pending'; ?></span></td>
                                <td style="display:flex;gap:6px;">
                                    <?php if(!$s['is_approved']): ?>
                                    <a href="approve_shop.php?id=<?php echo $s['id']; ?>" class="btn btn-sm btn-success">Approve</a>
                                    <?php endif; ?>
                                    <a href="delete_shop.php?id=<?php echo $s['id']; ?>" class="btn btn-sm" style="background:#FEE2E2;color:var(--danger);" onclick="return confirm('Remove this shop?')"><i class="fa-solid fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Right Panel -->
                <div style="display:flex;flex-direction:column;gap:20px;">
                    <div class="card">
                        <h3 style="font-size:16px;font-weight:700;margin-bottom:20px;">Quick Actions</h3>
                        <div style="display:flex;flex-direction:column;gap:10px;">
                            <a href="manage_mechanics.php" class="btn btn-primary"><i class="fa-solid fa-wrench"></i> Manage Mechanic Shops</a>
                            <a href="manage_parts.php" class="btn btn-outline"><i class="fa-solid fa-gears"></i> Manage Parts Shops</a>
                            <a href="manage_workers.php" class="btn btn-outline"><i class="fa-solid fa-users"></i> Manage All Workers</a>
                            <a href="manage_common.php" class="btn btn-outline"><i class="fa-solid fa-shop"></i> Common Problem Shops</a>
                        </div>
                    </div>
                    <div class="card">
                        <h3 style="font-size:16px;font-weight:700;margin-bottom:20px;">Recent Customers</h3>
                        <div style="display:flex;flex-direction:column;gap:10px;">
                        <?php while($c=$customers->fetch_assoc()): ?>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:36px;height:36px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;"><?php echo strtoupper(substr($c['name'],0,1)); ?></div>
                            <div>
                                <div style="font-weight:600;font-size:14px;"><?php echo htmlspecialchars($c['name']); ?></div>
                                <div style="font-size:12px;color:var(--text-light);"><?php echo $c['phone']; ?></div>
                            </div>
                            <div style="margin-left:auto;font-size:11px;color:var(--text-light);"><?php echo date('d M',strtotime($c['created_at'])); ?></div>
                        </div>
                        <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
