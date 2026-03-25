<?php
require_once '../includes/db.php';
$role = $_GET['role'] ?? 'customer';
$titles = [
    'customer'       => 'Customer Registration',
    'mechanic_owner' => 'Garage Owner Registration',
    'parts_owner'    => 'Spare Parts Shop Registration'
];
$title = $titles[$role] ?? 'Customer Registration';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - Road Mithra</title>
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
        @media(max-width:768px){.login-page{grid-template-columns:1fr;}.login-graphic{display:none;}.login-panel{max-width:100%;padding:40px 25px;}}
    </style>
</head>
<body>
<div class="login-page">
    <div class="login-panel">
        <a href="login.php?role=<?php echo $role; ?>" class="login-back"><i class="fa-solid fa-arrow-left"></i> Back to Login</a>

        <div class="login-heading">
            <h1><?php echo ($role == 'customer') ? 'Create Account' : 'Register Shop'; ?></h1>
            <p><?php echo ($role == 'customer') ? 'Join Road Mithra for premium roadside assistance' : 'Partner with us to grow your business'; ?></p>
        </div>

        <form id="registerForm" action="../includes/process_register.php" method="POST">
            <input type="hidden" name="role" value="<?php echo $role; ?>">
            
            <div class="form-group">
                <label class="form-label"><?php echo ($role == 'customer') ? 'Full Name' : 'Owner Name'; ?></label>
                <input type="text" name="name" class="form-input" placeholder="Enter full name" required>
            </div>

            <?php if ($role !== 'customer'): ?>
            <div class="form-group">
                <label class="form-label">Shop Name</label>
                <input type="text" name="shop_name" class="form-input" placeholder="Name of your garage/shop" required>
            </div>
            <?php endif; ?>

            <div class="form-group" style="display:grid; grid-template-columns: 1fr 1.5fr; gap:15px;">
                <div>
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-input" placeholder="9876543210" maxlength="10" required>
                </div>
                <div>
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-input" placeholder="name@example.com" required>
                </div>
            </div>

            <?php if ($role !== 'customer'): ?>
            <div class="form-group">
                <label class="form-label">Business Address</label>
                <textarea name="address" class="form-input" placeholder="Full shop location..." rows="2" style="resize:none;" required></textarea>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label class="form-label">Set Password</label>
                <input type="password" name="password" class="form-input" placeholder="Minimum 6 characters" required minlength="6">
            </div>

            <button type="submit" id="submitBtn" class="btn btn-primary" style="width:100%; justify-content:center; padding:16px; font-size: 16px; border-radius: 14px;">
                <span id="btnText"><?php echo ($role == 'customer') ? 'Create My Account' : 'Register My Shop'; ?></span>
                <i class="fa-solid fa-user-plus" id="btnIcon" style="margin-left: 10px;"></i>
            </button>

            <div style="text-align:center; margin-top: 25px;">
                <p style="font-size:14px; color:var(--text-light);">Already have an account? <a href="login.php?role=<?php echo $role; ?>" style="color:var(--primary); font-weight:700; text-decoration:none;">Login here</a></p>
            </div>
        </form>
    </div>

    <div class="login-graphic">
        <div style="position: absolute; width: 600px; height: 600px; background: rgba(255,255,255,0.05); border-radius: 50%; top: -200px; right: -200px;"></div>
        <img src="../assets/images/logo.png" alt="Road Mithra" style="width:120px; border-radius:24px; filter: drop-shadow(0 25px 50px rgba(0,0,0,0.4)); position: relative; z-index: 2;">
        <h2>Road Mithra</h2>
        <p><?php echo ($role == 'customer') ? 'Experience the most reliable roadside assistance network. From quick fixes to emergency towing, we\'ve got you covered.' : 'Expand your reach and manage your services digitally. Join thousands of verified partners nationwide.'; ?></p>
        <div style="margin-top:50px; display:flex; gap:25px; flex-wrap:wrap; justify-content:center; position: relative; z-index: 2;">
            <div style="text-align:center; background:rgba(255,255,255,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); padding: 20px 30px; border-radius:24px;">
                <div style="font-size:32px; font-weight:900;">24/7</div>
                <div style="font-size:12px; opacity:0.8; font-weight: 600; text-transform: uppercase;">Support</div>
            </div>
            <div style="text-align:center; background:rgba(255,255,255,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); padding: 20px 30px; border-radius:24px;">
                <div style="font-size:32px; font-weight:900;">100%</div>
                <div style="font-size:12px; opacity:0.8; font-weight: 600; text-transform: uppercase;">Verified</div>
            </div>
        </div>
    </div>
</div>

<script>
    const registerForm = document.getElementById('registerForm');
    registerForm.addEventListener('submit', () => {
        const btnText = document.getElementById('btnText');
        const btnIcon = document.getElementById('btnIcon');
        btnText.innerText = 'Creating Account...';
        btnIcon.className = 'fa-solid fa-spinner fa-spin';
    });
</script>
</body>
</html>
