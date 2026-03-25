<?php
require_once '../includes/db.php';
if (!isset($_SESSION['owner']) || $_SESSION['role'] !== 'parts_owner') {
    header("Location: ../auth/login.php?role=parts_owner"); exit;
}
$owner = $_SESSION['owner'];
$sid = $owner['id'];
$oid = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($oid <= 0) {
    header("Location: parts_dashboard.php"); exit;
}

// Fetch order details
$stmt = $conn->prepare("SELECT spo.*, GROUP_CONCAT(CONCAT(sp.name,' x ',spoi.quantity) SEPARATOR ', ') as items
    FROM spare_parts_orders spo
    LEFT JOIN spare_parts_order_items spoi ON spoi.order_id = spo.id
    LEFT JOIN spare_parts sp ON sp.id = spoi.part_id
    WHERE spo.id = ? AND spo.shop_id = ?
    GROUP BY spo.id");
$stmt->bind_param("ii", $oid, $sid);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "<script>alert('Order not found or unauthorized'); window.location.href='parts_dashboard.php';</script>";
    exit;
}

// Fetch available delivery workers
$workers = $conn->query("SELECT * FROM workers WHERE shop_id = $sid AND type = 'delivery' AND is_available = 1");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Delivery - Road Mithra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/web.css">
    <script src="../assets/js/config.js"></script>
    <style>
        .assign-container { max-width: 600px; margin: 40px auto; padding: 20px; }
        .order-summary { background: white; padding: 30px; border-radius: 24px; box-shadow: var(--shadow); margin-bottom: 25px; border: 1px solid var(--border); }
        .worker-card { 
            display: flex; align-items: center; justify-content: space-between; 
            padding: 20px; background: white; border-radius: 18px; margin-bottom: 12px;
            border: 2px solid transparent; cursor: pointer; transition: 0.3s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }
        .worker-card:hover { border-color: var(--primary-light); transform: translateY(-2px); }
        .worker-card.selected { border-color: var(--primary); background: var(--primary-light); }
        .worker-info { display: flex; align-items: center; gap: 15px; }
        .worker-avatar { width: 45px; height: 45px; background: var(--primary); color: white; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-weight: 800; }
        .status-dot { width: 10px; height: 10px; border-radius: 50%; background: #10B981; }
    </style>
</head>
<body>
<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-logo"><img src="../assets/images/logo.png" alt="Logo"><span>Road Mithra</span></div>
        <div class="sidebar-section-label">Parts Shop</div>
        <a href="parts_dashboard.php" class="nav-link"><i class="fa-solid fa-house"></i> Dashboard</a>
        <a href="customer_orders.php" class="nav-link"><i class="fa-solid fa-cart-shopping"></i> Customer Orders</a>
        <div class="sidebar-footer"><a href="../auth/logout.php" class="nav-link danger"><i class="fa-solid fa-power-off"></i> Logout</a></div>
    </aside>

    <div class="main-area">
        <header class="top-bar">
            <div class="top-bar-title"><h1>Assign Delivery</h1><p>Order #SPO<?php echo $oid; ?> &bull; Selection</p></div>
            <a href="parts_dashboard.php" class="btn btn-outline btn-sm"><i class="fa-solid fa-xmark"></i> Cancel</a>
        </header>

        <div class="page-content">
            <div class="assign-container">
                <div class="order-summary">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                        <span class="badge badge-orange">PENDING ASSIGNMENT</span>
                        <span style="font-size:14px; color:var(--text-light); font-weight:600;"><?php echo date('d M, Y', strtotime($order['created_at'])); ?></span>
                    </div>
                    <h3 style="font-size:22px; margin-bottom:10px;"><?php echo htmlspecialchars($order['customer_name'] ?: 'Customer'); ?></h3>
                    <p style="color:var(--text-light); font-size:14px; margin-bottom:20px;"><i class="fa-solid fa-location-dot" style="margin-right:8px;"></i> <?php echo htmlspecialchars($order['delivery_address']); ?></p>
                    
                    <div style="background:var(--bg); padding:15px; border-radius:14px; border:1px dashed var(--border);">
                        <div style="font-size:12px; font-weight:800; color:var(--text-light); text-transform:uppercase; margin-bottom:8px;">Order Items</div>
                        <div style="font-size:15px; font-weight:600; color:var(--text-dark); line-height:1.6;"><?php echo htmlspecialchars($order['items']); ?></div>
                    </div>
                </div>

                <h4 style="font-weight:800; color:var(--text-dark); margin-bottom:20px; display:flex; justify-content:space-between; align-items:center;">
                    Available Team Members
                    <span style="font-size:12px; background:#D1FAE5; color:#059669; padding:4px 12px; border-radius:100px;"><?php echo $workers->num_rows; ?> Online</span>
                </h4>

                <div id="workersList">
                    <?php if($workers->num_rows > 0): while($w=$workers->fetch_assoc()): ?>
                    <div class="worker-card" onclick="selectWorker(this, <?php echo $w['id']; ?>)">
                        <div class="worker-info">
                            <div class="worker-avatar"><?php echo strtoupper($w['name'][0]); ?></div>
                            <div>
                                <div style="font-weight:800; font-size:15px;"><?php echo htmlspecialchars($w['name']); ?></div>
                                <div style="font-size:12px; color:var(--text-light);"><?php echo $w['phone']; ?></div>
                            </div>
                        </div>
                        <div class="status-dot"></div>
                    </div>
                    <?php endwhile; else: ?>
                    <div style="text-align:center; padding:40px; background:white; border-radius:24px; border:1px solid var(--border);">
                        <i class="fa-solid fa-user-slash" style="font-size:32px; color:var(--text-light); margin-bottom:15px; display:block;"></i>
                        <p style="color:var(--text-light); font-weight:600;">No available delivery workers found.</p>
                        <a href="manage_delivery_workers.php" style="color:var(--primary); font-size:14px; font-weight:800; text-decoration:none; margin-top:10px; display:inline-block;">Manage Team</a>
                    </div>
                    <?php endif; ?>
                </div>

                <div style="margin-top:35px;">
                    <button id="assignBtn" disabled onclick="confirmAssignment()" class="btn btn-primary" style="width:100% ;justify-content:center; padding:18px; font-size:16px; border-radius:16px; box-shadow: var(--shadow-lg);">
                        Confirm Assignment
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let selectedWorkerId = null;

function selectWorker(el, id) {
    document.querySelectorAll('.worker-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    selectedWorkerId = id;
    document.getElementById('assignBtn').disabled = false;
}

async function confirmAssignment() {
    if(!selectedWorkerId) return;
    
    document.getElementById('assignBtn').disabled = true;
    document.getElementById('assignBtn').innerText = "Assigning...";

    const fd = new FormData();
    fd.append('action', 'assign_worker');
    fd.append('order_id', '<?php echo $oid; ?>');
    fd.append('worker_id', selectedWorkerId);

    try {
        const res = await fetch(apiUrl('api/owner_api.php'), {
            method: 'POST',
            body: fd
        });
        const result = await res.json();
        if(result.success) {
            alert("Delivery assigned successfully!");
            window.location.href = 'parts_dashboard.php';
        } else {
            alert("Error: " + result.message);
            document.getElementById('assignBtn').disabled = false;
            document.getElementById('assignBtn').innerText = "Confirm Assignment";
        }
    } catch(e) {
        alert("Server communication failed.");
        document.getElementById('assignBtn').disabled = false;
    }
}
</script>
</body>
</html>
