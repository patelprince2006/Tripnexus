<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'];
    $user_id = $_SESSION['user_id'];
    $amount = $_POST['amount'];
    $transaction_id = 'TNX' . time() . rand(10, 99); // Generate mock ID

    // 1. Insert into payments table
    $pay_query = "INSERT INTO payments (booking_id, user_id, amount, transaction_id, payment_status) 
                  VALUES ($1, $2, $3, $4, 'success')";
    pg_query_params($conn, $pay_query, array($booking_id, $user_id, $amount, $transaction_id));

    // 2. Update booking status to 'confirmed'
    $update_query = "UPDATE bookings SET status = 'confirmed' WHERE id = $1";
    pg_query_params($conn, $update_query, array($booking_id));

    // Redirect to "My Bookings" page
    header("Location: my_booking_standlone.php?success=true");
}
?>