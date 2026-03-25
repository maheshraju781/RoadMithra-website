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
    <title>Your Cart - Road Mithra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="../assets/css/web.css">
    <script src="../assets/js/config.js"></script>
    <style>
        :root {
            --primary: #4F46E5;
            --primary-highlight: #6366F1;
            --primary-light: #F5F7FF;
            --secondary: #10B981;
            --text-dark: #0F172A;
            --text-mid: #475569;
            --text-light: #94A3B8;
            --border: #F1F5F9;
            --bg-card: #FFFFFF;
            --radius-md: 14px;
            --radius-lg: 24px;
            --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        body { background: #FCFDFF; font-family: 'Inter', sans-serif; }

        .checkout-grid {
            display: grid; grid-template-columns: 1fr 380px; gap: 32px; align-items: start;
        }

        .checkout-main {
            background: var(--bg-card); border-radius: var(--radius-lg); padding: 40px; box-shadow: var(--shadow); border: 1px solid var(--border);
        }

        .checkout-sidebar {
            background: var(--bg-card); border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow); border: 1px solid var(--border);
            position: sticky; top: 100px;
        }

        /* Progress Steps */
        .p-steps { display: flex; justify-content: space-between; margin-bottom: 48px; position: relative; }
        .p-steps::before { content: ''; position: absolute; top: 16px; left: 0; right: 0; height: 3px; background: #F1F5F9; z-index: 1; border-radius: 10px; }
        .p-step-item { display: flex; flex-direction: column; align-items: center; gap: 10px; flex: 1; z-index: 2; }
        .p-step-icon { 
            width: 35px; height: 35px; border-radius: 50%; background: #F1F5F9; color: var(--text-light);
            display: flex; align-items: center; justify-content: center; font-weight: 700; transition: 0.4s;
            border: 3px solid white;
        }
        .p-step-item.active .p-step-icon { background: var(--primary); color: white; box-shadow: 0 0 0 4px var(--primary-light); }
        .p-step-text { font-size: 13px; font-weight: 600; color: var(--text-light); transition: 0.3s; }
        .p-step-item.active .p-step-text { color: var(--primary); }

        .cart-item {
            display: flex; align-items: center; gap: 24px; padding: 24px; border-bottom: 1px solid #F8FAFC;
        }
        .cart-item:last-child { border-bottom: none; }
        .cart-item-img { 
            width: 72px; height: 72px; background: #F8FAFC; border-radius: 16px;
            display: flex; align-items: center; justify-content: center; font-size: 26px; color: var(--primary);
            border: 1px solid #F1F5F9;
        }

        .qty-controls { display: flex; align-items: center; gap: 14px; background: #F8FAFC; padding: 6px 12px; border-radius: 12px; }
        .qty-btn { width: 26px; height: 26px; border-radius: 8px; border: none; background: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.04); }
        .qty-btn:hover { background: var(--primary); color: white; }

        .list-item-card {
            display: flex; align-items: center; justify-content: space-between;
            padding: 24px; background: white; border: 2px solid #F8FAFC;
            border-radius: 20px; margin-bottom: 18px; cursor: pointer;
            transition: 0.3s ease;
        }
        .list-item-card:hover { border-color: var(--primary-light); transform: translateY(-2px); }
        .list-item-card.selected { border-color: var(--primary); background: #F5F7FF; }
        
        .icon-box {
            width: 56px; height: 56px; background: #F8FAFC; border-radius: 16px;
            display: flex; align-items: center; justify-content: center; font-size: 24px; color: var(--text-mid);
            margin-right: 20px; transition: 0.3s;
        }
        .list-item-card.selected .icon-box { background: var(--primary); color: white; }

        .footer-actions {
            margin-top: 40px; padding-top: 32px; border-top: 1px solid #F1F5F9; display: flex; justify-content: flex-end;
        }
        .btn-checkout {
            background: var(--primary); color: white; padding: 18px 48px; border-radius: 16px;
            font-weight: 700; font-size: 16px; border: none; cursor: pointer; transition: 0.3s;
            display: flex; align-items: center; gap: 12px;
        }
        .btn-checkout:hover:not(:disabled) { background: var(--primary-highlight); transform: translateY(-3px); box-shadow: 0 12px 24px rgba(79,70,229,0.25); }
        .btn-checkout:disabled { background: #E2E8F0; color: #94A3B8; cursor: not-allowed; }

        #map { height: 380px; border-radius: 20px; border: 2px solid #F1F5F9; margin: 24px 0; }
        
        .step-view { display: none; }
        .step-view.active { display: block; animation: fadeInUp 0.5s ease; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        .confirm-card { background: #F8FAFC; padding: 24px; border-radius: 20px; border: 1px solid #F1F5F9; margin-bottom: 24px; }
        .confirm-row { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .confirm-title { font-size: 12px; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
        .confirm-text { font-size: 16px; font-weight: 600; color: var(--text-dark); }
    </style>
</head>
<body>

    <div class="app-layout">
        <aside class="sidebar">
            <div class="sidebar-logo"><img src="../assets/images/logo.png" alt="Logo"><span>Road Mithra</span></div>
            <div class="sidebar-section-label">Main Menu</div>
            <a href="dashboard.php" class="nav-link"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="buy_spares.php" class="nav-link"><i class="fa-solid fa-cart-shopping"></i> Shop Parts</a>
            <a href="bookings.php" class="nav-link"><i class="fa-solid fa-calendar-check"></i> My Orders</a>
            <div class="sidebar-footer"><a href="../auth/logout.php" class="nav-link danger"><i class="fa-solid fa-power-off"></i> Logout</a></div>
        </aside>

        <main class="main-area">
            <header class="top-bar">
                <div class="top-bar-title"><h1>Your Shopping Bag</h1><p>Review items and delivery location</p></div>
                <div class="top-bar-right">
                    <a href="buy_spares.php" class="btn btn-outline btn-sm"><i class="fa-solid fa-plus"></i> Add More</a>
                </div>
            </header>

            <div class="checkout-grid">
                <div class="checkout-main">
                    <!-- PROGRESS STEPS -->
                    <div class="p-steps">
                        <div id="pStep1" class="p-step-item active">
                            <div class="p-step-icon">1</div>
                            <span class="p-step-text">Bag</span>
                        </div>
                        <div id="pStep2" class="p-step-item">
                            <div class="p-step-icon">2</div>
                            <span class="p-step-text">Delivery</span>
                        </div>
                        <div id="pStep3" class="p-step-item">
                            <div class="p-step-icon">3</div>
                            <span class="p-step-text">Payment</span>
                        </div>
                        <div id="pStep4" class="p-step-item">
                            <div class="p-step-icon">4</div>
                            <span class="p-step-text">Review</span>
                        </div>
                    </div>

                    <!-- STEP 1: REVIEW ITEMS -->
                    <div id="cartStep" class="step-view active">
                        <h2 style="font-size: 22px; font-weight: 800; margin-bottom: 24px;">Review Items</h2>
                        <div id="cartItems">
                            <!-- Items injected by JS -->
                        </div>

                        <div class="footer-actions">
                            <button class="btn-checkout" id="toDelivery" onclick="nextStep('addressStep')">
                                Set Delivery Address <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 2: DELIVERY ADDRESS -->
                    <div id="addressStep" class="step-view">
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 24px;">
                            <button onclick="prevStep('cartStep')" style="background:none; border:none; cursor:pointer; font-size:18px;"><i class="fa-solid fa-arrow-left"></i></button>
                            <h2 style="font-size: 22px; font-weight: 800;">Address Details</h2>
                        </div>
                        
                        <div style="margin-bottom: 16px;">
                            <div style="background: white; border: 1.5px solid var(--border); border-radius: 12px; padding: 12px 18px; display: flex; align-items: center; gap: 12px;">
                                <i class="fa-solid fa-magnifying-glass" style="color: var(--text-light);"></i>
                                <input type="text" id="locSearchInput" placeholder="Search your area..." style="border:none; outline:none; width:100%; font-size:14px;" onkeypress="if(event.key === 'Enter') searchLocation()">
                                <button onclick="searchLocation()" style="background:none; border:none; color:var(--primary); cursor:pointer;"><i class="fa-solid fa-location-dot"></i></button>
                            </div>
                        </div>

                        <div id="map"></div>
                        
                        <div style="margin-top: 24px;">
                            <label style="font-size: 14px; font-weight: 700; color: var(--text-dark); display: block; margin-bottom: 8px;">Full Building / House Address</label>
                            <textarea id="deliveryAddress" style="width:100%; padding:18px; border-radius:14px; border:1.5px solid var(--border); font-family:inherit; resize:none;" rows="3" placeholder="Door No, Floor, Street Name..."></textarea>
                        </div>

                        <div class="footer-actions">
                            <button class="btn-checkout" onclick="nextStep('paymentStep')">
                                Choose Payment <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 3: PAYMENT METHOD -->
                    <div id="paymentStep" class="step-view">
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 24px;">
                            <button onclick="prevStep('addressStep')" style="background:none; border:none; cursor:pointer; font-size:18px;"><i class="fa-solid fa-arrow-left"></i></button>
                            <h2 style="font-size: 22px; font-weight: 800;">Payment Mode</h2>
                        </div>

                        <div class="list-item-card" id="payCOD" onclick="selectPayment('cod')">
                            <div style="display: flex; align-items: center;">
                                <div class="icon-box"><i class="fa-solid fa-money-bill-transfer"></i></div>
                                <div>
                                    <h4 style="font-size:16px; font-weight:700;">Cash on Delivery</h4>
                                    <p style="font-size:13px; color:var(--text-mid);">Pay when your parts arrive</p>
                                </div>
                            </div>
                        </div>

                        <div class="list-item-card" id="payOnline" onclick="selectPayment('online')">
                            <div style="display: flex; align-items: center;">
                                <div class="icon-box" style="color: #3B82F6;"><i class="fa-solid fa-shield-halved"></i></div>
                                <div>
                                    <h4 style="font-size:16px; font-weight:700;">Secure UPI / Online</h4>
                                    <p style="font-size:13px; color:var(--text-mid);">Pay via GPay, PhonePe or Cards</p>
                                </div>
                            </div>
                        </div>

                        <div class="footer-actions">
                            <button class="btn-checkout" id="toConfirm" disabled onclick="nextStep('confirmStep')">
                                Order Review <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 4: REVIEW & CONFIRM -->
                    <div id="confirmStep" class="step-view">
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 24px;">
                            <button onclick="prevStep('paymentStep')" style="background:none; border:none; cursor:pointer; font-size:18px;"><i class="fa-solid fa-arrow-left"></i></button>
                            <h2 style="font-size: 22px; font-weight: 800;">Review & Place</h2>
                        </div>

                        <div class="confirm-card">
                            <div class="confirm-row">
                                <div>
                                    <div class="confirm-title">Shipping To</div>
                                    <div class="confirm-text" id="confirmAddrText">-</div>
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div>
                                    <div class="confirm-title">Method</div>
                                    <div class="confirm-text" id="confirmPayText">-</div>
                                </div>
                                <div style="text-align: right;">
                                    <div class="confirm-title">Total Bill</div>
                                    <div class="confirm-text" id="confirmTotal" style="color:var(--primary); font-size:20px; font-weight:800;">₹0</div>
                                </div>
                            </div>
                        </div>

                        <div id="onlinePaymentUI" style="display: none; padding: 24px; background: #F5F7FF; border-radius: 20px; border: 2px dashed #C7D2FE; margin-bottom: 24px;">
                            <label style="font-size:13px; font-weight:700; color:var(--primary); display:block; text-align:center; margin-bottom:10px;">ENTER UPI ID</label>
                            <input type="text" id="upiId" placeholder="e.g. your-name@upi" style="width:100%; padding:14px; border-radius:12px; border:1px solid #C7D2FE; text-align:center; font-weight:700; font-size:15px; margin-bottom:12px;">
                            <div style="display: flex; justify-content: center; gap: 15px; color: #94A3B8; font-size: 20px;">
                                <i class="fa-brands fa-google-pay"></i>
                                <i class="fa-solid fa-money-check-dollar"></i>
                                <i class="fa-solid fa-lock"></i>
                            </div>
                        </div>

                        <div class="footer-actions">
                            <button class="btn-checkout" id="placeOrderBtn" onclick="placeOrder()" style="width: 100%; justify-content: center;">
                                <span id="btnText">Place Final Order</span> <i class="fa-solid fa-circle-check"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="checkout-sidebar">
                    <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 24px; color: var(--text-dark);">Order Summary</h3>
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 14px; color: var(--text-mid);">
                            <span>Subtotal</span>
                            <span id="subtotal" style="font-weight: 600;">₹0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px; color: var(--text-mid);">
                            <span>Delivery Fee</span>
                            <span style="font-weight: 600;">₹50.00</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px; color: var(--text-mid);">
                            <span>Tax (GST)</span>
                            <span style="font-weight: 600;">₹10.00</span>
                        </div>
                    </div>
                    <div style="margin-top: 24px; padding-top: 24px; border-top: 1.5px dashed var(--border); display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 18px; font-weight: 800; color: var(--text-dark);">Grand Total</span>
                        <span id="grandTotal" style="font-size: 18px; font-weight: 800; color: var(--primary);">₹0</span>
                    </div>
                    
                    <div style="margin-top: 32px; padding: 16px; background: #F8FAFC; border-radius: 12px; display: flex; gap: 12px; align-items: center;">
                        <i class="fa-solid fa-lock" style="color: var(--secondary); font-size: 20px;"></i>
                        <p style="font-size: 11px; color: var(--text-mid); line-height: 1.4;">Safe and Secure Payments via SSL Encryption</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const customerId = <?php echo $user['id']; ?>;
        let cartData = { items: [], total: 0 };
        let map, marker;
        let selectedLat = 17.3850, selectedLng = 78.4867; // Default Hyderabad

        // Initialize Map
        function initMap() {
            map = L.map('map').setView([selectedLat, selectedLng], 13);
            L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: '&copy; Google Maps'
            }).addTo(map);

            marker = L.marker([selectedLat, selectedLng], {draggable: true}).addTo(map);
            
            map.on('click', function(e) {
                selectedLat = e.latlng.lat;
                selectedLng = e.latlng.lng;
                marker.setLatLng(e.latlng);
                reverseGeocode(e.latlng.lat, e.latlng.lng);
            });

            marker.on('dragend', function(e) {
                const pos = marker.getLatLng();
                selectedLat = pos.lat;
                selectedLng = pos.lng;
                reverseGeocode(pos.lat, pos.lng);
            });

            // Try to get user location
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(pos => {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    selectedLat = lat;
                    selectedLng = lng;
                    map.setView([lat, lng], 15);
                    marker.setLatLng([lat, lng]);
                    reverseGeocode(lat, lng);
                });
            }
        }

        async function reverseGeocode(lat, lng) {
            try {
                const res = await fetch(apiUrl(`road_backend/location_proxy.php?lat=${lat}&lng=${lng}`));
                const data = await res.json();
                if(data.display_name) {
                    document.getElementById('deliveryAddress').value = data.display_name;
                }
            } catch(e) { console.error("Reverse geocoding error:", e); }
        }

        async function searchLocation() {
            const query = document.getElementById('locSearchInput').value;
            if(!query) return;
            try {
                const res = await fetch(apiUrl(`road_backend/location_proxy.php?query=${encodeURIComponent(query)}`));
                const data = await res.json();
                if (data && data.length > 0) {
                    const result = data[0];
                    selectedLat = parseFloat(result.lat);
                    selectedLng = parseFloat(result.lon);
                    map.setView([selectedLat, selectedLng], 16);
                    marker.setLatLng([selectedLat, selectedLng]);
                    reverseGeocode(selectedLat, selectedLng);
                } else {
                    alert("Location not found. Please try a more specific address.");
                }
            } catch(e) { 
                console.error("Search error:", e);
                alert("Search failed. Please check your internet connection."); 
            }
        }

        async function fetchCart() {
            try {
                const res = await fetch(apiUrl(`road_backend/customer/cart.php?customer_id=${customerId}`));
                const result = await res.json();
                if(result.success) {
                    cartData = result.data;
                    renderCart();
                }
            } catch(e) { console.error(e); }
        }

        let selectedPayment = '';

        function nextStep(stepId) {
            document.querySelectorAll('.step-view').forEach(s => s.classList.remove('active'));
            document.getElementById(stepId).classList.add('active');
            
            // Update progress steps visual
            const stepNum = stepId === 'cartStep' ? 1 : stepId === 'addressStep' ? 2 : stepId === 'paymentStep' ? 3 : 4;
            document.querySelectorAll('.p-step-item').forEach((item, idx) => {
                if(idx < stepNum) item.classList.add('active');
                else item.classList.remove('active');
            });
            
            if(stepId === 'addressStep') {
                setTimeout(() => { map.invalidateSize(); }, 300);
            }
            if(stepId === 'confirmStep') {
                // Set Review Data
                document.getElementById('confirmAddrText').innerText = document.getElementById('deliveryAddress').value || "No address provided";
                document.getElementById('confirmPayText').innerText = selectedPayment === 'cod' ? 'Cash on Delivery' : 'Online Payment';
                document.getElementById('confirmTotal').innerText = document.getElementById('grandTotal').innerText;
                
                if(selectedPayment === 'online') {
                    document.getElementById('onlinePaymentUI').style.display = 'block';
                    document.getElementById('btnText').innerText = 'Verify & Pay';
                } else {
                    document.getElementById('onlinePaymentUI').style.display = 'none';
                    document.getElementById('btnText').innerText = 'Confirm Order';
                }
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function prevStep(stepId) {
            nextStep(stepId);
        }

        function selectPayment(method) {
            selectedPayment = method;
            document.querySelectorAll('.list-item-card').forEach(c => c.classList.remove('selected'));
            if(method === 'cod') document.getElementById('payCOD').classList.add('selected');
            if(method === 'online') document.getElementById('payOnline').classList.add('selected');
            document.getElementById('toConfirm').disabled = false;
        }

        function updateTotals(subtotal) {
            document.getElementById('subtotal').innerText = '₹' + subtotal.toFixed(2);
            const extra = subtotal > 0 ? 60 : 0;
            const grandTotal = subtotal + extra;
            document.getElementById('grandTotal').innerText = '₹' + grandTotal.toFixed(2);
            
            // Sync with confirm step as well in case user jumps
            const confirmTotalElem = document.getElementById('confirmTotal');
            if(confirmTotalElem) confirmTotalElem.innerText = '₹' + grandTotal.toFixed(2);
        }

        function renderCart() {
            const container = document.getElementById('cartItems');
            if(cartData.items.length === 0) {
                container.innerHTML = `
                    <div style="text-align:center; padding:50px;">
                        <i class="fa-solid fa-cart-arrow-down" style="font-size:50px; color:#EEE; margin-bottom:20px;"></i>
                        <h3>Your cart is empty</h3>
                        <p style="color:#666; margin-bottom:25px;">Looks like you haven't added anything yet.</p>
                        <a href="buy_spares.php" class="btn btn-primary">Go to Market</a>
                    </div>
                `;
                document.getElementById('toDelivery').disabled = true;
                updateTotals(0);
                return;
            }

            container.innerHTML = '';
            cartData.items.forEach(item => {
                const row = document.createElement('div');
                row.className = 'cart-item';
                row.innerHTML = `
                    <div class="cart-item-img"><i class="fa-solid fa-gears"></i></div>
                    <div style="flex:1;">
                        <h4 style="font-size:16px; font-weight:700;">${item.name}</h4>
                        <p style="font-size:12px; color:var(--text-light);"><i class="fa-solid fa-shop"></i> ${item.shop_name}</p>
                        <div style="margin-top:8px; font-weight:800; font-size:17px; color:var(--text-dark);">₹${item.price}</div>
                    </div>
                    <div class="qty-controls">
                        <button class="qty-btn" onclick="updateQty(${item.cart_id}, ${item.quantity - 1})"><i class="fa-solid fa-minus"></i></button>
                        <span style="font-weight:700; width:25px; text-align:center;">${item.quantity}</span>
                        <button class="qty-btn" onclick="updateQty(${item.cart_id}, ${item.quantity + 1})"><i class="fa-solid fa-plus"></i></button>
                    </div>
                    <div style="width:100px; text-align:right;">
                        <span style="display:block; font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:700;">Subtotal</span>
                        <span style="font-weight:800; font-size:16px;">₹${item.subtotal}</span>
                    </div>
                    <button style="background:none; border:none; color:var(--danger); cursor:pointer; font-size:18px; margin-left:15px;" onclick="removeItem(${item.cart_id})"><i class="fa-solid fa-trash-can"></i></button>
                `;
                container.appendChild(row);
            });

            document.getElementById('toDelivery').disabled = false;
            updateTotals(cartData.total);
        }

        async function placeOrder() {
            const address = document.getElementById('deliveryAddress').value;
            if(!address || address.length < 5) {
                alert("Please provide a complete delivery address.");
                nextStep('addressStep');
                return;
            }

            if(selectedPayment === 'online') {
                const upi = document.getElementById('upiId').value;
                if(!upi || !upi.includes('@')) {
                    alert("Please enter a valid (Mock) UPI ID to proceed.");
                    return;
                }
            }

            const amount = cartData.total + 60;
            const btn = document.getElementById('placeOrderBtn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Processing...';

            // Simulate server delay like typical premium apps
            await new Promise(r => setTimeout(r, 2000));

            const formData = new FormData();
            formData.append('customer_id', customerId);
            formData.append('customer_name', '<?php echo $user['name']; ?>');
            formData.append('customer_phone', '<?php echo $user['phone']; ?>');
            formData.append('customer_address', address);
            formData.append('customer_lat', selectedLat);
            formData.append('customer_lng', selectedLng);
            formData.append('total_amount', amount);
            formData.append('payment_method', selectedPayment === 'cod' ? 'cod' : 'online_upi');
            
            const simplifiedItems = cartData.items.map(i => ({
                part_id: i.part_id,
                shop_id: i.shop_id,
                part_name: i.name,
                price: i.price,
                quantity: i.quantity
            }));
            formData.append('items', JSON.stringify(simplifiedItems));

            try {
                const res = await fetch(apiUrl('road_backend/customer/place_spare_parts_order.php'), {
                    method: 'POST',
                    body: formData
                });
                const result = await res.json();
                if(result.success) {
                    alert(selectedPayment === 'cod' ? "Order placed successfully!" : "Payment successful! Order placed.");
                    window.location.href = `bookings.php`; 
                } else {
                    alert("Order failed: " + result.message);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch(e) { 
                alert("Error connecting to server.");
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        async function updateQty(cartId, newQty) {
            if(newQty < 1) return removeItem(cartId);
            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('cart_id', cartId);
            formData.append('customer_id', customerId);
            formData.append('quantity', newQty);
            try {
                const res = await fetch(apiUrl('road_backend/customer/cart.php'), { method: 'POST', body: formData });
                const result = await res.json();
                if(result.success) fetchCart();
            } catch(e) { console.error(e); }
        }

        async function removeItem(cartId) {
            if(!confirm("Remove this item?")) return;
            const formData = new FormData();
            formData.append('action', 'remove');
            formData.append('cart_id', cartId);
            formData.append('customer_id', customerId);
            try {
                const res = await fetch(apiUrl('road_backend/customer/cart.php'), { method: 'POST', body: formData });
                const result = await res.json();
                if(result.success) fetchCart();
            } catch(e) { console.error(e); }
        }

        window.onload = () => {
            fetchCart();
            initMap();
        };
    </script>
</body>
</html>
