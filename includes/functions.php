<?php
require_once 'db.php';

/**
 * Find shops within a certain radius (10km default)
 * @param float $lat Customer Latitude
 * @param float $lng Customer Longitude
 * @param string $type 'mechanic' or 'spare_parts'
 * @param int $radius Radius in KM
 */
function find_nearby_shops($lat, $lng, $type, $radius = 10) {
    global $conn;
    
    $query = "SELECT *, ( 6371 * acos( cos( radians(?) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(?) ) + sin( radians(?) ) * sin( radians( lat ) ) ) ) AS distance 
              FROM shop_owners 
              WHERE shop_type = ? AND is_active = 1 AND is_open = 1
              HAVING distance < ? 
              ORDER BY distance";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("dddsd", $lat, $lng, $lat, $type, $radius);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Assign worker to a booking
 */
function assign_worker($booking_id, $worker_id) {
    global $conn;
    $stmt = $conn->prepare("UPDATE bookings SET worker_id = ?, status = 'assigned' WHERE id = ?");
    $stmt->bind_param("ii", $worker_id, $booking_id);
    return $stmt->execute();
}
?>
