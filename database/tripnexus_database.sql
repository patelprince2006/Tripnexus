-- ===================================================================
-- TripNexus Database — Complete MySQL Schema
-- Compatible with XAMPP / MySQL 5.7+ / MariaDB 10.3+
-- ===================================================================
-- Run this file in phpMyAdmin or MySQL CLI:
--   mysql -u root < tripnexus_database.sql
-- ===================================================================

CREATE DATABASE IF NOT EXISTS `tripnexus`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `tripnexus`;

-- -------------------------------------------------------------------
-- 1. USERS
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `fullname` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `theme` VARCHAR(10) DEFAULT 'light',
    `status` VARCHAR(20) DEFAULT 'active',
    `email_verified_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------------------
-- 2. ADMINS
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) UNIQUE NOT NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` VARCHAR(20) DEFAULT 'superadmin',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------------------
-- 3. AIRPORTS
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `airports` (
    `airport_code` VARCHAR(3) PRIMARY KEY,
    `airport_name` VARCHAR(100) NOT NULL,
    `city` VARCHAR(50) NOT NULL,
    `country` VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- -------------------------------------------------------------------
-- 4. AIRLINES
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `airlines` (
    `airline_id` INT AUTO_INCREMENT PRIMARY KEY,
    `airline_name` VARCHAR(100) NOT NULL,
    `airline_logo` TEXT
) ENGINE=InnoDB;

-- -------------------------------------------------------------------
-- 5. FLIGHTS
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `flights` (
    `flight_id` INT AUTO_INCREMENT PRIMARY KEY,
    `flight_number` VARCHAR(10) UNIQUE NOT NULL,
    `airline_id` INT,
    `departure_airport` VARCHAR(3),
    `arrival_airport` VARCHAR(3),
    `departure_time` DATETIME NOT NULL,
    `arrival_time` DATETIME NOT NULL,
    `base_price` DECIMAL(10, 2) NOT NULL,
    `total_seats` INT DEFAULT 60,
    `available_seats` INT NOT NULL,
    `status` ENUM('scheduled', 'boarding', 'departed', 'landed', 'cancelled') DEFAULT 'scheduled',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `check_seats` CHECK (`available_seats` >= 0),
    FOREIGN KEY (`airline_id`) REFERENCES `airlines`(`airline_id`),
    FOREIGN KEY (`departure_airport`) REFERENCES `airports`(`airport_code`),
    FOREIGN KEY (`arrival_airport`) REFERENCES `airports`(`airport_code`)
) ENGINE=InnoDB;

-- -------------------------------------------------------------------
-- 6. BUSES
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `buses` (
    `bus_id` INT AUTO_INCREMENT PRIMARY KEY,
    `operator_name` VARCHAR(100) NOT NULL,
    `bus_number` VARCHAR(50),
    `from_location` VARCHAR(100) NOT NULL,
    `to_location` VARCHAR(100) NOT NULL,
    `departure_time` DATETIME NOT NULL,
    `arrival_time` DATETIME NOT NULL,
    `price` DECIMAL(10, 2) NOT NULL,
    `bus_type` VARCHAR(50),
    `available_seats` INT DEFAULT 40,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------------------
-- 7. TRAINS
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `trains` (
    `train_id` INT AUTO_INCREMENT PRIMARY KEY,
    `train_name` VARCHAR(100) NOT NULL,
    `train_number` VARCHAR(50) NOT NULL,
    `from_station` VARCHAR(100) NOT NULL,
    `to_station` VARCHAR(100) NOT NULL,
    `departure_time` DATETIME NOT NULL,
    `arrival_time` DATETIME NOT NULL,
    `price` DECIMAL(10, 2) NOT NULL,
    `available_seats` INT DEFAULT 120,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------------------
-- 8. HOTELS
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `hotels` (
    `hotel_id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `city` VARCHAR(100) NOT NULL,
    `address` TEXT,
    `description` TEXT,
    `price_per_night` DECIMAL(10, 2) NOT NULL,
    `rating` DECIMAL(2, 1) DEFAULT 0,
    `amenities` TEXT,
    `main_image` TEXT,
    `latitude` DECIMAL(10, 8),
    `longitude` DECIMAL(11, 8),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------------------
-- 9. HOTEL ROOMS
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `hotel_rooms` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `hotel_id` INT NOT NULL,
    `room_type` VARCHAR(50) NOT NULL,
    `price` DECIMAL(10, 2) NOT NULL,
    `available_count` INT DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`hotel_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------------
-- 10. TOUR PACKAGES
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tour_packages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `location` VARCHAR(100) NOT NULL,
    `duration` INT NOT NULL,
    `price` DECIMAL(10, 2) NOT NULL,
    `description` TEXT,
    `itinerary` TEXT,
    `main_image` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------------------
-- 11. BOOKINGS
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bookings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `booking_type` ENUM('flight', 'bus', 'train', 'hotel', 'tour') NOT NULL,
    `reference_id` INT NOT NULL,
    `booking_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    `total_amount` DECIMAL(10, 2) NOT NULL,
    `travel_date` TIMESTAMP NULL,
    `passengers` INT DEFAULT 1,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------------
-- 12. PAYMENTS
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `payment_method` VARCHAR(50) DEFAULT 'card',
    `payment_status` ENUM('success', 'failed', 'pending', 'refunded') DEFAULT 'pending',
    `payment_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `transaction_id` VARCHAR(100),
    FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------------
-- 13. REVIEWS
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `review_type` ENUM('hotel', 'tour', 'flight', 'bus', 'train', 'website') NOT NULL,
    `reference_id` INT DEFAULT 0,
    `rating` INT CHECK (`rating` >= 1 AND `rating` <= 5),
    `comment` TEXT,
    `status` VARCHAR(20) DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------------
-- 14. NOTIFICATIONS
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `type` VARCHAR(50) DEFAULT 'general',
    `is_read` TINYINT(1) DEFAULT 0,
    `email_sent_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------------
-- 15. WISHLIST
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `wishlist` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `item_type` ENUM('flight', 'bus', 'train', 'hotel', 'tour') NOT NULL,
    `reference_id` INT NOT NULL,
    `added_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_wishlist` (`user_id`, `item_type`, `reference_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------------
-- 16. CONTACT MESSAGES
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `message` TEXT NOT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------------------
-- 17. PASSWORD RESET TOKENS
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `expires_at` TIMESTAMP NOT NULL,
    `used` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;


-- ===================================================================
-- SAMPLE DATA
-- ===================================================================

-- Default Admin (username: admin, password: admin123)
INSERT IGNORE INTO `admins` (`username`, `email`, `password`, `role`) VALUES
('admin', 'admin@tripnexus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin');

-- Airports
INSERT IGNORE INTO `airports` (`airport_code`, `airport_name`, `city`, `country`) VALUES
('BOM', 'Chhatrapati Shivaji Maharaj International Airport', 'Mumbai', 'India'),
('DEL', 'Indira Gandhi International Airport', 'Delhi', 'India'),
('BLR', 'Kempegowda International Airport', 'Bangalore', 'India'),
('HYD', 'Rajiv Gandhi International Airport', 'Hyderabad', 'India'),
('MAA', 'Chennai International Airport', 'Chennai', 'India'),
('COK', 'Cochin International Airport', 'Kochi', 'India'),
('GOI', 'Manohar International Airport', 'Goa', 'India'),
('CCU', 'Netaji Subhas Chandra Bose International Airport', 'Kolkata', 'India'),
('PNQ', 'Pune Airport', 'Pune', 'India'),
('JAI', 'Jaipur International Airport', 'Jaipur', 'India');

-- Airlines
INSERT IGNORE INTO `airlines` (`airline_name`, `airline_logo`) VALUES
('Air India', 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e3/Air_India_Logo.svg/200px-Air_India_Logo.svg.png'),
('IndiGo', 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/69/IndiGo_Airlines_logo.svg/200px-IndiGo_Airlines_logo.svg.png'),
('SpiceJet', 'https://upload.wikimedia.org/wikipedia/en/thumb/a/a3/SpiceJet_logo.svg/200px-SpiceJet_logo.svg.png'),
('Vistara', 'https://upload.wikimedia.org/wikipedia/en/thumb/3/3e/Vistara_logo.svg/200px-Vistara_logo.svg.png'),
('Air Asia India', 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f5/AirAsia_New_Logo.svg/200px-AirAsia_New_Logo.svg.png'),
('Akasa Air', 'https://upload.wikimedia.org/wikipedia/en/thumb/0/0d/Akasa_Air_logo.svg/200px-Akasa_Air_logo.svg.png'),
('Go First', 'https://upload.wikimedia.org/wikipedia/en/thumb/3/3a/Go_First_logo.svg/200px-Go_First_logo.svg.png'),
('Alliance Air', 'https://upload.wikimedia.org/wikipedia/en/thumb/8/8d/Alliance_Air_logo.svg/200px-Alliance_Air_logo.svg.png');

-- Flights (using fixed dates — update these as needed)
INSERT IGNORE INTO `flights` (`flight_number`, `airline_id`, `departure_airport`, `arrival_airport`, `departure_time`, `arrival_time`, `base_price`, `total_seats`, `available_seats`, `status`) VALUES
('AI101', 1, 'BOM', 'DEL', DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 1 DAY), INTERVAL 150 MINUTE), 4500.00, 180, 120, 'scheduled'),
('6E202', 2, 'DEL', 'BLR', DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 1 DAY), INTERVAL 195 MINUTE), 3800.00, 180, 90, 'scheduled'),
('SG303', 3, 'BLR', 'HYD', DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 2 DAY), INTERVAL 90 MINUTE), 2500.00, 180, 150, 'scheduled'),
('UK404', 4, 'HYD', 'BOM', DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 2 DAY), INTERVAL 120 MINUTE), 3200.00, 180, 140, 'scheduled'),
('AI505', 1, 'MAA', 'DEL', DATE_ADD(NOW(), INTERVAL 3 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 3 DAY), INTERVAL 180 MINUTE), 5200.00, 180, 100, 'scheduled'),
('6E606', 2, 'DEL', 'COK', DATE_ADD(NOW(), INTERVAL 3 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 3 DAY), INTERVAL 200 MINUTE), 6100.00, 180, 80, 'scheduled'),
('I5707', 5, 'COK', 'BLR', DATE_ADD(NOW(), INTERVAL 4 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 4 DAY), INTERVAL 75 MINUTE), 1800.00, 180, 160, 'scheduled'),
('QP808', 6, 'BLR', 'MAA', DATE_ADD(NOW(), INTERVAL 4 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 4 DAY), INTERVAL 60 MINUTE), 1950.00, 180, 170, 'scheduled'),
('AI909', 1, 'GOI', 'BOM', DATE_ADD(NOW(), INTERVAL 5 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 5 DAY), INTERVAL 80 MINUTE), 3500.00, 180, 130, 'scheduled'),
('SG110', 3, 'CCU', 'DEL', DATE_ADD(NOW(), INTERVAL 5 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 5 DAY), INTERVAL 140 MINUTE), 4200.00, 180, 110, 'scheduled'),
('UK211', 4, 'PNQ', 'GOI', DATE_ADD(NOW(), INTERVAL 6 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 6 DAY), INTERVAL 70 MINUTE), 2800.00, 180, 145, 'scheduled'),
('6E312', 2, 'JAI', 'BOM', DATE_ADD(NOW(), INTERVAL 6 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 6 DAY), INTERVAL 130 MINUTE), 3900.00, 180, 95, 'scheduled');

