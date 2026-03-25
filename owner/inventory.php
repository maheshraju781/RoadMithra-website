<?php
require_once '../includes/db.php';
if (!isset($_SESSION['owner'])) { header("Location: ../auth/login.php"); exit; }
$sid = $_SESSION['owner']['id'];
$items = $conn->query("SELECT * FROM spare_parts WHERE shop_id=$sid ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - Road Mithra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/web.css">
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
            <div class="top-bar-title"><h1>My Products / Inventory</h1><p>Manage your spare parts catalog</p></div>
            <div class="top-bar-right">
                <input type="text" class="form-input" id="searchInput" placeholder="Search products..." style="width:240px;padding:10px 15px;">
                <a href="add_product.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Product</a>
            </div>
        </header>
        <div class="page-content">
            <div class="card">
                <div class="table-container">
                    <table class="data-table" id="inventoryTable">
                        <thead><tr><th>Product Name</th><th>Category</th><th>Brand</th><th>Vehicle</th><th>Stock</th><th>Price</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php while($p=$items->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:12px;">
                                    <div style="width:38px;height:38px;border-radius:10px;background:var(--primary-light);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:16px;">
                                        <i class="fa-solid fa-box-open"></i>
                                    </div>
                                    <span style="font-weight:600;"><?php echo htmlspecialchars($p['name']);?></span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($p['category']??'—');?></td>
                            <td><?php echo htmlspecialchars($p['brand']??'—');?></td>
                            <td><span class="badge badge-blue"><?php echo str_replace('_',' ',$p['vehicle_type']??'both');?></span></td>
                            <td>
                                <?php $q=$p['quantity']; $cls=$q<=5?'badge-red':($q<=15?'badge-orange':'badge-green'); ?>
                                <span class="badge <?php echo $cls;?>"><?php echo $q;?> Units</span>
                            </td>
                            <td style="font-weight:700;color:var(--secondary);">₹<?php echo number_format($p['price'],2);?></td>
                            <td>
                                <a href="edit_product.php?id=<?php echo $p['id'];?>" class="btn btn-sm btn-outline" style="margin-right:5px;"><i class="fa-solid fa-pen"></i></a>
                                <a href="delete_product.php?id=<?php echo $p['id'];?>" class="btn btn-sm" style="background:#FEE2E2;color:var(--danger);" onclick="return confirm('Delete this product?')"><i class="fa-solid fa-trash"></i></a>
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
<script>
document.getElementById('searchInput').addEventListener('input',function(){
    const q=this.value.toLowerCase();
    document.querySelectorAll('#inventoryTable tbody tr').forEach(r=>{
        r.style.display=r.innerText.toLowerCase().includes(q)?'':'none';
    });
});
</script>
</body>
</html>
