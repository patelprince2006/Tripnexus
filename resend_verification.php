<?php
session_start();
include 'db.php';
require_once 'includes/EmailService.php';
require_once 'config/mail_config.php';

// Check if email is provided (from form or session)
$email = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
} elseif (isset($_SESSION['pending_verification_email'])) {
    $email = $_SESSION['pending_verification_email'];
}

if (!$email) {
    echo "<script>alert('Please provide an email address'); history.back();</script>";
    exit();
}

// Find user by email
$query = pg_prepare($conn, "find_user", 'SELECT id, fullname FROM users WHERE email = $1 AND is_verified = false');
$result = pg_execute($conn, "find_user", array($email));

if (pg_num_rows($result) === 0) {
    echo "<script>alert('No unverified account found with this email'); history.back();</script>";
    exit();
}

$user = pg_fetch_assoc($result);
$userId = $user['id'];
$fullname = $user['fullname'];

// Generate new verification code
$verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expiryTime = date('Y-m-d H:i:s', strtotime('+' . VERIFICATION_CODE_EXPIRY . ' minutes'));

// Update verification code
$updateQuery = pg_prepare($conn, "update_code", 
    'UPDATE users SET verification_code = $1, verification_code_expiry = $2 WHERE id = $3');
$updateResult = pg_execute($conn, "update_code", array($verificationCode, $expiryTime, $userId));

if (!$updateResult) {
    echo "<script>alert('Error updating verification code'); history.back();</script>";
    exit();
}

// Send verification email
$emailService = new EmailService($conn);
$emailSent = $emailService->sendVerificationEmail($email, $fullname, $verificationCode);

if ($emailSent) {
    // Save notification
    $emailService->saveNotification($userId, 'verification', 'New Verification Code Sent', 
        'A new verification code has been sent to ' . $email);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        echo "<script>alert('Verification code sent! Check your email.'); window.location='verify_email.html';</script>";
    } else {
        // If accessed directly from session
        echo "<script>alert('Verification code resent! Check your email.'); window.location='verify_email.html';</script>";
    }
} else {
    echo "<script>alert('Failed to send email. Please try again.'); history.back();</script>";
}

pg_close($conn);
?>
