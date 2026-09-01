<?php
session_start();
require_once __DIR__ . '/../database/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login to continue booking.'); window.location='../user/login.html';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$booking_type = isset($_POST['service_type']) ? trim($_POST['service_type']) : '';
$reference_id = isset($_POST['reference_id']) ? intval($_POST['reference_id']) : 0;
$total_amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0.0;
$travel_date = isset($_POST['travel_date']) ? trim($_POST['travel_date']) : '';

if ($booking_type === '' || $reference_id <= 0 || $total_amount <= 0) {
    echo "<script>alert('Unable to create booking. Please try again.'); window.location='../index.php';</script>";
    exit;
}

if (!defined('DB_CONNECTED') || !DB_CONNECTED) {
    echo "<script>alert('Database connection failed. Booking not created.'); window.location='../index.php';</script>";
    exit;
}

$user_id = intval($_SESSION['user_id']);

$query = "INSERT INTO bookings (user_id, booking_type, reference_id, total_amount, travel_date) VALUES (?, ?, ?, ?, ?)";
$params = array($user_id, $booking_type, $reference_id, $total_amount, $travel_date === '' ? null : $travel_date);
$res = db_query($conn, $query, $params);

if (!$res) {
    echo "<script>alert('Booking failed. Please try again.'); window.location='../index.php';</script>";
    exit;
}

$booking_id = db_insert_id($conn);
if (!$booking_id) {
    $booking_id = db_fetch_value(db_query($conn, "SELECT MAX(id) FROM bookings"), 0, 0);
}

header("Location: checkout.php?booking_id={$booking_id}");
exit;
?>
