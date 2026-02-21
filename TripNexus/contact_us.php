<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input to remove HTML tags
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

    // Format the data string
    $timestamp = date("Y-m-d H:i:s");
    $data = "--- New Message ---\n";
    $data .= "Date: $timestamp\n";
    $data .= "Name: $first_name $last_name\n";
    $data .= "Email: $email\n";
    $data .= "Message: $message\n";
    $data .= "--------------------\n\n";

    // Write to file (FILE_APPEND keeps old messages)
    $file = 'messages.txt';
    if (file_put_contents($file, $data, FILE_APPEND)) {
        echo "<script>alert('Message saved successfully!'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Error saving message. Please try again.'); history.back();</script>";
    }
} else {
    header("Location: index.php");
    exit();
}
