<?php
session_start();
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../database/razorpay_config.php';

$booking_id = $_GET['booking_id'];
$booking = null;
$error_msg = null;
$user_email = '';
$user_fullname = '';

// Fetch booking info and user info to show the user
if (defined('DB_CONNECTED') && DB_CONNECTED) {
    $res = db_query($conn, "SELECT b.*, u.email, u.fullname FROM bookings b JOIN users u ON b.user_id = u.id WHERE b.id = ?", array($booking_id));
    $booking = db_fetch_assoc($res);
    if ($booking) {
        $user_email = $booking['email'];
        $user_fullname = $booking['fullname'];
    }
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
                    <div class="text-center mb-4">
                        <img src="../photos/logo.png" alt="TripNexus" style="height: 50px;">
                        <h3 class="mt-2">Secure Payment</h3>
                    </div>
                    <hr>
                    <div class="mb-4">
                        <p class="mb-1 text-muted">Booking Reference</p>
                        <h6 class="fw-bold">#<?php echo $booking_id; ?> (<?php echo strtoupper($booking['booking_type']); ?>)</h6>
                    </div>
                    <?php
                        $amount = isset($booking['total_amount']) ? $booking['total_amount'] : 0;
                        $amount_paise = $amount * 100; // Razorpay takes amount in paise
                    ?>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="text-muted">Total Amount:</span>
                        <h5 class="fw-bold text-primary">₹<?php echo number_format($amount, 2); ?></h5>
                    </div>
                    
                    <button id="rzp-button1" class="btn btn-primary w-100 py-3 fw-bold rounded-pill">Pay with Razorpay</button>

                    <form action="complete_payment.php" method="POST" id="razorpay-form">
                        <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                        <input type="hidden" name="amount" value="<?php echo $amount; ?>">
                        <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
                        <input type="hidden" name="razorpay_signature" id="razorpay_signature">
                    </form>

                    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
                    <script>
                        var options = {
                            "key": "<?php echo RAZORPAY_KEY_ID; ?>",
                            "amount": "<?php echo $amount_paise; ?>",
                            "currency": "<?php echo RAZORPAY_CURRENCY; ?>",
                            "name": "TripNexus",
                            "description": "Payment for Booking #<?php echo $booking_id; ?>",
                            "image": "../photos/logo.png",
                            "handler": function (response){
                                document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                                document.getElementById('razorpay_signature').value = response.razorpay_signature;
                                document.getElementById('razorpay-form').submit();
                            },
                            "prefill": {
                                "name": "<?php echo $user_fullname; ?>",
                                "email": "<?php echo $user_email; ?>"
                            },
                            "theme": {
                                "color": "#0d6efd"
                            }
                        };
                        var rzp1 = new Razorpay(options);
                        document.getElementById('rzp-button1').onclick = function(e){
                            rzp1.open();
                            e.preventDefault();
                        }
                    </script>
                <?php endif; ?>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="../user/my_booking_standlone.php" class="text-decoration-none text-muted small">← Back to My Bookings</a>
        </div>
    </div>
</body>
</html>
