<?php
include 'D:/xampp/htdocs/SGP-vijay_new/database/db.php';

$user_id = 1; // Dummy user ID for testing
$list_query = "SELECT 
        b.*,
        CASE 
            WHEN b.booking_type = 'flight' THEN f.flight_number
            WHEN b.booking_type = 'hotel' THEN h.name
            WHEN b.booking_type = 'tour' THEN t.name
            ELSE NULL
        END as item_name
    FROM bookings b
    LEFT JOIN flights f ON b.booking_type = 'flight' AND b.reference_id = f.flight_id
    LEFT JOIN hotels h ON b.booking_type = 'hotel' AND b.reference_id = h.hotel_id
    LEFT JOIN tour_packages t ON b.booking_type = 'tour' AND b.reference_id = t.id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC";

try {
    $list_res = db_query($conn, $list_query, array($user_id));
    if ($list_res) {
        echo "Query executed successfully!\n";
        while ($row = db_fetch_assoc($list_res)) {
             echo "Booking ID: " . $row['id'] . " type: " . $row['booking_type'] . "\n";
        }
    } else {
        echo "Query failed: " . db_last_error($conn) . "\n";
    }
} catch (Exception $e) {
    echo "Caught exception: " . $e->getMessage() . "\n";
}
?>
