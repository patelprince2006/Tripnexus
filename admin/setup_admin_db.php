<?php
// admin/setup_admin_db.php
include '../db.php';

echo "<h1>Setting up Admin Database...</h1>";

// 1. Create Admins Table
$sql_admins = "
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'superadmin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
";
$res = db_query($conn, $sql_admins);
if ($res) {
    echo "Table 'admins' created successfully.<br>";
} else {
    echo "Error creating 'admins' table: " . db_last_error($conn) . "<br>";
    // Try to check if table exists
    $check_table = db_query($conn, "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'admins'");
    if ($check_table) {
        $table_exists = (int) db_fetch_value($check_table, 0, 0);
        if ($table_exists > 0) {
            echo "Table 'admins' already exists.<br>";
        }
    }
}

// Insert Default Admin (admin / admin123)
$password = password_hash("admin123", PASSWORD_DEFAULT);
$check_admin = db_query($conn, "SELECT * FROM admins WHERE username = 'admin'");
if ($check_admin && db_num_rows($check_admin) == 0) {
    $insert_admin = "INSERT INTO admins (username, email, password, role) VALUES ('admin', 'admin@example.com', '$password', 'superadmin')";
    $result = db_query($conn, $insert_admin);
    if ($result) {
        echo "Default admin user created (admin / admin123).<br>";
    } else {
        echo "Error creating admin user: " . db_last_error($conn) . "<br>";
    }
} else {
    echo "Admin user already exists.<br>";
}

// 2. Update Users Table (Add status column)
$check_col = db_query($conn, "SELECT column_name FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name='users' AND column_name='status'");
if ($check_col && db_num_rows($check_col) == 0) {
    $alter_res = db_query($conn, "ALTER TABLE users ADD COLUMN status VARCHAR(20) DEFAULT 'active'");
    if ($alter_res) {
        echo "Column 'status' added to 'users' table.<br>";
    }
}

// 3. Create Hotels Table
$sql_hotels = "
CREATE TABLE IF NOT EXISTS hotels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    city VARCHAR(50) NOT NULL,
    address TEXT NOT NULL,
    description TEXT,
    price_per_night DECIMAL(10, 2) NOT NULL,
    rating DECIMAL(2, 1) DEFAULT 0,
    main_image TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
";
db_query($conn, $sql_hotels);

// 4. Create Hotel Rooms Table
$sql_rooms = "
CREATE TABLE IF NOT EXISTS hotel_rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    room_type VARCHAR(50) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    available_count INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE
) ENGINE=InnoDB;
";
db_query($conn, $sql_rooms);

// 5. Create Tour Packages Table
$sql_tours = "
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
";
db_query($conn, $sql_tours);

// 6. Create Bookings Table
$sql_bookings = "
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
";
db_query($conn, $sql_bookings);

// 7. Create Payments Table
$sql_payments = "
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
";
db_query($conn, $sql_payments);

// 8. Create Reviews Table
$sql_reviews = "
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
";
db_query($conn, $sql_reviews);

// 9. Generate Notification Table
$sql_notifs = "
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'general',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
";
db_query($conn, $sql_notifs);


echo "<br><strong>Setup Complete.</strong>";
?>
