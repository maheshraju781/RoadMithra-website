<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin']) || $_SESSION['role'] !== 'admin') { header("Location: ../auth/login.php?role=admin"); exit; }

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $conn->query("UPDATE shop_owners SET is_approved = 1 WHERE id = $id");
}

$ref = $_SERVER['HTTP_REFERER'] ?: 'dashboard.php';
header("Location: $ref");
exit;
