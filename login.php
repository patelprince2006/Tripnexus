<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Fetch user from database
    $result = db_prepare($conn, "login_query", 'SELECT id, fullname, password, is_verified FROM users WHERE email = ?');
    $result = db_execute($conn, "login_query", array($email));

    if (db_num_rows($result) > 0) {
        $row = db_fetch_assoc($result);

        // Check if email is verified
        if (!$row['is_verified']) {
            $_SESSION['pending_verification_email'] = $email;
            echo "<script>alert('Please verify your email first. We sent a verification code to your email.'); window.location='verify_email.html';</script>";
            exit();
        }

        // Verify the hashed password
        if (password_verify($password, $row['password'])) {
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
db_close($conn);
?>