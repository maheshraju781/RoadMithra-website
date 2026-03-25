<?php
require_once '../includes/db.php';
if (!isset($_SESSION['otp_phone'])) {
    header("Location: login.php"); exit;
}
$phone = $_SESSION['otp_phone'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Road Mithra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/web.css">
    <link rel="icon" type="image/x-icon" href="../assets/images/logo.png">
    <style>
        body { background: #F8F9FA; display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: 'Inter', sans-serif; }
        .otp-card {
            background: white; padding: 50px; border-radius: 30px; box-shadow: 0 20px 50px rgba(0,0,0,0.05);
            max-width: 450px; width: 90%; text-align: center;
        }
        .otp-icon {
            width: 80px; height: 80px; background: var(--primary-light); color: var(--primary);
            border-radius: 20px; display: flex; align-items: center; justify-content: center;
            font-size: 32px; margin: 0 auto 30px auto;
        }
        .otp-inputs { display: flex; gap: 12px; justify-content: center; margin: 30px 0; }
        .otp-input {
            width: 50px; height: 60px; border: 2.5px solid #EEE; border-radius: 12px;
            text-align: center; font-size: 24px; font-weight: 800; color: var(--primary);
            transition: all 0.2s;
        }
        .otp-input:focus { border-color: var(--primary); outline: none; background: #F5F3FF; }
        .resend-link { color: var(--primary); font-weight: 700; text-decoration: none; font-size: 14px; }
        .resend-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="otp-card">
    <div class="otp-icon"><i class="fa-solid fa-shield-halved"></i></div>
    <h1 style="font-size: 24px; font-weight: 800; margin-bottom: 10px;">Verification Code</h1>
    <p style="color: #666; font-size: 15px; line-height: 1.6;">We have sent a 6-digit code to your mobile number <br><strong>+91 <?php echo htmlspecialchars($phone); ?></strong></p>

    <form action="../includes/process_otp.php" method="POST" id="otpForm">
        <div class="otp-inputs">
            <input type="text" maxlength="1" class="otp-input" required autofocus>
            <input type="text" maxlength="1" class="otp-input" required>
            <input type="text" maxlength="1" class="otp-input" required>
            <input type="text" maxlength="1" class="otp-input" required>
            <input type="text" maxlength="1" class="otp-input" required>
            <input type="text" maxlength="1" class="otp-input" required>
        </div>
        <input type="hidden" name="otp" id="fullOtp">
        
        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 16px; font-size: 16px;">
            Verify & Create Account
        </button>
    </form>

    <div style="margin-top: 30px;">
        <p style="font-size: 14px; color: #666;">Didn't receive code? <a href="#" class="resend-link">Resend in 0:45</a></p>
    </div>
</div>

<script>
    const inputs = document.querySelectorAll('.otp-input');
    const form = document.getElementById('otpForm');
    const fullOtpInput = document.getElementById('fullOtp');

    inputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            if (e.target.value.length > 0 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
            updateFullOtp();
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && e.target.value.length === 0 && index > 0) {
                inputs[index - 1].focus();
            }
        });
    });

    function updateFullOtp() {
        let otp = "";
        inputs.forEach(i => otp += i.value);
        fullOtpInput.value = otp;
    }

    form.addEventListener('submit', (e) => {
        updateFullOtp();
        if (fullOtpInput.value.length !== 6) {
            e.preventDefault();
            alert("Please enter all 6 digits.");
        }
    });

    // Auto-fill logic for local testing
    console.log("TESTING OTP: 123456");
</script>

</body>
</html>
