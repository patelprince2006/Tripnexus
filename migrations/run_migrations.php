<?php
/**
 * TripNexus - Database Migration Runner
 * Executes all pending SQL migration files
 */

include_once __DIR__ . '/../db.php';

class MigrationRunner {
    private $conn;
    private $migrationsDir;
    private $results = [];

    public function __construct($connection, $migrationsDir) {
        $this->conn = $connection;
        $this->migrationsDir = $migrationsDir;
    }

    /**
     * Run all pending migrations
     */
    public function runPendingMigrations() {
        // First, ensure migrations table exists
        $this->createMigrationsTable();

        $files = $this->getMigrationFiles();

        if (empty($files)) {
            $this->results[] = ['status' => 'info', 'message' => 'No migration files found.'];
            return $this->results;
        }

        foreach ($files as $file) {
            $this->executeMigration($file);
        }

        return $this->results;
    }

    /**
     * Create migrations table if it doesn't exist
     */
    private function createMigrationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    migration_name VARCHAR(255) UNIQUE NOT NULL,
                    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB;";

        $result = db_query($this->conn, $sql);
        if (!$result) {
            $this->results[] = [
                'status' => 'error',
                'message' => 'Failed to create migrations table: ' . db_last_error($this->conn)
            ];
        }
    }

    /**
     * Get all SQL migration files sorted by name
     */
    private function getMigrationFiles() {
        $files = scandir($this->migrationsDir);
        $sqlFiles = array_filter($files, function($f) {
            return strpos($f, '.sql') !== false && is_file($this->migrationsDir . '/' . $f);
        });
        sort($sqlFiles);
        return array_values($sqlFiles);
    }

    /**
     * Execute a single migration file
     */
    private function executeMigration($filename) {
        $migrationName = pathinfo($filename, PATHINFO_FILENAME);
        $filepath = $this->migrationsDir . '/' . $filename;

        // Check if migration already executed
        $checkQuery = db_query(
            $this->conn,
            "SELECT id FROM migrations WHERE migration_name = ?",
            array($migrationName)
        );

        if (db_num_rows($checkQuery) > 0) {
            $this->results[] = [
                'status' => 'skip',
                'message' => "-> Already executed: $migrationName"
            ];
            return;
        }

        // Read and execute SQL file
        $sql = file_get_contents($filepath);

        if (!$sql) {
            $this->results[] = [
                'status' => 'error',
                'message' => "Failed to read: $migrationName"
            ];
            return;
        }

        // Split into individual statements (simple splitter for these migrations)
        $statements = preg_split('/;\s*/', $sql);
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement === '') {
                continue;
            }
            $result = db_query($this->conn, $statement);
            if (!$result) {
                $this->results[] = [
                    'status' => 'error',
                    'message' => "Failed to execute: $migrationName - " . db_last_error($this->conn)
                ];
                return;
            }
        }

        // Record migration as executed
        $insertQuery = db_query(
            $this->conn,
            "INSERT INTO migrations (migration_name) VALUES (?)",
            array($migrationName)
        );

        if ($insertQuery) {
            $this->results[] = [
                'status' => 'success',
                'message' => "Executed: $migrationName"
            ];
        } else {
            $this->results[] = [
                'status' => 'error',
                'message' => "Failed to record: $migrationName"
            ];
        }
    }

    /**
     * Get formatted results for display
     */
    public function getFormattedResults() {
        $output = "\n" . str_repeat("=", 60) . "\n";
        $output .= "DATABASE MIGRATION RESULTS\n";
        $output .= str_repeat("=", 60) . "\n\n";

        foreach ($this->results as $result) {
            $output .= "[{$result['status']}] {$result['message']}\n";
        }

        $output .= "\n" . str_repeat("=", 60) . "\n";
        return $output;
    }
}

// Run migrations only if this file is accessed directly
if (php_sapi_name() === 'cli' || (isset($_GET['run']) && $_GET['run'] === 'true')) {
    $migrationsDir = __DIR__;
    $runner = new MigrationRunner($conn, $migrationsDir);
    $results = $runner->runPendingMigrations();

    echo $runner->getFormattedResults();

    // Close connection
    db_close($conn);
}
?>
