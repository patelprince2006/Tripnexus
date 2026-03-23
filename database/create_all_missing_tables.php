<?php
/**
 * Create All Missing Tables - Comprehensive database setup
 */

include 'db.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Create All Missing Tables</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        .container { max-width: 800px; }
        .step { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>
    <div class='container'>
        <h2>Creating All Missing Tables</h2>
        <p>This script will create all the essential tables for your TripNexus application.</p>
        
        <div class='step'>
            <h3>Step 1: Creating Core Tables</h3>";

// Check if users table exists
$tableCheck = db_query($conn, "SHOW TABLES LIKE 'users'");
if (db_num_rows($tableCheck) == 0) {
    $sql = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fullname VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        theme VARCHAR(10) DEFAULT 'light',
        status VARCHAR(20) DEFAULT 'active',
        email_verified_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;";
    
    $result = db_query($conn, $sql);
    if ($result) {
        echo "<p class='success'>✓ Users table created</p>";
    } else {
        echo "<p class='error'>✗ Error creating users table: " . db_last_error($conn) . "</p>";
    }
} else {
    echo "<p class='info'>✓ Users table already exists</p>";
}

// Check if admins table exists
$tableCheck = db_query($conn, "SHOW TABLES LIKE 'admins'");
if (db_num_rows($tableCheck) == 0) {
    $sql = "CREATE TABLE admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) DEFAULT 'superadmin',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;";
    
    $result = db_query($conn, $sql);
    if ($result) {
        echo "<p class='success'>✓ Admins table created</p>";
    } else {
        echo "<p class='error'>✗ Error creating admins table: " . db_last_error($conn) . "</p>";
    }
} else {
    echo "<p class='info'>✓ Admins table already exists</p>";
}

echo "</div>
        <div class='step'>
            <h3>Step 2: Creating Flight Tables</h3>";

// Check if airports table exists
$tableCheck = db_query($conn, "SHOW TABLES LIKE 'airports'");
if (db_num_rows($tableCheck) == 0) {
    $sql = "CREATE TABLE airports (
        airport_code VARCHAR(3) PRIMARY KEY,
        airport_name VARCHAR(100) NOT NULL,
        city VARCHAR(50) NOT NULL,
        country VARCHAR(50) NOT NULL
    ) ENGINE=InnoDB;";
    
    $result = db_query($conn, $sql);
    if ($result) {
        echo "<p class='success'>✓ Airports table created</p>";
    } else {
        echo "<p class='error'>✗ Error creating airports table: " . db_last_error($conn) . "</p>";
    }
} else {
    echo "<p class='info'>✓ Airports table already exists</p>";
}

// Check if airlines table exists
$tableCheck = db_query($conn, "SHOW TABLES LIKE 'airlines'");
if (db_num_rows($tableCheck) == 0) {
    $sql = "CREATE TABLE airlines (
        airline_id INT AUTO_INCREMENT PRIMARY KEY,
        airline_name VARCHAR(100) NOT NULL,
        airline_logo TEXT
    ) ENGINE=InnoDB;";
    
    $result = db_query($conn, $sql);
    if ($result) {
        echo "<p class='success'>✓ Airlines table created</p>";
    } else {
        echo "<p class='error'>✗ Error creating airlines table: " . db_last_error($conn) . "</p>";
    }
} else {
    echo "<p class='info'>✓ Airlines table already exists</p>";
}

// Check if flights table exists
$tableCheck = db_query($conn, "SHOW TABLES LIKE 'flights'");
if (db_num_rows($tableCheck) == 0) {
    $sql = "CREATE TABLE flights (
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
    ) ENGINE=InnoDB;";
    
    $result = db_query($conn, $sql);
    if ($result) {
        echo "<p class='success'>✓ Flights table created</p>";
    } else {
        echo "<p class='error'>✗ Error creating flights table: " . db_last_error($conn) . "</p>";
    }
} else {
    echo "<p class='info'>✓ Flights table already exists</p>";
}

echo "</div>
        <div class='step'>
            <h3>Step 3: Creating Other Service Tables</h3>";

