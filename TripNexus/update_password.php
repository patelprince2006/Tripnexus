<?php
session_start();
include 'db.php';
require_once 'includes/EmailService.php';
require_once 'config/mail_config.php';

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

    if (strlen($password) < 8) {
        echo "<script>alert('Password must be at least 8 characters long.'); history.back();</script>";
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Find user by reset token and check expiry
    $checkQuery = pg_prepare($conn, "check_token", 
        'SELECT id, email, fullname FROM users WHERE reset_token = $1 AND token_expiry > NOW()');
    $checkResult = pg_execute($conn, "check_token", array($token));

    if (pg_num_rows($checkResult) === 0) {
        echo "<script>alert('Invalid or expired reset link. Please request a new one.'); window.location='forgot_password.html';</script>";
        exit();
    }

    $user = pg_fetch_assoc($checkResult);
    $userId = $user['id'];
    $userEmail = $user['email'];
    $fullname = $user['fullname'];

    // Update password and clear reset token
    $updateQuery = pg_prepare($conn, "update_password", 
        'UPDATE users SET password = $1, reset_token = NULL, token_expiry = NULL WHERE id = $2');
    $updateResult = pg_execute($conn, "update_password", array($hashed_password, $userId));

    if ($updateResult && pg_affected_rows($updateResult) > 0) {
        // Send confirmation email
        $emailService = new EmailService($conn);
        $emailService->saveNotification($userId, 'password_reset', 'Password Updated Successfully', 
            'Your password has been successfully updated.');

        echo "<script>alert('Password updated successfully! You can now login with your new password.'); window.location='login.html';</script>";
    } else {
        echo "<script>alert('Error updating password. Please try again.'); history.back();</script>";
    }
}
pg_close($conn);
?>