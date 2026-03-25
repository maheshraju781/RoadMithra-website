<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Payment - Road Mithra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/web.css">
    <script src="../assets/js/config.js"></script>
    <style>
        .payment-card {
            max-width: 500px;
            margin: 50px auto;
            background: white;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
        }
        .payment-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 40px;
            color: white;
            text-align: center;
        }
        .payment-body {
            padding: 40px;
        }
        .upi-apps { 
            display: flex; 
            justify-content: center; 
            gap: 15px; 
            margin: 25px 0; 
        }
        .upi-app { 
            flex: 1;
            padding: 12px;
            background: #F9FAFB; 
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md); 
            display: flex; 
            flex-direction: column;
            align-items: center; 
            gap: 8px;
            font-size: 11px; 
            font-weight: 700; 
            color: var(--text-mid);
            cursor: pointer;
            transition: 0.2s;
        }
        .upi-app:hover {
            border-color: var(--primary);
            background: var(--primary-light);
            color: var(--primary);
        }
        .upi-app i { font-size: 20px; }
        
        .timer-display { 
            font-size: 32px; 
            font-weight: 800; 
            color: var(--text-dark); 
            margin: 20px 0; 
            font-variant-numeric: tabular-nums;
        }
        
        .loader-ring {
            display: inline-block;
            width: 80px;
            height: 80px;
            border: 6px solid var(--primary-light);
            border-radius: 50%;
            border-top: 6px solid var(--primary);
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body style="background:var(--bg);">

    <div class="payment-card">
        <div class="payment-header">
            <h2 style="font-size:24px; font-weight:800;">Secure Checkout</h2>
            <p style="opacity:0.9; font-size:14px; margin-top:5px;" id="paymentDesc">Processing your request...</p>
        </div>

        <div class="payment-body" id="paymentInput">
            <div style="text-align:center; margin-bottom:30px;">
                <span style="font-size:12px; font-weight:700; text-transform:uppercase; color:var(--text-light); letter-spacing:1px;">Amount to Pay</span>
                <div style="font-size:48px; font-weight:800; color:var(--text-dark);" id="displayAmount">₹0.00</div>
            </div>

            <div class="form-group">
                <label class="form-label">Select UPI App</label>
                <div class="upi-apps">
                    <div class="upi-app"><i class="fa-brands fa-google-pay"></i> GPay</div>
                    <div class="upi-app"><i class="fa-solid fa-mobile-screen"></i> PhonePe</div>
                    <div class="upi-app"><i class="fa-solid fa-wallet"></i> Paytm</div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Enter UPI ID</label>
                <input type="text" class="form-input" placeholder="e.g. mobile-number@upi" id="upiId" style="text-align:center; font-weight:600; font-size:18px;">
            </div>

            <button class="btn btn-primary" style="width: 100%; justify-content:center; padding:18px; font-size:16px;" onclick="startPayment()">
                Verify and Pay <i class="fa-solid fa-shield-check" style="margin-left:10px;"></i>
            </button>
            <p style="margin-top: 20px; font-size:12px; color:var(--text-light); text-align:center;">
                <i class="fa-solid fa-lock" style="margin-right:5px;"></i> SSL Encrypted Payment Gateway
            </p>
        </div>

        <!-- Processing View -->
        <div class="payment-body" id="paymentProcessing" style="display: none; text-align:center;">
            <div class="loader-ring"></div>
            <h3 style="font-size:20px; margin-bottom:10px;">Requesting Payment</h3>
            <p style="color: var(--text-light); font-size: 14px;">Please open your UPI app and approve the request within the time limit.</p>

            <div class="timer-display" id="timer">04:59</div>

            <div style="background:#F9FAFB; padding:20px; border-radius:var(--radius-md); margin-bottom:25px; text-align:left;">
                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                    <span style="font-size:13px; color:var(--text-light);">Reference ID:</span>
                    <span style="font-size:13px; font-weight:700;">RM_<?php echo time(); ?></span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span style="font-size:13px; color:var(--text-light);">Status:</span>
                    <span style="font-size:13px; font-weight:700; color:var(--accent);">Awaiting Approval</span>
                </div>
            </div>

            <button class="btn btn-outline" style="width: 100%; justify-content:center; border-color: var(--danger); color: var(--danger);" onclick="location.reload()">
                Cancel and Go Back
            </button>
        </div>
    </div>

    <script>
        let checkoutData = null;

        window.onload = () => {
            const dataStr = localStorage.getItem('road_mithra_checkout');
            if(!dataStr) {
                alert("Invalid session. Please try again.");
                window.location.href = 'buy_spares.php';
                return;
            }
            checkoutData = JSON.parse(dataStr);
            document.getElementById('displayAmount').innerText = '₹' + checkoutData.total;
            document.getElementById('paymentDesc').innerText = 'Payment for ' + checkoutData.items.length + ' item(s)';
        };

        function startPayment() {
            const id = document.getElementById('upiId').value;
            if(!id || !id.includes('@')) { 
                alert("Please enter a valid UPI ID (e.g. name@bank)"); 
                return; 
            }
            
            document.getElementById('paymentInput').style.display = 'none';
            document.getElementById('paymentProcessing').style.display = 'block';
            
            startCountdown(300); // 5 minutes
            
            // Simulate payment success after 5 seconds
            setTimeout(() => {
                completeOrder();
            }, 5000);
        }

        function startCountdown(duration) {
            let timer = duration, minutes, seconds;
            const timerEl = document.getElementById('timer');
            const interval = setInterval(function () {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                timerEl.textContent = minutes + ":" + seconds;

                if (--timer < 0) {
                    clearInterval(interval);
                    alert("Payment timeout. Please try again.");
                    location.reload();
                }
            }, 1000);
        }

        async function completeOrder() {
            // Map our local structure to the backend's expected structure
            const formData = new FormData();
            formData.append('customer_id', <?php echo $_SESSION['user']['id']; ?>);
            formData.append('customer_name', checkoutData.customer_name);
            formData.append('customer_phone', checkoutData.customer_phone);
            formData.append('customer_address', checkoutData.address);
            formData.append('customer_lat', checkoutData.lat);
            formData.append('customer_lng', checkoutData.lng);
            formData.append('total_amount', checkoutData.total);
            formData.append('payment_method', 'online_upi');
            
            const simplifiedItems = checkoutData.items.map(i => ({
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
                    localStorage.removeItem('road_mithra_checkout');
                    alert("Order placed successfully! Redirecting to tracking...");
                    window.location.href = `view_order.php?order_id=${result.data.order_id}`;
                } else {
                    alert("Order placement failed: " + result.message);
                }
            } catch(e) {
                console.error(e);
                alert("Critical error connecting to server.");
            }
        }
    </script>

</body>
</html>
