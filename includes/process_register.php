<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = $_POST['role'] ?? 'customer';
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Default coordinates (can be updated later)
    $lat = 13.0827; 
    $lng = 80.2707;

    if ($role === 'customer') {
        // Check if exists
        $stmt = $conn->prepare("SELECT id FROM customers WHERE phone = ? OR email = ?");
        $stmt->bind_param("ss", $phone, $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            echo "<script>alert('Customer account already exists'); window.history.back();</script>";
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO customers (name, phone, email, password, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssdd", $name, $phone, $email, $password, $lat, $lng);
        
        if ($stmt->execute()) {
            $last_id = $conn->insert_id;
            $new_stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
            $new_stmt->bind_param("i", $last_id);
            $new_stmt->execute();
            $_SESSION['user'] = $new_stmt->get_result()->fetch_assoc();
            $_SESSION['role'] = 'customer';
            header("Location: ../customer/dashboard.php");
        } else {
            echo "<script>alert('Registration failed'); window.history.back();</script>";
        }
    } else {
        // Shop Owner Registration
        $shop_name = trim($_POST['shop_name'] ?? 'My Shop');
        $address = trim($_POST['address'] ?? '');
        $shop_type = ($role === 'mechanic_owner') ? 'mechanic' : 'spare_parts';

        $stmt = $conn->prepare("SELECT id FROM shop_owners WHERE phone = ? OR email = ?");
        $stmt->bind_param("ss", $phone, $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            echo "<script>alert('Shop owner account already exists'); window.history.back();</script>";
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO shop_owners (shop_name, owner_name, email, phone, password, shop_type, latitude, longitude, address, is_approved) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $is_approved = 0; // New shops need approval
        $stmt->bind_param("ssssssddsi", $shop_name, $name, $email, $phone, $password, $shop_type, $lat, $lng, $address, $is_approved);
        
        if ($stmt->execute()) {
            echo "<script>alert('Registration successful! Please wait for admin approval.'); window.location.href='../auth/login.php?role=$role';</script>";
        } else {ss
            echo "<script>alert('Registration failed'); window.history.back();</script>";
        }
    }
}
?>
