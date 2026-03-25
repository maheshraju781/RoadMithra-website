<?php
require_once '../includes/db.php';
if (!isset($_SESSION['worker']) || $_SESSION['role'] !== 'worker') {
    header("Location: ../auth/login.php?role=worker"); exit;
}
$worker = $_SESSION['worker'];
$wid = $worker['id'];

// Using correct table: mechanic_bookings (for mechanic workers) + spare_parts_orders (for delivery workers)
$type = strtolower($worker['type']);
$isDelivery = (strpos($type, 'delivery') !== false);

if ($isDelivery) {
    $pending   = $conn->query("SELECT COUNT(*) c FROM spare_parts_orders WHERE worker_id=$wid AND status='assigned'")->fetch_assoc()['c'];
    $active    = $conn->query("SELECT COUNT(*) c FROM spare_parts_orders WHERE worker_id=$wid AND status='out_for_delivery'")->fetch_assoc()['c'];
    $completed = $conn->query("SELECT COUNT(*) c FROM spare_parts_orders WHERE worker_id=$wid AND status='delivered'")->fetch_assoc()['c'];
    $newReqs   = $conn->query("SELECT COUNT(*) c FROM spare_parts_orders WHERE shop_id=(SELECT shop_id FROM workers WHERE id=$wid) AND status='pending'")->fetch_assoc()['c'];
} else {
    $pending   = $conn->query("SELECT COUNT(*) c FROM mechanic_bookings WHERE worker_id=$wid AND status='assigned'")->fetch_assoc()['c'];
    $active    = $conn->query("SELECT COUNT(*) c FROM mechanic_bookings WHERE worker_id=$wid AND status='in_progress'")->fetch_assoc()['c'];
    $completed = $conn->query("SELECT COUNT(*) c FROM mechanic_bookings WHERE worker_id=$wid AND status='completed'")->fetch_assoc()['c'];
    $newReqs   = $conn->query("SELECT COUNT(*) c FROM mechanic_bookings WHERE (worker_id IS NULL OR worker_id=$wid) AND status IN ('pending','assigned')")->fetch_assoc()['c'];
}

