<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin']) || $_SESSION['role'] !== 'admin') { header("Location: ../auth/login.php?role=admin"); exit; }
$admin = $_SESSION['admin'];

$query = "SELECT * FROM shop_owners WHERE shop_type='spare_parts' ORDER BY created_at DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Spare Part Shops - Road Mithra</title>
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
        <a href="manage_parts.php" class="nav-link active"><i class="fa-solid fa-gears"></i> Spare Part Shops</a>
        <a href="manage_workers.php" class="nav-link"><i class="fa-solid fa-users"></i> All Workers</a>
        <a href="manage_customers.php" class="nav-link"><i class="fa-solid fa-user-group"></i> Customers</a>
        <a href="manage_common.php" class="nav-link"><i class="fa-solid fa-triangle-exclamation"></i> Common Shops</a>
        <a href="worker_requests.php" class="nav-link"><i class="fa-solid fa-clipboard-list"></i> Worker Requests</a>
        <div class="sidebar-footer"><a href="../auth/logout.php" class="nav-link danger"><i class="fa-solid fa-power-off"></i> Logout</a></div>
    </aside>

    <div class="main-area">
        <header class="top-bar">
            <div class="top-bar-title">
                <h1>Spare Part Shops</h1>
                <p>Manage all registered spare part shops and their approval status</p>
            </div>
            <div class="top-bar-right">
                <div class="avatar"><?php echo strtoupper(substr($admin['username'],0,1)); ?></div>
            </div>
        </header>

        <div class="page-content">
            <div class="card">
                <div class="table-container">
                    <table class="data-table">
                        <thead><tr><th>Shop Name</th><th>Owner</th><th>Phone</th><th>Email</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody>
                        <?php while($s=$result->fetch_assoc()): ?>
                        <tr>
                            <td><span style="font-weight:600;"><?php echo htmlspecialchars($s['shop_name']); ?></span></td>
                            <td><?php echo htmlspecialchars($s['owner_name']); ?></td>
                            <td><?php echo $s['phone']; ?></td>
                            <td style="font-size:13px;"><?php echo htmlspecialchars($s['email']); ?></td>
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
        </div>
    </div>
</div>
</body>
</html>
