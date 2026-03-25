<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['otp'])) {
    $entered_otp = trim($_POST['otp']);
    $session_otp = $_SESSION['otp_code'] ?? '';
    $phone = $_SESSION['otp_phone'] ?? '';

    if ($entered_otp === $session_otp && !empty($phone)) {
        // Success! Clear OTP from session
        unset($_SESSION['otp_code']);

        // Check if user exists
        $stmt = $conn->prepare("SELECT * FROM customers WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            // Log in existing user
            $_SESSION['user'] = $res->fetch_assoc();
            $_SESSION['role'] = 'customer';
            if (isset($_POST['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'redirect' => '../customer/dashboard.php']);
                exit;
            }
            header("Location: ../customer/dashboard.php");
        } else {
            // Create a default new user (they can update details in profile later)
            // Or we could redirect to a 'Complete Profile' page
            $name = "User " . substr($phone, -4);
            $ins = $conn->prepare("INSERT INTO customers (name, phone) VALUES (?, ?)");
            $ins->bind_param("ss", $name, $phone);
            $ins->execute();
            
            $new_id = $conn->insert_id;
            $res_new = $conn->query("SELECT * FROM customers WHERE id = $new_id");
            $_SESSION['user'] = $res_new->fetch_assoc();
            $_SESSION['role'] = 'customer';
            if (isset($_POST['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'redirect' => '../customer/dashboard.php']);
                exit;
            }
            header("Location: ../customer/dashboard.php");
        }
    } else {
        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid OTP. Please try again.']);
            exit;
        }
        echo "<script>alert('Invalid OTP. Please try again.'); window.history.back();</script>";
    }
} else {
    header("Location: ../auth/login.php");
}
?>
