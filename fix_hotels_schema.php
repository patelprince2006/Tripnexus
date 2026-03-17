<?php
include 'db.php';

$columnAdds = [
    'description' => "ALTER TABLE hotels ADD COLUMN description TEXT",
    'main_image' => "ALTER TABLE hotels ADD COLUMN main_image TEXT"
];

foreach ($columnAdds as $col => $sql) {
    $check = db_query(
        $conn,
        "SELECT column_name FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'hotels' AND column_name = ?",
        [$col]
    );

    if ($check && db_num_rows($check) === 0) {
        if (db_query($conn, $sql)) {
            echo "Executed: $sql\n";
        } else {
            echo "Error executing $sql: " . db_last_error($conn) . "\n";
        }
    }
}
?>
