<?php
include 'db.php';

$columnAdds = [
    'sent_at' => "ALTER TABLE notifications ADD COLUMN sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
    'title' => "ALTER TABLE notifications ADD COLUMN title VARCHAR(100)"
];

foreach ($columnAdds as $col => $sql) {
    $check = db_query(
        $conn,
        "SELECT column_name FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'notifications' AND column_name = ?",
        [$col]
    );

    if ($check && db_num_rows($check) === 0) {
        if (db_query($conn, $sql)) {
            echo "Executed: $sql\n";
        } else {
            echo "Note/Error executing $sql: " . db_last_error($conn) . "\n";
        }
    }
}

// Make user_id nullable if it exists and is currently NOT NULL
$colInfo = db_query(
    $conn,
    "SELECT column_type, is_nullable FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'notifications' AND column_name = ?",
    ['user_id']
);

if ($colInfo && db_num_rows($colInfo) > 0) {
    $info = db_fetch_assoc($colInfo);
    if (isset($info['is_nullable']) && $info['is_nullable'] === 'NO') {
        $columnType = $info['column_type'];
        $alterSql = "ALTER TABLE notifications MODIFY user_id $columnType NULL";
        if (db_query($conn, $alterSql)) {
            echo "Executed: $alterSql\n";
        } else {
            echo "Note/Error executing $alterSql: " . db_last_error($conn) . "\n";
        }
    }
}
?>
