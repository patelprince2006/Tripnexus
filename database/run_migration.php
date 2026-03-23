<?php
/**
 * Simple migration runner for the notifications table
 */

// Set content type for browser output
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Running Migration</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
    </style>
</head>
<body>
    <h2>Running Database Migration</h2>
    <div id='output'>";

include 'db.php';

// Check if notifications table exists
$tableCheck = db_query($conn, "SHOW TABLES LIKE 'notifications'");

if (db_num_rows($tableCheck) > 0) {
    echo "<p class='info'>✓ Notifications table already exists.</p>";
} else {
    echo "<p class='info'>Creating notifications table...</p>";
    
    $createTableSQL = "
    CREATE TABLE `notifications` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `type` VARCHAR(50) DEFAULT 'general',
        `subject` VARCHAR(255) NOT NULL,
        `message` TEXT NOT NULL,
        `is_read` TINYINT(1) DEFAULT 0,
        `email_sent_at` TIMESTAMP NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        INDEX `idx_notifications_user_id` (`user_id`),
        INDEX `idx_notifications_type` (`type`)
    ) ENGINE=InnoDB;
    ";
    
    $result = db_query($conn, $createTableSQL);
    
    if ($result) {
        echo "<p class='success'>✓ Notifications table created successfully!</p>";
    } else {
        echo "<p class='error'>✗ Error creating notifications table: " . db_last_error($conn) . "</p>";
    }
}

// Test the table creation
$testQuery = db_query($conn, "SELECT id, type, subject, message, created_at, is_read FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5", array(1));

if ($testQuery) {
    echo "<p class='success'>✓ Table structure is correct and queryable.</p>";
    echo "<p class='success'>✓ The dashboard.php error should now be resolved!</p>";
} else {
    echo "<p class='error'>✗ Table test failed: " . db_last_error($conn) . "</p>";
}

db_close($conn);

echo "</div>
    <p><a href='dashboard.php'>Try accessing dashboard.php again</a></p>
</body>
</html>";
?>