<?php
$c = new mysqli('127.0.0.1', 'root', '', 'roadmithra');
if ($c->connect_error) die("Conn failed: " . $c->connect_error);
$r = $c->query("SELECT id, shop_name, shop_type FROM shop_owners");
while($row = $r->fetch_assoc()) {
    echo $row['shop_name'] . " -> " . $row['shop_type'] . "\n";
}
?>
