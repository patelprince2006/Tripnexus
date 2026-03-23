<?php
// Test script to verify admin user creation
include '../database/db.php';

echo "<h2>Testing Admin User Creation</h2>";

// Test 1: Check if admins table exists
echo "<h3>Test 1: Checking if admins table exists</h3>";
$result = db_query($conn, "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'admins'");
$table_exists = $result ? (int) db_fetch_value($result, 0, 0) : 0;
if ($table_exists > 0) {
    echo "<p style='color: green;'>âœ“ Admins table exists</p>";
} else {
    echo "<p style='color: red;'>âœ— Admins table does not exist</p>";
    echo "<p style='color: blue;'>ðŸ’¡ To fix this issue, please run: <a href='setup_admin_db.php'>setup_admin_db.php</a></p>";
}

// Test 2: Check if default admin user exists
echo "<h3>Test 2: Checking if default admin user exists</h3>";
$result = db_query($conn, "SELECT * FROM admins WHERE username = 'admin'");
if ($result && db_num_rows($result) > 0) {
    $admin = db_fetch_assoc($result);
    echo "<p style='color: green;'>âœ“ Default admin user exists (ID: {$admin['id']}, Username: {$admin['username']})</p>";
} else {
    echo "<p style='color: red;'>âœ— Default admin user does not exist</p>";
}

// Test 3: Test database connection with admin query
echo "<h3>Test 3: Testing database connection with admin query</h3>";
$result = db_query($conn, "SELECT COUNT(*) as total FROM admins");
if ($result) {
    $count = db_fetch_assoc($result);
    echo "<p style='color: green;'>âœ“ Database connection working. Total admins: {$count['total']}</p>";
} else {
    echo "<p style='color: red;'>âœ— Database connection failed: " . db_last_error($conn) . "</p>";
}

echo "<h3>Test Complete</h3>";
echo "<p><a href='login.php'>Go to Admin Login</a></p>";
echo "<p><a href='../index.php'>Back to Website</a></p>";
?>

