<?php
$c = new mysqli('127.0.0.1', 'root', '', 'roadmithra');
if ($c->connect_error) die("Conn failed: " . $c->connect_error);
$r = $c->query("SELECT name, shop_name, type FROM workers WHERE is_common_problem = 1");
while($row = $r->fetch_assoc()) {
    print_r($row);
}
?>