-- Buses
INSERT INTO `buses` (`operator_name`, `bus_number`, `from_location`, `to_location`, `departure_time`, `arrival_time`, `price`, `bus_type`, `available_seats`) VALUES
('VRL Travels', 'KA-01-AB-1234', 'Bangalore', 'Hyderabad', DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 1 DAY), INTERVAL 8 HOUR), 1200.00, 'AC Sleeper', 36),
('Orange Tours', 'TS-09-CD-5678', 'Hyderabad', 'Bangalore', DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 1 DAY), INTERVAL 9 HOUR), 1100.00, 'AC Semi-Sleeper', 40),
('SRS Travels', 'MH-01-EF-9012', 'Mumbai', 'Pune', DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 2 DAY), INTERVAL 4 HOUR), 600.00, 'AC Seater', 45),
('Neeta Travels', 'MH-04-GH-3456', 'Mumbai', 'Goa', DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 2 DAY), INTERVAL 10 HOUR), 1500.00, 'Volvo AC Sleeper', 30),
('KSRTC', 'KA-57-IJ-7890', 'Bangalore', 'Chennai', DATE_ADD(NOW(), INTERVAL 3 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 3 DAY), INTERVAL 6 HOUR), 800.00, 'AC Seater', 48),
('APSRTC', 'AP-28-KL-1234', 'Hyderabad', 'Vijayawada', DATE_ADD(NOW(), INTERVAL 3 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 3 DAY), INTERVAL 5 HOUR), 700.00, 'Non-AC Sleeper', 40),
('Paulo Travels', 'GA-01-MN-5678', 'Goa', 'Mumbai', DATE_ADD(NOW(), INTERVAL 4 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 4 DAY), INTERVAL 11 HOUR), 1400.00, 'Volvo Multi-Axle', 36),
('Parveen Travels', 'TN-01-OP-9012', 'Chennai', 'Bangalore', DATE_ADD(NOW(), INTERVAL 4 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 4 DAY), INTERVAL 7 HOUR), 900.00, 'AC Semi-Sleeper', 42);

