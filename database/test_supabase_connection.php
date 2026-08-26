<?php
// ===================================================================
// TripNexus - Supabase Connection Tester
// Project: jtsuchakskithnoohjup
// REST API: https://jtsuchakskithnoohjup.supabase.co/rest/v1/
// ===================================================================

require_once __DIR__ . '/supabase_config.php';
require_once __DIR__ . '/SupabaseClient.php';
require_once __DIR__ . '/db.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=========================================================\n";
echo "TripNexus - Supabase Connection Diagnostic\n";
echo "=========================================================\n\n";

echo "[1] Configuration Settings:\n";
echo "  - Project Ref  : " . SUPABASE_PROJECT_REF . "\n";
echo "  - Base URL     : " . SUPABASE_URL . "\n";
echo "  - REST Endpoint: " . SUPABASE_REST_URL . "\n";
echo "  - DB Host      : " . SUPABASE_DB_HOST . ":" . SUPABASE_DB_PORT . "\n";
echo "  - Active Driver: " . DB_DRIVER . "\n";
echo "  - API Key Set  : " . (SUPABASE_API_KEY !== 'YOUR_SUPABASE_ANON_KEY' ? 'YES' : 'NO (Using placeholder)') . "\n\n";

echo "[2] Testing Supabase REST API Client:\n";
$client = new SupabaseClient();

// Query 'airports' table via REST API
$res = $client->from('airports')->select('*')->limit(5)->execute();

if ($res['success']) {
    echo "  SUCCESS! Connected to Supabase REST API.\n";
    echo "  HTTP Status Code: " . $res['status'] . "\n";
    echo "  Sample Data Returned (" . count($res['data']) . " records):\n";
    foreach ($res['data'] as $item) {
        echo "    - [" . ($item['airport_code'] ?? 'N/A') . "] " . ($item['airport_name'] ?? 'N/A') . " (" . ($item['city'] ?? 'N/A') . ")\n";
    }
} else {
    echo "  NOTICE: REST API Response:\n";
    echo "  HTTP Status Code: " . $res['status'] . "\n";
    echo "  Details         : " . $res['error'] . "\n";
    if ($res['status'] == 401 || $res['status'] == 403) {
        echo "\n  TIP: Update SUPABASE_API_KEY in 'database/supabase_config.php' with your project's anon key from the Supabase Dashboard.\n";
    }
}

echo "\n[3] Testing Application Abstraction Layer (`db_query`):\n";
$db_res = db_query($conn, "SELECT COUNT(*) as cnt FROM airports");
if ($db_res) {
    $count = db_fetch_value($db_res, 0, 0);
    echo "  SUCCESS! `db_query` returned count: " . ($count ?? '0') . "\n";
} else {
    echo "  Notice: `db_query` executed via current driver.\n";
}

echo "\n=========================================================\n";
echo "Diagnostic Complete.\n";
echo "=========================================================\n";
?>
