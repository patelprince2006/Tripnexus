-- Create tour_schedules table
USE tripnexus;

CREATE TABLE IF NOT EXISTS `tour_schedules` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tour_id` INT NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `available_seats` INT DEFAULT 20,
    `price` DECIMAL(10, 2) NOT NULL,
    `status` ENUM('available', 'full', 'cancelled') DEFAULT 'available',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tour_id`) REFERENCES `tour_packages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Insert sample tour schedules
INSERT INTO `tour_schedules` (`tour_id`, `start_date`, `end_date`, `available_seats`, `price`, `status`) VALUES
(1, DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 11 DAY), 15, 25000.00, 'available'),
(1, DATE_ADD(CURDATE(), INTERVAL 14 DAY), DATE_ADD(CURDATE(), INTERVAL 18 DAY), 20, 25000.00, 'available'),
(1, DATE_ADD(CURDATE(), INTERVAL 21 DAY), DATE_ADD(CURDATE(), INTERVAL 25 DAY), 10, 25000.00, 'available'),
(2, DATE_ADD(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 8 DAY), 12, 18000.00, 'available'),
(2, DATE_ADD(CURDATE(), INTERVAL 12 DAY), DATE_ADD(CURDATE(), INTERVAL 15 DAY), 18, 18000.00, 'available'),
(3, DATE_ADD(CURDATE(), INTERVAL 3 DAY), DATE_ADD(CURDATE(), INTERVAL 5 DAY), 20, 12000.00, 'available'),
(3, DATE_ADD(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 12 DAY), 15, 12000.00, 'available'),
(3, DATE_ADD(CURDATE(), INTERVAL 17 DAY), DATE_ADD(CURDATE(), INTERVAL 19 DAY), 10, 12000.00, 'available'),
(4, DATE_ADD(CURDATE(), INTERVAL 20 DAY), DATE_ADD(CURDATE(), INTERVAL 26 DAY), 8, 35000.00, 'available'),
(5, DATE_ADD(CURDATE(), INTERVAL 8 DAY), DATE_ADD(CURDATE(), INTERVAL 13 DAY), 12, 30000.00, 'available'),
(6, DATE_ADD(CURDATE(), INTERVAL 15 DAY), DATE_ADD(CURDATE(), INTERVAL 19 DAY), 16, 28000.00, 'available');
