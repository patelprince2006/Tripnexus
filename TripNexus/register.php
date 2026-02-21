<?php
session_start();
include 'db.php';
require_once 'includes/EmailService.php';
require_once 'config/mail_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validation
    if (!$email) {
        echo "<script>alert('Invalid email address'); history.back();</script>";
        exit;
    }

    if ($password !== $confirmPassword) {
        echo "<script>alert('Passwords do not match'); history.back();</script>";
        exit;
    }

    if (strlen($password) < 8) {
        echo "<script>alert('Password must be at least 8 characters'); history.back();</script>";
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists
    $check_res = pg_prepare($conn, "email_exists", 'SELECT email FROM users WHERE email = $1');
    $check_res = pg_execute($conn, "email_exists", array($email));

    if (pg_num_rows($check_res) > 0) {
        echo "<script>alert('Email already registered!'); history.back();</script>";
        exit;
    }

    // Generate 6-digit verification code
    $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiryTime = date('Y-m-d H:i:s', strtotime('+' . VERIFICATION_CODE_EXPIRY . ' minutes'));

    // Insert new user into database
    $insert_res = pg_prepare($conn, "reg_user", 
        'INSERT INTO users (fullname, email, password, verification_code, verification_code_expiry, is_verified) 
         VALUES ($1, $2, $3, $4, $5, false)');
    $insert_res = pg_execute($conn, "reg_user", array($fullname, $email, $hashedPassword, $verificationCode, $expiryTime));

    if (!$insert_res) {
        echo "<script>alert('Error: " . pg_last_error($conn) . "'); history.back();</script>";
        exit;
    }

    // Send verification email
    $emailService = new EmailService($conn);
    $emailSent = $emailService->sendVerificationEmail($email, $fullname, $verificationCode);

    if ($emailSent) {
        // Store email in session for reference
        $_SESSION['pending_verification_email'] = $email;
        
        // Save notification
        $getUserId = pg_query_params($conn, 'SELECT id FROM users WHERE email = $1', array($email));
        $userData = pg_fetch_assoc($getUserId);
        
        if ($userData) {
            $emailService->saveNotification($userData['id'], 'verification', 'Verify Your Email', 
                'A verification code has been sent to ' . $email);
        }

        echo "<script>window.location='verify_email.html';</script>";
    } else {
        // Delete user if email sending failed
        pg_query_params($conn, 'DELETE FROM users WHERE email = $1', array($email));
        echo "<script>alert('Failed to send verification email. Please try again.'); history.back();</script>";
    }
}
pg_close($conn);
?>