<?php
session_start();
include '../database/db.php';
require_once '../email/EmailService.php';
require_once '../email/mail_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = trim($_POST['token']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($token) || empty($password) || empty($confirm_password)) {
        echo "<script>alert('All fields are required'); history.back();</script>";
        exit();
    }

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match.'); history.back();</script>";
        exit();
    }

    // Password strength validation
    if (strlen($password) < 8) {
        echo "<script>alert('Password must be at least 8 characters long.'); history.back();</script>";
        exit();
    }
    if (!preg_match('/[A-Z]/', $password)) {
        echo "<script>alert('Password must contain at least one uppercase letter (A-Z).'); history.back();</script>";
        exit();
    }
    if (!preg_match('/[a-z]/', $password)) {
        echo "<script>alert('Password must contain at least one lowercase letter (a-z).'); history.back();</script>";
        exit();
    }
    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\x27:"\\\\|,.<>\/?~`]/', $password)) {
        echo "<script>alert('Password must contain at least one special character (!@#\$%^&* etc.).'); history.back();</script>";
        exit();
    }

    // Check if database is connected
    if (!DB_CONNECTED) {
        echo "<script>alert('Database connection failed. Please try again later.'); history.back();</script>";
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Find user by reset token and check expiry
    $checkQuery = db_prepare($conn, "check_token", 
        'SELECT id, email, fullname FROM users WHERE reset_token = ? AND token_expiry > NOW()');
    $checkResult = db_execute($conn, "check_token", array($token));

    if (!$checkResult || db_num_rows($checkResult) === 0) {
        echo "<script>alert('Invalid or expired reset link. Please request a new one.'); window.location='forgot_password.html';</script>";
        exit();
    }

    $user = db_fetch_assoc($checkResult);
    $userId = $user['id'];
    $userEmail = $user['email'];
    $fullname = $user['fullname'];

    // Update password and clear reset token
    $updateQuery = db_prepare($conn, "update_password", 
        'UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE id = ?');
    $updateResult = db_execute($conn, "update_password", array($hashed_password, $userId));

    if ($updateResult) {
        // Send confirmation email
        $emailService = new EmailService($conn);
        $emailService->saveNotification($userId, 'password_reset', 'Password Updated Successfully', 
            'Your password has been successfully updated.');

        echo "<script>alert('Password updated successfully! You can now login with your new password.'); window.location='login.html';</script>";
    } else {
        echo "<script>alert('Error updating password. Please try again.'); history.back();</script>";
    }
}

if (DB_CONNECTED) {
    db_close($conn);
}
?>