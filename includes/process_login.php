<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = $_POST['role'] ?? 'customer';
    $password = $_POST['password'] ?? '';
    
    if ($role == 'customer') {
        $phone = trim($_POST['phone'] ?? '');
        
        // App users might not have a password, let's allow login for them (in a real app we'd use OTP)
        // If password is provided, check it. If not, and it's an app user (password empty in DB), we might need another way.
        // For now, let's match the app's flexibility: if they exist, let them in, or if password is provided, it must match.
        $stmt = $conn->prepare("SELECT * FROM customers WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $user = $res->fetch_assoc();
            // If user has a password in DB, verify it. If DB password is empty, they are an app user - allow login.
            if (empty($user['password']) || $user['password'] === $password) {
                $_SESSION['user'] = $user;
                $_SESSION['role'] = 'customer';
                header("Location: ../customer/dashboard.php");
            } else {
                echo "<script>alert('Incorrect Password'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('Customer account not found. Please register first.'); window.history.back();</script>";
        }
        exit;
    } elseif ($role == 'mechanic_owner' || $role == 'parts_owner') {
        $phone = trim($_POST['phone'] ?? '');
        $target_type = ($role == 'mechanic_owner') ? 'mechanic' : 'spare_parts';
        
        // Find owner by phone, password AND the correct shop type for this portal
        $stmt = $conn->prepare("SELECT * FROM shop_owners WHERE phone = ? AND password = ? AND shop_type = ?");
        $stmt->bind_param("sss", $phone, $password, $target_type);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $owner = $res->fetch_assoc();
            if ($owner['is_approved'] == 0) {
                echo "<script>alert('Your account is pending admin approval'); window.history.back();</script>";
            } else {
                $_SESSION['owner'] = $owner;
                $_SESSION['role'] = $role;
                // Redirect to the appropriate dashboard
                if ($owner['shop_type'] == 'mechanic') header("Location: ../owner/mechanic_dashboard.php");
                else header("Location: ../owner/parts_dashboard.php");
            }
        } else {
            $msg = ($role == 'parts_owner') ? 'Invalid Spare Parts Shop credentials' : 'Invalid Mechanic Shop credentials';
            echo "<script>alert('$msg'); window.history.back();</script>";
        }
    } elseif ($role == 'worker') {
        $worker_id = trim($_POST['worker_id'] ?? '');
        $stmt = $conn->prepare("SELECT * FROM workers WHERE (worker_id = ? OR phone = ?) AND password = ?");
        $stmt->bind_param("sss", $worker_id, $worker_id, $password);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $_SESSION['worker'] = $res->fetch_assoc();
            $_SESSION['role'] = 'worker';
            header("Location: ../worker/dashboard.php");
        } else {
            echo "<script>alert('Invalid Credentials for Worker'); window.history.back();</script>";
        }
    } elseif ($role == 'admin') {
        $username = trim($_POST['username'] ?? '');
        $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $_SESSION['admin'] = $res->fetch_assoc();
            $_SESSION['role'] = 'admin';
            header("Location: ../admin/dashboard.php");
        } else {
            echo "<script>alert('Invalid Admin Credentials'); window.history.back();</script>";
        }
    }
}
?>
