<?php
// query_airlines.php — queries `airlines` table and prints JSON
require __DIR__ . '/db.php';

header('Content-Type: application/json');

$res = pg_query($conn, "SELECT airline_id, airline_name, airline_logo FROM airlines ORDER BY airline_id");
if (!$res) {
    http_response_code(500);
    echo json_encode(["error" => pg_last_error($conn)]);
    exit;
}

$rows = [];
while ($row = pg_fetch_assoc($res)) {
    $rows[] = $row;
}

echo json_encode(["count" => count($rows), "rows" => $rows], JSON_PRETTY_PRINT);

pg_close($conn);
?>