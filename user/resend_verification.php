<?php
session_start();
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../email/EmailService.php';
require_once __DIR__ . '/../email/mail_config.php';

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

// Check if database is connected
if (!defined('DB_CONNECTED') || !DB_CONNECTED) {
    echo "<script>alert('Database connection failed. Please try again later.'); history.back();</script>";
    exit();
}

// Find user by email
$query = db_prepare($conn, "find_user", 'SELECT id, fullname FROM users WHERE email = ? AND is_verified = false');
$result = db_execute($conn, "find_user", array($email));

if (!$result || db_num_rows($result) === 0) {
    echo "<script>alert('No unverified account found with this email'); history.back();</script>";
    exit();
}

$user = db_fetch_assoc($result);
$userId = $user['id'];
$fullname = $user['fullname'];

// Generate new verification code
$verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expiryTime = date('Y-m-d H:i:s', strtotime('+' . VERIFICATION_CODE_EXPIRY . ' minutes'));

// Update verification code
$updateQuery = db_prepare($conn, "update_code", 
    'UPDATE users SET verification_code = ?, verification_code_expiry = ? WHERE id = ?');
$updateResult = db_execute($conn, "update_code", array($verificationCode, $expiryTime, $userId));

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
        echo "<script>alert('Verification code sent! Check your email.'); window.location='verify_email_ui.php?email=" . urlencode($email) . "';</script>";
    } else {
        // If accessed directly from session
        echo "<script>alert('Verification code resent! Check your email.'); window.location='verify_email_ui.php?email=" . urlencode($email) . "';</script>";
    }
} else {
    echo "<script>alert('Failed to send email. Please try again.'); history.back();</script>";
}

if (defined('DB_CONNECTED') && DB_CONNECTED) {
    db_close($conn);
}
?>
