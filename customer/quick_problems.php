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
    <title>Common Problems - Road Mithra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/web.css">
    <script src="../assets/js/config.js"></script>
    <!-- Leaflet Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        .category-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 20px;
        }
        .category-card {
            background: white; border-radius: 20px; padding: 25px; text-align: center; border: 1.5px solid #EEE; transition: 0.3s; cursor: pointer;
        }
        .category-card:hover { border-color: var(--primary); transform: translateY(-5px); box-shadow: var(--shadow); }
        .category-card.selected { border-color: var(--primary); background: var(--primary-light); }
        .category-card i { font-size: 30px; margin-bottom: 12px; }

        .map-view { height: 350px; border-radius: 20px; margin: 20px 0; border: 1px solid #EEE; position: relative; overflow: hidden; }
        #map { height: 100%; width: 100%; }
        .location-dot { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -100%); z-index: 1000; color: var(--secondary); font-size: 35px; pointer-events: none; text-shadow: 0 4px 10px rgba(0,0,0,0.1); }

        .worker-list { margin-top: 20px; }
        .worker-card { 
            background: white; border-radius: 12px; padding: 15px; display: flex; align-items: center; gap: 15px; margin-bottom: 12px; border: 1px solid #EEE; cursor: pointer; transition: 0.2s;
        }
        .worker-card:hover { border-color: var(--primary); }
        .worker-card.selected { border-color: var(--primary); background: #F5F7FF; }
        .worker-card img { width: 45px; height: 45px; border-radius: 50%; background: #EEE; }

        .step-view { display: none; }
        .step-view.active { display: block; }
        
        .loading {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8);
            display: none; align-items: center; justify-content: center; z-index: 2000; flex-direction: column;
        }


        /* Search & Location UI */
        .search-container { position: relative; margin-bottom: 15px; }
        .search-input {
            width: 100%; padding: 14px 45px 14px 20px; border-radius: 14px; border: 1.5px solid #EEE;
            font-size: 14px; font-family: inherit; outline: none; transition: 0.2s;
        }
        .search-input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(79,70,229,0.05); }
        .search-btn {
            position: absolute; right: 15px; top: 50%; transform: translateY(-50%);
            background: none; border: none; color: var(--text-light); cursor: pointer; font-size: 16px;
        }
        .locate-btn {
            position: absolute; bottom: 85px; right: 20px; z-index: 1000;
            background: white; width: 44px; height: 44px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); cursor: pointer; border: none; color: var(--primary);
            transition: 0.2s;
        }
        .locate-btn:hover { background: #F8F9FA; transform: scale(1.05); }
    </style>
</head>
<body style="background: #FFF5F8;">

    <div id="loading" class="loading">
        <i class="fa-solid fa-circle-notch fa-spin" style="font-size: 40px; color: var(--primary);"></i>
        <p style="margin-top: 15px; font-weight: 600;" id="loadingText">Searching for nearby help...</p>
    </div>

    <div class="web-layout" style="max-width: 600px; margin: 0 auto; background: white; min-height: 100vh;">
        
        <!-- Step 1: Select Problem -->
        <div id="step1" class="step-view active">
            <header style="padding: 24px; display: flex; align-items: center; gap: 15px;">
                <a href="dashboard.php" style="color: black; font-size: 20px;"><i class="fa-solid fa-arrow-left"></i></a>
                <h2 style="font-size: 20px; font-weight: 800;">Common Problems</h2>
            </header>
            <div style="padding: 0 24px;">
                <p style="color: var(--text-light); margin-bottom: 25px;">Select the service you need help with right now.</p>
                <div class="category-grid">
                    <div class="category-card" id="card_puncture" onclick="selectCategory('puncture_expert', 'Puncture Repair', 'fa-tools', 150, this)">
                        <i class="fa-solid fa-tools" style="color: #FF9800;"></i>
                        <h4>Puncture</h4>
                        <p style="font-size: 11px; color: #AAA; margin-top: 5px;">Starting ₹150</p>
                    </div>
                    <div class="category-card" id="card_battery" onclick="selectCategory('battery_technician', 'Battery Jumpstart', 'fa-bolt', 400, this)">
                        <i class="fa-solid fa-bolt" style="color: #2196F3;"></i>
                        <h4>Battery</h4>
                        <p style="font-size: 11px; color: #AAA; margin-top: 5px;">Starting ₹400</p>
                    </div>
                    <div class="category-card" id="card_towing" onclick="selectCategory('towing_driver', 'Towing Service', 'fa-truck-pickup', 1200, this)">
                        <i class="fa-solid fa-truck-pickup" style="color: #F44336;"></i>
                        <h4>Towing</h4>
                        <p style="font-size: 11px; color: #AAA; margin-top: 5px;">Starting ₹1200</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Set Location & Find Worker -->
        <div id="step2" class="step-view">
            <header style="padding: 24px; display: flex; align-items: center; gap: 15px;">
                <a href="#" onclick="showStep(1)" style="color: black;"><i class="fa-solid fa-arrow-left"></i></a>
                <h2 style="font-size: 20px; font-weight: 800;" id="selectedServiceName">Set Location</h2>
            </header>
            <div style="padding: 0 24px;">
                <div class="search-container">
                    <input type="text" id="locationSearch" class="search-input" placeholder="Search for area, street or landmark..." onkeypress="if(event.key === 'Enter') searchLocation()">
                    <button class="search-btn" onclick="searchLocation()"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>

                <div class="map-view">
                    <div id="map"></div>
                    <div class="location-dot"><i class="fa-solid fa-location-dot"></i></div>
                    <button class="locate-btn" title="My Location" onclick="setCurrentLocation()"><i class="fa-solid fa-location-crosshairs"></i></button>
                </div>
                
                <div style="background: #F8F9FA; border-radius: 12px; padding: 15px; margin-bottom: 20px; font-size: 14px; display: flex; align-items: center; gap: 12px; border: 1px solid #F1F5F9;">
                    <i class="fa-solid fa-location-arrow" style="color: var(--primary);"></i>
                    <span id="currentAddr" style="font-weight: 500;">Detecting location...</span>
                </div>
                <button class="btn btn-primary" style="width: 100%; justify-content: center; padding: 16px; font-size: 16px; border-radius: 14px; box-shadow: 0 10px 20px rgba(79,70,229,0.15);" onclick="findWorkers()">Find Nearby Assistance</button>
            </div>
        </div>

        <!-- Step 3: Select Worker & Book -->
        <div id="step3" class="step-view">
            <header style="padding: 24px; display: flex; align-items: center; gap: 15px;">
                <a href="#" onclick="showStep(2)" style="color: black;"><i class="fa-solid fa-arrow-left"></i></a>
                <h2 style="font-size: 20px; font-weight: 800;">Available Experts</h2>
            </header>
            <div style="padding: 0 24px;">
                <p style="font-size: 14px; color: var(--text-light); margin-bottom: 20px;">We found the following experts near you.</p>
                <div id="workerList" class="worker-list">
                    <!-- Workers inyected here -->
                </div>
            </div>
        </div>

        <!-- Step 4: Confirm Booking -->
        <div id="step4" class="step-view">
            <header style="padding: 24px; display: flex; align-items: center; gap: 15px;">
                <a href="#" onclick="showStep(3)" style="color: black;"><i class="fa-solid fa-arrow-left"></i></a>
                <h2 style="font-size: 20px; font-weight: 800;">Confirm Details</h2>
            </header>
            <div style="padding: 0 24px;">
                <div style="background: white; border-radius: 20px; padding: 25px; border: 1.5px solid #F1F5F9; margin-bottom: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.02);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                        <span style="color: #64748B;">Service</span>
                        <strong id="confService" style="color: var(--primary);">Puncture</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                        <span style="color: #64748B;">Expert</span>
                        <strong id="confWorker">John Doe</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                        <span style="color: #64748B;">Estimated Cost</span>
                        <strong id="confPrice" style="color: var(--success); font-size: 18px;">₹150</strong>
                    </div>
                    <div style="padding-top: 15px; border-top: 1px dashed #E2E8F0; margin-top: 5px;">
                        <span style="display: block; color: #64748B; font-size: 12px; margin-bottom: 5px;">LOCATION</span>
                        <p id="confAddr" style="font-size: 13px; font-weight: 500; line-height: 1.5; color: #334155;"></p>
                    </div>
                </div>
                
                <div style="background: #FFFBEB; border: 1px solid #FEF3C7; border-radius: 12px; padding: 15px; margin-bottom: 30px; display: flex; gap: 12px;">
                    <i class="fa-solid fa-circle-info" style="color: #D97706; margin-top: 3px;"></i>
                    <p style="font-size: 12px; color: #92400E; line-height: 1.4;">The worker will verify the issue on arrival. Payment can be made directly after service completion.</p>
                </div>
                <button class="btn btn-primary" style="width: 100%; justify-content: center; padding: 18px; font-size: 16px; border-radius: 14px;" onclick="confirmBooking()">Complete Booking Now</button>
            </div>
        </div>
    </div>


    <script>

        let selection = {
            type: '',
            name: '',
            price: 0,
            lat: 13.0827,
            lng: 80.2707,
            addr: '',
            worker_id: 0
        };

        let map, marker;
        const customer_id = <?php echo $user['id']; ?>;

        function selectCategory(type, name, icon, price, element) {
            document.querySelectorAll('.category-card').forEach(c => c.classList.remove('selected'));
            element.classList.add('selected');
            
            selection.type = type;
            selection.name = name;
            selection.price = price;
            document.getElementById('selectedServiceName').innerText = name;
            document.getElementById('confService').innerText = name;
            document.getElementById('confPrice').innerText = '₹' + price;
            
            showStep(2);
            setTimeout(initMap, 200);
        }

        function initMap() {
            if (map) { map.invalidateSize(); return; }
            map = L.map('map').setView([selection.lat, selection.lng], 15);
            L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: '&copy; Google Maps'
            }).addTo(map);
            
            map.on('move', () => {
                const center = map.getCenter();
                selection.lat = center.lat;
                selection.lng = center.lng;
                updateAddress(center.lat, center.lng);
            });

            map.on('click', (e) => {
                map.panTo(e.latlng);
            });
            // Try to get current location automatically
            setCurrentLocation(true);
            updateAddress(selection.lat, selection.lng);
        }

        async function updateAddress(lat, lng) {
            try {
                const res = await fetch(apiUrl(`road_backend/location_proxy.php?lat=${lat}&lng=${lng}`));
                const data = await res.json();
                selection.addr = data.display_name || "Unknown Location";
                // Shorten address for display
                const parts = selection.addr.split(',').filter(p => p.trim());
                document.getElementById('currentAddr').innerText = parts.slice(0, 3).join(', ');
            } catch(e) {}
        }

        async function searchLocation() {
            const query = document.getElementById('locationSearch').value;
            if (!query) return;
            showLoading("Searching location...");
            try {
                const res = await fetch(apiUrl(`road_backend/location_proxy.php?query=${encodeURIComponent(query)}`));
                const data = await res.json();
                hideLoading();
                if (data && data.length > 0) {
                    const result = data[0];
                    selection.lat = parseFloat(result.lat);
                    selection.lng = parseFloat(result.lon);
                    map.setView([selection.lat, selection.lng], 15);
                    updateAddress(selection.lat, selection.lng);
                } else {
                    alert("Location not found. Please try a different name.");
                }
            } catch (e) {
                hideLoading();
                console.error("Search error:", e);
                alert("Search failed. Check your connection.");
            }
        }

        function setCurrentLocation(silent = false) {
            if (!navigator.geolocation) {
                if(!silent) alert("Geolocation is not supported by your browser.");
                return;
            }
            if(!silent) showLoading("Detecting your location...");
            navigator.geolocation.getCurrentPosition((pos) => {
                if(!silent) hideLoading();
                selection.lat = pos.coords.latitude;
                selection.lng = pos.coords.longitude;
                if(map) {
                    map.setView([selection.lat, selection.lng], 15);
                }
                updateAddress(selection.lat, selection.lng);
            }, (err) => {
                if(!silent) {
                    hideLoading();
                    alert("Unable to retrieve your location. Please search manually.");
                }
            });
        }

        async function findWorkers() {
            showLoading("Searching for nearby " + selection.name + "...");
            try {
                const res = await fetch(apiUrl(`road_backend/customer/get_nearby_workers.php?latitude=${selection.lat}&longitude=${selection.lng}&worker_type=${selection.type}&radius=15`));
                const result = await res.json();
                hideLoading();
                
                if(result.success && result.data.workers.length > 0) {
                    renderWorkers(result.data.workers);
                    showStep(3);
                } else {
                    alert("No " + selection.name + " providers found in your 15km radius. Please try a different location.");
                }
            } catch(e) {
                hideLoading();
                alert("Error connecting to server.");
            }
        }

        function renderWorkers(workers) {
            const list = document.getElementById('workerList');
            list.innerHTML = '';
            workers.forEach(w => {
                const card = document.createElement('div');
                card.className = 'worker-card';
                card.onclick = () => {
                    document.querySelectorAll('.worker-card').forEach(c => c.classList.remove('selected'));
                    card.classList.add('selected');
                    selection.worker_id = w.id;
                    document.getElementById('confWorker').innerText = w.name;
                    document.getElementById('confAddr').innerText = selection.addr;
                    showStep(4);
                };
                card.innerHTML = `
                    <div style="width: 45px; height: 45px; border-radius: 50%; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700;">${w.name[0]}</div>
                    <div style="flex: 1;">
                        <h4 style="font-size: 15px;">${w.name}</h4>
                        <p style="font-size: 12px; color: var(--text-light);">${Math.round(w.distance * 10)/10} km away</p>
                    </div>
                    <div style="text-align: right;">
                        <div style="color: var(--success); font-weight: 700;">₹${selection.price}</div>
                        <div style="font-size: 10px; color: #AAA;">Estimated</div>
                    </div>
                `;
                list.appendChild(card);
            });
        }

        async function confirmBooking() {
            showLoading("Booking expert...");
            const formData = new FormData();
            formData.append('customer_id', customer_id);
            formData.append('worker_id', selection.worker_id);
            formData.append('problem_type', selection.name);
            formData.append('vehicle_type', 'Any');
            formData.append('amount', selection.price);
            formData.append('customer_lat', selection.lat);
            formData.append('customer_lng', selection.lng);

            try {
                const res = await fetch(apiUrl('road_backend/customer/book_common_problem.php'), {
                    method: 'POST', body: formData
                });
                const result = await res.json();
                hideLoading();
                if(result.success) {
                    alert("Booking successful! Moving to tracking.");
                    window.location.href = "book_mechanic.php?booking_id=" + result.data.booking_id;
                } else {
                    alert("Booking failed: " + result.message);
                }
            } catch(e) {
                hideLoading();
                alert("Error booking.");
            }
        }

        function showStep(s) {
            document.querySelectorAll('.step-view').forEach(v => v.classList.remove('active'));
            document.getElementById('step' + s).classList.add('active');
        }

        function showLoading(t) {
            document.getElementById('loadingText').innerText = t;
            document.getElementById('loading').style.display = 'flex';
        }
        function hideLoading() {
            document.getElementById('loading').style.display = 'none';
        }
    </script>
</body>
</html>
