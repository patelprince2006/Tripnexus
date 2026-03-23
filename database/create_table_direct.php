<?php
// Direct table creation script
include 'db.php';

echo "Creating notifications table...\n";

$sql = "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) DEFAULT 'general',
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    email_sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notifications_user_id (user_id),
    INDEX idx_notifications_type (type)
) ENGINE=InnoDB;";

$result = db_query($conn, $sql);

if ($result) {
    echo "✓ Notifications table created successfully!\n";
    
    // Test the query that was failing
    $testQuery = db_query($conn, "SELECT id, type, subject, message, created_at, is_read FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5", array(1));
    
    if ($testQuery) {
        echo "✓ Test query successful - dashboard.php should now work!\n";
    } else {
        echo "✗ Test query failed: " . db_last_error($conn) . "\n";
    }
} else {
    echo "✗ Error creating table: " . db_last_error($conn) . "\n";
}

db_close($conn);
?>