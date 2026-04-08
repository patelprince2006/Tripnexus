<?php
session_start();
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../email/EmailService.php';
require_once __DIR__ . '/../email/mail_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $emailInput = strtolower(trim($_POST['email'] ?? ''));
    $email = filter_var($emailInput, FILTER_VALIDATE_EMAIL);
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

    // Password strength validation
    if (strlen($password) < 8) {
        echo "<script>alert('Password must be at least 8 characters long.'); history.back();</script>";
        exit;
    }
    if (!preg_match('/[A-Z]/', $password)) {
        echo "<script>alert('Password must contain at least one uppercase letter (A-Z).'); history.back();</script>";
        exit;
    }
    if (!preg_match('/[a-z]/', $password)) {
        echo "<script>alert('Password must contain at least one lowercase letter (a-z).'); history.back();</script>";
        exit;
    }
    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\x27:"\\\\|,.<>\/?~`]/', $password)) {
        echo "<script>alert('Password must contain at least one special character (!@#\$%^&* etc.).'); history.back();</script>";
        exit;
    }

    // Check if database is connected
    if (!defined('DB_CONNECTED') || !DB_CONNECTED) {
        echo "<script>alert('Database connection failed. Please try again later.'); history.back();</script>";
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Generate verification code
    $verificationCode = sprintf("%06d", mt_rand(100000, 999999));
    $expiryTime = date('Y-m-d H:i:s', strtotime('+' . VERIFICATION_CODE_EXPIRY . ' minutes'));

    // Insert new user into database
    $insert_res = db_prepare($conn, "reg_user", 
        'INSERT INTO users (fullname, email, password, verification_code, verification_code_expiry, is_verified) 
         VALUES (?, ?, ?, ?, ?, 0)');
    $insert_res = db_execute($conn, "reg_user", array($fullname, $email, $hashedPassword, $verificationCode, $expiryTime));

    if (!$insert_res) {
        if (mysqli_errno($conn) === 1062) {
            echo "<script>alert('Email already registered!'); history.back();</script>";
            exit;
        }
        echo "<script>alert('Error: " . db_last_error($conn) . "'); history.back();</script>";
        exit;
    }

    // Send verification email
    $emailService = new EmailService($conn);
    if ($emailService->sendVerificationEmail($email, $fullname, $verificationCode)) {
        echo "<script>alert('Registration successful! A verification code has been sent to your email.'); window.location='verify_email_ui.php?email=" . urlencode($email) . "';</script>";
    } else {
        echo "<script>alert('Registration successful, but failed to send verification email. Please try to resend it from the login page.'); window.location='login.html';</script>";
    }
}

if (defined('DB_CONNECTED') && DB_CONNECTED) {
    db_close($conn);
}
?>
