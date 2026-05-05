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

// Select the database
mysqli_select_db($conn, $dbname);

// Set consistent timezone for both PHP and MySQL
date_default_timezone_set('Asia/Kolkata');
mysqli_query($conn, "SET time_zone = '+05:30'");

// 3. Auto-Setup Tables and Sample Data if missing
$check_users_table = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
$check_hotels_table = mysqli_query($conn, "SHOW TABLES LIKE 'hotels'");
$check_airports_table = mysqli_query($conn, "SHOW TABLES LIKE 'airports'");

if (mysqli_num_rows($check_users_table) == 0 || mysqli_num_rows($check_hotels_table) == 0 || mysqli_num_rows($check_airports_table) == 0) {
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

// Ensure sample data is present even if tables exist
$sample_hotels = [
    ['Taj Mahal Palace', 'Mumbai', 'Apollo Bunder, Colaba, Mumbai 400001', 'Iconic luxury waterfront hotel with sea views, world-class dining, and heritage charm since 1903.', 15000.00, 5.0, 'Pool, Spa, Wifi, Restaurant, Bar, Gym, Concierge', ''],
    ['Hyatt Regency', 'Delhi', 'Bhikaji Cama Place, Ring Road, New Delhi 110066', 'Premium business and leisure hotel in the heart of New Delhi with modern amenities.', 12000.00, 4.8, 'Pool, Gym, Wifi, Restaurant, Business Center', ''],
    ['Goa Beach Resort', 'Goa', 'Calangute Beach Road, Bardez, Goa 403516', 'Charming beachfront resort with tropical ambiance and direct beach access.', 5000.00, 4.2, 'Beach Access, Bar, Wifi, Pool, Water Sports', ''],
    ['ITC Royal Bengal', 'Kolkata', 'JBS Haldane Avenue, Kolkata 700046', 'Ultra-luxury hotel blending Bengali heritage with contemporary design.', 11000.00, 4.7, 'Pool, Spa, Wifi, Fine Dining, Gym, Club Lounge', ''],
    ['The Leela Palace', 'Bangalore', 'Old Airport Road, HAL 2nd Stage, Bangalore 560008', 'Royal palace-style luxury hotel surrounded by lush gardens.', 13500.00, 4.9, 'Pool, Spa, Wifi, Golf, Restaurant, Bar', ''],
    ['Radisson Blu', 'Jaipur', 'Airport Plaza, Tonk Road, Jaipur 302015', 'Modern hotel near Jaipur airport with spacious rooms and rooftop dining.', 6500.00, 4.3, 'Pool, Gym, Wifi, Restaurant, Airport Shuttle', ''],
    ['Backwater Retreat', 'Kochi', 'Kumbalangi, Ernakulam, Kochi 682007', 'Serene backwater hideaway offering authentic Kerala houseboat experience.', 4000.00, 4.1, 'Backwater View, Ayurveda Spa, Wifi, Restaurant', ''],
    ['Hotel Marina Bay', 'Chennai', 'Anna Salai, Mount Road, Chennai 600002', 'Stylish city-center hotel with pool, rooftop views, and South Indian cuisine.', 5500.00, 4.0, 'Pool, Wifi, Restaurant, Gym, Rooftop Lounge', ''],
    ['Lake Palace Udaipur', 'Udaipur', 'Pichola Lake, Udaipur 313001', 'Iconic floating palace on Lake Pichola offering regal accommodations with stunning lake views.', 18000.00, 5.0, 'Pool, Spa, Wifi, Restaurant, Bar, Lake View, Boat Service', ''],
    ['Gateway Hotel', 'Agra', 'Fatehabad Road, Agra 282001', 'Premium hotel with breathtaking views of the Taj Mahal and modern amenities.', 9000.00, 4.6, 'Pool, Gym, Wifi, Restaurant, Taj View, Bar', ''],
    ['Manali Heights', 'Manali', 'Mall Road, Manali 175131', 'Cozy mountain resort with panoramic Himalayan views and comfortable stay.', 6000.00, 4.3, 'Mountain View, Wifi, Restaurant, Bonfire, Travel Desk', ''],
    ['Pondicherry Beach Resort', 'Pondicherry', 'Beach Road, Pondicherry 605001', 'French colonial style beach resort with serene ambiance and coastal charm.', 7500.00, 4.4, 'Beach Access, Pool, Wifi, Restaurant, Bar, Spa', ''],
    ['Ranthambore Tiger Resort', 'Ranthambore', 'Near Ranthambore National Park, Sawai Madhopur 322001', 'Wildlife resort offering jungle safari experiences and comfortable stay amidst nature.', 8500.00, 4.5, 'Safari Tours, Pool, Wifi, Restaurant, Wildlife Viewing', ''],
    ['Shimla Haven', 'Shimla', 'Mall Road, Shimla 171001', 'Charming heritage hotel in the Queen of Hills with colonial architecture.', 7000.00, 4.2, 'Mountain View, Wifi, Restaurant, Heater, Fireplace', ''],
    ['Varanasi Ghat View', 'Varanasi', 'Assi Ghat, Varanasi 221005', 'Riverside hotel overlooking the Ganges with authentic spiritual experience.', 5500.00, 4.1, 'Ghat View, Wifi, Restaurant, Temple Tours, Yoga', ''],
    ['Amritsar Grand', 'Amritsar', 'Golden Temple Road, Amritsar 143001', 'Comfortable hotel near Golden Temple offering easy access to religious sites.', 4500.00, 4.0, 'Wifi, Restaurant, Temple View, Parking, 24hr Service', ''],
    ['Gangtok Himalayan', 'Gangtok', 'Mahatma Gandhi Marg, Gangtok 737101', 'Scenic mountain hotel in Sikkim with stunning Kanchenjunga views.', 8000.00, 4.4, 'Mountain View, Wifi, Restaurant, Spa, Cable Car Access', ''],
    ['Ooty Lake View', 'Ooty', 'Lake Road, Ooty 643001', 'Beautiful hill station hotel overlooking Ooty Lake with colonial charm.', 6500.00, 4.3, 'Lake View, Wifi, Restaurant, Garden, Tea Tours', ''],
    ['Darjeeling Tea Estate', 'Darjeeling', 'Mall Road, Darjeeling 734101', 'Heritage hotel with tea plantation views and cozy mountain ambiance.', 7200.00, 4.4, 'Tea Garden View, Wifi, Restaurant, Tea Tours, Fireplace', ''],
    ['Nainital Lake Resort', 'Nainital', 'Thandi Sadak, Nainital 263001', 'Serene lakeside resort with panoramic views of Naini Lake.', 6800.00, 4.2, 'Lake View, Wifi, Restaurant, Boating, Mountain View', ''],
    ['Kanyakumari Beach', 'Kanyakumari', 'Beach Road, Kanyakumari 629702', 'Coastal resort at India\'s southern tip with sunrise and sunset views.', 5800.00, 4.1, 'Beach Access, Wifi, Restaurant, Sunrise View, Temple Tours', ''],
    ['Mussoorie Gateway', 'Mussoorie', 'Mall Road, Mussoorie 248001', 'Hill station hotel with breathtaking Doon Valley views.', 6200.00, 4.0, 'Valley View, Wifi, Restaurant, Cable Car, Nature Walks', ''],
    ['Srinagar Houseboat', 'Srinagar', 'Dal Lake, Srinagar 190001', 'Authentic Kashmir houseboat experience on Dal Lake with shikara rides.', 9500.00, 4.7, 'Lake View, Wifi, Restaurant, Shikara Ride, Garden View', ''],
    ['Ahmedabad Heritage', 'Ahmedabad', 'Law Garden, Ahmedabad 380006', 'Heritage hotel in Gujarat showcasing traditional architecture and culture.', 5200.00, 4.1, 'Wifi, Restaurant, Heritage Tours, Garden, Parking', ''],
    ['Surat Central', 'Surat', 'Ring Road, Surat 395003', 'Modern business hotel in Surat with excellent connectivity.', 4800.00, 4.0, 'Wifi, Restaurant, Gym, Business Center, Parking', ''],
    ['Vadodara Palace', 'Vadodara', 'Alkapuri, Vadodara 390005', 'Elegant hotel in Gujarat with palace-style architecture.', 6000.00, 4.2, 'Pool, Wifi, Restaurant, Bar, Gym, Garden', ''],
    ['Nagpur Orange', 'Nagpur', 'Sitabuldi, Nagpur 440012', 'Comfortable hotel in Nagpur with modern amenities and easy access.', 4200.00, 4.0, 'Wifi, Restaurant, Parking, Gym, 24hr Service', ''],
    ['Indore Rajwada', 'Indore', 'Rajwada, Indore 452001', 'Central hotel in Indore near Rajwada Palace with local flavors.', 4600.00, 4.1, 'Wifi, Restaurant, Local Food Tours, Parking, Gym', '']
];

// Add hotels one by one, skipping those that already exist
foreach ($sample_hotels as $hotel) {
    $check_query = "SELECT hotel_id FROM hotels WHERE name = ? AND city = ?";
    $check_result = db_query($conn, $check_query, [$hotel[0], $hotel[1]]);
    
    if (!$check_result || db_num_rows($check_result) == 0) {
        db_query($conn, "INSERT INTO hotels (name, city, address, description, price_per_night, rating, amenities, main_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", $hotel);
    }
}

// Ensure sample airports are present
$check_airports_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM airports");
if ($check_airports_count) {
    $count = db_fetch_assoc($check_airports_count)['count'];
    if ($count == 0) {
        // Insert sample airports
        $sample_airports = [
            ['BOM', 'Chhatrapati Shivaji Maharaj International Airport', 'Mumbai', 'India'],
            ['DEL', 'Indira Gandhi International Airport', 'Delhi', 'India'],
            ['BLR', 'Kempegowda International Airport', 'Bangalore', 'India'],
            ['HYD', 'Rajiv Gandhi International Airport', 'Hyderabad', 'India'],
            ['MAA', 'Chennai International Airport', 'Chennai', 'India']
        ];
        
        foreach ($sample_airports as $airport) {
            db_query($conn, "INSERT IGNORE INTO airports (airport_code, airport_name, city, country) VALUES (?, ?, ?, ?)", $airport);
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

// Fix: Add missing verification columns to users table if they don't exist
$required_columns = [
    'is_verified' => "ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0",
    'verification_code' => "ALTER TABLE users ADD COLUMN verification_code VARCHAR(100)",
    'verification_code_expiry' => "ALTER TABLE users ADD COLUMN verification_code_expiry TIMESTAMP NULL",
    'email_verified_at' => "ALTER TABLE users ADD COLUMN email_verified_at TIMESTAMP NULL",
    'reset_token' => "ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL",
    'token_expiry' => "ALTER TABLE users ADD COLUMN token_expiry DATETIME DEFAULT NULL"
];

foreach ($required_columns as $col_name => $alter_sql) {
    $check_col = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE '$col_name'");
    if (mysqli_num_rows($check_col) == 0) {
        mysqli_query($conn, $alter_sql);
    }
}

// Fix: Ensure admins table exists and has a valid admin account
$check_admins_table = mysqli_query($conn, "SHOW TABLES LIKE 'admins'");
if (mysqli_num_rows($check_admins_table) == 0) {
    // Create admins table
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `admins` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(50) UNIQUE NOT NULL,
        `email` VARCHAR(100) UNIQUE NOT NULL,
        `password` VARCHAR(255) NOT NULL,
        `role` VARCHAR(20) DEFAULT 'superadmin',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
}

// Check if admin user exists, if not create it with password admin123
$check_admin = mysqli_query($conn, "SELECT id FROM admins WHERE username = 'admin'");
if (mysqli_num_rows($check_admin) == 0) {
    // Create admin user with password admin123
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    mysqli_query($conn, "INSERT INTO admins (username, email, password, role) VALUES ('admin', 'admin@tripnexus.com', '$admin_password', 'superadmin')");
} else {
    // Update admin password to admin123 to ensure it works
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    mysqli_query($conn, "UPDATE admins SET password = '$admin_password' WHERE username = 'admin'");
}

// Fix: Ensure wishlist table has item_id column instead of reference_id
$check_wishlist_cols = mysqli_query($conn, "SHOW COLUMNS FROM wishlist LIKE 'item_id'");
if (mysqli_num_rows($check_wishlist_cols) == 0) {
    // Check if reference_id exists and rename it
    $check_ref_id = mysqli_query($conn, "SHOW COLUMNS FROM wishlist LIKE 'reference_id'");
    if (mysqli_num_rows($check_ref_id) > 0) {
        mysqli_query($conn, "ALTER TABLE wishlist CHANGE COLUMN reference_id item_id INT NOT NULL");
    } else {
        // Add item_id column
        mysqli_query($conn, "ALTER TABLE wishlist ADD COLUMN item_id INT NOT NULL");
    }
    
    // Also add item_name column if missing
    $check_item_name = mysqli_query($conn, "SHOW COLUMNS FROM wishlist LIKE 'item_name'");
    if (mysqli_num_rows($check_item_name) == 0) {
        mysqli_query($conn, "ALTER TABLE wishlist ADD COLUMN item_name VARCHAR(255)");
    }
}

// Ensure tour_schedules table exists and has sample data
$check_tour_schedules_table = mysqli_query($conn, "SHOW TABLES LIKE 'tour_schedules'");
if (mysqli_num_rows($check_tour_schedules_table) == 0) {
    // Create tour_schedules table
    mysqli_query($conn, "CREATE TABLE `tour_schedules` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `tour_id` INT NOT NULL,
        `start_date` DATE NOT NULL,
        `end_date` DATE NOT NULL,
        `available_seats` INT DEFAULT 20,
        `price` DECIMAL(10, 2) NOT NULL,
        `status` ENUM('available', 'full', 'cancelled') DEFAULT 'available',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`tour_id`) REFERENCES `tour_packages`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB");
}

// Add sample tour schedules if none exist
$check_schedules_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM tour_schedules");
if ($check_schedules_count) {
    $count = db_fetch_assoc($check_schedules_count)['count'];
    if ($count == 0) {
        $sample_schedules = [
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
        
        foreach ($sample_schedules as $schedule) {
            db_query($conn, "INSERT INTO tour_schedules (tour_id, start_date, end_date, available_seats, price, status) VALUES (?, ?, ?, ?, ?, ?)", $schedule);
        }
    }
}

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
