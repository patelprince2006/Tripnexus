<?php
/**
 * Fix Wishlist Table - Creates the missing wishlist table
 */

include 'db.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Fix Wishlist Table</title>
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
        <h2>Fixing Wishlist Table Issue</h2>
        <p>This script will create the missing wishlist table that search_flight.php needs.</p>
        
        <h3>Step 1: Creating wishlist table...</h3>";

// Check if table already exists
$tableCheck = db_query($conn, "SHOW TABLES LIKE 'wishlist'");

if (db_num_rows($tableCheck) > 0) {
    echo "<p class='info'>✓ Wishlist table already exists.</p>";
} else {
    $sql = "CREATE TABLE wishlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        item_type ENUM('flight', 'bus', 'train', 'hotel', 'tour') NOT NULL,
        item_id INT NOT NULL,
        item_name VARCHAR(255),
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_wishlist (user_id, item_type, item_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;";
    
    $result = db_query($conn, $sql);
    
    if ($result) {
        echo "<p class='success'>✓ Wishlist table created successfully!</p>";
    } else {
        echo "<p class='error'>✗ Error creating wishlist table: " . db_last_error($conn) . "</p>";
    }
}

echo "<h3>Step 2: Testing the query that was failing...</h3>";

// Test the exact query from search_flight.php
$testQuery = db_query($conn, "SELECT item_id FROM wishlist WHERE user_id = ? AND item_type = 'flight'", array(1));

if ($testQuery) {
    echo "<p class='success'>✓ Test query successful!</p>";
    echo "<p class='success'>✓ The search_flight.php error should now be resolved!</p>";
    
    // Show some info about the table
    $rowCount = db_num_rows($testQuery);
    echo "<p class='info'>Table contains $rowCount wishlist item(s) for user ID 1</p>";
} else {
    echo "<p class='error'>✗ Test query failed: " . db_last_error($conn) . "</p>";
}

echo "<h3>Step 3: Verification Complete</h3>
        <p class='success'>The 'Table \'tripnexus.wishlist\' doesn\'t exist' error should now be fixed!</p>
        
        <div style='margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;'>
            <h4>Next Steps:</h4>
            <ul>
                <li>Try accessing <a href='search_flight.php'>search_flight.php</a> again</li>
                <li>The wishlist functionality should now work properly</li>
                <li>Users can now add/remove flights from their wishlist</li>
            </ul>
        </div>
    </div>
</body>
</html>";

db_close($conn);
?>