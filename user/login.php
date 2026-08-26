<?php
session_start();
require_once __DIR__ . '/../database/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Fetch user from database
    $result = db_prepare($conn, "login_query", 'SELECT id, fullname, password, is_verified, status FROM users WHERE email = ?');
    $result = db_execute($conn, "login_query", array($email));

    if (db_num_rows($result) > 0) {
        $row = db_fetch_assoc($result);

        // Verify the hashed password
        if (password_verify($password, $row['password'])) {
            // Check if user is verified
            if (empty($row['is_verified']) || $row['is_verified'] == 0 || $row['is_verified'] === 'false') {
                echo "<script>alert('Your account is not verified. Please verify your email code first.'); window.location='verify_email_ui.php?email=" . urlencode($email) . "';</script>";
                exit();
            }

            // Check if user is active
            if (isset($row['status']) && (strtolower($row['status']) === 'inactive' || strtolower($row['status']) === 'deactivated')) {
                echo "<script>alert('Your account has been deactivated. Please contact support.'); history.back();</script>";
                exit();
            }

            $_SESSION['user_id'] = $row['id'];
            $_SESSION['fullname'] = $row['fullname'];

            header("Location: dashboard.php");
            exit();
        } else {
            echo "<script>alert('Invalid password'); history.back();</script>";
        }
    } else {
        echo "<script>alert('No account found with this email'); history.back();</script>";
    }
}
if (defined('DB_CONNECTED') && DB_CONNECTED) {
    db_close($conn);
}
?>
