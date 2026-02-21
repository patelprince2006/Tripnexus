<?php
// Database Setup & Insert Sample Data
require 'db.php';

// Create tables if they don't exist
$create_airports = "
CREATE TABLE IF NOT EXISTS airports (
    airport_code VARCHAR(3) PRIMARY KEY,
    airport_name VARCHAR(100) NOT NULL,
    city VARCHAR(50) NOT NULL,
    country VARCHAR(50) NOT NULL
);
";

$create_airlines = "
CREATE TABLE IF NOT EXISTS airlines (
    airline_id SERIAL PRIMARY KEY,
    airline_name VARCHAR(100) NOT NULL,
    airline_logo TEXT
);
";

$create_flights = "
CREATE TABLE IF NOT EXISTS flights (
    flight_id SERIAL PRIMARY KEY,
    flight_number VARCHAR(10) UNIQUE NOT NULL,
    airline_id INT REFERENCES airlines(airline_id),
    departure_airport VARCHAR(3) REFERENCES airports(airport_code),
    arrival_airport VARCHAR(3) REFERENCES airports(airport_code),
    departure_time TIMESTAMPTZ NOT NULL,
    arrival_time TIMESTAMPTZ NOT NULL,
    base_price DECIMAL(10, 2) NOT NULL,
    total_seats INT DEFAULT 60,
    available_seats INT NOT NULL,
    status VARCHAR(20) DEFAULT 'scheduled' CHECK (status IN ('scheduled', 'boarding', 'departed', 'landed', 'cancelled')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT check_seats CHECK (available_seats >= 0)
);
";

// Execute table creation
pg_query($conn, $create_airports);
pg_query($conn, $create_airlines);
pg_query($conn, $create_flights);

echo "<h2>Step 1: Tables Created ✓</h2>";

// Insert sample airports
$airports_sql = "
INSERT INTO airports (airport_code, airport_name, city, country) VALUES
('BOM', 'Bombay International Airport', 'Mumbai', 'India'),
('DEL', 'Indira Gandhi International Airport', 'Delhi', 'India'),
('BLR', 'Kempegowda International Airport', 'Bangalore', 'India'),
('HYD', 'Rajiv Gandhi International Airport', 'Hyderabad', 'India')
ON CONFLICT (airport_code) DO NOTHING;
";

pg_query($conn, $airports_sql);
echo "<h2>Step 2: Airports Inserted ✓</h2>";

// Insert sample airlines
$airlines_sql = "
INSERT INTO airlines (airline_name, airline_logo) VALUES
('Air India', 'https://example.com/logos/airindia.png'),
('IndiGo', 'https://example.com/logos/indigo.png'),
('Spice Jet', 'https://example.com/logos/spicejet.png'),
('Vistara', 'https://example.com/logos/vistara.png')
ON CONFLICT DO NOTHING;
";

pg_query($conn, $airlines_sql);
echo "<h2>Step 3: Airlines Inserted ✓</h2>";

// Insert sample flights
$flights_sql = "
INSERT INTO flights (flight_number, airline_id, departure_airport, arrival_airport, departure_time, arrival_time, base_price, total_seats, available_seats, status) VALUES
('AI101', 1, 'BOM', 'DEL', '2026-02-15 08:00:00+05:30', '2026-02-15 10:15:00+05:30', 4500.00, 60, 45, 'scheduled'),
('6E202', 2, 'DEL', 'BLR', '2026-02-15 14:30:00+05:30', '2026-02-15 17:45:00+05:30', 3800.00, 60, 32, 'scheduled'),
('SG303', 3, 'BLR', 'HYD', '2026-02-16 09:00:00+05:30', '2026-02-16 10:30:00+05:30', 2500.00, 60, 50, 'scheduled'),
('UK404', 4, 'HYD', 'BOM', '2026-02-16 18:00:00+05:30', '2026-02-16 20:00:00+05:30', 3200.00, 60, 55, 'scheduled')
ON CONFLICT (flight_number) DO NOTHING;
";

pg_query($conn, $flights_sql);
echo "<h2>Step 4: Flights Inserted ✓</h2>";

// Verify data was inserted
$verify_airports = pg_query($conn, "SELECT COUNT(*) as count FROM airports;");
$verify_airlines = pg_query($conn, "SELECT COUNT(*) as count FROM airlines;");
$verify_flights = pg_query($conn, "SELECT COUNT(*) as count FROM flights;");

$airport_count = pg_fetch_assoc($verify_airports)['count'];
$airline_count = pg_fetch_assoc($verify_airlines)['count'];
$flight_count = pg_fetch_assoc($verify_flights)['count'];

echo "
<div style='background: #f0f0f0; padding: 20px; border-radius: 8px; margin-top: 20px;'>
    <h3>✅ Database Setup Complete!</h3>
    <p><strong>Airports:</strong> $airport_count records</p>
    <p><strong>Airlines:</strong> $airline_count records</p>
    <p><strong>Flights:</strong> $flight_count records</p>
    <br>
    <a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;'>Go Back to Home</a>
</div>
";

pg_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 50px;
        }
        h2 {
            color: #27ae60;
            margin: 20px 0;
        }
        h3 {
            color: #333;
        }
    </style>
</head>
</html>