-- Trains
INSERT INTO `trains` (`train_name`, `train_number`, `from_station`, `to_station`, `departure_time`, `arrival_time`, `price`, `available_seats`) VALUES
('Rajdhani Express', '12433', 'Chennai Central', 'New Delhi', DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(NOW(), INTERVAL 3 DAY), 3500.00, 120),
('Shatabdi Express', '12007', 'Chennai Central', 'Mysore', DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 2 DAY), INTERVAL 7 HOUR), 800.00, 150),
('Duronto Express', '12267', 'Mumbai CST', 'New Delhi', DATE_ADD(NOW(), INTERVAL 3 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 3 DAY), INTERVAL 16 HOUR), 2800.00, 100),
('Karnataka Express', '12627', 'Bangalore City', 'New Delhi', DATE_ADD(NOW(), INTERVAL 3 DAY), DATE_ADD(NOW(), INTERVAL 5 DAY), 2200.00, 200),
('Deccan Queen', '12123', 'Mumbai CST', 'Pune', DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 1 DAY), INTERVAL 3 HOUR), 350.00, 180),
('Howrah Mail', '12809', 'Chennai Central', 'Howrah', DATE_ADD(NOW(), INTERVAL 4 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 4 DAY), INTERVAL 26 HOUR), 1500.00, 130),
('Vande Bharat Express', '20607', 'Chennai Central', 'Bangalore City', DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 1 DAY), INTERVAL 5 HOUR), 1200.00, 160),
('Garib Rath Express', '12215', 'Mumbai Bandra', 'New Delhi', DATE_ADD(NOW(), INTERVAL 5 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 5 DAY), INTERVAL 19 HOUR), 1100.00, 250);

