<?php
session_start();
require_once __DIR__ . '/../database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!defined('DB_CONNECTED') || !DB_CONNECTED) {
        header("Location: ../user/my_booking_standlone.php?error=Database%20connection%20failed");
        exit();
    }

    $booking_id = $_POST['booking_id'];
    $user_id = $_SESSION['user_id'];
    $amount = $_POST['amount'];
    
    // Check if Razorpay payment ID is provided, otherwise fallback to mock ID
    if (isset($_POST['razorpay_payment_id'])) {
        $transaction_id = $_POST['razorpay_payment_id'];
        $payment_method = 'razorpay';
    } else {
        $transaction_id = 'TNX' . time() . rand(10, 99); // Generate mock ID
        $payment_method = 'card_mock';
    }

    // 1. Insert into payments table
    $pay_query = "INSERT INTO payments (booking_id, user_id, amount, transaction_id, payment_method, payment_status) 
                  VALUES (?, ?, ?, ?, ?, 'success')";
    db_query($conn, $pay_query, array($booking_id, $user_id, $amount, $transaction_id, $payment_method));

    // Fetch booking details
    $b_res = db_query($conn, "SELECT booking_type, reference_id, passengers FROM bookings WHERE id = ?", [$booking_id]);
    $b_data = db_fetch_assoc($b_res);

    if ($b_data && $b_data['booking_type'] === 'flight') {
        $passengers = (int)($b_data['passengers'] ?? 1);
        $ref_id = (int)$b_data['reference_id'];
        
        // Atomic decrement: check available_seats >= passengers
        $dec_res = db_query($conn, "UPDATE flights SET available_seats = available_seats - ? WHERE flight_id = ? AND available_seats >= ?", [$passengers, $ref_id, $passengers]);
        
        if (db_affected_rows($conn) === 0) {
            // Overbooking / race condition detected!
            db_query($conn, "UPDATE bookings SET status = 'cancelled' WHERE id = ?", [$booking_id]);
            db_query($conn, "UPDATE payments SET payment_status = 'refunded' WHERE booking_id = ?", [$booking_id]);
            header("Location: ../user/my_booking_standlone.php?error=Flight%20is%20fully%20booked.%20Payment%20refunded.");
            exit();
        }
    }

    // 2. Update booking status to 'confirmed'
    $update_query = "UPDATE bookings SET status = 'confirmed' WHERE id = ?";
    db_query($conn, $update_query, array($booking_id));

    // Redirect to "My Bookings" page
    header("Location: ../user/my_booking_standlone.php?success=true");
}
?>