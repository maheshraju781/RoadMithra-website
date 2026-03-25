<?php
require_once '../includes/db.php';
if (!isset($_SESSION['owner']) || $_SESSION['role'] !== 'mechanic_owner') {
    header("Location: ../auth/login.php?role=mechanic_owner"); exit;
}
$owner = $_SESSION['owner'];
$sid = $owner['id'];

// Fetch bookings for this shop
$bookings = $conn->query("SELECT mb.*, c.name as customer_name, w.name as worker_name 
                         FROM mechanic_bookings mb 
                         LEFT JOIN customers c ON mb.customer_id = c.id 
                         LEFT JOIN workers w ON mb.worker_id = w.id 
                         WHERE mb.shop_id = $sid 
                         ORDER BY mb.created_at DESC");

// Fetch available workers to assign
$workers = $conn->query("SELECT id, name FROM workers WHERE shop_id=$sid AND is_available=1");
$worker_list = [];
while($w = $workers->fetch_assoc()) $worker_list[] = $w;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Bookings - Road Mithra</title>
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
        <a href="service_bookings.php" class="nav-link active"><i class="fa-solid fa-wrench"></i> Service Bookings</a>
        <a href="manage_workers.php" class="nav-link"><i class="fa-solid fa-users"></i> Mechanics / Workers</a>
        <a href="history.php" class="nav-link"><i class="fa-solid fa-clock-rotate-left"></i> History</a>
        <div class="sidebar-footer"><a href="../auth/logout.php" class="nav-link danger"><i class="fa-solid fa-power-off"></i> Logout</a></div>
    </aside>

    <div class="main-area">
        <header class="top-bar">
            <div class="top-bar-title"><h1>Service Bookings</h1><p>Manage customer requests and assignments</p></div>
        </header>

        <div class="page-content">
            <div class="card">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Vehicle & Problem</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Assigned Mechanic</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($bookings && $bookings->num_rows > 0): while($b = $bookings->fetch_assoc()): ?>
                            <tr>
                                <td style="font-weight:700;">#<?php echo $b['id']; ?></td>
                                <td>
                                    <div style="font-weight:600;"><?php echo htmlspecialchars($b['customer_name']); ?></div>
                                    <div style="font-size:12px; color:#AAA;"><?php echo $b['customer_phone']; ?></div>
                                </td>
                                <td>
                                    <div><?php echo str_replace('_', ' ', $b['vehicle_type']); ?></div>
                                    <div style="font-size:12px; font-weight:700; color:var(--primary);"><?php echo htmlspecialchars($b['problem_type']); ?></div>
                                </td>
                                <td><?php echo date('d M, h:i A', strtotime($b['created_at'])); ?></td>
                                <td><span class="badge <?php echo $b['status']=='completed'?'badge-green':($b['status']=='pending'?'badge-orange':'badge-blue'); ?>"><?php echo strtoupper($b['status']); ?></span></td>
                                <td>
                                    <?php if($b['worker_id']): ?>
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <div style="width:24px; height:24px; border-radius:50%; background:#EEE; display:flex; align-items:center; justify-content:center; font-size:10px;"><?php echo strtoupper($b['worker_name'][0]); ?></div>
                                            <span><?php echo htmlspecialchars($b['worker_name']); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <select onchange="assignWorker(<?php echo $b['id']; ?>, this.value)" style="padding:5px; border-radius:5px; border:1px solid #CCC; font-size:12px; width:100%;">
                                            <option value="">Assign Now...</option>
                                            <?php foreach($worker_list as $w): ?>
                                            <option value="<?php echo $w['id']; ?>"><?php echo $w['name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button onclick="rejectBooking(<?php echo $b['id']; ?>)" class="btn btn-sm btn-outline danger" style="margin-top:5px; width:100%;">Reject</button>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-outline btn-sm" onclick="viewDetails(<?php echo htmlspecialchars(json_encode($b)); ?>)">Details</button>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="7" style="text-align:center; padding:40px; color:#AAA;">No bookings found for your shop.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    async function assignWorker(bookingId, workerId) {
        if(!workerId) return;
        if(!confirm("Assign this booking to the selected mechanic?")) return;

        const formData = new FormData();
        formData.append('booking_id', bookingId);
        formData.append('worker_id', workerId);
        formData.append('action', 'assign_worker');

        try {
            const res = await fetch(apiUrl('api/owner_api.php'), {
                method: 'POST', body: formData
            });
            const result = await res.json();
            if(result.success) { alert("Assigned successfully!"); location.reload(); }
            else alert("Error: " + result.message);
        } catch(e) { alert("Server error"); }
    }

    async function rejectBooking(bookingId) {
        if(!confirm("Are you sure you want to reject this booking?")) return;
        
        const fd = new FormData();
        fd.append('action', 'reject_booking');
        fd.append('booking_id', bookingId);

        try {
            const res = await fetch(apiUrl('api/owner_api.php'), {
                method: 'POST', body: fd
            });
            const result = await res.json();
            if(result.success) {
                alert("Booking rejected successfully.");
                location.reload();
            } else {
                alert("Error: " + result.message);
            }
        } catch(e) { alert("Server error"); }
    }

    function viewDetails(b) {
        alert(`Problem: ${b.problem_type}\nDescription: ${b.problem_description || 'No description provided'}\nAddress: ${b.customer_address}`);
    }
</script>
</body>
</html>
