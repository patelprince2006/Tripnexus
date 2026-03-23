<?php
/**
 * Fix Notifications Table - Creates the missing notifications table
 */

include '../database/db.php';

// Check if notifications table exists
$tableCheck = db_query($conn, "SHOW TABLES LIKE 'notifications'");

if (db_num_rows($tableCheck) > 0) {
    echo "✓ Notifications table already exists.\n";
} else {
    echo "Creating notifications table...\n";
    
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
        echo "✓ Notifications table created successfully!\n";
    } else {
        echo "✗ Error creating notifications table: " . db_last_error($conn) . "\n";
    }
}

// Test the table creation
$testQuery = db_query($conn, "SELECT id, type, subject, message, created_at, is_read FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5", array(1));

if ($testQuery) {
    echo "✓ Table structure is correct and queryable.\n";
    echo "✓ The dashboard.php error should now be resolved!\n";
} else {
    echo "✗ Table test failed: " . db_last_error($conn) . "\n";
}

db_close($conn);
?>