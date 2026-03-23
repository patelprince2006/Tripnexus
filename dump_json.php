<?php
include 'D:/xampp/htdocs/SGP-vijay_new/database/db.php';
$res = mysqli_query($conn, 'DESCRIBE bookings');
$columns = [];
while($row = mysqli_fetch_assoc($res)) {
    $columns[] = $row;
}
file_put_contents('D:/xampp/htdocs/SGP-vijay_new/schema_dump.json', json_encode($columns, JSON_PRETTY_PRINT));
?>
