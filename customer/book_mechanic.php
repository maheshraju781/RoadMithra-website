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
    <title>Book Mechanic - Road Mithra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/web.css">
    <script src="../assets/js/config.js"></script>
    <!-- Leaflet Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        .map-container {
            height: 400px; width: 100%; border-radius: 20px;
            overflow: hidden; margin-bottom: 20px; border: 1px solid #EEE;
            position: relative;
        }
        #map { height: 100%; width: 100%; }
        .location-overlay {
            position: absolute; top: 50%; left: 50%; 
            transform: translate(-50%, -100%); z-index: 1000;
            pointer-events: none; color: #E11D48; font-size: 45px;
            filter: drop-shadow(0 10px 10px rgba(0,0,0,0.2));
            transition: 0.2s;
            animation: bounce 1s infinite alternate;
        }
        @keyframes bounce {
            from { transform: translate(-50%, -100%); }
            to { transform: translate(-50%, -110%) scale(1.05); }
        }
        .step-view { display: none; }
        .step-view.active { display: block; }

        /* Vehicle Modal Style */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 100;
        }
        .modal-content {
            background: white; padding: 24px; border-radius: 16px; width: 85%; max-width: 400px;
        }
        .modal-content h3 { margin-bottom: 20px; font-size: 18px; }
        .vehicle-option {
            padding: 16px; font-size: 16px; cursor: pointer; border-radius: 8px; transition: 0.2s;
        }
        .vehicle-option:hover { background: #F5F5F5; }

        /* Problem List Styling */
        .search-bar {
            background: #F1F3F4; border-radius: 12px; padding: 12px 16px; display: flex; align-items: center; gap: 10px; margin-bottom: 20px;
        }
        .search-bar input { border: none; background: transparent; outline: none; width: 100%; }

        .image-upload-box {
            text-align: center; padding: 40px 20px;
        }
        .upload-icon {
            font-size: 60px; color: var(--secondary-color); margin-bottom: 20px;
        }
        .upload-options {
            display: flex; justify-content: center; gap: 40px; margin-top: 30px;
        }
        .upload-opt { text-align: center; cursor: pointer; }
        .upload-opt .circle {
            width: 70px; height: 70px; border: 1px solid #DDD; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; color: var(--secondary-color); margin-bottom: 10px; transition: 0.3s;
        }
        .upload-opt:hover .circle { background: #F5F5F5; transform: scale(1.05); }

        .preview-grid {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 20px;
        }
        .preview-item { width: 100%; aspect-ratio: 1; border-radius: 8px; overflow: hidden; border: 1px solid #EEE; position: relative; }
        .preview-item img { width: 100%; height: 100%; object-fit: cover; }

        /* Tracking Screen Elements */
        .progress-bar {
            display: flex; justify-content: space-between; position: relative; margin: 20px 0;
        }
        .progress-bar::before {
            content: ''; position: absolute; top: 12px; left: 0; width: 100%; height: 2px; background: #EEE; z-index: -1;
        }
        .progress-step {
            text-align: center; font-size: 11px; color: var(--text-light);
        }
        .progress-step .dot {
            width: 24px; height: 24px; background: #EEE; border-radius: 50%; margin: 0 auto 8px auto; border: 4px solid white;
        }
        .progress-step.active { color: var(--primary-color); font-weight: 600; }
        .progress-step.active .dot { background: var(--primary-color); }

        .tracker-card {
            background: white; border-top-left-radius: 30px; border-top-right-radius: 30px; padding: 24px; box-shadow: 0 -10px 20px rgba(0,0,0,0.05);
            position: fixed; bottom: 0; width: 100%; left: 0; z-index: 10;
        }
        .timer-box { background: #E3F2FD; padding: 8px 12px; border-radius: 20px; color: var(--secondary-color); font-weight: 700; display: flex; align-items: center; gap: 8px; }
        
        .action-btns { display: flex; gap: 12px; margin-top: 20px; }
        .btn-outline {
            flex: 1; padding: 14px; border: 1.5px solid var(--primary-color); color: var(--primary-color); border-radius: 12px; font-weight: 600; background: white;
        }

        .loading-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8);
            display: none; align-items: center; justify-content: center; z-index: 2000; flex-direction: column;
        }

        /* Improved Problem List Cards */
        .list-item-card {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 20px; background: white; border: 1.5px solid #F1F5F9;
            border-radius: 16px; margin-bottom: 15px; cursor: pointer;
            transition: 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .list-item-card:hover { border-color: var(--primary); transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.03); }
        .list-item-card.selected { border-color: var(--primary); background: #EEF2FF; }
        
        .problem-icon-box {
            width: 50px; height: 50px; background: #F8FAFC; border-radius: 14px;
            display: flex; align-items: center; justify-content: center; font-size: 20px; color: #475569;
            margin-right: 15px; transition: 0.2s;
        }
        .list-item-card.selected .problem-icon-box { background: var(--primary); color: white; }

        .problem-info h4 { font-size: 16px; font-weight: 700; color: #1E293B; margin-bottom: 4px; }
        .problem-info p { font-size: 13px; color: #64748B; font-weight: 500; }
        
        .radio-circle {
            width: 24px; height: 24px; border: 2px solid #E2E8F0; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; transition: 0.2s;
        }
        .list-item-card.selected .radio-circle { border-color: var(--primary); }
        .list-item-card.selected .radio-circle::after {
            content: ''; width: 12px; height: 12px; background: var(--primary); border-radius: 50%;
        }

        .footer-action-bar {
            position: fixed; bottom: 0; left: 0; width: 100%; background: white;
            padding: 20px 24px; border-top: 1px solid #F1F5F9; z-index: 100;
            display: flex; justify-content: center; box-shadow: 0 -10px 30px rgba(0,0,0,0.02);
        }
        .primary-btn {
            width: 100%; max-width: 500px; padding: 16px; border-radius: 14px; background: var(--primary);
            color: white; border: none; font-size: 16px; font-weight: 700; cursor: pointer;
            transition: 0.2s; display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .primary-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(79,70,229,0.3); }
        .primary-btn:disabled { background: #E2E8F0; color: #94A3B8; cursor: not-allowed; }

        .search-bar {
            background: #F8FAFC; border: 1.5px solid #F1F5F9; border-radius: 14px; padding: 12px 18px; 
            display: flex; align-items: center; gap: 12px; margin-bottom: 25px; transition: 0.2s;
        }
        .search-bar:focus-within { border-color: var(--primary); background: white; box-shadow: 0 0 0 4px rgba(79,70,229,0.05); }
        .search-bar input { border: none; background: transparent; outline: none; width: 100%; font-size: 15px; font-weight: 500; }
    </style>
</head>
<body style="background: #FFF5F8;">

    <div id="loading" class="loading-overlay">
        <i class="fa-solid fa-spinner fa-spin" style="font-size: 40px; color: var(--primary-color);"></i>
        <p style="margin-top: 15px; font-weight: 600;" id="loadingText">Processing...</p>
    </div>

    <!-- 1. VEHICLE SELECTION MODAL (Step 1) -->
    <div id="vehicleStep" class="modal-overlay">
        <div class="modal-content">
            <h3>Select Vehicle Type</h3>
            <div class="vehicle-option" onclick="setVehicle('2_wheeler')">2 Wheeler</div>
            <div class="vehicle-option" onclick="setVehicle('4_wheeler')">4 Wheeler</div>
            <div style="text-align: right; margin-top: 20px;">
                <a href="dashboard.php" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">Cancel</a>
            </div>
        </div>
    </div>

    <!-- 2. LOCATION SELECTION (Step 2) -->
    <div id="locationStep" class="step-view">
        <div style="padding: 24px; display: flex; align-items: center; gap: 15px;">
            <a href="#" onclick="prevStep('vehicleStep')" style="color: black; font-size: 20px;"><i class="fa-solid fa-arrow-left"></i></a>
            <h2 style="font-size: 20px; font-weight: 800;">Set Pickup Location</h2>
        </div>
        
        <div style="padding: 0 24px;">
            <div style="position: relative; margin-bottom: 12px;">
                <div style="background: white; border: 1px solid #EEE; border-radius: 12px; padding: 12px 18px; display: flex; align-items: center; gap: 10px; box-shadow: var(--shadow-soft);">
                    <i class="fa-solid fa-magnifying-glass" style="color: #CCC;"></i>
                    <input type="text" id="locSearchInput" placeholder="Search area, colony or street..." style="border:none; outline:none; width:100%; font-size:14px;" onkeypress="if(event.key === 'Enter') searchLocation()">
                    <button onclick="searchLocation()" style="background:none; border:none; color:var(--primary-color); cursor:pointer;"><i class="fa-solid fa-location-arrow"></i></button>
                </div>
            </div>

            <div class="map-container">
                <div id="map"></div>
                <div class="location-overlay"><i class="fa-solid fa-location-dot"></i></div>
                <button class="locate-btn" title="My Location" onclick="setCurrentLocation()" style="position: absolute; bottom: 20px; right: 20px; z-index: 1000; background: white; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1); cursor: pointer; border: none; color: var(--primary-color); transition: 0.2s;">
                    <i class="fa-solid fa-location-crosshairs"></i>
                </button>
            </div>

            <div style="background: white; border: 1.5px solid #EEE; border-radius: 16px; padding: 20px; display: flex; gap: 15px; align-items: center; margin-bottom: 30px;">
                <div style="width: 40px; height: 40px; background: #FFF1F2; color: #E11D48; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="fa-solid fa-location-crosshairs"></i>
                </div>
                <div>
                    <p style="font-size: 11px; text-transform: uppercase; color: #AAA; font-weight: 700; margin-bottom: 4px;">Current Address</p>
                    <span id="displayLocation" style="font-size: 14px; font-weight: 600;">Detecting location...</span>
                </div>
            </div>
            <button class="btn btn-primary" style="width: 100%; justify-content: center; padding: 18px;" onclick="nextStep('shopStep')">Confirm Location & Continue</button>
        </div>
    </div>

    <!-- 2.5 SELECT SHOP (Step 2.5) -->
    <div id="shopStep" class="step-view">
        <header style="padding: 24px; display: flex; align-items: center; gap: 15px;">
            <a href="#" onclick="prevStep('locationStep')" style="color: black; font-size: 20px;"><i class="fa-solid fa-arrow-left"></i></a>
            <h2 style="font-size: 20px; font-weight: 800;">Choose Mechanic</h2>
        </header>
        
        <div style="padding: 0 24px; padding-bottom: 120px; max-width: 600px; margin: 0 auto;">
            <p style="color: #64748B; margin-bottom: 20px; font-size: 14px;">Select the best mechanic shop near your current location.</p>
            <div id="shopList">
                <!-- Shops injected by JS -->
            </div>
        </div>

        <div class="footer-action-bar">
            <button id="nextToProblem" class="primary-btn" disabled onclick="nextStep('problemStep')">
                Continue to Problems <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
    </div>

    <!-- 3. DESCRIBE PROBLEM (Step 3) -->
    <div id="problemStep" class="step-view">
        <header style="padding: 24px; display: flex; align-items: center; gap: 15px;">
            <a href="#" onclick="prevStep('shopStep')" style="color: black; font-size: 20px;"><i class="fa-solid fa-arrow-left"></i></a>
            <h2 style="font-size: 20px; font-weight: 800;">Describe Problem</h2>
        </header>
        
        <div style="padding: 0 24px; padding-bottom: 120px; max-width: 600px; margin: 0 auto;">
            <p style="color: #64748B; margin-bottom: 20px; font-size: 14px;">Select the issue you're facing. This helps the mechanic prepare the right tools.</p>
            
            <div class="search-bar">
                <i class="fa-solid fa-magnifying-glass" style="color: #94A3B8;"></i>
                <input type="text" placeholder="Search common problems (e.g. Brake, Oil...)" onkeyup="filterProblems(this.value)">
            </div>

            <div id="problemsList">
                <!-- Problems will be injected by JS -->
            </div>
        </div>

        <div class="footer-action-bar">
            <button id="nextToPayment" class="primary-btn" disabled onclick="nextStep('paymentStep')">
                Continue <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
    </div>

    <!-- 4. PAYMENT METHOD (Step 4) -->
    <div id="paymentStep" class="step-view">
        <header style="padding: 24px; display: flex; align-items: center; gap: 15px;">
            <a href="#" onclick="prevStep('problemStep')" style="color: black; font-size: 20px;"><i class="fa-solid fa-arrow-left"></i></a>
            <h2 style="font-size: 20px; font-weight: 800;">Payment Method</h2>
        </header>
        
        <div style="padding: 0 24px; padding-bottom: 120px; max-width: 600px; margin: 0 auto;">
            <div style="background: white; padding: 20px; border-radius: 16px; margin-bottom: 30px; border: 1.5px solid #F1F5F9; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                <p style="color: #64748B; font-size: 11px; text-transform: uppercase; font-weight: 700; margin-bottom: 8px;">Booking Summary</p>
                <h4 id="summaryProblemName" style="font-size: 18px; font-weight: 700; margin-bottom: 4px;">Problem Name</h4>
                <p id="summaryProblemPrice" style="color: var(--secondary); font-weight: 700; font-size: 15px;">Approx. ₹00 - ₹00</p>
            </div>

            <h3 style="font-size: 16px; font-weight: 800; margin-bottom: 16px; color: #1E293B;">Select Payment Method</h3>
            <div class="list-item-card" onclick="selectPayment('cod', this)">
                <div style="display: flex; align-items: center;">
                    <div class="problem-icon-box"><i class="fa-solid fa-wallet"></i></div>
                    <div class="problem-info">
                        <h4>Cash on Delivery</h4>
                        <p>Pay after service at your location</p>
                    </div>
                </div>
                <div class="radio-circle"></div>
            </div>

            <div class="list-item-card" onclick="selectPayment('online', this)">
                <div style="display: flex; align-items: center;">
                    <div class="problem-icon-box" style="color: #3B82F6;"><i class="fa-solid fa-credit-card"></i></div>
                    <div class="problem-info">
                        <h4>Online Payment</h4>
                        <p>Secure payment via UPI or Cards</p>
                    </div>
                </div>
                <div class="radio-circle"></div>
            </div>
        </div>

        <div class="footer-action-bar">
            <button id="nextToUpload" class="primary-btn" disabled onclick="nextStep('uploadStep')">
                Continue to Photos <i class="fa-solid fa-arrow-right"></i>
            </button>
    </div>
    </div>
    <!-- 5. UPLOAD PHOTOS (Step 5) -->
    <div id="uploadStep" class="step-view">
        <header style="padding: 24px; display: flex; align-items: center; gap: 15px;">
            <a href="#" onclick="prevStep('paymentStep')" style="color: black; font-size: 20px;"><i class="fa-solid fa-arrow-left"></i></a>
            <h2 style="font-size: 20px; font-weight: 800;">Upload Photos</h2>
        </header>

        <div style="padding: 0 24px; padding-bottom: 120px; max-width: 600px; margin: 0 auto; text-align: center;">
            <div class="image-upload-box" style="background: white; border-radius: 20px; border: 1.5px dashed #E2E8F0; padding: 40px; margin-bottom: 25px;">
                <div style="font-size: 50px; color: #94A3B8; margin-bottom: 20px;"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                <h3 style="font-size: 18px; font-weight: 800; color: #1E293B; margin-bottom: 8px;">Add visual details</h3>
                <p style="color: #64748B; font-size: 14px; margin-bottom: 30px;">Photos help the mechanic understand the issue and bring the right parts.</p>

                <div class="upload-options">
                    <label class="primary-btn" style="max-width: 250px; cursor: pointer;">
                        <input type="file" id="mediaInput" multiple accept="image/*" style="display: none;" onchange="handleFileUpload(event)">
                        <i class="fa-solid fa-images"></i> Select from Gallery
                    </label>
                </div>

                <div id="imagePreview" class="preview-grid" style="margin-top: 30px;"></div>
            </div>
        </div>

        <div class="footer-action-bar" style="gap: 15px;">
            <button class="primary-btn" style="background: #F8FAFC; color: #64748B; border: 1.5px solid #E2E8F0;" onclick="nextStep('confirmStep')">Skip for Now</button>
            <button class="primary-btn" onclick="nextStep('confirmStep')">Review Booking <i class="fa-solid fa-arrow-right"></i></button>
        </div>
    </div>

    <!-- 6. CONFIRM BOOKING (Step 6) -->
    <div id="confirmStep" class="step-view">
        <header style="padding: 24px; display: flex; align-items: center; gap: 15px;">
            <a href="#" onclick="prevStep('uploadStep')" style="color: black; font-size: 20px;"><i class="fa-solid fa-arrow-left"></i></a>
            <h2 style="font-size: 20px; font-weight: 800;">Review & Confirm</h2>
        </header>
        
        <div style="padding: 0 24px; padding-bottom: 140px; max-width: 600px; margin: 0 auto;">
            <div style="background: white; border-radius: 20px; padding: 25px; border: 1.5px solid #F1F5F9; box-shadow: 0 10px 30px rgba(0,0,0,0.02); margin-bottom: 20px;">
                <h4 style="font-size: 17px; font-weight: 800; color: #1E293B; margin-bottom: 18px; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-circle-check" style="color: var(--success);"></i> Booking Details
                </h4>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 14px; border-bottom: 1px solid #F8FAFC; padding-bottom: 12px;">
                    <span style="color: #64748B; font-weight: 600;">Shop/Mechanic</span>
                    <strong id="confirmShopName" style="color: #1E293B;">Select a Shop</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 14px; border-bottom: 1px solid #F8FAFC; padding-bottom: 12px;">
                    <span style="color: #64748B; font-weight: 600;">Vehicle Type</span>
                    <strong id="confirmVehicle" style="color: #1E293B;">2 Wheeler</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 14px; border-bottom: 1px solid #F8FAFC; padding-bottom: 12px;">
                    <span style="color: #64748B; font-weight: 600;">Service Required</span>
                    <strong id="confirmProblem" style="color: #1E293B;">Engine Starting</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 14px; border-bottom: 1px solid #F8FAFC; padding-bottom: 12px;">
                    <span style="color: #64748B; font-weight: 600;">Estimated Cost</span>
                    <strong id="confirmCost" style="color: var(--secondary); font-size: 16px;">₹00</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 14px;">
                    <span style="color: #64748B; font-weight: 600;">Payment Mode</span>
                    <strong id="confirmPayment" style="color: #1E293B;">CASH ON DELIVERY</strong>
                </div>
            </div>

            <div style="background: #F8FAFC; border-radius: 20px; padding: 20px; border: 1.5px solid #F1F5F9;">
                <h4 style="font-size: 14px; font-weight: 800; color: #1E293B; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Pickup Location</h4>
                <p id="confirmLocation" style="font-size: 14px; color: #64748B; line-height: 1.5;"></p>
            </div>

            <div style="margin-top: 25px; display: flex; gap: 12px; background: #FFFBEB; padding: 15px; border-radius: 12px; border: 1px solid #FEF3C7;">
                <i class="fa-solid fa-triangle-exclamation" style="color: #D97706; font-size: 18px;"></i>
                <p style="font-size: 12px; color: #92400E; line-height: 1.4;">The mechanic will confirm the final price after on-site inspection of your vehicle.</p>
            </div>
        </div>

        <div class="footer-action-bar">
            <button class="primary-btn" style="background: linear-gradient(135deg, var(--primary), var(--secondary));" onclick="submitBooking()">
                Confirm & Book Mechanic Now
            </button>
        </div>
    </div>

    <!-- 7. TRACKING (Step 7) -->
    <div id="trackingStep" class="step-view">
        <div style="padding: 16px; display: flex; justify-content: space-between; align-items: center; background: white;">
            <a href="dashboard.php" style="color: black;"><i class="fa-solid fa-arrow-left"></i></a>
            <h2 style="font-size: 18px;">Mechanic Tracking</h2>
            <a href="dashboard.php" style="color: black; font-weight: 600; text-decoration: none;">Home</a>
        </div>
        <div class="map-view" id="trackingMap" style="height: calc(100vh - 350px); width: 100%;">
        </div>

        <div class="tracker-card">
            <div style="width: 40px; height: 4px; background: #EEE; border-radius: 2px; margin: 0 auto 15px auto;"></div>
            
            <div class="progress-bar">
                <div class="progress-step active"><div class="dot"></div>Accepted</div>
                <div class="progress-step"><div class="dot"></div>On the Way</div>
                <div class="progress-step"><div class="dot"></div>Arrived</div>
                <div class="progress-step"><div class="dot"></div>Working</div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div class="user-profile-circle" style="background: #F3E5F5; color: var(--primary-color); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div>
                        <h4 style="font-size: 16px;" id="trackerStatus">Searching for mechanic...</h4>
                        <p style="font-size: 12px; color: #AAA;">Booking ID: <span id="bookedId">#---</span></p>
                    </div>
                </div>
                <div class="timer-box">
                    <i class="fa-regular fa-clock"></i> <span id="timer">15:00</span>
                </div>
            </div>

            <div class="action-btns">
                <button id="trackMsgBtn" class="btn-outline" onclick="alert('Waiting for mechanic assignment...')">Message</button>
                <button id="trackCallBtn" class="primary-btn" style="flex: 1.5;" onclick="alert('Waiting for mechanic assignment...')">Call Now</button>
            </div>
        </div>
    </div>

    <!-- JavaScript Logic -->
    <script>
        const userData = {
            id: <?php echo $user['id']; ?>,
            name: "<?php echo addslashes($user['name']); ?>",
            phone: "<?php echo $user['phone']; ?>"
        };

        let bookingData = {
            customer_id: userData.id,
            shop_id: 0,
            vehicle_type: '2_wheeler',
            problem_type: '',
            customer_lat: 13.0827,
            customer_lng: 80.2707,
            customer_address: 'Chennai',
            customer_name: userData.name,
            customer_phone: userData.phone,
            payment_method: 'cod',
            photos: []
        };

        let map, marker, trackMap;
        
        function initMap() {
            if (map) {
                map.invalidateSize();
                return;
            }
            map = L.map('map').setView([bookingData.customer_lat, bookingData.customer_lng], 15);
            L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: '&copy; Google Maps'
            }).addTo(map);

            map.on('move', function() {
                const center = map.getCenter();
                bookingData.customer_lat = center.lat;
                bookingData.customer_lng = center.lng;
                updateAddress(center.lat, center.lng);
            });

            map.on('click', function(e) {
                map.panTo(e.latlng);
            });
            
            // Try to get current location automatically
            setCurrentLocation(true);
            
            // Initial address update
            updateAddress(bookingData.customer_lat, bookingData.customer_lng);
        }

        async function searchLocation() {
            const query = document.getElementById('locSearchInput').value;
            if(!query) return;
            
            showLoading("Searching location...");
            try {
                const res = await fetch(apiUrl(`road_backend/location_proxy.php?query=${encodeURIComponent(query)}`));
                const data = await res.json();
                hideLoading();
                
                if (data && data.length > 0) {
                    const result = data[0];
                    bookingData.customer_lat = parseFloat(result.lat);
                    bookingData.customer_lng = parseFloat(result.lon);
                    
                    if (map) {
                        map.setView([bookingData.customer_lat, bookingData.customer_lng], 16);
                    }
                    updateAddress(bookingData.customer_lat, bookingData.customer_lng);
                } else {
                    alert("Location not found. Please try a more specific area name.");
                }
            } catch(e) {
                hideLoading();
                console.error("Search error:", e);
                alert("Search failed. Please check your internet connection.");
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
                bookingData.customer_lat = pos.coords.latitude;
                bookingData.customer_lng = pos.coords.longitude;
                if(map) {
                    map.setView([bookingData.customer_lat, bookingData.customer_lng], 15);
                }
                updateAddress(bookingData.customer_lat, bookingData.customer_lng);
            }, (err) => {
                if(!silent) {
                    hideLoading();
                    alert("Unable to retrieve your location. Please move the map manually.");
                }
            });
        }

        async function updateAddress(lat, lng) {
            try {
                const res = await fetch(apiUrl(`road_backend/location_proxy.php?lat=${lat}&lng=${lng}`));
                const data = await res.json();
                const addr = data.display_name || "Custom Location";
                bookingData.customer_address = addr;
                document.getElementById('displayLocation').innerText = addr.split(',').slice(0, 3).join(',');
                document.getElementById('confirmLocation').innerText = addr;
            } catch(e) {
                console.error("Geocoding failed", e);
            }
        }

        const problems = [
            { name: 'Engine Starting Problem', price: '₹800 - ₹1200', icon: 'fa-motorcycle' },
            { name: 'Engine Major Repair', price: '₹2500 - ₹6000', icon: 'fa-triangle-exclamation' },
            { name: 'Brake Failure', price: '₹300 - ₹600', icon: 'fa-circle-stop' },
            { name: 'Brake Pad Replacement', price: '₹500 - ₹900', icon: 'fa-screwdriver-wrench' },
            { name: 'Clutch Wire Replace', price: '₹250 - ₹400', icon: 'fa-link' },
            { name: 'Clutch Plate Issue', price: '₹1200 - ₹2000', icon: 'fa-gears' },
            { name: 'Puncture Help', price: '₹100 - ₹300', icon: 'fa-tools' },
            { name: 'Oil Leakage', price: '₹400 - ₹800', icon: 'fa-oil-can' }
        ];

        function setVehicle(type) {
            bookingData.vehicle_type = type;
            document.getElementById('confirmVehicle').innerText = type.replace('_',' ');
            nextStep('locationStep');
        }

        function filterProblems(val) {
            renderProblems(val);
        }

        function renderProblems(filter = '') {
            const list = document.getElementById('problemsList');
            list.innerHTML = '';
            problems.filter(p => p.name.toLowerCase().includes(filter.toLowerCase())).forEach(p => {
                const card = document.createElement('div');
                card.className = 'list-item-card';
                if(bookingData.problem_type === p.name) card.classList.add('selected');
                card.onclick = () => selectProblem(p, card);
                card.innerHTML = `
                    <div style="display: flex; align-items: center;">
                        <div class="problem-icon-box">
                            <i class="fa-solid ${p.icon}"></i>
                        </div>
                        <div class="problem-info">
                            <h4>${p.name}</h4>
                            <p>Approx. ${p.price}</p>
                        </div>
                    </div>
                    <div class="radio-circle"></div>
                `;
                list.appendChild(card);
            });
        }

        function selectProblem(p, element) {
            document.querySelectorAll('.list-item-card').forEach(c => c.classList.remove('selected'));
            element.classList.add('selected');
            bookingData.problem_type = p.name;
            document.getElementById('nextToPayment').disabled = false;
            
            document.getElementById('summaryProblemName').innerText = p.name;
            document.getElementById('summaryProblemPrice').innerText = 'Approx. ' + p.price;
            document.getElementById('confirmProblem').innerText = p.name;
            document.getElementById('confirmCost').innerText = p.price;
        }

        function selectPayment(method, element) {
            document.querySelectorAll('#paymentStep .list-item-card').forEach(c => c.classList.remove('selected'));
            element.classList.add('selected');
            bookingData.payment_method = method;
            document.getElementById('confirmPayment').innerText = method.toUpperCase();
            document.getElementById('nextToUpload').disabled = false;
        }

        async function handleFileUpload(event) {
            const files = event.target.files;
            if (files.length === 0) return;

            showLoading("Uploading photos...");
            const formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('media[]', files[i]);
            }

            try {
                const res = await fetch(apiUrl('road_backend/upload_media.php'), {
                    method: 'POST',
                    body: formData
                });
                const result = await res.json();
                hideLoading();

                if (result.success) {
                    bookingData.photos = result.data.urls;
                    renderPreviews();
                } else {
                    alert("Upload failed: " + result.message);
                }
            } catch (e) {
                hideLoading();
                alert("Upload error. Please try again.");
            }
        }

        function renderPreviews() {
            const grid = document.getElementById('imagePreview');
            grid.innerHTML = '';
            bookingData.photos.forEach(url => {
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.innerHTML = `<img src="${apiUrl('road_backend/' + url)}" alt="Preview">`;
                grid.appendChild(div);
            });
        }

        async function submitBooking() {
            showLoading("Creating your booking...");
            
            const formData = new FormData();
            for (let key in bookingData) {
                if (key === 'photos') {
                    formData.append(key, JSON.stringify(bookingData[key]));
                } else {
                    formData.append(key, bookingData[key]);
                }
            }

            try {
                const res = await fetch(apiUrl('road_backend/customer/book_mechanic_service.php'), {
                    method: 'POST',
                    body: formData
                });
                const result = await res.json();
                hideLoading();

                if (result.success) {
                    nextStep('trackingStep');
                    startTracking(result.data.booking_id);
                } else {
                    alert("Booking failed: " + result.message);
                }
            } catch (e) {
                hideLoading();
                alert("Connection error. Please check your internet.");
            }
        }

        function initTrackingMap() {
            if(trackMap) {
                trackMap.invalidateSize();
                return;
            }
            trackMap = L.map('trackingMap').setView([bookingData.customer_lat, bookingData.customer_lng], 15);
            L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: '&copy; Google Maps'
            }).addTo(trackMap);
            
            const markerHtml = `<div style="color: #E11D48; font-size: 32px;"><i class="fa-solid fa-location-dot"></i></div>`;
            const icon = L.divIcon({
                html: markerHtml,
                className: 'custom-div-icon',
                iconSize: [32, 32],
                iconAnchor: [16, 32]
            });
            
            L.marker([bookingData.customer_lat, bookingData.customer_lng], {icon: icon}).addTo(trackMap)
                .bindPopup('Your Location').openPopup();
            
            setTimeout(() => trackMap.invalidateSize(), 500);
        }

        let trackingInterval;
        let mechanicMarker;

        function startTracking(bookingId) {
            document.getElementById('bookedId').innerText = '#' + bookingId;
            initTrackingMap();
            pollBookingStatus(bookingId);
            if(trackingInterval) clearInterval(trackingInterval);
            trackingInterval = setInterval(() => pollBookingStatus(bookingId), 5000);
        }

        async function pollBookingStatus(id) {
            try {
                const res = await fetch(apiUrl(`road_backend/customer/track_mechanic.php?booking_id=${id}`));
                const result = await res.json();
                if(result.success) {
                    updateTrackingUI(result.data);
                }
            } catch(e) { console.error("Poll error", e); }
        }

        function updateTrackingUI(data) {
            const statusMap = {
                'pending': 'Searching for mechanic...',
                'accepted': 'Mechanic Accepted!',
                'on_the_way': 'Mechanic is on the way!',
                'arrived': 'Mechanic Arrived!',
                'working': 'Mechanic is working...',
                'completed': 'Task Completed!'
            };
            
            document.getElementById('trackerStatus').innerText = data.mechanic_name + ': ' + (statusMap[data.status] || data.status);
            
            if(data.mechanic_phone) {
                document.getElementById('trackCallBtn').onclick = () => window.location.href = 'tel:' + data.mechanic_phone;
                document.getElementById('trackMsgBtn').onclick = () => window.location.href = 'https://wa.me/91' + data.mechanic_phone;
                document.getElementById('trackCallBtn').disabled = false;
                document.getElementById('trackMsgBtn').disabled = false;
            }

            if(trackMap && data.mechanic_lat && data.mechanic_lng) {
                if(!mechanicMarker) {
                    const mechIcon = L.divIcon({
                        html: `<div style="color: #3B82F6; font-size: 32px;"><i class="fa-solid fa-motorcycle"></i></div>`,
                        className: 'mech-icon',
                        iconSize: [32, 32],
                        iconAnchor: [16, 32]
                    });
                    mechanicMarker = L.marker([data.mechanic_lat, data.mechanic_lng], {icon: mechIcon}).addTo(trackMap)
                        .bindPopup('Mechanic: ' + data.mechanic_name);
                } else {
                    mechanicMarker.setLatLng([data.mechanic_lat, data.mechanic_lng]);
                }
                
                const bounds = L.latLngBounds([data.customer_lat, data.customer_lng], [data.mechanic_lat, data.mechanic_lng]);
                trackMap.fitBounds(bounds, {padding: [50, 50], maxZoom: 16});
            }

            if(data.status === 'completed') clearInterval(trackingInterval);
        }

        function nextStep(stepId) {
            document.querySelectorAll('.step-view').forEach(v => v.classList.remove('active'));
            document.getElementById('vehicleStep').style.display = 'none';

            const target = document.getElementById(stepId);
            if(stepId === 'locationStep') {
                target.classList.add('active');
                setTimeout(initMap, 200);
            } else if(stepId === 'shopStep') {
                target.classList.add('active');
                fetchShops();
            } else if(target) {
                if(stepId === 'vehicleStep') target.style.display = 'flex';
                else target.classList.add('active');
            }

            if(stepId === 'problemStep') renderProblems();
        }

        async function fetchShops() {
            showLoading("Finding nearby mechanic shops...");
            try {
                const res = await fetch(apiUrl(`road_backend/customer/get_nearby_shops.php?latitude=${bookingData.customer_lat}&longitude=${bookingData.customer_lng}&shop_type=mechanic&radius=15`));
                const result = await res.json();
                hideLoading();
                if(result.success) {
                    renderShops(result.data.shops);
                } else {
                    alert("Unable to fetch shops: " + result.message);
                }
            } catch(e) {
                hideLoading();
                alert("Error connecting to location service.");
            }
        }

        function renderShops(shops) {
            const list = document.getElementById('shopList');
            list.innerHTML = '';
            if(!shops || shops.length === 0) {
                list.innerHTML = '<div style="text-align:center; padding:40px; color:#64748B;"><i class="fa-solid fa-store-slash" style="font-size:40px; margin-bottom:15px; display:block;"></i>No mechanic shops found in your 15km radius.</div>';
                return;
            }
            shops.forEach(s => {
                const card = document.createElement('div');
                card.className = 'list-item-card';
                if(bookingData.shop_id == s.id) card.classList.add('selected');
                card.onclick = () => selectShop(s, card);
                card.innerHTML = `
                    <div style="display: flex; align-items: center;">
                        <div class="problem-icon-box" style="background:#E0E7FF; color:var(--primary);"><i class="fa-solid fa-shop"></i></div>
                        <div class="problem-info">
                            <h4 style="display:flex; justify-content:space-between; align-items:center; width:100%;">
                                ${s.shop_name}
                                <span style="font-size:12px; color:#F59E0B; font-weight:700;"><i class="fa-solid fa-star"></i> ${parseFloat(s.avg_rating || 0).toFixed(1)} <span style="color:#64748B; font-weight:400;">(${s.total_reviews || 0})</span></span>
                            </h4>
                            <p>${s.address ? s.address.split(',').slice(0,2).join(',') : 'Nearby Area'} • ${s.distance} km away</p>
                        </div>
                    </div>
                    <div class="radio-circle"></div>
                `;
                list.appendChild(card);
            });
        }

        function selectShop(s, element) {
            document.querySelectorAll('#shopStep .list-item-card').forEach(c => c.classList.remove('selected'));
            element.classList.add('selected');
            bookingData.shop_id = s.id;
            document.getElementById('confirmShopName').innerText = s.shop_name;
            document.getElementById('nextToProblem').disabled = false;
        }

        function prevStep(stepId) {
            nextStep(stepId);
        }

        function showLoading(text) {
            document.getElementById('loadingText').innerText = text;
            document.getElementById('loading').style.display = 'flex';
        }
        function hideLoading() {
            document.getElementById('loading').style.display = 'none';
        }

        window.onload = () => {
            const urlParams = new URLSearchParams(window.location.search);
            const bookingId = urlParams.get('booking_id');
            if (bookingId) {
                document.getElementById('vehicleStep').style.display = 'none';
                nextStep('trackingStep');
                startTracking(bookingId);
            }
        };
    </script>
</body>
</html>
