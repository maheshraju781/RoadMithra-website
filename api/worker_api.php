<?php
require_once '../includes/db.php';

if (!isset($_SESSION['worker'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$worker_id = $_SESSION['worker']['id'];
$action = $_GET['action'] ?? '';

if ($action == 'toggle_status') {
    $status = (int)$_GET['status'];
    $stmt = $conn->prepare("UPDATE workers SET is_available = ? WHERE id = ?");
    $stmt->bind_param("ii", $status, $worker_id);
    
    if ($stmt->execute()) {
        $_SESSION['worker']['is_available'] = $status;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} elseif ($action == 'accept_request') {
    $booking_id = (int)$_POST['booking_id'];
    $stmt = $conn->prepare("UPDATE mechanic_bookings SET status = 'accepted', worker_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $worker_id, $booking_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>
