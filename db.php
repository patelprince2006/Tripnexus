<?php
    // MySQL Connection Details (update these for your local setup)
    $host = "localhost";
    $port = 3306;
    $dbname = "sgp";
    $user = "root";
    $password = "";

    // Establish connection to MySQL server (no DB yet)
    $conn = @mysqli_connect($host, $user, $password, '', $port);

    $DB_CONNECTED = false;

    if (!$conn) {
        $errorMessage = mysqli_connect_error();

        die("
            <div style='font-family: sans-serif; padding: 20px; border: 1px solid #f44336; background: #ffebee; border-radius: 5px;'>
                <h3 style='color: #d32f2f; margin-top: 0;'>Database Connection Failed</h3>
                <p><strong>Error:</strong> " . htmlspecialchars($errorMessage) . "</p>
                <hr style='border: 0; border-top: 1px solid #ffcdd2; margin: 15px 0;'>
                <strong>Troubleshooting Steps:</strong>
                <ul>
                    <li><strong>Check MySQL Service:</strong> Ensure MySQL is running in XAMPP.</li>
                    <li><strong>Verify Credentials:</strong> Update <code>db.php</code> with the correct username, password, and database name.</li>
                </ul>
            </div>
        ");
    }

    // Create database if it does not exist, then select it
    $safeDb = preg_replace('/[^a-zA-Z0-9_]/', '', $dbname);
    if ($safeDb !== $dbname || $safeDb === '') {
        die("Invalid database name in db.php");
    }

    @mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    if (!@mysqli_select_db($conn, $dbname)) {
        $errorMessage = mysqli_error($conn);
        die("Unable to select database: " . htmlspecialchars($errorMessage));
    }

    $DB_CONNECTED = true;

    // MySQL helpers (prepared statements + fetch utilities)
    $GLOBALS['DB_PREPARED'] = [];

    function _db_convert_placeholders($query) {
        return preg_replace('/\\$\\d+/', '?', $query);
    }

    function _db_infer_types($params) {
        $types = '';
        foreach ($params as $p) {
            if (is_int($p)) {
                $types .= 'i';
            } elseif (is_float($p)) {
                $types .= 'd';
            } elseif (is_bool($p)) {
                $types .= 'i';
            } else {
                $types .= 's';
            }
        }
        return $types;
    }

    function _db_bind_params($stmt, $params) {
        if (!$params || count($params) === 0) {
            return;
        }
        $types = _db_infer_types($params);
        $refs = [];
        foreach ($params as $key => $value) {
            $refs[$key] = &$params[$key];
        }
        array_unshift($refs, $types);
        call_user_func_array([$stmt, 'bind_param'], $refs);
    }

    function db_query($conn, $query, $params = []) {
        if (!$params || count($params) === 0) {
            return mysqli_query($conn, $query);
        }
        $converted = _db_convert_placeholders($query);
        $stmt = mysqli_prepare($conn, $converted);
        if (!$stmt) {
            return false;
        }
        _db_bind_params($stmt, $params);
        if (!mysqli_stmt_execute($stmt)) {
            return false;
        }
        $result = mysqli_stmt_get_result($stmt);
        return $result ? $result : $stmt;
    }

    function db_prepare($conn, $name, $query) {
        $converted = _db_convert_placeholders($query);
        $stmt = mysqli_prepare($conn, $converted);
        if (!$stmt) {
            return false;
        }
        $GLOBALS['DB_PREPARED'][$name] = $stmt;
        return $stmt;
    }

    function db_execute($conn, $name, $params = []) {
        if (!isset($GLOBALS['DB_PREPARED'][$name])) {
            return false;
        }
        $stmt = $GLOBALS['DB_PREPARED'][$name];
        _db_bind_params($stmt, $params);
        if (!mysqli_stmt_execute($stmt)) {
            return false;
        }
        $result = mysqli_stmt_get_result($stmt);
        return $result ? $result : $stmt;
    }

    function db_fetch_assoc($result) {
        if ($result instanceof mysqli_result) {
            return mysqli_fetch_assoc($result);
        }
        if ($result instanceof mysqli_stmt) {
            $res = mysqli_stmt_get_result($result);
            return $res ? mysqli_fetch_assoc($res) : false;
        }
        return false;
    }

    function db_fetch_all($result) {
        if ($result instanceof mysqli_result) {
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
        return false;
    }

    function db_num_rows($result) {
        if ($result instanceof mysqli_result) {
            return mysqli_num_rows($result);
        }
        if ($result instanceof mysqli_stmt) {
            mysqli_stmt_store_result($result);
            return mysqli_stmt_num_rows($result);
        }
        return 0;
    }

    function db_fetch_value($result, $row, $col) {
        if (!($result instanceof mysqli_result)) {
            return null;
        }
        if ($row !== null) {
            mysqli_data_seek($result, $row);
        }
        $data = mysqli_fetch_array($result, MYSQLI_NUM);
        return $data[$col] ?? null;
    }

    function db_affected_rows($result) {
        if ($result instanceof mysqli_stmt) {
            return mysqli_stmt_affected_rows($result);
        }
        if ($result instanceof mysqli_result) {
            return mysqli_num_rows($result);
        }
        if ($result instanceof mysqli) {
            return mysqli_affected_rows($result);
        }
        return 0;
    }

    function db_escape($conn, $string) {
        return mysqli_real_escape_string($conn, $string);
    }

    function db_last_error($conn) {
        return mysqli_error($conn);
    }

    function db_close($conn) {
        return mysqli_close($conn);
    }


    function db_table_exists($conn, $table) {
        $table = mysqli_real_escape_string($conn, $table);
        $sql = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = '$table'";
        $res = mysqli_query($conn, $sql);
        if (!$res) {
            return false;
        }
        $row = mysqli_fetch_row($res);
        return isset($row[0]) && (int)$row[0] > 0;
    }

    function db_ensure_schema($conn) {
        // Create core tables if missing
        if (!db_table_exists($conn, 'users')) {
            $users = "
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    fullname VARCHAR(100) NOT NULL,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    phone VARCHAR(20) NULL,
                    theme VARCHAR(20) NULL,
                    reset_token VARCHAR(255) NULL,
                    token_expiry TIMESTAMP NULL,
                    is_verified TINYINT(1) DEFAULT 0,
                    verification_code VARCHAR(100),
                    verification_code_expiry TIMESTAMP NULL,
                    email_verified_at TIMESTAMP NULL,
                    status VARCHAR(20) DEFAULT 'active',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB;
            ";
            db_query($conn, $users);
        }

        // Flights schema
        db_query($conn, "
            CREATE TABLE IF NOT EXISTS airports (
                airport_code VARCHAR(3) PRIMARY KEY,
                airport_name VARCHAR(100) NOT NULL,
                city VARCHAR(50) NOT NULL,
                country VARCHAR(50) NOT NULL
            ) ENGINE=InnoDB;
        ");

        db_query($conn, "
            CREATE TABLE IF NOT EXISTS airlines (
                airline_id INT AUTO_INCREMENT PRIMARY KEY,
                airline_name VARCHAR(100) NOT NULL,
                airline_logo TEXT
            ) ENGINE=InnoDB;
        ");

        db_query($conn, "
            CREATE TABLE IF NOT EXISTS flights (
                flight_id INT AUTO_INCREMENT PRIMARY KEY,
                flight_number VARCHAR(10) UNIQUE NOT NULL,
                airline_id INT,
                departure_airport VARCHAR(3),
                arrival_airport VARCHAR(3),
                departure_time DATETIME NOT NULL,
                arrival_time DATETIME NOT NULL,
                base_price DECIMAL(10, 2) NOT NULL,
                total_seats INT DEFAULT 60,
                available_seats INT NOT NULL,
                status ENUM('scheduled', 'boarding', 'departed', 'landed', 'cancelled') DEFAULT 'scheduled',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT check_seats CHECK (available_seats >= 0),
                FOREIGN KEY (airline_id) REFERENCES airlines(airline_id),
                FOREIGN KEY (departure_airport) REFERENCES airports(airport_code),
                FOREIGN KEY (arrival_airport) REFERENCES airports(airport_code)
            ) ENGINE=InnoDB;
        ");

        // Buses / Trains / Hotels (public listings)
        db_query($conn, "
            CREATE TABLE IF NOT EXISTS buses (
                bus_id INT AUTO_INCREMENT PRIMARY KEY,
                operator_name VARCHAR(100) NOT NULL,
                bus_number VARCHAR(50),
                from_location VARCHAR(100) NOT NULL,
                to_location VARCHAR(100) NOT NULL,
                departure_time DATETIME NOT NULL,
                arrival_time DATETIME NOT NULL,
                price DECIMAL(10, 2) NOT NULL,
                bus_type VARCHAR(50)
            ) ENGINE=InnoDB;
        ");

        db_query($conn, "
            CREATE TABLE IF NOT EXISTS trains (
                train_id INT AUTO_INCREMENT PRIMARY KEY,
                train_name VARCHAR(100) NOT NULL,
                train_number VARCHAR(50) NOT NULL,
                from_station VARCHAR(100) NOT NULL,
                to_station VARCHAR(100) NOT NULL,
                departure_time DATETIME NOT NULL,
                arrival_time DATETIME NOT NULL,
                price DECIMAL(10, 2) NOT NULL
            ) ENGINE=InnoDB;
        ");

        db_query($conn, "
            CREATE TABLE IF NOT EXISTS hotels (
                hotel_id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                city VARCHAR(100) NOT NULL,
                address TEXT,
                description TEXT,
                price_per_night DECIMAL(10, 2) NOT NULL,
                rating DECIMAL(2, 1) DEFAULT 0,
                amenities TEXT,
                main_image TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;
        ");

        // Tours
        db_query($conn, "
            CREATE TABLE IF NOT EXISTS tour_packages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                location VARCHAR(100) NOT NULL,
                duration INT NOT NULL,
                price DECIMAL(10, 2) NOT NULL,
                description TEXT,
                itinerary TEXT,
                main_image TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;
        ");

        // Bookings
        db_query($conn, "
            CREATE TABLE IF NOT EXISTS bookings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                booking_type ENUM('flight', 'hotel', 'tour') NOT NULL,
                reference_id INT NOT NULL,
                booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
                total_amount DECIMAL(10, 2) NOT NULL,
                travel_date TIMESTAMP NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB;
        ");

        // Payments
        db_query($conn, "
            CREATE TABLE IF NOT EXISTS payments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                booking_id INT NOT NULL,
                user_id INT NOT NULL,
                amount DECIMAL(10, 2) NOT NULL,
                payment_status ENUM('success', 'failed', 'pending', 'refunded') DEFAULT 'pending',
                payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                transaction_id VARCHAR(100),
                FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB;
        ");

        // Reviews
        db_query($conn, "
            CREATE TABLE IF NOT EXISTS reviews (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                review_type ENUM('hotel', 'tour', 'flight', 'website') NOT NULL,
                reference_id INT DEFAULT 0,
                rating INT,
                comment TEXT,
                status VARCHAR(20) DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB;
        ");

        // Notifications (per app usage)
        // Ensure subject column exists for notifications
        $colCheck = db_query($conn, "SELECT column_name FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'notifications' AND column_name = 'subject'");
        if ($colCheck && db_num_rows($colCheck) == 0) {
            db_query($conn, "ALTER TABLE notifications ADD COLUMN subject VARCHAR(255) NOT NULL");
        }

        db_query($conn, "
            CREATE TABLE IF NOT EXISTS notifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                subject VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                type VARCHAR(50) DEFAULT 'general',
                email_sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                is_read TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB;
        ");

        // Default admin account
        if (db_table_exists($conn, 'admins')) {
            $check_admin = db_query($conn, "SELECT id FROM admins WHERE username = 'admin'");
            if ($check_admin && db_num_rows($check_admin) == 0) {
                $password = password_hash('admin123', PASSWORD_DEFAULT);
                db_query($conn, "INSERT INTO admins (username, email, password, role) VALUES ('admin', 'admin@example.com', '$password', 'superadmin')");
            }
        } else {
            db_query($conn, "
                CREATE TABLE IF NOT EXISTS admins (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) UNIQUE NOT NULL,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    role VARCHAR(20) DEFAULT 'superadmin',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB;
            ");
            $password = password_hash('admin123', PASSWORD_DEFAULT);
            db_query($conn, "INSERT INTO admins (username, email, password, role) VALUES ('admin', 'admin@example.com', '$password', 'superadmin')");
        }

        // Seed sample data if empty
        $count_airports = db_query($conn, "SELECT COUNT(*) as c FROM airports");
        $airport_count = $count_airports ? (int)db_fetch_assoc($count_airports)['c'] : 0;
        if ($airport_count === 0) {
            db_query($conn, "
                INSERT IGNORE INTO airports (airport_code, airport_name, city, country) VALUES
                ('BOM', 'Bombay International Airport', 'Mumbai', 'India'),
                ('DEL', 'Indira Gandhi International Airport', 'Delhi', 'India'),
                ('BLR', 'Kempegowda International Airport', 'Bangalore', 'India'),
                ('HYD', 'Rajiv Gandhi International Airport', 'Hyderabad', 'India');
            ");
        }

        $count_airlines = db_query($conn, "SELECT COUNT(*) as c FROM airlines");
        $airline_count = $count_airlines ? (int)db_fetch_assoc($count_airlines)['c'] : 0;
        if ($airline_count === 0) {
            db_query($conn, "
                INSERT IGNORE INTO airlines (airline_name, airline_logo) VALUES
                ('Air India', 'https://example.com/logos/airindia.png'),
                ('IndiGo', 'https://example.com/logos/indigo.png'),
                ('Spice Jet', 'https://example.com/logos/spicejet.png'),
                ('Vistara', 'https://example.com/logos/vistara.png');
            ");
        }

        $count_flights = db_query($conn, "SELECT COUNT(*) as c FROM flights");
        $flight_count = $count_flights ? (int)db_fetch_assoc($count_flights)['c'] : 0;
        if ($flight_count === 0) {
            db_query($conn, "
                INSERT IGNORE INTO flights (flight_number, airline_id, departure_airport, arrival_airport, departure_time, arrival_time, base_price, total_seats, available_seats, status) VALUES
                ('AI101', 1, 'BOM', 'DEL', '2026-02-15 08:00:00', '2026-02-15 10:15:00', 4500.00, 60, 45, 'scheduled'),
                ('6E202', 2, 'DEL', 'BLR', '2026-02-15 14:30:00', '2026-02-15 17:45:00', 3800.00, 60, 32, 'scheduled'),
                ('SG303', 3, 'BLR', 'HYD', '2026-02-16 09:00:00', '2026-02-16 10:30:00', 2500.00, 60, 50, 'scheduled'),
                ('UK404', 4, 'HYD', 'BOM', '2026-02-16 18:00:00', '2026-02-16 20:00:00', 3200.00, 60, 55, 'scheduled');
            ");
        }

        $count_buses = db_query($conn, "SELECT COUNT(*) as c FROM buses");
        $bus_count = $count_buses ? (int)db_fetch_assoc($count_buses)['c'] : 0;
        if ($bus_count === 0) {
            db_query($conn, "
                INSERT INTO buses (operator_name, bus_number, from_location, to_location, departure_time, arrival_time, price, bus_type) VALUES
                ('VRL Travels', 'KA-01-AB-1234', 'Bangalore', 'Hyderabad', DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 1 DAY), INTERVAL 8 HOUR), 1200.00, 'AC Sleeper'),
                ('Orange Tours', 'TS-09-CD-5678', 'Hyderabad', 'Bangalore', DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 1 DAY), INTERVAL 9 HOUR), 1100.00, 'AC Semi-Sleeper');
            ");
        }

        $count_trains = db_query($conn, "SELECT COUNT(*) as c FROM trains");
        $train_count = $count_trains ? (int)db_fetch_assoc($count_trains)['c'] : 0;
        if ($train_count === 0) {
            db_query($conn, "
                INSERT INTO trains (train_name, train_number, from_station, to_station, departure_time, arrival_time, price) VALUES
                ('Rajdhani Express', '12433', 'Chennai', 'Delhi', DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(NOW(), INTERVAL 3 DAY), 3500.00),
                ('Shatabdi Express', '12007', 'Chennai', 'Mysore', DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 2 DAY), INTERVAL 7 HOUR), 800.00);
            ");
        }

        $count_hotels = db_query($conn, "SELECT COUNT(*) as c FROM hotels");
        $hotel_count = $count_hotels ? (int)db_fetch_assoc($count_hotels)['c'] : 0;
        if ($hotel_count === 0) {
            db_query($conn, "
                INSERT INTO hotels (name, city, address, description, price_per_night, rating, amenities, main_image) VALUES
                ('Taj Mahal Palace', 'Mumbai', 'Apollo Bunder, Mumbai', 'Luxury waterfront hotel', 15000.00, 5.0, 'Pool, Spa, Wifi', ''),
                ('Hyatt Regency', 'Delhi', 'Bhikaji Cama Place, New Delhi', 'Business & leisure hotel', 12000.00, 4.8, 'Pool, Gym, Wifi', ''),
                ('Goa Beach Resort', 'Goa', 'Calangute Beach, Goa', 'Beachfront resort', 5000.00, 4.2, 'Beach Access, Bar, Wifi', '');
            ");
        }
    }


// Auto-create schema on first load
db_ensure_schema($conn);
?>

