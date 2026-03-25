<?php
require_once '../includes/db.php';
if (!isset($_SESSION['worker']) || $_SESSION['role'] !== 'worker') {
    header("Location: ../auth/login.php?role=worker"); exit;
}
$worker = $_SESSION['worker'];
$wid = (int)$worker['id'];
$shop_id = !empty($worker['shop_id']) ? (int)$worker['shop_id'] : null;
$is_mech = (strpos(strtolower($worker['type'] ?? ''), 'delivery') === false);

// Fetch new requests from both potential tables
if ($is_mech) {
    $shop_filter = ($shop_id !== null) ? "(shop_id=$shop_id OR shop_id IS NULL)" : "shop_id IS NULL";
    $query = "SELECT id, customer_name, customer_address, customer_lat, customer_lng, problem_type, created_at, 'mechanic' as type 
              FROM mechanic_bookings 
              WHERE $shop_filter 
              AND (worker_id IS NULL OR worker_id=$wid) 
              AND status IN ('pending', 'assigned') 
              ORDER BY created_at DESC";
    $requests = $conn->query($query);
} else {
    $shop_filter = ($shop_id !== null) ? "shop_id=$shop_id" : "1=1"; // For delivery, usually shop-specific
    $query = "SELECT id, customer_name, delivery_address as customer_address, customer_lat, customer_lng, 'Spare Parts Delivery' as problem_type, created_at, 'order' as type 
              FROM spare_parts_orders 
              WHERE $shop_filter AND worker_id IS NULL AND status='pending' 
              ORDER BY created_at DESC";
    $requests = $conn->query($query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Requests - Road Mithra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/web.css">
    <script src="../assets/js/config.js"></script>
    <!-- Leaflet Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        .request-card {
            background: white; border-radius: 16px; padding: 20px; border: 1.5px solid #EEE; display: flex; gap: 20px; margin-bottom: 20px; transition: 0.3s;
        }
        .request-card:hover { border-color: var(--primary); box-shadow: var(--shadow); }
        .mini-map { width: 150px; height: 150px; border-radius: 12px; background: #EEE; overflow: hidden; flex-shrink: 0; }
        .req-info { flex: 1; }
    </style>
</head>
<body>
<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-logo"><img src="../assets/images/logo.png" alt="Logo"><span>Road Mithra</span></div>
        <div class="sidebar-section-label">Worker Portal</div>
        <a href="dashboard.php" class="nav-link"><i class="fa-solid fa-house"></i> Dashboard</a>
        <a href="new_requests.php" class="nav-link active"><i class="fa-solid fa-bell"></i> New Requests</a>
        <a href="active_jobs.php" class="nav-link"><i class="fa-solid fa-briefcase"></i> Active Jobs</a>
        <a href="completed_jobs.php" class="nav-link"><i class="fa-solid fa-circle-check"></i> Completed</a>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="nav-link danger"><i class="fa-solid fa-power-off"></i> Logout</a>
        </div>
    </aside>

    <div class="main-area">
        <header class="top-bar">
            <div class="top-bar-title"><h1>New Service Requests</h1><p>Available jobs in your area</p></div>
        </header>

        <div class="page-content" style="max-width: 900px;">
            <?php if($requests && $requests->num_rows > 0): while($req = $requests->fetch_assoc()): ?>
            <div class="request-card">
                <div class="mini-map" id="map_<?php echo $req['type'].'_'.$req['id']; ?>" 
                     data-lat="<?php echo $req['customer_lat']; ?>" 
                     data-lng="<?php echo $req['customer_lng']; ?>"></div>
                
                <div class="req-info">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                        <div>
                            <span class="badge badge-purple"><?php echo strtoupper($req['type']); ?></span>
                            <h3 style="font-size:18px; margin: 8px 0;"><?php echo htmlspecialchars($req['problem_type']); ?></h3>
                            <div style="font-size:12px; color:var(--text-light); font-weight:600;"><i class="fa-regular fa-clock" style="margin-right:4px;"></i> <?php echo date('d M Y, h:i A', strtotime($req['created_at'])); ?></div>
                        </div>
                        <div style="text-align:right;">
                            <p style="font-size:12px; color:#AAA;">Customer</p>
                            <p style="font-weight:700;"><?php echo htmlspecialchars($req['customer_name']); ?></p>
                        </div>
                    </div>
                    
                    <p style="font-size:13px; color:var(--text-light); margin-top:10px; margin-bottom:15px;"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($req['customer_address']); ?></p>
                    
                    <div style="display:flex; gap:12px;">
                        <button class="btn btn-primary" onclick="acceptJob(<?php echo $req['id']; ?>, '<?php echo $req['type']; ?>')">Accept Job</button>
                        <button class="btn btn-outline" onclick="viewOnMap(<?php echo $req['customer_lat']; ?>, <?php echo $req['customer_lng']; ?>)">View Full Map</button>
                    </div>
                </div>
            </div>
            <?php endwhile; else: ?>
            <div class="card" style="text-align:center; padding:60px;">
                <i class="fa-solid fa-inbox" style="font-size:50px; color:#EEE; margin-bottom:20px;"></i>
                <h3>No new requests</h3>
                <p>Relax! We'll notify you when someone needs help.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function initMaps() {
        document.querySelectorAll('.mini-map').forEach(container => {
            const lat = parseFloat(container.dataset.lat);
            const lng = parseFloat(container.dataset.lng);
            if(!lat || !lng) return;
            
            const m = L.map(container.id, {
                zoomControl: false,
                attributionControl: false,
                dragging: false,
                scrollWheelZoom: false
            }).setView([lat, lng], 14);
            
            L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: '&copy; Google Maps'
            }).addTo(m);
            const markerHtml = `<div style="color: #E11D48; font-size: 24px;"><i class="fa-solid fa-location-dot"></i></div>`;
            const icon = L.divIcon({
                html: markerHtml,
                className: 'custom-div-icon',
                iconSize: [24, 24],
                iconAnchor: [12, 24]
            });
            L.marker([lat, lng], {icon: icon}).addTo(m);
            m.invalidateSize();
        });
    }

    async function acceptJob(id, type) {
        if(!confirm("Accept this job?")) return;
        
        const formData = new FormData();
        formData.append('worker_id', <?php echo $wid; ?>);
        formData.append('job_id', id);
        formData.append('status', 'accepted');
        formData.append('table', type === 'mechanic' ? 'mechanic' : 'enhanced');

        try {
            const res = await fetch(apiUrl('road_backend/worker/worker_api.php?action=update_status'), {
                method: 'POST',
                body: formData
            });
            const result = await res.json();
            if(result.success) {
                alert("Job accepted! Go to Active Jobs to start.");
                window.location.href = 'active_jobs.php';
            } else {
                alert("Error: " + result.message);
            }
        } catch(e) { alert("Server error"); }
    }

    function viewOnMap(lat, lng) {
        window.open(`https://www.google.com/maps?q=${lat},${lng}`, '_blank');
    }

    window.onload = initMaps;
</script>
</body>
</html>
