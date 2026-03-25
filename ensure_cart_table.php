<?php
require_once 'includes/db.php';

$sql = "CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    part_id INT NOT NULL,
    shop_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (part_id) REFERENCES spare_parts(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Cart table ready.";
} else {
    echo "Error: " . $conn->error;
}
?>
