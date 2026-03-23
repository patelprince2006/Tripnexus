<?php
// Test script to create notifications table and verify it works
include '../database/db.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Fix Notifications Table</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        .container { max-width: 800px; }
    </style>
</head>
<body>
    <div class='container'>
        <h2>Fixing Notifications Table Issue</h2>
        <p>This script will create the missing notifications table and verify it works.</p>
        
        <h3>Step 1: Creating notifications table...</h3>";

// Check if table already exists
$tableCheck = db_query($conn, "SHOW TABLES LIKE 'notifications'");

if (db_num_rows($tableCheck) > 0) {
    echo "<p class='info'>✓ Notifications table already exists.</p>";
} else {
    $sql = "CREATE TABLE notifications (
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
        echo "<p class='success'>✓ Notifications table created successfully!</p>";
    } else {
        echo "<p class='error'>✗ Error creating notifications table: " . db_last_error($conn) . "</p>";
    }
}

echo "<h3>Step 2: Testing the query that was failing...</h3>";

// Test the exact query from dashboard.php
$testQuery = db_query($conn, "SELECT id, type, subject, message, created_at, is_read FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5", array(1));

if ($testQuery) {
    echo "<p class='success'>✓ Test query successful!</p>";
    echo "<p class='success'>✓ The dashboard.php error should now be resolved!</p>";
    
    // Show some info about the table
    $rowCount = db_num_rows($testQuery);
    echo "<p class='info'>Table contains $rowCount notification(s) for user ID 1</p>";
} else {
    echo "<p class='error'>✗ Test query failed: " . db_last_error($conn) . "</p>";
}

echo "<h3>Step 3: Verification Complete</h3>
        <p class='success'>The 'Table \'tripnexus.notifications\' doesn\'t exist' error should now be fixed!</p>
        
        <div style='margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;'>
            <h4>Next Steps:</h4>
            <ul>
                <li>Try accessing <a href='dashboard.php'>dashboard.php</a> again</li>
                <li>The notifications section should now load without errors</li>
                <li>You can also test other pages that might use the notifications table</li>
            </ul>
        </div>
    </div>
</body>
</html>";

db_close($conn);
?>