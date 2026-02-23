<?php
session_start();
require 'db.php';

$booking_id = $_GET['booking_id'];
// Fetch booking info to show the user
$res = pg_query_params($conn, "SELECT * FROM bookings WHERE id = $1", array($booking_id));
$booking = pg_fetch_assoc($res);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout | TripNexus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow border-0 mx-auto" style="max-width: 500px;">
            <div class="card-body p-4">
                <h3>Secure Payment</h3>
                <hr>
                <p>Booking ID: #<?php echo $booking_id; ?></p>
                <h5>Total Amount: ₹<?php echo number_format($booking['price'], 2); ?></h5>
                
                <form action="complete_payment.php" method="POST">
                    <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                    <input type="hidden" name="amount" value="<?php echo $booking['price']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Card Number</label>
                        <input type="text" class="form-control" placeholder="0000 0000 0000 0000" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Pay Now</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>