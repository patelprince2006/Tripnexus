<?php
session_start();
require_once __DIR__ . '/../database/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if booking_id is provided
if (!isset($_POST['booking_id'])) {
    header("Location: my_booking_standlone.php?error=missing_booking_id");
    exit();
}

$booking_id = (int)$_POST['booking_id'];

// Get the booking details
$get_booking_query = "SELECT * FROM bookings WHERE id = ? AND user_id = ?";
$booking_result = db_query($conn, $get_booking_query, array($booking_id, $user_id));

if (!$booking_result || db_num_rows($booking_result) === 0) {
    header("Location: my_booking_standlone.php?error=booking_not_found");
    exit();
}

$booking = db_fetch_assoc($booking_result);

// Check if booking is already cancelled
if ($booking['status'] === 'cancelled') {
    header("Location: my_booking_standlone.php?error=already_cancelled");
    exit();
}

// Now, restore seats/rooms based on booking type
$booking_type = $booking['booking_type'];
$reference_id = $booking['reference_id'];
$passengers = $booking['passengers'] ?? 1;

$restore_success = true;

switch ($booking_type) {
    case 'flight':
        $restore_query = "UPDATE flights SET available_seats = available_seats + ? WHERE flight_id = ?";
        db_query($conn, $restore_query, array($passengers, $reference_id));
        break;
    case 'bus':
        $restore_query = "UPDATE buses SET available_seats = available_seats + ? WHERE bus_id = ?";
        db_query($conn, $restore_query, array($passengers, $reference_id));
        break;
    case 'train':
        $restore_query = "UPDATE trains SET available_seats = available_seats + ? WHERE train_id = ?";
        db_query($conn, $restore_query, array($passengers, $reference_id));
        break;
    case 'hotel':
        $restore_query = "UPDATE hotel_rooms SET available_count = available_count + ? WHERE id = ?";
        db_query($conn, $restore_query, array($passengers, $reference_id));
        break;
    case 'tour':
        // Check if tour uses tour_schedules or just tour_packages
        $check_schedule = db_query($conn, "SHOW TABLES LIKE 'tour_schedules'");
        if ($check_schedule && db_num_rows($check_schedule) > 0) {
            $restore_query = "UPDATE tour_schedules SET available_seats = available_seats + ? WHERE id = ?";
            db_query($conn, $restore_query, array($passengers, $reference_id));
        }
        break;
}

// Update booking status to cancelled
$update_query = "UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ?";
$update_result = db_query($conn, $update_query, array($booking_id, $user_id));

if ($update_result) {
    header("Location: my_booking_standlone.php?success=cancelled");
} else {
    header("Location: my_booking_standlone.php?error=cancellation_failed");
}
exit();
?>