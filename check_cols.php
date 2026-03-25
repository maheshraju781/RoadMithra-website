<?php
require_once 'road_backend/config/database.php';
$res = $conn->query("DESCRIBE workers");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
?>
