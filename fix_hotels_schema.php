<?php
include 'db.php';

$queries = [
    "ALTER TABLE hotels ADD COLUMN IF NOT EXISTS description TEXT",
    "ALTER TABLE hotels ADD COLUMN IF NOT EXISTS main_image TEXT"
];

foreach ($queries as $sql) {
    if (pg_query($conn, $sql)) {
        echo "Executed: $sql\n";
    } else {
        echo "Error executing $sql: " . pg_last_error($conn) . "\n";
    }
}
?>
