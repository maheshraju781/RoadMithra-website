<?php
require_once '../includes/db.php';

if (!isset($_SESSION['owner'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = $_SESSION['owner']['id'];
$action = $_REQUEST['action'] ?? '';

if ($action == 'toggle_shop') {
    $status = (int)$_REQUEST['status'];
    $stmt = $conn->prepare("UPDATE shop_owners SET is_open = ? WHERE id = ?");
    $stmt->bind_param("ii", $status, $id);
    if ($stmt->execute()) {
        $_SESSION['owner']['is_open'] = $status;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} elseif ($action == 'assign_worker') {
    $booking_id = (int)($_POST['booking_id'] ?? 0);
    $order_id = (int)($_POST['order_id'] ?? 0);
    $worker_id = (int)$_POST['worker_id'];
    
    // Check if worker belongs to this shop
    $check = $conn->prepare("SELECT id FROM workers WHERE id = ? AND shop_id = ?");
    $check->bind_param("ii", $worker_id, $id);
    $check->execute();
    if ($check->get_result()->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Worker not found in your team']);
        exit;
    }

    if ($order_id > 0) {
        // Assign to spare parts order
        $stmt = $conn->prepare("UPDATE spare_parts_orders SET worker_id = ?, status = 'assigned' WHERE id = ? AND shop_id = ?");
        $stmt->bind_param("iii", $worker_id, $order_id, $id);
    } else {
        // Assign to mechanic booking
        $stmt = $conn->prepare("UPDATE mechanic_bookings SET worker_id = ?, status = 'assigned' WHERE id = ? AND shop_id = ?");
        $stmt->bind_param("iii", $worker_id, $booking_id, $id);
    }

    if ($stmt->execute()) {
        // Also update worker status to busy (optional but good for consistency)
        $conn->query("UPDATE workers SET is_available = 0 WHERE id = $worker_id");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
} elseif ($action == 'add_worker') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $password = $_POST['password']; // In real app, hash this
    $type = $_POST['type'];
    
    $stmt = $conn->prepare("INSERT INTO workers (name, phone, password, type, shop_id, is_approved, is_available) VALUES (?, ?, ?, ?, ?, 1, 1)");
    $stmt->bind_param("ssssi", $name, $phone, $password, $type, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
} elseif ($action == 'delete_worker') {
    $worker_id = (int)$_POST['worker_id'];
    $stmt = $conn->prepare("DELETE FROM workers WHERE id = ? AND shop_id = ?");
    $stmt->bind_param("ii", $worker_id, $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} elseif ($action == 'reject_order' || $action == 'reject_booking') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $booking_id = (int)($_POST['booking_id'] ?? 0);
    
    if ($order_id > 0) {
        $stmt = $conn->prepare("UPDATE spare_parts_orders SET status = 'cancelled' WHERE id = ? AND shop_id = ?");
        $stmt->bind_param("ii", $order_id, $id);
    } else {
        $stmt = $conn->prepare("UPDATE mechanic_bookings SET status = 'cancelled' WHERE id = ? AND shop_id = ?");
        $stmt->bind_param("ii", $booking_id, $id);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}
?>