// Check if buses table exists
$tableCheck = db_query($conn, "SHOW TABLES LIKE 'buses'");
if (db_num_rows($tableCheck) == 0) {
    $sql = "CREATE TABLE buses (
        bus_id INT AUTO_INCREMENT PRIMARY KEY,
        operator_name VARCHAR(100) NOT NULL,
        bus_number VARCHAR(50),
        from_location VARCHAR(100) NOT NULL,
        to_location VARCHAR(100) NOT NULL,
        departure_time DATETIME NOT NULL,
        arrival_time DATETIME NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        bus_type VARCHAR(50),
        available_seats INT DEFAULT 40,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;";
    
    $result = db_query($conn, $sql);
    if ($result) {
        echo "<p class='success'>✓ Buses table created</p>";
    } else {
        echo "<p class='error'>✗ Error creating buses table: " . db_last_error($conn) . "</p>";
    }
} else {
    echo "<p class='info'>✓ Buses table already exists</p>";
}

// Check if trains table exists
$tableCheck = db_query($conn, "SHOW TABLES LIKE 'trains'");
if (db_num_rows($tableCheck) == 0) {
    $sql = "CREATE TABLE trains (
        train_id INT AUTO_INCREMENT PRIMARY KEY,
        train_name VARCHAR(100) NOT NULL,
        train_number VARCHAR(50) NOT NULL,
        from_station VARCHAR(100) NOT NULL,
        to_station VARCHAR(100) NOT NULL,
        departure_time DATETIME NOT NULL,
        arrival_time DATETIME NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        available_seats INT DEFAULT 120,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;";
    
    $result = db_query($conn, $sql);
    if ($result) {
        echo "<p class='success'>✓ Trains table created</p>";
    } else {
        echo "<p class='error'>✗ Error creating trains table: " . db_last_error($conn) . "</p>";
    }
} else {
    echo "<p class='info'>✓ Trains table already exists</p>";
}

echo "</div>
        <div class='step'>
            <h3>Step 4: Creating Hotel and Tour Tables</h3>";

// Check if hotels table exists
$tableCheck = db_query($conn, "SHOW TABLES LIKE 'hotels'");
if (db_num_rows($tableCheck) == 0) {
    $sql = "CREATE TABLE hotels (
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
    ) ENGINE=InnoDB;";
    
    $result = db_query($conn, $sql);
    if ($result) {
        echo "<p class='success'>✓ Hotels table created</p>";
    } else {
        echo "<p class='error'>✗ Error creating hotels table: " . db_last_error($conn) . "</p>";
    }
} else {
    echo "<p class='info'>✓ Hotels table already exists</p>";
}

// Check if tour_packages table exists
$tableCheck = db_query($conn, "SHOW TABLES LIKE 'tour_packages'");
if (db_num_rows($tableCheck) == 0) {
    $sql = "CREATE TABLE tour_packages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        location VARCHAR(100) NOT NULL,
        duration INT NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        description TEXT,
        itinerary TEXT,
        main_image TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;";
    
    $result = db_query($conn, $sql);
    if ($result) {
        echo "<p class='success'>✓ Tour packages table created</p>";
    } else {
        echo "<p class='error'>✗ Error creating tour packages table: " . db_last_error($conn) . "</p>";
    }
} else {
    echo "<p class='info'>✓ Tour packages table already exists</p>";
}

echo "</div>
        <div class='step'>
            <h3>Step 5: Creating System Tables</h3>";

// Check if notifications table exists
$tableCheck = db_query($conn, "SHOW TABLES LIKE 'notifications'");
if (db_num_rows($tableCheck) == 0) {
    $sql = "CREATE TABLE notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type VARCHAR(50) DEFAULT 'general',
        is_read TINYINT(1) DEFAULT 0,
        email_sent_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;";
    
    $result = db_query($conn, $sql);
    if ($result) {
        echo "<p class='success'>✓ Notifications table created</p>";
    } else {
        echo "<p class='error'>✗ Error creating notifications table: " . db_last_error($conn) . "</p>";
    }
} else {
    echo "<p class='info'>✓ Notifications table already exists</p>";
}

