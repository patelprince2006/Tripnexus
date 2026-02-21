<?php
include 'db.php';

// SQL queries to create tables
$queries = [
    "CREATE TABLE IF NOT EXISTS buses (
        bus_id SERIAL PRIMARY KEY,
        operator_name VARCHAR(100) NOT NULL,
        bus_number VARCHAR(50),
        from_location VARCHAR(100) NOT NULL,
        to_location VARCHAR(100) NOT NULL,
        departure_time TIMESTAMP NOT NULL,
        arrival_time TIMESTAMP NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        bus_type VARCHAR(50) -- e.g., 'AC Sleeper', 'Non-AC Seater'
    )",
    "CREATE TABLE IF NOT EXISTS trains (
        train_id SERIAL PRIMARY KEY,
        train_name VARCHAR(100) NOT NULL,
        train_number VARCHAR(50) NOT NULL,
        from_station VARCHAR(100) NOT NULL,
        to_station VARCHAR(100) NOT NULL,
        departure_time TIMESTAMP NOT NULL,
        arrival_time TIMESTAMP NOT NULL,
        price DECIMAL(10, 2) NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS hotels (
        hotel_id SERIAL PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        city VARCHAR(100) NOT NULL,
        address TEXT,
        price_per_night DECIMAL(10, 2) NOT NULL,
        rating DECIMAL(2, 1) DEFAULT 0,
        amenities TEXT -- stored as comma separated values or JSON
    )"
];

foreach ($queries as $query) {
    $result = pg_query($conn, $query);
    if ($result) {
        echo "Table created successfully or already exists.<br>";
    } else {
        echo "Error creating table: " . pg_last_error($conn) . "<br>";
    }
}

// Insert dummy data for demonstration if tables are empty
// Check if buses table is empty
$check_buses = pg_query($conn, "SELECT COUNT(*) FROM buses");
$bus_count = pg_fetch_result($check_buses, 0, 0);

if ($bus_count == 0) {
    $insert_buses = "INSERT INTO buses (operator_name, bus_number, from_location, to_location, departure_time, arrival_time, price, bus_type) VALUES 
    ('VRL Travels', 'KA-01-AB-1234', 'Bangalore', 'Hyderabad', NOW() + INTERVAL '1 day', NOW() + INTERVAL '1 day 8 hours', 1200.00, 'AC Sleeper'),
    ('Orange Tours', 'TS-09-CD-5678', 'Hyderabad', 'Bangalore', NOW() + INTERVAL '1 day', NOW() + INTERVAL '1 day 9 hours', 1100.00, 'AC Semi-Sleeper')";
    pg_query($conn, $insert_buses);
    echo "Inserted dummy bus data.<br>";
}

// Check if trains table is empty
$check_trains = pg_query($conn, "SELECT COUNT(*) FROM trains");
$train_count = pg_fetch_result($check_trains, 0, 0);

if ($train_count == 0) {
    $insert_trains = "INSERT INTO trains (train_name, train_number, from_station, to_station, departure_time, arrival_time, price) VALUES 
    ('Rajdhani Express', '12433', 'Chennai', 'Delhi', NOW() + INTERVAL '2 days', NOW() + INTERVAL '3 days', 3500.00),
    ('Shatabdi Express', '12007', 'Chennai', 'Mysore', NOW() + INTERVAL '2 days', NOW() + INTERVAL '2 days 7 hours', 800.00)";
    pg_query($conn, $insert_trains);
    echo "Inserted dummy train data.<br>";
}

// Check if hotels table is empty
$check_hotels = pg_query($conn, "SELECT COUNT(*) FROM hotels");
$hotel_count = pg_fetch_result($check_hotels, 0, 0);

if ($hotel_count == 0) {
    $insert_hotels = "INSERT INTO hotels (name, city, address, price_per_night, rating, amenities) VALUES 
    ('Taj Mahal Palace', 'Mumbai', 'Apollo Bunder, Mumbai', 15000.00, 5.0, 'Pool, Spa, Wifi'),
    ('Hyatt Regency', 'Delhi', 'Bhikaji Cama Place, New Delhi', 12000.00, 4.8, 'Pool, Gym, Wifi'),
    ('Goa Beach Resort', 'Goa', 'Calangute Beach, Goa', 5000.00, 4.2, 'Beach Access, Bar, Wifi')";
    pg_query($conn, $insert_hotels);
    echo "Inserted dummy hotel data.<br>";
}

echo "Database setup complete.";
?>
