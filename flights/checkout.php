<?php
session_start();
require_once __DIR__ . '/../database/db.php';

$booking_id = $_GET['booking_id'];
$booking = null;
$error_msg = null;

// Fetch booking info to show the user
if (defined('DB_CONNECTED') && DB_CONNECTED) {
    $res = db_query($conn, "SELECT * FROM bookings WHERE id = ?", array($booking_id));
    $booking = db_fetch_assoc($res);
} else {
    $error_msg = "Unable to connect to database. Payment cannot be processed.";
}
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
                <?php if ($error_msg): ?>
                    <div class="alert alert-warning" role="alert">
                        <?php echo $error_msg; ?>
                    </div>
                <?php elseif (!$booking): ?>
                    <div class="alert alert-danger" role="alert">
                        Booking not found. Please check your booking ID.
                    </div>
                <?php else: ?>
                    <h3>Secure Payment</h3>
                    <hr>
                    <p>Booking ID: #<?php echo $booking_id; ?></p>
                    <?php
                        $amount = isset($booking['total_amount']) ? $booking['total_amount'] : (isset($booking['price']) ? $booking['price'] : 0);
                    ?>
                    <h5>Total Amount: ₹<?php echo number_format($amount, 2); ?></h5>
                    
                    <form action="complete_payment.php" method="POST">
                        <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                        <input type="hidden" name="amount" value="<?php echo $amount; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Card Number</label>
                            <input type="text" class="form-control" placeholder="0000 0000 0000 0000" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold">Pay Now</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
