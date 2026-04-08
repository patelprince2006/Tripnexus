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

    // 2. Update booking status to 'confirmed'
    $update_query = "UPDATE bookings SET status = 'confirmed' WHERE id = ?";
    db_query($conn, $update_query, array($booking_id));

    // Redirect to "My Bookings" page
    header("Location: ../user/my_booking_standlone.php?success=true");
}
?>