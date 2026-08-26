<?php
// ===================================================================
// TripNexus - Supabase Connection Configuration
// Project: jtsuchakskithnoohjup
// ===================================================================

if (!defined('SUPABASE_PROJECT_REF')) {
    define('SUPABASE_PROJECT_REF', 'jtsuchakskithnoohjup');
}

if (!defined('SUPABASE_URL')) {
    define('SUPABASE_URL', 'https://jtsuchakskithnoohjup.supabase.co');
}

if (!defined('SUPABASE_REST_URL')) {
    define('SUPABASE_REST_URL', 'https://jtsuchakskithnoohjup.supabase.co/rest/v1/');
}

// -------------------------------------------------------------------
// Supabase API Key (Anon / Service Role Key)
// Replace with your actual Supabase Anon key from Supabase Dashboard:
// Settings -> API -> Project API keys (anon / public)
// -------------------------------------------------------------------
if (!defined('SUPABASE_API_KEY')) {
    define('SUPABASE_API_KEY', 'YOUR_SUPABASE_ANON_KEY');
}

// -------------------------------------------------------------------
// Direct PostgreSQL Connection Credentials (Optional)
// Settings -> Database -> Connection string
// -------------------------------------------------------------------
if (!defined('SUPABASE_DB_HOST')) {
    define('SUPABASE_DB_HOST', 'db.jtsuchakskithnoohjup.supabase.co');
}

if (!defined('SUPABASE_DB_PORT')) {
    define('SUPABASE_DB_PORT', 5432);
}

if (!defined('SUPABASE_DB_USER')) {
    define('SUPABASE_DB_USER', 'postgres');
}

if (!defined('SUPABASE_DB_PASSWORD')) {
    define('SUPABASE_DB_PASSWORD', '');
}

if (!defined('SUPABASE_DB_NAME')) {
    define('SUPABASE_DB_NAME', 'postgres');
}

// -------------------------------------------------------------------
// Active Database Driver
// Options:
//   'supabase_api'  -> Connect via Supabase REST API (https://jtsuchakskithnoohjup.supabase.co/rest/v1/)
//   'supabase_pdo'  -> Connect directly to Supabase PostgreSQL via PDO
//   'mysql'         -> Fallback to local XAMPP MySQL database
// -------------------------------------------------------------------
if (!defined('DB_DRIVER')) {
    define('DB_DRIVER', 'supabase_api');
}
?>