// Recent jobs (from correct table)
if ($isDelivery) {
    $recent = $conn->query("SELECT id, customer_name, customer_phone, delivery_address as customer_address, status, total_amount as amount, payment_method, created_at, updated_at FROM spare_parts_orders WHERE worker_id=$wid ORDER BY created_at DESC LIMIT 5");
} else {
    $recent = $conn->query("SELECT id, customer_name, customer_phone, customer_address, problem_type, vehicle_type, status, amount, payment_method, created_at, updated_at FROM mechanic_bookings WHERE worker_id=$wid ORDER BY created_at DESC LIMIT 5");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Portal - Road Mithra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/web.css">
    <script src="../assets/js/config.js"></script>
</head>
<body>
<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-logo"><img src="../assets/images/logo.png" alt="Logo"><span>Road Mithra</span></div>
        <div class="sidebar-section-label">Worker Portal</div>
        <a href="dashboard.php" class="nav-link active"><i class="fa-solid fa-house"></i> Dashboard</a>
        <a href="new_requests.php" class="nav-link"><i class="fa-solid fa-bell"></i> New Requests
            <?php if($newReqs > 0): ?><span style="margin-left:auto;background:var(--secondary);color:white;font-size:11px;font-weight:700;padding:2px 7px;border-radius:20px;"><?php echo $newReqs; ?></span><?php endif; ?>
        </a>
        <a href="active_jobs.php" class="nav-link"><i class="fa-solid fa-briefcase"></i> Active Jobs</a>
        <a href="completed_jobs.php" class="nav-link"><i class="fa-solid fa-circle-check"></i> Completed</a>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="nav-link danger"><i class="fa-solid fa-power-off"></i> Logout</a>
        </div>
    </aside>

    <div class="main-area">
        <header class="top-bar">
            <div class="top-bar-title">
                <h1>Worker Dashboard</h1>
                <p>Welcome, <strong><?php echo htmlspecialchars($worker['name']); ?></strong> &bull; ID: <code><?php echo $worker['worker_id']; ?></code> &bull; Type: <?php echo ucfirst($worker['type']); ?></p>
            </div>
            <div class="top-bar-right">
                <div class="toggle-wrap">
                    <span style="font-size:13px;font-weight:600;color:var(--text-light);">Online</span>
                    <label class="toggle">
                        <input type="checkbox" id="statusToggle" <?php echo $worker['is_available'] ? 'checked' : ''; ?> onchange="toggleStatus()">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <div class="avatar"><?php echo strtoupper(substr($worker['name'],0,1)); ?></div>
            </div>
        </header>

        <div class="page-content">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon-box" style="background:#FEF3C7;color:#D97706;"><i class="fa-solid fa-clock"></i></div>
                    <div class="stat-text"><h2><?php echo $pending; ?></h2><p>Assigned / Pending</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-box" style="background:#D1FAE5;color:#059669;"><i class="fa-solid fa-spinner fa-spin"></i></div>
                    <div class="stat-text"><h2><?php echo $active; ?></h2><p>In Progress</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-box" style="background:#DBEAFE;color:#2563EB;"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="stat-text"><h2><?php echo $completed; ?></h2><p>Completed</p></div>
                </div>
                <div class="stat-card" style="background:linear-gradient(135deg,var(--primary),var(--secondary));border:none;cursor:pointer;" onclick="location.href='new_requests.php'">
                    <div class="stat-icon-box" style="background:rgba(255,255,255,0.2);color:white;"><i class="fa-solid fa-bolt"></i></div>
                    <div class="stat-text"><h2 style="color:white;"><?php echo $newReqs; ?></h2><p style="color:rgba(255,255,255,0.85);">New Requests</p></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Recent Assignments</h3>
                    <a href="completed_jobs.php" class="link-text">Full History</a>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Customer</th>
                                <?php if(!$isDelivery): ?><th>Problem</th><th>Vehicle</th><?php endif; ?>
                                <th>Address</th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th>Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if($recent && $recent->num_rows > 0): while($job=$recent->fetch_assoc()): ?>
                        <tr>
                            <td style="font-weight:700;">#<?php echo $job['id']; ?></td>
                            <td>
                                <div style="font-weight:600;"><?php echo htmlspecialchars($job['customer_name'] ?: '—'); ?></div>
                                <div style="font-size:12px;color:var(--text-light);"><?php echo $job['customer_phone'] ?: ''; ?></div>
                            </td>
                            <?php if(!$isDelivery): ?>
                            <td><?php echo htmlspecialchars($job['problem_type'] ?? 'General'); ?></td>
                            <td><span class="badge badge-blue"><?php echo str_replace('_',' ',$job['vehicle_type'] ?? '—'); ?></span></td>
                            <?php endif; ?>
                            <td style="max-width:150px;font-size:12px;color:var(--text-light);"><?php echo htmlspecialchars($job['customer_address'] ?? '—'); ?></td>
                            <td><span class="badge <?php
                                switch($job['status']) {
                                    case 'completed': case 'delivered': echo 'badge-green'; break;
                                    case 'in_progress': case 'out_for_delivery': echo 'badge-blue'; break;
                                    default: echo 'badge-orange'; break;
                                }?>"><?php echo strtoupper($job['status']); ?></span></td>
                            <td style="font-weight:700;color:var(--success);">₹<?php echo number_format($job['amount'], 0); ?></td>
                            <td><span class="badge badge-purple"><?php echo strtoupper($job['payment_method']); ?></span></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-light);">No assignments yet.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function toggleStatus() {
    const v = document.getElementById('statusToggle').checked ? 1 : 0;
    fetch(apiUrl(`api/worker_api.php?action=toggle_status&status=${v}`)).then(r => r.json());
}
</script>
</body>
</html>
