<?php
include __DIR__ . '/../database/db.php';

$queries = [
    // Create tour_schedules table for fixed dates
    "CREATE TABLE IF NOT EXISTS `tour_schedules` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `tour_id` INT NOT NULL,
        `start_date` DATE NOT NULL,
        `available_seats` INT DEFAULT 30,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`tour_id`) REFERENCES `tour_packages`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    // Create tour_itinerary table for detailed daily plans
    "CREATE TABLE IF NOT EXISTS `tour_itinerary` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `tour_id` INT NOT NULL,
        `day_number` INT NOT NULL,
        `route_from` VARCHAR(100),
        `route_to` VARCHAR(100),
        `transport_type` ENUM('Bus', 'Train', 'Flight', 'None') DEFAULT 'None',
        `transport_time` TIME,
        `hotel_id` INT,
        `activities` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`tour_id`) REFERENCES `tour_packages`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`hotel_id`) ON DELETE SET NULL
    ) ENGINE=InnoDB"
];

foreach ($queries as $query) {
    if (db_query($conn, $query)) {
        echo "Successfully executed: " . substr($query, 0, 50) . "...<br>";
    } else {
        echo "Error executing query: " . mysqli_error($conn) . "<br>";
    }
}

echo "Migration complete.";
?>