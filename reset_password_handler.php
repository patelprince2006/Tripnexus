<?php
session_start();
include 'db.php';
require_once 'includes/EmailService.php';
require_once 'config/mail_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL);
    
    if (!$email) {
        echo "<script>alert('Invalid email address'); history.back();</script>";
        exit();
    }

    // Check if email exists
    $check_query = pg_prepare($conn, "check_email", 'SELECT id, fullname FROM users WHERE email = $1');
    $result = pg_execute($conn, "check_email", array($email));
    
    if (pg_num_rows($result) > 0) {
        $user = pg_fetch_assoc($result);
        $userId = $user['id'];
        $fullname = $user['fullname'];

        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));
        
        // Update database with token
        $update_query = pg_prepare($conn, "update_token", 'UPDATE users SET reset_token = $1, token_expiry = $2 WHERE id = $3');
        $updateResult = pg_execute($conn, "update_token", array($token, $expiry, $userId));

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
pg_close($conn);
?>