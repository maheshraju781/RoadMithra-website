<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin']) || $_SESSION['role'] !== 'admin') { header("Location: ../auth/login.php?role=admin"); exit; }
$admin = $_SESSION['admin'];

$query = "SELECT w.*, so.shop_name as registered_shop_name 
          FROM workers w 
          LEFT JOIN shop_owners so ON w.shop_id = so.id 
          ORDER BY w.created_at DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Workers - Road Mithra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/web.css">
</head>
<body>
<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-logo"><img src="../assets/images/logo.png" alt="Logo"><span>Road Mithra</span></div>
        <div class="sidebar-section-label">Administration</div>
        <a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
        <a href="manage_mechanics.php" class="nav-link"><i class="fa-solid fa-wrench"></i> Mechanic Shops</a>
        <a href="manage_parts.php" class="nav-link"><i class="fa-solid fa-gears"></i> Spare Part Shops</a>
        <a href="manage_workers.php" class="nav-link active"><i class="fa-solid fa-users"></i> All Workers</a>
        <a href="manage_customers.php" class="nav-link"><i class="fa-solid fa-user-group"></i> Customers</a>
        <a href="manage_common.php" class="nav-link"><i class="fa-solid fa-triangle-exclamation"></i> Common Shops</a>
        <a href="worker_requests.php" class="nav-link"><i class="fa-solid fa-clipboard-list"></i> Worker Requests</a>
        <div class="sidebar-footer"><a href="../auth/logout.php" class="nav-link danger"><i class="fa-solid fa-power-off"></i> Logout</a></div>
    </aside>

    <div class="main-area">
        <header class="top-bar">
            <div class="top-bar-title">
                <h1>All Workers</h1>
                <p>Track and manage all workers across the platform</p>
            </div>
            <div class="top-bar-right">
                <div class="avatar"><?php echo strtoupper(substr($admin['username'],0,1)); ?></div>
            </div>
        </header>

        <div class="page-content">
            <div class="card">
                <div class="table-container">
                    <table class="data-table">
                        <thead><tr><th>Name</th><th>Type</th><th>Shop</th><th>Phone</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody>
                        <?php while($w=$result->fetch_assoc()): ?>
                        <tr>
                            <td><span style="font-weight:600;"><?php echo htmlspecialchars($w['name']); ?></span></td>
                            <td><span class="badge badge-purple"><?php echo ucfirst($w['type']); ?></span></td>
                            <td><?php echo htmlspecialchars($w['registered_shop_name'] ?: ($w['shop_name'] ?: 'Independent')); ?></td>
                            <td><?php echo $w['phone']; ?></td>
                            <td><span class="badge <?php echo $w['is_approved']?'badge-green':'badge-orange'; ?>"><?php echo $w['is_approved']?'Approved':'Pending'; ?></span></td>
                            <td style="display:flex;gap:6px;">
                                <?php if(!$w['is_approved']): ?>
                                <a href="approve_worker.php?id=<?php echo $w['id']; ?>" class="btn btn-sm btn-success">Approve</a>
                                <?php endif; ?>
                                <a href="delete_worker.php?id=<?php echo $w['id']; ?>" class="btn btn-sm" style="background:#FEE2E2;color:var(--danger);" onclick="return confirm('Remove this worker?')"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
