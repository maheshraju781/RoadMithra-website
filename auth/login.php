<?php
$role = $_GET['role'] ?? 'customer';
$labels = [
    'customer'       => ['title' => 'Customer Login', 'sub' => 'Access your account or create a new one'],
    'mechanic_owner' => ['title' => 'Mechanic Shop Login', 'sub' => 'Manage your garage & service bookings'],
    'parts_owner'    => ['title' => 'Spare Parts Shop Login', 'sub' => 'Manage inventory, deliveries & orders'],
    'worker'         => ['title' => 'Delivery / Mechanic Worker', 'sub' => 'Login with your Worker ID and password'],
    'admin'          => ['title' => 'Admin Portal', 'sub' => 'System administration access only'],
];
$info = $labels[$role] ?? $labels['customer'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $info['title']; ?> - Road Mithra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/web.css">
    <link rel="icon" type="image/x-icon" href="../assets/images/logo.png">
    <style>
        body { background: var(--bg); display: flex; align-items: center; justify-content: center; min-height: 100vh; overflow-x: hidden; }
        .login-page { display: grid; grid-template-columns: 1fr 1fr; min-height: 100vh; width: 100%; }
        .login-panel { padding: 60px; display: flex; flex-direction: column; justify-content: center; background: white; max-width: 520px; }
        .login-graphic { background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; flex-direction: column; align-items: center; justify-content: center; color: white; padding: 60px; position: relative; overflow: hidden; }
        .login-graphic h2 { font-size: 44px; font-weight: 900; margin: 25px 0 15px; letter-spacing: -1px; }
        .login-graphic p { font-size: 17px; opacity: 0.85; text-align: center; line-height: 1.8; max-width: 450px; }
        .login-back { display: inline-flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 600; color: var(--text-light); margin-bottom: 40px; text-decoration: none; }
        .login-back:hover { color: var(--primary); }
        .login-heading h1 { font-size: 34px; font-weight: 900; margin-bottom: 10px; color: var(--text-dark); letter-spacing: -1px; }
        .login-heading p { color: var(--text-light); font-size: 16px; margin-bottom: 40px; }
        
        /* Modal Styles */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.6); backdrop-filter: blur(8px);
            z-index: 2000; display: none; align-items: center; justify-content: center;
            opacity: 0; transition: opacity 0.3s ease;
        }
        .modal-overlay.active { display: flex; opacity: 1; }
        .otp-modal {
            background: white; padding: 50px; border-radius: 35px; width: 90%; max-width: 450px;
            text-align: center; transform: translateY(30px); transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 40px 100px rgba(0,0,0,0.3);
        }
        .modal-overlay.active .otp-modal { transform: translateY(0); }
        .otp-icon-wrap {
            width: 85px; height: 85px; background: #EEF2FF; color: var(--primary);
            border-radius: 24px; display: flex; align-items: center; justify-content: center;
            font-size: 36px; margin: 0 auto 30px auto;
        }
        .otp-inputs-wrap { display: flex; gap: 12px; justify-content: center; margin: 35px 0; }
        .otp-box {
            width: 54px; height: 65px; border: 2px solid #E5E7EB; border-radius: 15px;
            text-align: center; font-size: 26px; font-weight: 800; color: var(--primary);
            transition: all 0.2s;
        }
        .otp-box:focus { border-color: var(--primary); outline: none; background: #F5F3FF; box-shadow: 0 0 0 4px rgba(79,70,229,0.1); }
        
        .resend-timer { font-size: 14px; color: var(--text-light); margin-top: 30px; }
        .resend-btn { color: var(--primary); font-weight: 700; cursor: pointer; text-decoration: none; }
        .resend-btn.disabled { opacity: 0.5; cursor: not-allowed; }

        @media(max-width:768px){.login-page{grid-template-columns:1fr;}.login-graphic{display:none;}.login-panel{max-width:100%;padding:40px 25px;}}
    </style>
</head>
<body>
<div class="login-page">
    <!-- Panel -->
    <div class="login-panel">
        <a href="../index.php" class="login-back"><i class="fa-solid fa-arrow-left"></i> Back to Home</a>

        <div class="login-heading">
            <h1><?php echo $info['title']; ?></h1>
            <p><?php echo $info['sub']; ?></p>
        </div>

        <form id="loginForm" action="../includes/process_login.php" method="POST">
            <input type="hidden" name="role" value="<?php echo $role; ?>">
            <input type="hidden" name="ajax" value="1">

            <?php if ($role === 'admin'): ?>
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-input" placeholder="admin" required>
            </div>
            <?php elseif ($role === 'worker'): ?>
            <div class="form-group">
                <label class="form-label">Worker ID or Phone Number</label>
                <input type="text" name="worker_id" class="form-input" placeholder="e.g. WKR1001" required>
            </div>
            <?php else: ?>
            <div class="form-group">
                <label class="form-label">Registered Mobile Number</label>
                <input type="tel" name="phone" id="phoneInput" class="form-input" placeholder="10 digit mobile number" maxlength="10" required>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" placeholder="Enter your password" required>
            </div>

            <button type="submit" id="submitBtn" class="btn btn-primary" style="width:100%; justify-content:center; padding:16px; font-size: 16px; border-radius: 14px;">
                <span id="btnText">Login</span>
                <i class="fa-solid fa-arrow-right" id="btnIcon" style="margin-left: 10px;"></i>
            </button>

            <?php if (in_array($role, ['customer', 'mechanic_owner', 'parts_owner'])): ?>
            <div style="text-align:center; margin-top: 25px;">
                <p style="font-size:14px; color:var(--text-light);">Don't have an account? <a href="register.php?role=<?php echo $role; ?>" style="color:var(--primary); font-weight:700; text-decoration:none;">Register Now</a></p>
            </div>
            <?php elseif ($role !== 'admin'): ?>
            <div style="text-align:center; margin-top: 25px;">
                <a href="#" style="font-size:14px; color:var(--text-light); font-weight:500;">Forgot password?</a>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Graphic -->
    <div class="login-graphic">
        <div style="position: absolute; width: 600px; height: 600px; background: rgba(255,255,255,0.05); border-radius: 50%; top: -200px; right: -200px;"></div>
        <img src="../assets/images/logo.png" alt="Road Mithra" style="width:120px; border-radius:24px; filter: drop-shadow(0 25px 50px rgba(0,0,0,0.4)); position: relative; z-index: 2;">
        <h2>Road Mithra</h2>
        <p>Providing enterprise-grade roadside assistance solutions. Connect with verified professionals in seconds.</p>
        <div style="margin-top:50px; display:flex; gap:25px; flex-wrap:wrap; justify-content:center; position: relative; z-index: 2;">
            <div style="text-align:center; background:rgba(255,255,255,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); padding: 25px 35px; border-radius:24px;">
                <div style="font-size:36px; font-weight:900;">150+</div>
                <div style="font-size:13px; opacity:0.8; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Workshops</div>
            </div>
            <div style="text-align:center; background:rgba(255,255,255,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); padding: 25px 35px; border-radius:24px;">
                <div style="font-size:36px; font-weight:900;">2.5K</div>
                <div style="font-size:13px; opacity:0.8; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Daily Helps</div>
            </div>
        </div>
    </div>
</div>

<script>
    const loginForm = document.getElementById('loginForm');
    
    // Pure PHP/HTML handling for login now, but let's keep it clean
    loginForm.addEventListener('submit', () => {
        const btnText = document.getElementById('btnText');
        const btnIcon = document.getElementById('btnIcon');
        
        btnText.innerText = 'Authenticating...';
        btnIcon.className = 'fa-solid fa-spinner fa-spin';
    });
</script>
</body>
</html>