-- Hotels
INSERT INTO `hotels` (`name`, `city`, `address`, `description`, `price_per_night`, `rating`, `amenities`, `main_image`) VALUES
('Taj Mahal Palace', 'Mumbai', 'Apollo Bunder, Colaba, Mumbai 400001', 'Iconic luxury waterfront hotel with sea views, world-class dining, and heritage charm since 1903.', 15000.00, 5.0, 'Pool, Spa, Wifi, Restaurant, Bar, Gym, Concierge', ''),
('Hyatt Regency', 'Delhi', 'Bhikaji Cama Place, Ring Road, New Delhi 110066', 'Premium business and leisure hotel in the heart of New Delhi with modern amenities.', 12000.00, 4.8, 'Pool, Gym, Wifi, Restaurant, Business Center', ''),
('Goa Beach Resort', 'Goa', 'Calangute Beach Road, Bardez, Goa 403516', 'Charming beachfront resort with tropical ambiance and direct beach access.', 5000.00, 4.2, 'Beach Access, Bar, Wifi, Pool, Water Sports', ''),
('ITC Royal Bengal', 'Kolkata', 'JBS Haldane Avenue, Kolkata 700046', 'Ultra-luxury hotel blending Bengali heritage with contemporary design.', 11000.00, 4.7, 'Pool, Spa, Wifi, Fine Dining, Gym, Club Lounge', ''),
('The Leela Palace', 'Bangalore', 'Old Airport Road, HAL 2nd Stage, Bangalore 560008', 'Royal palace-style luxury hotel surrounded by lush gardens.', 13500.00, 4.9, 'Pool, Spa, Wifi, Golf, Restaurant, Bar', ''),
('Radisson Blu', 'Jaipur', 'Airport Plaza, Tonk Road, Jaipur 302015', 'Modern hotel near Jaipur airport with spacious rooms and rooftop dining.', 6500.00, 4.3, 'Pool, Gym, Wifi, Restaurant, Airport Shuttle', ''),
('Backwater Retreat', 'Kochi', 'Kumbalangi, Ernakulam, Kochi 682007', 'Serene backwater hideaway offering authentic Kerala houseboat experience.', 4000.00, 4.1, 'Backwater View, Ayurveda Spa, Wifi, Restaurant', ''),
('Hotel Marina Bay', 'Chennai', 'Anna Salai, Mount Road, Chennai 600002', 'Stylish city-center hotel with pool, rooftop views, and South Indian cuisine.', 5500.00, 4.0, 'Pool, Wifi, Restaurant, Gym, Rooftop Lounge', ''),
('Lake Palace Udaipur', 'Udaipur', 'Pichola Lake, Udaipur 313001', 'Iconic floating palace on Lake Pichola offering regal accommodations with stunning lake views.', 18000.00, 5.0, 'Pool, Spa, Wifi, Restaurant, Bar, Lake View, Boat Service', ''),
('Gateway Hotel', 'Agra', 'Fatehabad Road, Agra 282001', 'Premium hotel with breathtaking views of the Taj Mahal and modern amenities.', 9000.00, 4.6, 'Pool, Gym, Wifi, Restaurant, Taj View, Bar', ''),
('Manali Heights', 'Manali', 'Mall Road, Manali 175131', 'Cozy mountain resort with panoramic Himalayan views and comfortable stay.', 6000.00, 4.3, 'Mountain View, Wifi, Restaurant, Bonfire, Travel Desk', ''),
('Pondicherry Beach Resort', 'Pondicherry', 'Beach Road, Pondicherry 605001', 'French colonial style beach resort with serene ambiance and coastal charm.', 7500.00, 4.4, 'Beach Access, Pool, Wifi, Restaurant, Bar, Spa', ''),
('Ranthambore Tiger Resort', 'Ranthambore', 'Near Ranthambore National Park, Sawai Madhopur 322001', 'Wildlife resort offering jungle safari experiences and comfortable stay amidst nature.', 8500.00, 4.5, 'Safari Tours, Pool, Wifi, Restaurant, Wildlife Viewing', ''),
('Shimla Haven', 'Shimla', 'Mall Road, Shimla 171001', 'Charming heritage hotel in the Queen of Hills with colonial architecture.', 7000.00, 4.2, 'Mountain View, Wifi, Restaurant, Heater, Fireplace', ''),
('Varanasi Ghat View', 'Varanasi', 'Assi Ghat, Varanasi 221005', 'Riverside hotel overlooking the Ganges with authentic spiritual experience.', 5500.00, 4.1, 'Ghat View, Wifi, Restaurant, Temple Tours, Yoga', ''),
('Amritsar Grand', 'Amritsar', 'Golden Temple Road, Amritsar 143001', 'Comfortable hotel near Golden Temple offering easy access to religious sites.', 4500.00, 4.0, 'Wifi, Restaurant, Temple View, Parking, 24hr Service', ''),
('Gangtok Himalayan', 'Gangtok', 'Mahatma Gandhi Marg, Gangtok 737101', 'Scenic mountain hotel in Sikkim with stunning Kanchenjunga views.', 8000.00, 4.4, 'Mountain View, Wifi, Restaurant, Spa, Cable Car Access', ''),
('Ooty Lake View', 'Ooty', 'Lake Road, Ooty 643001', 'Beautiful hill station hotel overlooking Ooty Lake with colonial charm.', 6500.00, 4.3, 'Lake View, Wifi, Restaurant, Garden, Tea Tours', ''),
('Darjeeling Tea Estate', 'Darjeeling', 'Mall Road, Darjeeling 734101', 'Heritage hotel with tea plantation views and cozy mountain ambiance.', 7200.00, 4.4, 'Tea Garden View, Wifi, Restaurant, Tea Tours, Fireplace', ''),
('Nainital Lake Resort', 'Nainital', 'Thandi Sadak, Nainital 263001', 'Serene lakeside resort with panoramic views of Naini Lake.', 6800.00, 4.2, 'Lake View, Wifi, Restaurant, Boating, Mountain View', ''),
('Kanyakumari Beach', 'Kanyakumari', 'Beach Road, Kanyakumari 629702', 'Coastal resort at India\'s southern tip with sunrise and sunset views.', 5800.00, 4.1, 'Beach Access, Wifi, Restaurant, Sunrise View, Temple Tours', ''),
('Mussoorie Gateway', 'Mussoorie', 'Mall Road, Mussoorie 248001', 'Hill station hotel with breathtaking Doon Valley views.', 6200.00, 4.0, 'Valley View, Wifi, Restaurant, Cable Car, Nature Walks', ''),
('Srinagar Houseboat', 'Srinagar', 'Dal Lake, Srinagar 190001', 'Authentic Kashmir houseboat experience on Dal Lake with shikara rides.', 9500.00, 4.7, 'Lake View, Wifi, Restaurant, Shikara Ride, Garden View', ''),
('Ahmedabad Heritage', 'Ahmedabad', 'Law Garden, Ahmedabad 380006', 'Heritage hotel in Gujarat showcasing traditional architecture and culture.', 5200.00, 4.1, 'Wifi, Restaurant, Heritage Tours, Garden, Parking', ''),
('Surat Central', 'Surat', 'Ring Road, Surat 395003', 'Modern business hotel in Surat with excellent connectivity.', 4800.00, 4.0, 'Wifi, Restaurant, Gym, Business Center, Parking', ''),
('Vadodara Palace', 'Vadodara', 'Alkapuri, Vadodara 390005', 'Elegant hotel in Gujarat with palace-style architecture.', 6000.00, 4.2, 'Pool, Wifi, Restaurant, Bar, Gym, Garden', ''),
('Nagpur Orange', 'Nagpur', 'Sitabuldi, Nagpur 440012', 'Comfortable hotel in Nagpur with modern amenities and easy access.', 4200.00, 4.0, 'Wifi, Restaurant, Parking, Gym, 24hr Service', ''),
('Indore Rajwada', 'Indore', 'Rajwada, Indore 452001', 'Central hotel in Indore near Rajwada Palace with local flavors.', 4600.00, 4.1, 'Wifi, Restaurant, Local Food Tours, Parking, Gym', '');

