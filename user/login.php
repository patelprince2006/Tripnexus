<?php
session_start();
require_once __DIR__ . '/../database/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Fetch user from database
    $result = db_prepare($conn, "login_query", 'SELECT id, fullname, password FROM users WHERE email = ?');
    $result = db_execute($conn, "login_query", array($email));

    if (db_num_rows($result) > 0) {
        $row = db_fetch_assoc($result);

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
if (defined('DB_CONNECTED') && DB_CONNECTED) {
    db_close($conn);
}
?>
