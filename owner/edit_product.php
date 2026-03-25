<?php
require_once '../includes/db.php';
if (!isset($_SESSION['owner'])) { header("Location: ../auth/login.php"); exit; }
$shop_id = $_SESSION['owner']['id'];
$id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $price = (double)$_POST['price'];
    $quantity = (int)$_POST['quantity'];
    $category = trim($_POST['category']);
    $brand = trim($_POST['brand']);
    $vehicle_type = $_POST['vehicle_type'];

    $stmt = $conn->prepare("UPDATE spare_parts SET name=?, price=?, quantity=?, category=?, brand=?, vehicle_type=? WHERE id=? AND shop_id=?");
    $stmt->bind_param("sdisssii", $name, $price, $quantity, $category, $brand, $vehicle_type, $id, $shop_id);
    if($stmt->execute()) {
        header("Location: inventory.php?updated=1");
        exit;
    } else {
        $error = "Failed to update product.";
    }
}

$stmt = $conn->prepare("SELECT * FROM spare_parts WHERE id=? AND shop_id=?");
$stmt->bind_param("ii", $id, $shop_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) { die("Product not found or unauthorized access."); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Road Mithra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/web.css">
    <style>
        .form-card { background: white; border-radius: 20px; padding: 30px; border: 1.5px solid #F1F5F9; max-width: 600px; margin: 0 auto; }
        .old-val { font-size: 13px; color: #64748B; margin-top: 5px; font-weight: 500; }
        .old-val strong { color: var(--primary); }
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
                <div><h1>Edit Product</h1><p>Update your spare part details</p></div>
            </div>
        </header>

        <div class="page-content">
            <div class="form-card">
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="name" class="form-input" value="<?php echo htmlspecialchars($product['name']); ?>" required placeholder="Old Name: <?php echo htmlspecialchars($product['name']); ?>">
                        <div class="old-val">Current Name: <strong><?php echo htmlspecialchars($product['name']); ?></strong></div>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                        <div class="form-group">
                            <label class="form-label">Price (₹)</label>
                            <input type="number" step="0.01" name="price" class="form-input" value="<?php echo $product['price']; ?>" required>
                            <div class="old-val">Current Price: <strong>₹<?php echo number_format($product['price'], 2); ?></strong></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Stock Quantity</label>
                            <input type="number" name="quantity" class="form-input" value="<?php echo $product['quantity']; ?>" required>
                            <div class="old-val">Current Stock: <strong><?php echo $product['quantity']; ?> Units</strong></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-input" value="<?php echo htmlspecialchars($product['category']); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Brand</label>
                        <input type="text" name="brand" class="form-input" value="<?php echo htmlspecialchars($product['brand']); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Vehicle Type</label>
                        <select name="vehicle_type" class="form-input">
                            <option value="2_wheeler" <?php echo $product['vehicle_type']=='2_wheeler'?'selected':'';?>>2 Wheeler</option>
                            <option value="4_wheeler" <?php echo $product['vehicle_type']=='4_wheeler'?'selected':'';?>>4 Wheeler</option>
                            <option value="both" <?php echo $product['vehicle_type']=='both'?'selected':'';?>>Both</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:15px; margin-top:20px;">Update Product</button>
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
