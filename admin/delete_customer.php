<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin']) || $_SESSION['role'] !== 'admin') { header("Location: ../auth/login.php?role=admin"); exit; }

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $conn->query("DELETE FROM customers WHERE id = $id");
}

$ref = $_SERVER['HTTP_REFERER'] ?: 'manage_customers.php';
header("Location: $ref");
exit;
