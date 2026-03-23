<?php
include 'database/db.php';
$tables = ['buses', 'trains', 'hotels'];
foreach ($tables as $t) {
    $res = db_query($conn, "SELECT count(*) as count FROM $t");
    if ($res) {
        $row = db_fetch_assoc($res);
        echo "Table $t has " . $row['count'] . " rows.\n";
    } else {
        echo "Error querying table $t\n";
    }
}
?>
