<?php
include '../database/db.php';

// Check airlines
$result = db_query($conn, "SELECT airline_id, airline_name FROM airlines");
echo "Airlines:\n";
while ($row = db_fetch_assoc($result)) {
    echo $row['airline_id'] . ': ' . $row['airline_name'] . "\n";
}

$query = "INSERT IGNORE INTO flights (flight_number, airline_id, departure_airport, arrival_airport, departure_time, arrival_time, base_price, total_seats, available_seats, status) VALUES
('AI101', 1, 'BOM', 'DEL', '2026-03-27 08:00:00', '2026-03-27 10:15:00', 4500.00, 60, 45, 'scheduled'),
('6E202', 2, 'DEL', 'BLR', '2026-03-27 14:30:00', '2026-03-27 17:45:00', 3800.00, 60, 32, 'scheduled'),
('SG303', 3, 'BLR', 'HYD', '2026-03-27 09:00:00', '2026-03-27 10:30:00', 2500.00, 60, 50, 'scheduled'),
('UK404', 4, 'HYD', 'BOM', '2026-03-27 18:00:00', '2026-03-27 20:00:00', 3200.00, 60, 55, 'scheduled')";

$result = db_query($conn, $query);
if ($result) {
    echo "Flights inserted successfully for March 27, 2026";
} else {
    echo "Error: " . db_last_error($conn);
}
?>