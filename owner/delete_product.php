<?php
require_once '../includes/db.php';
if (!isset($_SESSION['owner'])) { header("Location: ../auth/login.php"); exit; }
$shop_id = $_SESSION['owner']['id'];
$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("DELETE FROM spare_parts WHERE id=? AND shop_id=?");
$stmt->bind_param("ii", $id, $shop_id);
if($stmt->execute()) {
    header("Location: inventory.php?deleted=1");
    exit;
} else {
    echo "<script>alert('Failed to delete product.'); window.history.back();</script>";
}
?>
