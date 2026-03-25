<?php
require_once '../includes/db.php';
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../auth/login.php?role=customer"); exit;
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Spare Parts - Road Mithra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/web.css">
    <script src="../assets/js/config.js"></script>
    <style>
        .product-grid {
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); 
            gap: 25px; 
            margin-top: 30px;
        }
        .product-card {
            background: white; 
            border-radius: var(--radius-lg); 
            padding: 0; 
            overflow: hidden;
            border: 1px solid var(--border); 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        .product-card:hover { 
            transform: translateY(-8px); 
            box-shadow: var(--shadow-lg); 
            border-color: var(--primary); 
        }
        .product-img-wrapper {
            position: relative;
            height: 180px; 
            background: #f8f9fa; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            overflow: hidden;
        }
        .product-img-wrapper i {
            font-size: 60px;
            color: #ddd;
            transition: 0.3s;
        }
        .product-card:hover .product-img-wrapper i {
            transform: scale(1.1);
            color: var(--primary-light);
        }
        .product-info {
            padding: 20px;
        }
        .shop-tag {
            font-size: 11px;
            font-weight: 700;
            color: var(--primary);
            background: var(--primary-light);
            padding: 4px 10px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 10px;
        }
        .product-name {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text-dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .price-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 15px;
        }
        .price-val {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-dark);
        }
        .add-btn {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--primary);
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
        }
        .add-btn:hover {
            background: var(--primary-dark);
            transform: scale(1.05);
        }
        .search-container {
            position: relative;
            max-width: 400px;
        }
        .search-container i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }
        .search-input {
            padding-left: 50px !important;
        }
    </style>
