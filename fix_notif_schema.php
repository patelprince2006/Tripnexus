<?php
include 'db.php';

$queries = [
    "ALTER TABLE notifications ADD COLUMN IF NOT EXISTS sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
    "ALTER TABLE notifications ADD COLUMN IF NOT EXISTS title VARCHAR(100)",
    "ALTER TABLE notifications ALTER COLUMN user_id DROP NOT NULL"
];

foreach ($queries as $sql) {
    if (pg_query($conn, $sql)) {
        echo "Executed: $sql\n";
    } else {
        echo "Note/Error executing $sql: " . pg_last_error($conn) . "\n";
    }
}
?>
