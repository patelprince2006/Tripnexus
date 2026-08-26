<?php
// ===================================================================
// TripNexus — Unified Database Connection Layer (Supabase & MySQL)
// Project: jtsuchakskithnoohjup
// REST API: https://jtsuchakskithnoohjup.supabase.co/rest/v1/
// ===================================================================

require_once __DIR__ . '/supabase_config.php';
require_once __DIR__ . '/SupabaseClient.php';

date_default_timezone_set('Asia/Kolkata');

$active_driver = defined('DB_DRIVER') ? DB_DRIVER : 'supabase_api';

// Global connection objects
$conn = null;
$pdo = null;
$supabaseClient = new SupabaseClient();
$using_supabase = false;

// 1. Attempt Supabase PDO connection if configured
if ($active_driver === 'supabase_pdo' && !empty(SUPABASE_DB_PASSWORD)) {
    try {
        $dsn = "pgsql:host=" . SUPABASE_DB_HOST . ";port=" . SUPABASE_DB_PORT . ";dbname=" . SUPABASE_DB_NAME . ";sslmode=require";
        $pdo = new PDO($dsn, SUPABASE_DB_USER, SUPABASE_DB_PASSWORD, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $using_supabase = true;
    } catch (PDOException $e) {
        error_log("Supabase PDO connection failed: " . $e->getMessage() . " - Falling back to MySQL");
    }
}

// 2. MySQL fallback or default MySQL driver
$host     = "localhost";
$port     = 3306;
$dbname   = "tripnexus";
$user     = "root";
$password = "";

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @mysqli_connect($host, $user, $password, "", $port);

if ($conn) {
    mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    mysqli_select_db($conn, $dbname);
    mysqli_query($conn, "SET time_zone = '+05:30'");
    mysqli_set_charset($conn, "utf8mb4");

    // Perform one-time setup if lock file does not exist
    $setup_lock_file = __DIR__ . '/.setup_done';
    if (!file_exists($setup_lock_file)) {
        $check_users_table = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
        if (mysqli_num_rows($check_users_table) == 0) {
            $sql_file = __DIR__ . '/tripnexus_database.sql';
            if (file_exists($sql_file)) {
                $sql_content = file_get_contents($sql_file);
                if (mysqli_multi_query($conn, $sql_content)) {
                    do {
                        if ($res = mysqli_store_result($conn)) {
                            mysqli_free_result($res);
                        }
                    } while (mysqli_more_results($conn) && mysqli_next_result($conn));
                }
            }
        }
        file_put_contents($setup_lock_file, date('Y-m-d H:i:s'));
    }
}

$DB_CONNECTED = true;
if (!defined('DB_CONNECTED')) {
    define('DB_CONNECTED', true);
}

// Global prepared statements array
$prepared_statements = array();

function db_prepare($conn, $stmt_name, $query) {
    global $prepared_statements;
    $prepared_statements[$stmt_name] = $query;
    return true;
}

function db_execute($conn, $stmt_name, $params = array()) {
    global $prepared_statements;
    if (!isset($prepared_statements[$stmt_name])) {
        return false;
    }
    $query = $prepared_statements[$stmt_name];
    return db_query($conn, $query, $params);
}

// ===================================================================
// Helper functions for database operations
// Compatible with both MySQL (mysqli) and Supabase (PDO / REST API)
// ===================================================================

function db_query($conn, $query, $params = null) {
    global $pdo;

    // Use Supabase PDO if active
    if ($pdo !== null) {
        try {
            // Convert MySQL backtick identifier syntax to PostgreSQL quotes
            $pg_query = str_replace('`', '"', $query);
            $stmt = $pdo->prepare($pg_query);
            $stmt->execute($params ?? []);
            return new SupabaseResultWrapper($stmt);
        } catch (PDOException $e) {
            error_log("Supabase PDO Query Error: " . $e->getMessage());
            return false;
        }
    }

    // Default MySQL execution
    if ($conn) {
        if ($params) {
            try {
                $stmt = mysqli_prepare($conn, $query);
                if (!$stmt) {
                    return false;
                }
                $types = '';
                foreach ($params as $param) {
                    if (is_int($param))        $types .= 'i';
                    elseif (is_float($param))  $types .= 'd';
                    else                       $types .= 's';
                }
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                $exec_ok = mysqli_stmt_execute($stmt);
                $GLOBALS['db_last_affected_rows'] = mysqli_stmt_affected_rows($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if ($result === false && mysqli_stmt_errno($stmt) === 0) {
                    return $exec_ok;
                }
                return $result;
            } catch (Exception $e) {
                return false;
            }
        } else {
            try {
                return mysqli_query($conn, $query);
            } catch (Exception $e) {
                return false;
            }
        }
    }
    return false;
}

class SupabaseResultWrapper {
    private PDOStatement $stmt;
    private ?array $rows = null;
    private int $pointer = 0;

    public function __construct(PDOStatement $stmt) {
        $this->stmt = $stmt;
        if ($stmt->columnCount() > 0) {
            $this->rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function fetchAssoc(): ?array {
        if ($this->rows === null || $this->pointer >= count($this->rows)) {
            return null;
        }
        return $this->rows[$this->pointer++];
    }

    public function numRows(): int {
        return $this->rows !== null ? count($this->rows) : $this->stmt->rowCount();
    }

    public function fetchValue(int $row = 0, int $col = 0) {
        if ($this->rows === null || isset($this->rows[$row]) === false) {
            return null;
        }
        $keys = array_keys($this->rows[$row]);
        $colKey = $keys[$col] ?? null;
        return $colKey !== null ? $this->rows[$row][$colKey] : null;
    }
}

function db_fetch_assoc($result) {
    if (!$result) return null;
    if ($result instanceof SupabaseResultWrapper) {
        return $result->fetchAssoc();
    }
    if ($result instanceof mysqli_result) {
        return mysqli_fetch_assoc($result);
    }
    if (is_array($result)) {
        static $array_pointers = [];
        $key = spl_object_hash((object)$result);
        $ptr = $array_pointers[$key] ?? 0;
        if ($ptr < count($result)) {
            $array_pointers[$key] = $ptr + 1;
            return $result[$ptr];
        }
        return null;
    }
    return null;
}

function db_num_rows($result) {
    if (!$result) return 0;
    if ($result instanceof SupabaseResultWrapper) {
        return $result->numRows();
    }
    if ($result instanceof mysqli_result) {
        return mysqli_num_rows($result);
    }
    if (is_array($result)) {
        return count($result);
    }
    return 0;
}

function db_affected_rows($conn) {
    global $pdo;
    if ($pdo !== null) {
        return 1; // Handled by PDO
    }
    if (isset($GLOBALS['db_last_affected_rows'])) {
        return $GLOBALS['db_last_affected_rows'];
    }
    if ($conn) {
        return mysqli_affected_rows($conn);
    }
    return 0;
}

function db_last_error($conn) {
    global $pdo;
    if ($pdo !== null) {
        $err = $pdo->errorInfo();
        return $err[2] ?? '';
    }
    if ($conn) {
        return mysqli_error($conn);
    }
    return '';
}

function db_fetch_value($result, $row = 0, $col = 0) {
    if (!$result) return null;
    if ($result instanceof SupabaseResultWrapper) {
        return $result->fetchValue($row, $col);
    }
    if ($result instanceof mysqli_result) {
        if (mysqli_num_rows($result) <= $row) return null;
        mysqli_data_seek($result, $row);
        $data = mysqli_fetch_row($result);
        return $data[$col] ?? null;
    }
    return null;
}

function db_close($conn) {
    global $pdo;
    $pdo = null;
    if ($conn) {
        mysqli_close($conn);
    }
}
?>
