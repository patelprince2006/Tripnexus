<?php
session_start();
include 'db.php';
require_once 'includes/EmailService.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $verificationCode = trim($_POST['verification_code']);
    
    // Validate input
    if (empty($verificationCode) || strlen($verificationCode) !== 6 || !ctype_digit($verificationCode)) {
        echo "<script>alert('Invalid verification code format'); history.back();</script>";
        exit();
    }

    // Find user with this verification code
    $query = pg_prepare($conn, "find_user_by_code", 'SELECT id, email, fullname FROM users WHERE verification_code = $1');
    $result = pg_execute($conn, "find_user_by_code", array($verificationCode));

    if (pg_num_rows($result) === 0) {
        echo "<script>alert('Invalid verification code'); history.back();</script>";
        exit();
    }

    $user = pg_fetch_assoc($result);
    $userId = $user['id'];
    $userEmail = $user['email'];
    $fullname = $user['fullname'];

    // Check if code has expired
    $checkExpiry = pg_prepare($conn, "check_code_expiry", 'SELECT verification_code_expiry FROM users WHERE id = $1');
    $expiryResult = pg_execute($conn, "check_code_expiry", array($userId));
    $expiryRow = pg_fetch_assoc($expiryResult);

    if ($expiryRow && strtotime($expiryRow['verification_code_expiry']) < time()) {
        echo "<script>alert('Verification code has expired. Please request a new one.'); window.location='resend_verification.php';</script>";
        exit();
    }

    // Mark user as verified
    $updateQuery = pg_prepare($conn, "verify_user", 
        'UPDATE users SET is_verified = true, email_verified_at = NOW(), verification_code = NULL, verification_code_expiry = NULL WHERE id = $1');
    $updateResult = pg_execute($conn, "verify_user", array($userId));

    if ($updateResult) {
        // Log notification
        $emailService = new EmailService($conn);
        $emailService->saveNotification($userId, 'verification', 'Email Verified', 
            'Your email has been successfully verified. You can now log in to your account.');

        echo "<script>alert('Email verified successfully! You can now login.'); window.location='login.html';</script>";
    } else {
        echo "<script>alert('Error verifying email. Please try again.'); history.back();</script>";
    }
} else {
    // If not POST request, redirect to verification page
    header("Location: verify_email.html");
    exit();
}

pg_close($conn);
?>
