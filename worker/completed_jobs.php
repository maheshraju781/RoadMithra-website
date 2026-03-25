<?php
require_once '../includes/db.php';
if (!isset($_SESSION['worker']) || $_SESSION['role'] !== 'worker') { header("Location: ../auth/login.php?role=worker"); exit; }
$worker = $_SESSION['worker'];
$wid = (int)$worker['id'];
$isMech = (strpos(strtolower($worker['type'] ?? ''), 'delivery') === false);

if ($isMech) {
    $jobs = $conn->query("SELECT * FROM mechanic_bookings WHERE worker_id=$wid AND status='completed' ORDER BY updated_at DESC");
} else {
    $jobs = $conn->query("SELECT *, delivery_address as customer_address, total_amount as amount, 'Spare Parts Delivery' as problem_type, '4_wheeler' as vehicle_type FROM spare_parts_orders WHERE worker_id=$wid AND status='delivered' ORDER BY updated_at DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completed Jobs - Road Mithra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/web.css">
</head>
<body>
<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-logo"><img src="../assets/images/logo.png" alt="Logo"><span>Road Mithra</span></div>
        <div class="sidebar-section-label">Worker Portal</div>
        <a href="dashboard.php" class="nav-link"><i class="fa-solid fa-house"></i> Dashboard</a>
        <a href="new_requests.php" class="nav-link"><i class="fa-solid fa-bell"></i> New Requests</a>
        <a href="active_jobs.php" class="nav-link"><i class="fa-solid fa-briefcase"></i> Active Jobs</a>
        <a href="completed_jobs.php" class="nav-link active"><i class="fa-solid fa-circle-check"></i> Completed</a>
        <div class="sidebar-footer"><a href="../auth/logout.php" class="nav-link danger"><i class="fa-solid fa-power-off"></i> Logout</a></div>
    </aside>
    <div class="main-area">
        <header class="top-bar">
            <div class="top-bar-title"><h1>Completed Jobs</h1><p>Your service history</p></div>
        </header>
        <div class="page-content">
            <div class="card">
                <div class="table-container">
                    <table class="data-table">
                        <thead><tr><th>#</th><th>Customer</th><th>Problem Type</th><th>Vehicle</th><th>Address</th><th>Amount</th><th>Payment</th><th>Completed At</th></tr></thead>
                        <tbody>
                        <?php if($jobs && $jobs->num_rows > 0): while($j=$jobs->fetch_assoc()): ?>
                        <tr>
                            <td style="font-weight:700;">#<?php echo $j['id'];?></td>
                            <td><?php echo htmlspecialchars($j['customer_name']??'—');?></td>
                            <td><?php echo htmlspecialchars($j['problem_type']??'General');?></td>
                            <td><span class="badge badge-blue"><?php echo str_replace('_',' ',$j['vehicle_type']);?></span></td>
                            <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($j['customer_address']??'—');?></td>
                            <td style="font-weight:700;color:var(--success);">₹<?php echo number_format($j['amount'],0);?></td>
                            <td><span class="badge badge-green"><?php echo strtoupper($j['payment_method']);?></span></td>
                            <td style="color:var(--text-light);font-size:13px;"><?php echo date('d M y, h:i A',strtotime($j['updated_at']));?></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="8" style="text-align:center;padding:50px;color:var(--text-light);">No completed jobs yet.</td></tr>
                        <?php endif;?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
