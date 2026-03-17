<?php
include 'db.php';
$query = "SELECT column_name, data_type 
          FROM information_schema.columns 
          WHERE table_name = 'notifications'
          ORDER BY ordinal_position";
$result = db_query($conn, $query);
if (!$result) {
    echo "Error checking schema: " . db_last_error($conn);
} else {
    while ($row = db_fetch_assoc($result)) {
        echo $row['column_name'] . " | " . $row['data_type'] . "\n";
    }
}
?>