</head>
<body>

    <div class="app-layout">
        <aside class="sidebar">
            <div class="sidebar-logo">
                <img src="../assets/images/logo.png" alt="Logo">
                <span>Road Mithra</span>
            </div>
            
            <div class="sidebar-section-label">Main Menu</div>
            <a href="dashboard.php" class="nav-link"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="buy_spares.php" class="nav-link active"><i class="fa-solid fa-cart-shopping"></i> Shop Parts</a>
            <a href="bookings.php" class="nav-link"><i class="fa-solid fa-calendar-check"></i> My Orders</a>
            
            <div class="sidebar-section-label">Support</div>
            <a href="#" class="nav-link"><i class="fa-solid fa-circle-question"></i> Help Center</a>
            
            <div class="sidebar-footer">
                <a href="../auth/logout.php" class="nav-link danger"><i class="fa-solid fa-power-off"></i> Logout</a>
            </div>
        </aside>

        <main class="main-area">
            <header class="top-bar">
                <div class="top-bar-title">
                    <h1>Spare Parts Market</h1>
                    <p>Genuine parts from verified local shops</p>
                </div>
                <div class="top-bar-right">
                    <div class="search-container">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="searchInput" placeholder="Search for filters, oils, brakes..." class="form-input search-input" onkeyup="searchParts(this.value)">
                    </div>
                    <a href="cart.php" class="btn btn-outline" style="position:relative;">
                        <i class="fa-solid fa-bag-shopping"></i> 
                        Cart
                        <span id="cartCount" style="position:absolute; top:-8px; right:-8px; background:var(--secondary); color:white; font-size:10px; width:20px; height:20px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; border:2px solid white;">0</span>
                    </a>
                </div>
            </header>

            <div class="page-content">
                <div class="alert alert-info" style="border:none; box-shadow:var(--shadow);">
                    <div class="stat-icon-box" style="background:var(--primary); color:white; width:40px; height:40px; border-radius:10px; font-size:16px;">
                        <i class="fa-solid fa-truck-fast"></i>
                    </div>
                    <div>
                        <strong style="display:block; font-size:15px;">Express Delivery</strong>
                        <p style="font-size:13px; opacity:0.8;">Get your parts delivered to your location in 45-60 minutes.</p>
                    </div>
                </div>

                <div id="productGrid" class="product-grid">
                    <!-- Products loaded via JS -->
                    <div style="grid-column: 1/-1; text-align:center; padding:100px;">
                        <i class="fa-solid fa-circle-notch fa-spin" style="font-size:40px; color:var(--primary);"></i>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        let allParts = [];

        async function loadParts() {
            try {
                const res = await fetch(apiUrl('road_backend/customer/get_parts.php'));
                const result = await res.json();
                if(result.success) {
                    allParts = result.data.parts;
                    renderParts(allParts);
                }
            } catch(e) {
                console.error(e);
            }
        }

        function renderParts(parts) {
            const grid = document.getElementById('productGrid');
            grid.innerHTML = '';
            if(parts.length === 0) {
                grid.innerHTML = `
                    <div style="grid-column: 1/-1; text-align:center; padding:100px;">
                        <i class="fa-solid fa-box-open" style="font-size:60px; color:#ddd; margin-bottom:20px;"></i>
                        <h3>No parts available yet</h3>
                        <p style="color:var(--text-light);">We're adding new shops every day. Check back soon!</p>
                    </div>
                `;
                return;
            }
            parts.forEach(p => {
                const card = document.createElement('div');
                card.className = 'product-card';
                card.innerHTML = `
                    <div class="product-img-wrapper">
                        <i class="fa-solid fa-gears"></i>
                        <div style="position:absolute; top:15px; left:15px;">
                            <span class="badge badge-green" style="box-shadow:0 4px 10px rgba(0,0,0,0.1)">IN STOCK</span>
                        </div>
                    </div>
                    <div class="product-info">
                        <span class="shop-tag"><i class="fa-solid fa-shop"></i> ${p.shop_name}</span>
                        <h4 class="product-name">${p.name}</h4>
                        <div style="display:flex; align-items:center; gap:5px; font-size:12px; color:var(--accent); margin-bottom:10px;">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star-half-stroke"></i>
                            <span style="color:var(--text-light); margin-left:3px;">(4.5)</span>
                        </div>
                        <div class="price-row">
                            <div style="display:flex; flex-direction:column;">
                                <span style="font-size:11px; text-transform:uppercase; color:var(--text-light); font-weight:700;">Price</span>
                                <span class="price-val">₹${p.price}</span>
                            </div>
                            <button class="add-btn" onclick="addToCart(${p.id})" title="Add to Cart">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>
                `;
                grid.appendChild(card);
            });
        }

        function searchParts(q) {
            const filtered = allParts.filter(p => 
                p.name.toLowerCase().includes(q.toLowerCase()) || 
                p.shop_name.toLowerCase().includes(q.toLowerCase())
            );
            renderParts(filtered);
        }

        async function addToCart(partId) {
            const part = allParts.find(p => p.id === partId);
            if(!part) return;

            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('customer_id', <?php echo $user['id']; ?>);
            formData.append('part_id', partId);
            formData.append('shop_id', part.shop_id);
            formData.append('quantity', 1);

            try {
                const addButton = event.currentTarget;
                addButton.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
                addButton.disabled = true;

                const res = await fetch(apiUrl('road_backend/customer/cart.php'), {
                    method: 'POST',
                    body: formData
                });
                const result = await res.json();
                if(result.success) {
                    updateCartCount();
                    // Show a quick success toast or feedback
                    addButton.innerHTML = '<i class="fa-solid fa-check"></i>';
                    addButton.style.background = 'var(--success)';
                    setTimeout(() => {
                        window.location.href = 'cart.php';
                    }, 500);
                } else {
                    alert(result.message);
                    addButton.innerHTML = '<i class="fa-solid fa-plus"></i>';
                    addButton.disabled = false;
                }
            } catch(e) {
                alert("Error adding to cart");
            }
        }

        async function updateCartCount() {
            try {
                const res = await fetch(apiUrl(`road_backend/customer/cart.php?customer_id=<?php echo $user['id']; ?>`));
                const result = await res.json();
                if(result.success) {
                    document.getElementById('cartCount').innerText = result.data.items.length;
                }
            } catch(e) {}
        }

        window.onload = () => {
            loadParts();
            updateCartCount();
        };
    </script>
</body>
</html>