-- Tour Packages
INSERT INTO `tour_packages` (`name`, `location`, `duration`, `price`, `description`, `itinerary`, `main_image`) VALUES
('Golden Triangle Tour', 'Delhi-Agra-Jaipur', 5, 25000.00, 'Explore India\'s most famous heritage circuit covering Delhi, Agra (Taj Mahal), and Jaipur (Pink City).', 'Day 1: Delhi sightseeing\nDay 2: Drive to Agra, Taj Mahal\nDay 3: Agra Fort, drive to Jaipur\nDay 4: Jaipur city tour\nDay 5: Return to Delhi', ''),
('Kerala Backwaters', 'Kerala', 4, 18000.00, 'Experience the serene backwaters of Kerala with a houseboat stay, spice plantations, and Ayurveda treatments.', 'Day 1: Arrive Kochi, sightseeing\nDay 2: Munnar tea gardens\nDay 3: Alleppey houseboat\nDay 4: Beach day, departure', ''),
('Goa Beach Holiday', 'Goa', 3, 12000.00, 'Relax on pristine beaches, explore Portuguese heritage, and enjoy vibrant nightlife in Goa.', 'Day 1: North Goa beaches & forts\nDay 2: South Goa, Basilica of Bom Jesus\nDay 3: Water sports & departure', ''),
('Ladakh Adventure', 'Leh-Ladakh', 7, 35000.00, 'High-altitude adventure through stunning mountain passes, ancient monasteries, and Pangong Lake.', 'Day 1: Arrive Leh, acclimatize\nDay 2: Leh sightseeing\nDay 3: Nubra Valley\nDay 4: Diskit, Hunder\nDay 5: Pangong Lake\nDay 6: Chang La, Hemis\nDay 7: Departure', ''),
('Rajasthan Royal Experience', 'Rajasthan', 6, 30000.00, 'Live like royalty as you explore majestic forts, palaces, and the Thar Desert.', 'Day 1: Jaipur\nDay 2: Jaipur forts\nDay 3: Jodhpur\nDay 4: Jaisalmer desert safari\nDay 5: Udaipur\nDay 6: Lake Pichola, departure', ''),
('Andaman Island Escape', 'Andaman & Nicobar', 5, 28000.00, 'Crystal-clear waters, coral reefs, and tropical forests on India\'s most beautiful islands.', 'Day 1: Port Blair, Cellular Jail\nDay 2: Havelock Island, Radhanagar Beach\nDay 3: Snorkeling, Elephant Beach\nDay 4: Neil Island\nDay 5: Return to Port Blair', '');
