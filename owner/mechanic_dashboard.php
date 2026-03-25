<?php
require_once '../includes/db.php';
if (!isset($_SESSION['owner']) || $_SESSION['role'] !== 'mechanic_owner') {
    header("Location: ../auth/login.php?role=mechanic_owner"); exit;
}
$owner = $_SESSION['owner'];
$sid = $owner['id'];

$pending   = $conn->query("SELECT COUNT(*) c FROM mechanic_bookings WHERE shop_id=$sid AND status='pending'")->fetch_assoc()['c'];
$active    = $conn->query("SELECT COUNT(*) c FROM mechanic_bookings WHERE shop_id=$sid AND status='in_progress'")->fetch_assoc()['c'];
$completed = $conn->query("SELECT COUNT(*) c FROM mechanic_bookings WHERE shop_id=$sid AND status='completed'")->fetch_assoc()['c'];
$workers   = $conn->query("SELECT COUNT(*) c FROM workers WHERE shop_id=$sid")->fetch_assoc()['c'];
$revenue   = $conn->query("SELECT SUM(amount) t FROM mechanic_bookings WHERE shop_id=$sid AND status='completed'")->fetch_assoc()['t'] ?? 0;
$bookings  = $conn->query("SELECT mb.*, w.name as wname FROM mechanic_bookings mb LEFT JOIN workers w ON mb.worker_id=w.id WHERE mb.shop_id=$sid ORDER BY mb.created_at DESC LIMIT 8");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mechanic Shop Manager - Road Mithra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/web.css">
    <script src="../assets/js/config.js"></script>
</head>
<body>
<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-logo"><img src="../assets/images/logo.png" alt="Logo"><span>Road Mithra</span></div>
        <div class="sidebar-section-label">Mechanic Shop</div>
        <a href="mechanic_dashboard.php" class="nav-link active"><i class="fa-solid fa-house"></i> Dashboard</a>
        <a href="service_bookings.php" class="nav-link"><i class="fa-solid fa-wrench"></i> Service Bookings</a>
        <a href="manage_workers.php" class="nav-link"><i class="fa-solid fa-users"></i> Mechanics / Workers</a>
        <a href="history.php" class="nav-link"><i class="fa-solid fa-clock-rotate-left"></i> History</a>
        <div class="sidebar-footer"><a href="../auth/logout.php" class="nav-link danger"><i class="fa-solid fa-power-off"></i> Logout</a></div>
    </aside>

    <div class="main-area">
        <header class="top-bar">
            <div class="top-bar-title">
                <h1><?php echo htmlspecialchars($owner['shop_name']); ?></h1>
                <p>Owner: <?php echo $owner['owner_name']; ?> &bull; <?php echo $owner['address'] ?: 'Location not set'; ?></p>
            </div>
            <div class="top-bar-right">
                <div class="toggle-wrap">
                    <span style="font-size:13px;font-weight:600;color:var(--text-light);">Shop Open</span>
                    <label class="toggle">
                        <input type="checkbox" id="shopStatus" <?php echo $owner['is_open']?'checked':''; ?> onchange="toggleShop()">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <div class="avatar"><?php echo strtoupper(substr($owner['shop_name'],0,1)); ?></div>
            </div>
        </header>

        <div class="page-content">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon-box" style="background:#D1FAE5;color:#059669;"><i class="fa-solid fa-indian-rupee-sign"></i></div>
                    <div class="stat-text"><h2>₹<?php echo number_format($revenue/1000,1);?>k</h2><p>Total Revenue</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-box" style="background:#FEF3C7;color:#D97706;"><i class="fa-solid fa-clock"></i></div>
                    <div class="stat-text"><h2><?php echo $pending;?></h2><p>Pending Requests</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-box" style="background:#DBEAFE;color:#2563EB;"><i class="fa-solid fa-gear fa-spin"></i></div>
                    <div class="stat-text"><h2><?php echo $active;?></h2><p>Active Service</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-box" style="background:var(--primary-light);color:var(--primary);"><i class="fa-solid fa-users"></i></div>
                    <div class="stat-text"><h2><?php echo $workers;?></h2><p>Team Members</p></div>
                </div>
            </div>

            <div class="two-col">
                <div class="card">
                    <div class="card-header"><h3>Recent Service Bookings</h3><a href="service_bookings.php" class="link-text">View All</a></div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead><tr><th>ID</th><th>Customer</th><th>Vehicle</th><th>Problem</th><th>Status</th><th>Assigned To</th></tr></thead>
                            <tbody>
                                <?php while($b=$bookings->fetch_assoc()): ?>
                                <tr>
                                    <td style="font-weight:700;">#<?php echo $b['id'];?></td>
                                    <td><?php echo htmlspecialchars($b['customer_name']??'—');?></td>
                                    <td><?php echo str_replace('_',' ',$b['vehicle_type']);?></td>
                                    <td><?php echo htmlspecialchars($b['problem_type']??'General');?></td>
                                    <td><span class="badge <?php echo $b['status']=='completed'?'badge-green':($b['status']=='in_progress'?'badge-blue':'badge-orange');?>"><?php echo $b['status'];?></span></td>
                                    <td><?php echo htmlspecialchars($b['wname']??'Unassigned');?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h3>Quick Actions</h3></div>
                    <div style="display:flex;flex-direction:column;gap:12px;">
                        <a href="service_bookings.php" class="btn btn-primary"><i class="fa-solid fa-list"></i> View All Requests</a>
                        <a href="manage_workers.php" class="btn btn-outline"><i class="fa-solid fa-user-plus"></i> Manage Team</a>
                        <a href="history.php" class="btn btn-outline"><i class="fa-solid fa-clock-rotate-left"></i> Completed History</a>
                    </div>
                    <hr style="margin:25px 0;border:1px solid var(--border);">
                    <h4 style="font-size:14px;font-weight:700;margin-bottom:15px;">Shop Summary</h4>
                    <div style="display:flex;flex-direction:column;gap:12px;font-size:14px;color:var(--text-mid);">
                        <div style="display:flex;justify-content:space-between;"><span>Phone</span><span style="font-weight:600;"><?php echo $owner['phone'];?></span></div>
                        <div style="display:flex;justify-content:space-between;"><span>Completed Jobs</span><span style="font-weight:600;"><?php echo $completed;?></span></div>
                        <div style="display:flex;justify-content:space-between;"><span>Shop Status</span>
                            <span class="badge <?php echo $owner['is_open']?'badge-green':'badge-red';?>"><?php echo $owner['is_open']?'OPEN':'CLOSED';?></span>
                        </div>
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
</script>
</body>
</html>
