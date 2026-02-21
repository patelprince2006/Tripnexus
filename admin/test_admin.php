<?php
// Test script to verify admin user creation
include '../db.php';

echo "<h2>Testing Admin User Creation</h2>";

// Test 1: Check if admins table exists
echo "<h3>Test 1: Checking if admins table exists</h3>";
$result = pg_query($conn, "SELECT to_regclass('admins')");
if ($result && pg_fetch_result($result, 0, 0)) {
    echo "<p style='color: green;'>✓ Admins table exists</p>";
} else {
    echo "<p style='color: red;'>✗ Admins table does not exist</p>";
    echo "<p style='color: blue;'>💡 To fix this issue, please run: <a href='setup_admin_db.php'>setup_admin_db.php</a></p>";
}

// Test 2: Check if default admin user exists
echo "<h3>Test 2: Checking if default admin user exists</h3>";
$result = pg_query($conn, "SELECT * FROM admins WHERE username = 'admin'");
if ($result && pg_num_rows($result) > 0) {
    $admin = pg_fetch_assoc($result);
    echo "<p style='color: green;'>✓ Default admin user exists (ID: {$admin['id']}, Username: {$admin['username']})</p>";
} else {
    echo "<p style='color: red;'>✗ Default admin user does not exist</p>";
}

// Test 3: Test database connection with admin query
echo "<h3>Test 3: Testing database connection with admin query</h3>";
$result = pg_query($conn, "SELECT COUNT(*) as total FROM admins");
if ($result) {
    $count = pg_fetch_assoc($result);
    echo "<p style='color: green;'>✓ Database connection working. Total admins: {$count['total']}</p>";
} else {
    echo "<p style='color: red;'>✗ Database connection failed: " . pg_last_error($conn) . "</p>";
}

echo "<h3>Test Complete</h3>";
echo "<p><a href='login.php'>Go to Admin Login</a></p>";
echo "<p><a href='../index.php'>Back to Website</a></p>";
?>