<?php
/**
 * fix_users_schema.php
 * Adds missing 'phone' and 'theme' columns to the 'users' table.
 */

include 'db.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Fix Users Schema | TripNexus</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 40px; background: #f8f9fa; }
        .container { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .info { color: #007bff; }
        h2 { border-bottom: 2px solid #eee; padding-bottom: 10px; }
    </style>
</head>
<body>
    <div class='container'>
        <h2>Fixing Users Table Schema</h2>";

// 1. Check for 'phone' column
$checkPhone = db_query($conn, "SHOW COLUMNS FROM users LIKE 'phone'");
if (db_num_rows($checkPhone) == 0) {
    echo "<p class='info'>Adding 'phone' column...</p>";
    $alterPhone = db_query($conn, "ALTER TABLE users ADD COLUMN phone VARCHAR(20) DEFAULT NULL AFTER password");
    if ($alterPhone) {
        echo "<p class='success'>✓ 'phone' column added successfully.</p>";
    } else {
        echo "<p class='error'>✗ Failed to add 'phone' column: " . db_last_error($conn) . "</p>";
    }
} else {
    echo "<p class='success'>✓ 'phone' column already exists.</p>";
}

// 2. Check for 'theme' column
$checkTheme = db_query($conn, "SHOW COLUMNS FROM users LIKE 'theme'");
if (db_num_rows($checkTheme) == 0) {
    echo "<p class='info'>Adding 'theme' column...</p>";
    $alterTheme = db_query($conn, "ALTER TABLE users ADD COLUMN theme VARCHAR(20) DEFAULT 'light' AFTER phone");
    if ($alterTheme) {
        echo "<p class='success'>✓ 'theme' column added successfully.</p>";
    } else {
        echo "<p class='error'>✗ Failed to add 'theme' column: " . db_last_error($conn) . "</p>";
    }
} else {
    echo "<p class='success'>✓ 'theme' column already exists.</p>";
}

echo "<h3>Status: Complete</h3>
        <p>The users table should now be compatible with the new settings page.</p>
        <p><a href='../user/settings.php'>Go to Settings Page</a></p>
    </div>
</body>
</html>";

db_close($conn);
?>
