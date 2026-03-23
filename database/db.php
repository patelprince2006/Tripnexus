<?php
// ===================================================================
// TripNexus — MySQL Database Connection (XAMPP)
// ===================================================================

$host     = "localhost";
$port     = 3306;
$dbname   = "tripnexus";
$user     = "root";
$password = "";

// Connect to MySQL (Initially without database to ensure it exists)
$conn = mysqli_connect($host, $user, $password, "", $port);

// Check connection
if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

// 1. Create Database if not exists
mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

// 2. Select the database
mysqli_select_db($conn, $dbname);

// 3. Auto-Setup Tables and Sample Data if 'users' table is missing
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($check_table) == 0) {
    $sql_file = __DIR__ . '/tripnexus_database.sql';
    if (file_exists($sql_file)) {
        $sql_content = file_get_contents($sql_file);
        // Execute multi-query
        if (mysqli_multi_query($conn, $sql_content)) {
            // Flush multi-results
            do {
                if ($res = mysqli_store_result($conn)) {
                    mysqli_free_result($res);
                }
            } while (mysqli_more_results($conn) && mysqli_next_result($conn));
        }
    }
}

// Set a connection flag for scripts
$DB_CONNECTED = true;
if (!defined('DB_CONNECTED')) {
    define('DB_CONNECTED', true);
}

// Set character set
mysqli_set_charset($conn, "utf8mb4");

// Optional: confirm connection (remove in production)
// echo "Connected successfully!";

// Global array for prepared statements
$prepared_statements = array();

// Helper functions for database operations
function db_query($conn, $query, $params = null) {
    if ($params) {
        try {
            $stmt = mysqli_prepare($conn, $query);
            if (!$stmt) {
                die("Prepare failed: " . mysqli_error($conn));
            }
            // Bind parameters
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            // For non-SELECT queries, mysqli_stmt_get_result returns false.
            // In that case, we should return true if execute was successful.
            if ($result === false && mysqli_stmt_errno($stmt) === 0) {
                return true;
            }
            return $result;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    } else {
        try {
            return mysqli_query($conn, $query);
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }
}

function db_prepare($conn, $name, $query) {
    global $prepared_statements;
    $prepared_statements[$name] = mysqli_prepare($conn, $query);
    if (!$prepared_statements[$name]) {
        die("Prepare failed for $name: " . mysqli_error($conn));
    }
    return $prepared_statements[$name];
}

function db_execute($conn, $name, $params) {
    global $prepared_statements;
    $stmt = $prepared_statements[$name];
    if (!$stmt) {
        die("Statement $name not prepared");
    }

    // Determine types
    $types = '';
    foreach ($params as $param) {
        if (is_int($param)) {
            $types .= 'i';
        } elseif (is_float($param)) {
            $types .= 'd';
        } else {
            $types .= 's';
        }
    }

    try {
        if (!mysqli_stmt_bind_param($stmt, $types, ...$params)) {
            return false;
        }

        if (!mysqli_stmt_execute($stmt)) {
            return false;
        }

        // SELECT and similar statements return a result set; INSERT/UPDATE/DELETE do not.
        $result = mysqli_stmt_get_result($stmt);
        if ($result === false) {
            // No result set (common for INSERT/UPDATE/DELETE) but execution succeeded.
            return true;
        }
        return $result;
    } catch (mysqli_sql_exception $e) {
        return false;
    }
}

function db_fetch_assoc($result) {
    if (!$result || !($result instanceof mysqli_result)) return null;
    return mysqli_fetch_assoc($result);
}

function db_num_rows($result) {
    if (!$result || !($result instanceof mysqli_result)) return 0;
    return mysqli_num_rows($result);
}

function db_last_error($conn) {
    return mysqli_error($conn);
}

function db_fetch_value($result, $row = 0, $col = 0) {
    if (!$result || mysqli_num_rows($result) <= $row) return null;
    mysqli_data_seek($result, $row);
    $data = mysqli_fetch_row($result);
    return $data[$col] ?? null;
}

function db_close($conn) {
    if ($conn) {
        mysqli_close($conn);
    }
}

?>
