<?php
require_once 'database/db.php';

echo "Setting up tour schedules...<br><br>";

// Create tour_schedules table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS `tour_schedules` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tour_id` INT NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `available_seats` INT DEFAULT 20,
    `price` DECIMAL(10, 2) NOT NULL,
    `status` ENUM('available', 'full', 'cancelled') DEFAULT 'available',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tour_id`) REFERENCES `tour_packages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB";

if (db_query($conn, $create_table_sql)) {
    echo "✓ tour_schedules table created or already exists<br>";
} else {
    echo "✗ Error creating tour_schedules table: " . db_last_error($conn) . "<br>";
}

// Insert sample tour schedules
$schedules = [
    [1, date('Y-m-d', strtotime('+7 days')), date('Y-m-d', strtotime('+11 days')), 15, 25000.00, 'available'],
    [1, date('Y-m-d', strtotime('+14 days')), date('Y-m-d', strtotime('+18 days')), 20, 25000.00, 'available'],
    [1, date('Y-m-d', strtotime('+21 days')), date('Y-m-d', strtotime('+25 days')), 10, 25000.00, 'available'],
    [2, date('Y-m-d', strtotime('+5 days')), date('Y-m-d', strtotime('+8 days')), 12, 18000.00, 'available'],
    [2, date('Y-m-d', strtotime('+12 days')), date('Y-m-d', strtotime('+15 days')), 18, 18000.00, 'available'],
    [3, date('Y-m-d', strtotime('+3 days')), date('Y-m-d', strtotime('+5 days')), 20, 12000.00, 'available'],
    [3, date('Y-m-d', strtotime('+10 days')), date('Y-m-d', strtotime('+12 days')), 15, 12000.00, 'available'],
    [3, date('Y-m-d', strtotime('+17 days')), date('Y-m-d', strtotime('+19 days')), 10, 12000.00, 'available'],
    [4, date('Y-m-d', strtotime('+20 days')), date('Y-m-d', strtotime('+26 days')), 8, 35000.00, 'available'],
    [5, date('Y-m-d', strtotime('+8 days')), date('Y-m-d', strtotime('+13 days')), 12, 30000.00, 'available'],
    [6, date('Y-m-d', strtotime('+15 days')), date('Y-m-d', strtotime('+19 days')), 16, 28000.00, 'available']
];

$inserted = 0;
foreach ($schedules as $schedule) {
    $check = db_query($conn, "SELECT id FROM tour_schedules WHERE tour_id = ? AND start_date = ?", [$schedule[0], $schedule[1]]);
    if (db_num_rows($check) == 0) {
        if (db_query($conn, "INSERT INTO tour_schedules (tour_id, start_date, end_date, available_seats, price, status) VALUES (?, ?, ?, ?, ?, ?)", $schedule)) {
            $inserted++;
        }
    }
}

echo "✓ $inserted sample tour schedules added<br><br>";
echo "<strong>Setup complete!</strong> You can now book tours.<br>";
echo "<a href='index.php'>Go to homepage</a>";
?>
