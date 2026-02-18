<?php
// admin/setup_admin_db.php
include '../db.php';

echo "<h1>Setting up Admin Database...</h1>";

// 1. Create Admins Table
$sql_admins = "
CREATE TABLE IF NOT EXISTS admins (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'superadmin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";
$res = pg_query($conn, $sql_admins);
if ($res) {
    echo "Table 'admins' created successfully.<br>";
} else {
    echo "Error creating 'admins' table: " . pg_last_error($conn) . "<br>";
    // Try to check if table exists
    $check_table = pg_query($conn, "SELECT to_regclass('admins')");
    if ($check_table) {
        $table_exists = pg_fetch_result($check_table, 0, 0);
        if ($table_exists) {
            echo "Table 'admins' already exists.<br>";
        }
    }
}

// Insert Default Admin (admin / admin123)
$password = password_hash("admin123", PASSWORD_DEFAULT);
$check_admin = pg_query($conn, "SELECT * FROM admins WHERE username = 'admin'");
if ($check_admin && pg_num_rows($check_admin) == 0) {
    $insert_admin = "INSERT INTO admins (username, email, password, role) VALUES ('admin', 'admin@example.com', '$password', 'superadmin')";
    $result = pg_query($conn, $insert_admin);
    if ($result) {
        echo "Default admin user created (admin / admin123).<br>";
    } else {
        echo "Error creating admin user: " . pg_last_error($conn) . "<br>";
    }
} else {
    echo "Admin user already exists.<br>";
}

// 2. Update Users Table (Add status column)
$check_col = pg_query($conn, "SELECT column_name FROM information_schema.columns WHERE table_name='users' AND column_name='status'");
if (pg_num_rows($check_col) == 0) {
    $alter_res = pg_query($conn, "ALTER TABLE users ADD COLUMN status VARCHAR(20) DEFAULT 'active'");
    if ($alter_res) echo "Column 'status' added to 'users' table.<br>";
}

// 3. Create Hotels Table
$sql_hotels = "
CREATE TABLE IF NOT EXISTS hotels (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    city VARCHAR(50) NOT NULL,
    address TEXT NOT NULL,
    description TEXT,
    price_per_night DECIMAL(10, 2) NOT NULL,
    rating DECIMAL(2, 1) DEFAULT 0,
    main_image TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";
pg_query($conn, $sql_hotels);

// 4. Create Hotel Rooms Table
$sql_rooms = "
CREATE TABLE IF NOT EXISTS hotel_rooms (
    id SERIAL PRIMARY KEY,
    hotel_id INT REFERENCES hotels(id) ON DELETE CASCADE,
    room_type VARCHAR(50) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    available_count INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";
pg_query($conn, $sql_rooms);

// 5. Create Tour Packages Table
$sql_tours = "
CREATE TABLE IF NOT EXISTS tour_packages (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    duration INT NOT NULL, -- in days
    price DECIMAL(10, 2) NOT NULL,
    description TEXT,
    itinerary TEXT, -- Store as JSON or text
    main_image TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";
pg_query($conn, $sql_tours);

// 6. Create Bookings Table
// Polymorphic association for flight/hotel/tour.
// We need to support linking to different tables.
// A simple way is separate tables, but unified table is better for "All Bookings".
// We will use booking_type and reference_id.
$sql_bookings = "
CREATE TABLE IF NOT EXISTS bookings (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    booking_type VARCHAR(20) NOT NULL CHECK (booking_type IN ('flight', 'hotel', 'tour')),
    reference_id INT NOT NULL, -- flight_id / hotel_id / tour_id
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'confirmed', 'cancelled', 'completed')),
    total_amount DECIMAL(10, 2) NOT NULL,
    travel_date TIMESTAMP
);
";
pg_query($conn, $sql_bookings);

// 7. Create Payments Table
$sql_payments = "
CREATE TABLE IF NOT EXISTS payments (
    id SERIAL PRIMARY KEY,
    booking_id INT REFERENCES bookings(id) ON DELETE CASCADE,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    amount DECIMAL(10, 2) NOT NULL,
    payment_status VARCHAR(20) DEFAULT 'pending' CHECK (payment_status IN ('success', 'failed', 'pending', 'refunded')),
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    transaction_id VARCHAR(100)
);
";
pg_query($conn, $sql_payments);

// 8. Create Reviews Table
$sql_reviews = "
CREATE TABLE IF NOT EXISTS reviews (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    review_type VARCHAR(20) NOT NULL CHECK (review_type IN ('hotel', 'tour', 'flight', 'website')),
    reference_id INT DEFAULT 0,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    status VARCHAR(20) DEFAULT 'pending', -- pending/approved/rejected
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";
pg_query($conn, $sql_reviews);

// 9. Generate Notification Table
$sql_notifs = "
CREATE TABLE IF NOT EXISTS notifications (
    id SERIAL PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'general',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";
pg_query($conn, $sql_notifs);


echo "<br><strong>Setup Complete.</strong>";
?>
