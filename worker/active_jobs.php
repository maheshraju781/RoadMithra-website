<?php
require_once '../includes/db.php';
if (!isset($_SESSION['worker']) || $_SESSION['role'] !== 'worker') {
    header("Location: ../auth/login.php?role=worker"); exit;
}
$worker = $_SESSION['worker'];
$wid = (int)$worker['id'];

// Get active jobs from DB
// For simplicity, we'll fetch from mechanic_bookings or spare_parts_orders
$active_mech = $conn->query("SELECT mb.*, c.name as customer_name, c.phone as customer_phone FROM mechanic_bookings mb JOIN customers c ON mb.customer_id=c.id WHERE mb.worker_id=$wid AND mb.status IN ('accepted', 'assigned', 'on_the_way', 'arrived', 'in_progress') ORDER BY created_at DESC");
$active_orders = $conn->query("SELECT o.*, o.customer_name, o.customer_phone, o.delivery_address as customer_address FROM spare_parts_orders o WHERE o.worker_id=$wid AND o.status IN ('confirmed', 'processing', 'shipped', 'out_for_delivery') ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Jobs - Road Mithra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/web.css">
    <script src="../assets/js/config.js"></script>
    <!-- Leaflet Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        .active-job-card { background: white; border-radius: 20px; padding: 25px; border: 1.5px solid var(--primary); margin-bottom: 25px; box-shadow: var(--shadow); }
        .map-full { height: 300px; border-radius: 12px; margin: 15px 0; border: 1px solid #EEE; overflow: hidden; }
        .status-pill { padding: 6px 15px; border-radius: 30px; font-size: 12px; font-weight: 700; background: var(--primary-light); color: var(--primary); }
    </style>
</head>
<body>
<div class="app-layout">
    <!-- Sidebar same as new_requests -->
    <aside class="sidebar">
        <div class="sidebar-logo"><img src="../assets/images/logo.png" alt="Logo"><span>Road Mithra</span></div>
        <div class="sidebar-section-label">Worker Portal</div>
        <a href="dashboard.php" class="nav-link"><i class="fa-solid fa-house"></i> Dashboard</a>
        <a href="new_requests.php" class="nav-link"><i class="fa-solid fa-bell"></i> New Requests</a>
        <a href="active_jobs.php" class="nav-link active"><i class="fa-solid fa-briefcase"></i> Active Jobs</a>
        <a href="completed_jobs.php" class="nav-link"><i class="fa-solid fa-circle-check"></i> Completed</a>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="nav-link danger"><i class="fa-solid fa-power-off"></i> Logout</a>
        </div>
    </aside>

    <div class="main-area">
        <header class="top-bar">
            <div class="top-bar-title"><h1>Active Assignments</h1><p>Jobs you are currently working on</p></div>
        </header>

        <div class="page-content" style="max-width: 800px;">
            <?php 
                $has_jobs = false;
                if($active_mech && $active_mech->num_rows > 0) { $has_jobs = true; while($job = $active_mech->fetch_assoc()): 
            ?>
            <div class="active-job-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <div>
                        <span class="status-pill"><?php echo strtoupper($job['status']); ?></span>
                        <h2 style="margin-top:10px; font-size:20px;"><?php echo htmlspecialchars($job['problem_type'] ?: 'Mechanic Service'); ?></h2>
                        <div style="font-size:12px; color:var(--text-light); margin-top:5px; font-weight:600;"><i class="fa-regular fa-clock" style="margin-right:4px;"></i> <?php echo date('d M Y, h:i A', strtotime($job['created_at'])); ?></div>
                    </div>
                    <div style="text-align:right;">
                        <p style="font-weight:700; font-size:18px;">#<?php echo $job['id']; ?></p>
                        <p style="font-size:12px; color:#AAA;">Booking ID</p>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                    <div>
                        <p style="font-size:12px; color:#AAA; text-transform:uppercase; font-weight:700;">Customer Details</p>
                        <h4 style="margin:5px 0;"><?php echo htmlspecialchars($job['customer_name']); ?></h4>
                        <p style="font-size:14px;"><i class="fa-solid fa-phone"></i> <?php echo $job['customer_phone']; ?></p>
                    </div>
                    <div>
                        <p style="font-size:12px; color:#AAA; text-transform:uppercase; font-weight:700;">Vehicle</p>
                        <h4 style="margin:5px 0;"><?php echo str_replace('_', ' ', $job['vehicle_type']); ?></h4>
                    </div>
                </div>

                <div class="map-full" id="map_<?php echo $job['id']; ?>" data-lat="<?php echo $job['customer_lat']; ?>" data-lng="<?php echo $job['customer_lng']; ?>"></div>

                <div style="display:flex; gap:12px; margin-top:20px;">
                    <button class="btn btn-primary" onclick="updateStatus(<?php echo $job['id']; ?>, 'start_trip', 'mechanic')">Start Trip</button>
                    <button class="btn btn-outline" onclick="updateStatus(<?php echo $job['id']; ?>, 'arrive', 'mechanic')">Arrived</button>
                    <button class="btn btn-outline" onclick="finishJob(<?php echo $job['id']; ?>, 'mechanic', '<?php echo $job['payment_method']; ?>')">Complete Job</button>
                </div>
                <div style="margin-top:10px; font-size:12px; font-weight:700; color:#64748B;">
                    <i class="fa-solid fa-wallet"></i> Payment Type: <span style="color:var(--primary);"><?php echo strtoupper($job['payment_method'] ?: 'COD'); ?></span>
                </div>
            </div>
            <?php endwhile; } ?>

            <?php 
                if($active_orders && $active_orders->num_rows > 0) { $has_jobs = true; while($job = $active_orders->fetch_assoc()): 
            ?>
            <div class="active-job-card" style="border-color: var(--secondary);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <div>
                        <span class="status-pill" style="background:#FCE7F3; color:var(--secondary);"><?php echo strtoupper($job['status']); ?></span>
                        <h2 style="margin-top:10px; font-size:20px;">Spare Parts Delivery</h2>
                        <div style="font-size:12px; color:var(--text-light); margin-top:5px; font-weight:600;"><i class="fa-regular fa-clock" style="margin-right:4px;"></i> <?php echo date('d M Y, h:i A', strtotime($job['created_at'])); ?></div>
                    </div>
                    <div style="text-align:right;">
                        <p style="font-weight:700; font-size:18px;">#<?php echo $job['id']; ?></p>
                        <p style="font-size:12px; color:#AAA;">Order ID</p>
                    </div>
                </div>

                <div class="map-full" id="map_order_<?php echo $job['id']; ?>" data-lat="<?php echo $job['customer_lat']; ?>" data-lng="<?php echo $job['customer_lng']; ?>"></div>

                <div style="display:flex; gap:12px; margin-top:20px;">
                    <button class="btn btn-primary" style="background:var(--secondary);" onclick="updateStatus(<?php echo $job['id']; ?>, 'start_trip', 'enhanced')">Start Delivery</button>
                    <button class="btn btn-outline" onclick="finishJob(<?php echo $job['id']; ?>, 'enhanced', '<?php echo $job['payment_method'] ?: 'cod'; ?>')">Mark Delivered</button>
                </div>
                <div style="margin-top:10px; font-size:12px; font-weight:700; color:#64748B;">
                    <i class="fa-solid fa-wallet"></i> Payment Type: <span style="color:var(--secondary);"><?php echo strtoupper($job['payment_method'] ?: 'COD'); ?></span>
                </div>
            </div>
            <?php endwhile; } ?>

            <?php if(!$has_jobs): ?>
            <div class="card" style="text-align:center; padding:60px;">
                <i class="fa-solid fa-mug-hot" style="font-size:50px; color:#EEE; margin-bottom:20px;"></i>
                <h3>No active jobs</h3>
                <p>Go to <a href="new_requests.php" style="color:var(--primary); font-weight:700;">New Requests</a> to find work.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function initMaps() {
        document.querySelectorAll('.map-full').forEach(container => {
            const lat = parseFloat(container.dataset.lat);
            const lng = parseFloat(container.dataset.lng);
            if(!lat || !lng) return;
            
            const m = L.map(container.id).setView([lat, lng], 15);
            L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: '&copy; Google Maps'
            }).addTo(m);
            const markerHtml = `<div style="color: #E11D48; font-size: 32px;"><i class="fa-solid fa-location-dot"></i></div>`;
            const icon = L.divIcon({
                html: markerHtml,
                className: 'custom-div-icon',
                iconSize: [32, 32],
                iconAnchor: [16, 32]
            });
            L.marker([lat, lng], {icon: icon}).addTo(m).bindPopup("Customer Location").openPopup();
            m.invalidateSize();
        });
    }

    async function updateStatus(id, action, table) {
        const formData = new FormData();
        formData.append('worker_id', <?php echo $wid; ?>);
        formData.append('job_id', id);
        formData.append('job_type', table === 'mechanic' ? 'booking' : 'delivery');
        formData.append('booking_table', table);

        try {
            const res = await fetch(apiUrl('road_backend/worker/worker_api.php?action=' + action), {
                method: 'POST', body: formData
            });
            const result = await res.json();
            if(result.success) { alert(result.message); location.reload(); }
            else alert(result.message);
        } catch(e) { alert("Error"); }
    }

    async function finishJob(id, table, method) {
        if(!confirm("Are you sure the job is finished?")) return;
        
        let amount = "0";
        if (method === 'cod') {
            amount = prompt("CASH ON DELIVERY: Enter final amount collected (₹):", "0");
        } else {
            if(!confirm("ONLINE PAYMENT: Confirm service is completed (Customer already paid)?")) return;
            amount = "0"; // Already paid online, maybe fetch actual amount if needed, but for completion we just mark it.
        }
        
        if(amount === null) return;

        const formData = new FormData();
        formData.append('worker_id', <?php echo $wid; ?>);
        formData.append('job_id', id);
        formData.append('job_type', table === 'mechanic' ? 'booking' : 'delivery');
        formData.append('booking_table', table);
        formData.append('amount', amount);
        formData.append('payment_method', method);
        formData.append('payment_status', 'paid');

        try {
            const res = await fetch(apiUrl('road_backend/worker/worker_api.php?action=complete_job'), {
                method: 'POST', body: formData
            });
            const result = await res.json();
            if(result.success) { alert("Job Completed!"); window.location.href='completed_jobs.php'; }
            else alert(result.message);
        } catch(e) { alert("Error"); }
    }

    window.onload = initMaps;
</script>
</body>
</html>
