<?php
require_once '../includes/db.php';
if (!isset($_SESSION['owner'])) { header("Location: ../auth/login.php"); exit; }
$shop_id = $_SESSION['owner']['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');
    $price = (double)$_POST['price'];
    $quantity = (int)$_POST['quantity'];
    $category = trim($_POST['category'] ?? 'General');
    $brand = trim($_POST['brand'] ?? '');
    $vehicle_type = $_POST['vehicle_type'] ?? 'both';

    $stmt = $conn->prepare("INSERT INTO spare_parts (shop_id, name, description, price, quantity, category, brand, vehicle_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdisss", $shop_id, $name, $description, $price, $quantity, $category, $brand, $vehicle_type);
    
    if($stmt->execute()) {
        header("Location: inventory.php?added=1");
        exit;
    } else {
        $error = "Failed to add product: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product - Road Mithra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/web.css">
    <style>
        .form-card { background: white; border-radius: 20px; padding: 30px; border: 1.5px solid #F1F5F9; max-width: 600px; margin: 0 auto; box-shadow: var(--shadow); }
    </style>
</head>
<body>
<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-logo"><img src="../assets/images/logo.png" alt="Logo"><span>Road Mithra</span></div>
        <div class="sidebar-section-label">Parts Shop</div>
        <a href="parts_dashboard.php" class="nav-link"><i class="fa-solid fa-house"></i> Dashboard</a>
        <a href="customer_orders.php" class="nav-link"><i class="fa-solid fa-cart-shopping"></i> Customer Orders</a>
        <a href="inventory.php" class="nav-link active"><i class="fa-solid fa-boxes-stacked"></i> Inventory</a>
        <a href="manage_delivery_workers.php" class="nav-link"><i class="fa-solid fa-truck-ramp-box"></i> Delivery Team</a>
        <a href="history.php" class="nav-link"><i class="fa-solid fa-clock-rotate-left"></i> History</a>
        <div class="sidebar-footer"><a href="../auth/logout.php" class="nav-link danger"><i class="fa-solid fa-power-off"></i> Logout</a></div>
    </aside>

    <div class="main-area">
        <header class="top-bar">
            <div class="top-bar-title" style="display:flex; align-items:center; gap:15px;">
                <a href="inventory.php" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
                <div><h1>Add Product</h1><p>Register a new spare part to your inventory</p></div>
            </div>
        </header>

        <div class="page-content">
            <div class="form-card">
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="name" class="form-input" required placeholder="e.g. Engine Oil 2L">
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                        <div class="form-group">
                            <label class="form-label">Price (₹)</label>
                            <input type="number" step="0.01" name="price" class="form-input" required placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Initial Stock</label>
                            <input type="number" name="quantity" class="form-input" required placeholder="0">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" class="form-input" rows="3" placeholder="Additional details..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-input" placeholder="e.g. Engine Components">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Brand</label>
                        <input type="text" name="brand" class="form-input" placeholder="e.g. Castrol">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Vehicle Type</label>
                        <select name="vehicle_type" class="form-input">
                            <option value="2_wheeler">2 Wheeler</option>
                            <option value="4_wheeler">4 Wheeler</option>
                            <option value="both" selected>Both</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:15px; margin-top:20px;">Add to Inventory</button>
                    <?php if(isset($error)): ?>
                    <p style="color:var(--danger); text-align:center; margin-top:15px; font-weight:600;"><?php echo $error;?></p>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
