<?php
include 'db.php';
$res = db_query($conn, "SHOW COLUMNS FROM users");
while($row = db_fetch_assoc($res)) {
    echo $row['Field'] . "\n";
}
db_close($conn);
?>
