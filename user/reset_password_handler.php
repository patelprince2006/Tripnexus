<?php
session_start();
include '../database/db.php';
require_once '../email/EmailService.php';
require_once '../email/mail_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL);
    
    if (!$email) {
        echo "<script>alert('Invalid email address'); history.back();</script>";
        exit();
    }

    // Check if database is connected
    if (!DB_CONNECTED) {
        echo "<script>alert('Database connection failed. Please try again later.'); history.back();</script>";
        exit();
    }

    // Check if email exists
    $check_query = db_prepare($conn, "check_email", 'SELECT id, fullname FROM users WHERE email = ?');
    $result = db_execute($conn, "check_email", array($email));
    
    if (db_num_rows($result) > 0) {
        $user = db_fetch_assoc($result);
        $userId = $user['id'];
        $fullname = $user['fullname'];

        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));
        
        // Update database with token
        $update_query = db_prepare($conn, "update_token", 'UPDATE users SET reset_token = ?, token_expiry = ? WHERE id = ?');
        $updateResult = db_execute($conn, "update_token", array($token, $expiry, $userId));

        if ($updateResult) {
            // Send password reset email
            $emailService = new EmailService($conn);
            $resetLink = APP_URL . '/new_password.html?token=' . $token;
            $emailSent = $emailService->sendPasswordResetEmail($email, $fullname, $token);

            if ($emailSent) {
                // Save notification
                $emailService->saveNotification($userId, 'password_reset', 'Password Reset Requested', 
                    'A password reset link has been sent to ' . $email);

                echo "<script>alert('Reset link sent to your email! Check your inbox.'); window.location='login.html';</script>";
            } else {
                echo "<script>alert('Failed to send reset email. Please try again.'); history.back();</script>";
            }
        } else {
            echo "<script>alert('Error processing your request. Please try again.'); history.back();</script>";
        }
    } else {
        echo "<script>alert('No account found with this email'); history.back();</script>";
    }
}

if (DB_CONNECTED) {
    db_close($conn);
}
?>