<?php
include '../database/db.php';

$result = db_query($conn, 'DESCRIBE wishlist');
if ($result) {
    echo "Wishlist table columns:\n";
    while ($row = db_fetch_assoc($result)) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
} else {
    echo 'Table does not exist or error: ' . db_last_error($conn);
}
?>