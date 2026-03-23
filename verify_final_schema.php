<?php
include 'D:/xampp/htdocs/SGP-vijay_new/database/db.php';
$res = mysqli_query($conn, 'DESCRIBE bookings');
$columns = [];
while($row = mysqli_fetch_assoc($res)) {
    $columns[] = $row['Field'] . ' (' . $row['Type'] . ')';
}
echo implode("\n", $columns) . "\n";
?>
