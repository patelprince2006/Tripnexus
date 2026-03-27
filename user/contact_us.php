<?php
require_once __DIR__ . '/../database/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $first_name = htmlspecialchars(strip_tags(trim($_POST['first_name'])));
    $last_name = htmlspecialchars(strip_tags(trim($_POST['last_name'])));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $message = htmlspecialchars(strip_tags(trim($_POST['message'])));

    if (!$email) {
        echo "<script>alert('Invalid email address.'); history.back();</script>";
        exit();
    }

    if (strlen($message) < 10) {
        echo "<script>alert('Message is too short.'); history.back();</script>";
        exit();
    }

    // Save to database
    if (defined('DB_CONNECTED') && DB_CONNECTED) {
        // Ensure table exists
        $sql_table = "CREATE TABLE IF NOT EXISTS contact_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL,
            message TEXT NOT NULL,
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;";
        db_query($conn, $sql_table);

        $query = "INSERT INTO contact_messages (first_name, last_name, email, message) VALUES (?, ?, ?, ?)";
        $result = db_query($conn, $query, array($first_name, $last_name, $email, $message));

        if ($result) {
            echo "<script>alert('Message sent successfully! We will get back to you soon.'); window.location='../index.php';</script>";
        } else {
            echo "<script>alert('Error sending message. Please try again.'); history.back();</script>";
        }
    } else {
        echo "<script>alert('Database connection failed. Please try again later.'); history.back();</script>";
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>
