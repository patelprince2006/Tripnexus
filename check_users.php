<?php
include 'db.php';
$result = db_query($conn, 'SELECT id, fullname, email FROM users ORDER BY id LIMIT 5');
echo 'Available users:<br>';
while ($row = db_fetch_assoc($result)) {
    echo 'ID: ' . $row['id'] . ', Name: ' . $row['fullname'] . ', Email: ' . $row['email'] . '<br>';
}
?>