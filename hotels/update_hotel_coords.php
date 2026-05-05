<?php
require_once __DIR__ . '/../database/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['hotel_id'], $_POST['lat'], $_POST['lng'])) {
    $hotel_id = intval($_POST['hotel_id']);
    $lat = floatval($_POST['lat']);
    $lng = floatval($_POST['lng']);

    $sql = "UPDATE hotels SET latitude = ?, longitude = ? WHERE hotel_id = ?";
    db_query($conn, $sql, [$lat, $lng, $hotel_id]);
    echo "Success";
}
?>