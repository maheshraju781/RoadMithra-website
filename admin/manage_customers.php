<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin']) || $_SESSION['role'] !== 'admin') { header("Location: ../auth/login.php?role=admin"); exit; }
$admin = $_SESSION['admin'];

$query = "SELECT * FROM customers ORDER BY created_at DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers - Road Mithra</title>
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
        <a href="manage_workers.php" class="nav-link"><i class="fa-solid fa-users"></i> All Workers</a>
        <a href="manage_customers.php" class="nav-link active"><i class="fa-solid fa-user-group"></i> Customers</a>
        <a href="manage_common.php" class="nav-link"><i class="fa-solid fa-triangle-exclamation"></i> Common Shops</a>
        <a href="worker_requests.php" class="nav-link"><i class="fa-solid fa-clipboard-list"></i> Worker Requests</a>
        <div class="sidebar-footer"><a href="../auth/logout.php" class="nav-link danger"><i class="fa-solid fa-power-off"></i> Logout</a></div>
    </aside>

    <div class="main-area">
        <header class="top-bar">
            <div class="top-bar-title">
                <h1>Registered Customers</h1>
                <p>View and manage platform user base</p>
            </div>
            <div class="top-bar-right">
                <div class="avatar"><?php echo strtoupper(substr($admin['username'],0,1)); ?></div>
            </div>
        </header>

        <div class="page-content">
            <div class="card">
                <div class="table-container">
                    <table class="data-table">
                        <thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Registered On</th><th>Action</th></tr></thead>
                        <tbody>
                        <?php while($c=$result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div style="width:32px;height:32px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;"><?php echo strtoupper(substr($c['name'],0,1)); ?></div>
                                    <span style="font-weight:600;"><?php echo htmlspecialchars($c['name']); ?></span>
                                </div>
                            </td>
                            <td><?php echo $c['phone']; ?></td>
                            <td><?php echo htmlspecialchars($c['email']); ?></td>
                            <td style="font-size:13px;"><?php echo date('d M Y, H:i', strtotime($c['created_at'])); ?></td>
                            <td>
                                <a href="delete_customer.php?id=<?php echo $c['id']; ?>" class="btn btn-sm" style="background:#FEE2E2;color:var(--danger);" onclick="return confirm('Remove this customer?')"><i class="fa-solid fa-trash"></i></a>
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
