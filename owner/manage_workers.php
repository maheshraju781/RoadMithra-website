<?php
require_once '../includes/db.php';
if (!isset($_SESSION['owner']) || $_SESSION['role'] !== 'mechanic_owner') {
    header("Location: ../auth/login.php?role=mechanic_owner"); exit;
}
$owner = $_SESSION['owner'];
$sid = $owner['id'];

// Fetch workers for this shop
$workers = $conn->query("SELECT * FROM workers WHERE shop_id = $sid ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Workers - Road Mithra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/web.css">
    <script src="../assets/js/config.js"></script>
</head>
<body>
<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-logo"><img src="../assets/images/logo.png" alt="Logo"><span>Road Mithra</span></div>
        <div class="sidebar-section-label">Mechanic Shop</div>
        <a href="mechanic_dashboard.php" class="nav-link"><i class="fa-solid fa-house"></i> Dashboard</a>
        <a href="service_bookings.php" class="nav-link"><i class="fa-solid fa-wrench"></i> Service Bookings</a>
        <a href="manage_workers.php" class="nav-link active"><i class="fa-solid fa-users"></i> Mechanics / Workers</a>
        <a href="history.php" class="nav-link"><i class="fa-solid fa-clock-rotate-left"></i> History</a>
        <div class="sidebar-footer"><a href="../auth/logout.php" class="nav-link danger"><i class="fa-solid fa-power-off"></i> Logout</a></div>
    </aside>

    <div class="main-area">
        <header class="top-bar">
            <div class="top-bar-title"><h1>Manage Team</h1><p>Add and manage your mechanics</p></div>
            <button class="btn btn-primary" onclick="showAddModal()"><i class="fa-solid fa-plus"></i> Add New Mechanic</button>
        </header>

        <div class="page-content">
            <div class="card">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Phone</th>
                                <th>Experience</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($workers && $workers->num_rows > 0): while($w = $workers->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div style="font-weight:600;"><?php echo htmlspecialchars($w['name']); ?></div>
                                    <div style="font-size:12px; color:#AAA;">Joined <?php echo date('M Y', strtotime($w['created_at'])); ?></div>
                                </td>
                                <td><span class="badge badge-purple"><?php echo str_replace('_', ' ', strtoupper($w['type'])); ?></span></td>
                                <td><?php echo $w['phone']; ?></td>
                                <td><?php echo $w['experience'] ?? '0'; ?> Years</td>
                                <td>
                                    <span class="badge <?php echo $w['is_available']?'badge-green':'badge-orange'; ?>">
                                        <?php echo $w['is_available']?'AVAILABLE':'BUSY'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-outline btn-sm" onclick="editWorker(<?php echo $w['id']; ?>)">Edit</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteWorker(<?php echo $w['id']; ?>)">Remove</button>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="6" style="text-align:center; padding:40px; color:#AAA;">No workers added yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Simple Add Modal Placeholder -->
<div id="addModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div class="card" style="width:400px;">
        <h3>Add Mechanic</h3>
        <hr style="margin:15px 0;">
        <form id="addWorkerForm">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Type</label>
                <select name="type" class="form-input">
                    <option value="mechanic">General Mechanic</option>
                    <option value="puncture_expert">Puncture Expert</option>
                    <option value="battery_technician">Battery Technician</option>
                    <option value="towing_driver">Towing Driver</option>
                </select>
            </div>
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="button" class="btn btn-outline" style="flex:1;" onclick="hideAddModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;">Save Worker</button>
            </div>
        </form>
    </div>
</div>

<script>
    function showAddModal() { document.getElementById('addModal').style.display = 'flex'; }
    function hideAddModal() { document.getElementById('addModal').style.display = 'none'; }

    document.getElementById('addWorkerForm').onsubmit = async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        formData.append('shop_id', <?php echo $sid; ?>);
        formData.append('action', 'add_worker');

        try {
            const res = await fetch(apiUrl('api/owner_api.php'), {
                method: 'POST', body: formData
            });
            const result = await res.json();
            if(result.success) { alert("Worker added!"); location.reload(); }
            else alert("Error: " + result.message);
        } catch(e) { alert("Server error"); }
    };

    async function deleteWorker(id) {
        if(!confirm("Remove this worker from your shop?")) return;
        const formData = new FormData();
        formData.append('worker_id', id);
        formData.append('action', 'delete_worker');
        
        try {
            const res = await fetch(apiUrl('api/owner_api.php'), {
                method: 'POST', body: formData
            });
            const result = await res.json();
            if(result.success) location.reload();
            else alert(result.message);
        } catch(e) {}
    }
</script>
</body>
</html>