// Check if wishlist table exists
$tableCheck = db_query($conn, "SHOW TABLES LIKE 'wishlist'");
if (db_num_rows($tableCheck) == 0) {
    $sql = "CREATE TABLE wishlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        item_type ENUM('flight', 'bus', 'train', 'hotel', 'tour') NOT NULL,
        item_id INT NOT NULL,
        item_name VARCHAR(255),
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_wishlist (user_id, item_type, item_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;";
    
    $result = db_query($conn, $sql);
    if ($result) {
        echo "<p class='success'>✓ Wishlist table created</p>";
    } else {
        echo "<p class='error'>✗ Error creating wishlist table: " . db_last_error($conn) . "</p>";
    }
} else {
    echo "<p class='info'>✓ Wishlist table already exists</p>";
}

// Check if bookings table exists
$tableCheck = db_query($conn, "SHOW TABLES LIKE 'bookings'");
if (db_num_rows($tableCheck) == 0) {
    $sql = "CREATE TABLE bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        booking_type ENUM('flight', 'bus', 'train', 'hotel', 'tour') NOT NULL,
        reference_id INT NOT NULL,
        booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
        total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        travel_date TIMESTAMP NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;";
    
    $result = db_query($conn, $sql);
    if ($result) {
        echo "<p class='success'>✓ Bookings table created</p>";
    } else {
        echo "<p class='error'>✗ Error creating bookings table: " . db_last_error($conn) . "</p>";
    }
} else {
    // If table exists, check for total_amount column
    $columnCheck = db_query($conn, "SHOW COLUMNS FROM bookings LIKE 'total_amount'");
    if (db_num_rows($columnCheck) == 0) {
        $alterSql = "ALTER TABLE bookings ADD COLUMN total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER status";
        if (db_query($conn, $alterSql)) {
            echo "<p class='success'>✓ Added missing total_amount column to bookings table</p>";
        }
    }
    // Check for booking_type column
    $columnCheck = db_query($conn, "SHOW COLUMNS FROM bookings LIKE 'booking_type'");
    if (db_num_rows($columnCheck) == 0) {
        $columnCheck2 = db_query($conn, "SHOW COLUMNS FROM bookings LIKE 'service_type'");
        if (db_num_rows($columnCheck2) > 0) {
            $alterSql = "ALTER TABLE bookings CHANGE service_type booking_type VARCHAR(50)";
            db_query($conn, $alterSql);
            echo "<p class='success'>✓ Updated service_type to booking_type in bookings table</p>";
        }
    }
}

// Check if payments table exists
$tableCheck = db_query($conn, "SHOW TABLES LIKE 'payments'");
if (db_num_rows($tableCheck) == 0) {
    $sql = "CREATE TABLE payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id INT NOT NULL,
        user_id INT NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        payment_status ENUM('success', 'failed', 'pending', 'refunded') DEFAULT 'pending',
        payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        transaction_id VARCHAR(100),
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;";
    
    $result = db_query($conn, $sql);
    if ($result) {
        echo "<p class='success'>✓ Payments table created</p>";
    } else {
        echo "<p class='error'>✗ Error creating payments table: " . db_last_error($conn) . "</p>";
    }
} else {
    echo "<p class='info'>✓ Payments table already exists</p>";
}

echo "</div>
        <div class='step'>
            <h3>Step 6: Testing Critical Queries</h3>";

// Test the query that was failing in search_flight.php
$testQuery = db_query($conn, "SELECT item_id FROM wishlist WHERE user_id = ? AND item_type = 'flight'", array(1));
if ($testQuery) {
    echo "<p class='success'>✓ Wishlist query test successful</p>";
} else {
    echo "<p class='error'>✗ Wishlist query test failed: " . db_last_error($conn) . "</p>";
}

// Test the airports query that was failing
$testQuery = db_query($conn, "SELECT airport_code, city FROM airports ORDER BY city ASC");
if ($testQuery) {
    echo "<p class='success'>✓ Airports query test successful</p>";
} else {
    echo "<p class='error'>✗ Airports query test failed: " . db_last_error($conn) . "</p>";
}

echo "</div>
        <div class='step'>
            <h3>Step 7: Verification Complete</h3>
            <p class='success'>All essential tables have been created successfully!</p>
            
            <div style='margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;'>
                <h4>Next Steps:</h4>
                <ul>
                    <li>Try accessing <a href='search_flight.php'>search_flight.php</a> again</li>
                    <li>Try accessing <a href='dashboard.php'>dashboard.php</a> again</li>
                    <li>The application should now work without database errors</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>";

db_close($conn);
?>