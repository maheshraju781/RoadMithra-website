<?php
require_once '../includes/db.php';
if (!isset($_SESSION['owner']) || $_SESSION['role'] !== 'parts_owner') {
    header("Location: ../auth/login.php?role=parts_owner"); exit;
}
$owner = $_SESSION['owner'];
$sid = $owner['id'];
$workers = $conn->query("SELECT * FROM workers WHERE shop_id = $sid AND type = 'delivery' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Team - Road Mithra</title>
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
        <a href="parts_dashboard.php" class="nav-link"><i class="fa-solid fa-house"></i> Dashboard</a>
        <a href="customer_orders.php" class="nav-link"><i class="fa-solid fa-cart-shopping"></i> Customer Orders</a>
        <a href="inventory.php" class="nav-link"><i class="fa-solid fa-boxes-stacked"></i> Inventory</a>
        <a href="manage_delivery_workers.php" class="nav-link active"><i class="fa-solid fa-truck-ramp-box"></i> Delivery Team</a>
        <div class="sidebar-footer"><a href="../auth/logout.php" class="nav-link danger"><i class="fa-solid fa-power-off"></i> Logout</a></div>
    </aside>
    <div class="main-area">
        <header class="top-bar">
            <div class="top-bar-title"><h1>Delivery Team</h1><p>Manage and track all delivery personnel</p></div>
            <div class="top-bar-right">
                <button class="btn btn-primary" onclick="showAddModal()"><i class="fa-solid fa-user-plus"></i> Add Delivery Worker</button>
            </div>
        </header>
        <div class="page-content">
            <div class="card">
                <div class="table-container">
                    <table class="data-table">
                        <thead><tr><th>Worker</th><th>Worker ID</th><th>Phone</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php if($workers && $workers->num_rows > 0): while($w=$workers->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:12px;">
                                    <div style="width:38px;height:38px;border-radius:50%;background:var(--primary-light);display:flex;align-items:center;justify-content:center;color:var(--primary);font-weight:700;">
                                        <?php echo strtoupper(substr($w['name'],0,1)); ?>
                                    </div>
                                    <span style="font-weight:600;"><?php echo htmlspecialchars($w['name']);?></span>
                                </div>
                            </td>
                            <td><code style="background:var(--bg);padding:4px 10px;border-radius:6px;font-size:12px;"><?php echo $w['worker_id'] ?: 'N/A';?></code></td>
                            <td><?php echo $w['phone'];?></td>
                            <td><span class="badge <?php echo $w['is_available']?'badge-green':'badge-orange';?>"><?php echo $w['is_available']?'Available':'On Delivery';?></span></td>
                            <td style="display:flex;gap:8px;">
                                <a href="tel:<?php echo $w['phone'];?>" class="btn btn-sm btn-outline" title="Call"><i class="fa-solid fa-phone"></i></a>
                                <button class="btn btn-sm btn-outline" title="Edit" onclick="alert('Feature coming soon')"><i class="fa-solid fa-pen"></i></button>
                                <button class="btn btn-sm" style="background:#FEE2E2;color:var(--danger);" onclick="deleteWorker(<?php echo $w['id']; ?>)"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="5" style="text-align:center;padding:50px;color:var(--text-light);">No delivery workers added yet.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div class="card" style="width:400px; padding:30px; border-radius:24px;">
        <h3 style="margin-bottom:20px;">Add Delivery Worker</h3>
        <form id="addWorkerForm">
            <input type="hidden" name="type" value="delivery">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-input" placeholder="e.g. John Doe" required>
            </div>
            <div class="form-group">
                <label class="form-label">Mobile Number</label>
                <input type="tel" name="phone" class="form-input" placeholder="10 digit number" maxlength="10" required>
            </div>
            <div class="form-group">
                <label class="form-label">Set Password</label>
                <input type="password" name="password" class="form-input" placeholder="Login password" required>
            </div>
            <div style="display:flex; gap:12px; margin-top:25px;">
                <button type="button" class="btn btn-outline" style="flex:1; justify-content:center;" onclick="hideAddModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1; justify-content:center;">Save Worker</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddModal() { document.getElementById('addModal').style.display = 'flex'; }
function hideAddModal() { document.getElementById('addModal').style.display = 'none'; }

document.getElementById('addWorkerForm').onsubmit = async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    fd.append('action', 'add_worker');

    try {
        const res = await fetch(apiUrl('api/owner_api.php'), { method: 'POST', body: fd });
        const result = await res.json();
        if(result.success) { alert("Worker added successfully!"); location.reload(); }
        else alert("Error: " + result.message);
    } catch(e) { alert("Server error"); }
};

async function deleteWorker(id) {
    if(!confirm("Are you sure you want to remove this worker?")) return;
    const fd = new FormData();
    fd.append('action', 'delete_worker');
    fd.append('worker_id', id);

    try {
        const res = await fetch(apiUrl('api/owner_api.php'), { method: 'POST', body: fd });
        const result = await res.json();
        if(result.success) location.reload();
        else alert("Error removing worker");
    } catch(e) { alert("Server error"); }
}
</script>
</body>
</html>
