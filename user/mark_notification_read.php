<?php
session_start();
require_once __DIR__ . '/../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

if (isset($_GET['id'])) {
    $notif_id = (int)$_GET['id'];
    $user_id = (int)$_SESSION['user_id'];

    db_query($conn, "UPDATE notifications SET is_read = true WHERE id = ? AND user_id = ?", [$notif_id, $user_id]);
}

if (isset($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    header("Location: dashboard.php");
}
exit();
?>
