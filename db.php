<?php
// Supabase Connection Details
$host = "db.ckfsjobfcpyamxejnjbu.supabase.co";
$port = "5432";
$dbname = "postgres";          
$user = "postgres";
$password = "Shivam1105#";

// Correct connection string
$connection_string = "host=$host port=$port dbname=$dbname user=$user password=$password sslmode=require";

// Establish PostgreSQL connection
$conn = pg_connect($connection_string);

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}
?>
