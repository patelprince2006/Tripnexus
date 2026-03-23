<?php
include 'database/db.php';

echo "<h2>Populating Sample Data</h2>";

// 1. BUSES
$buses = [
    ['VRL Travels', 'KA-01-F-1234', 'Mumbai', 'Goa', '2026-03-23 20:00:00', '2026-03-24 08:00:00', 1200.00, 'AC Sleeper'],
    ['SRS Travels', 'KA-02-F-5678', 'Bangalore', 'Hyderabad', '2026-03-23 22:00:00', '2026-03-24 07:00:00', 900.00, 'Semi-Sleeper'],
    ['Zingbus', 'DL-01-A-9999', 'Delhi', 'Manali', '2026-03-23 19:30:00', '2026-03-24 09:00:00', 1500.00, 'AC Sleeper'],
    ['Orange Travels', 'AP-03-B-4444', 'Hyderabad', 'Chennai', '2026-03-23 21:00:00', '2026-03-24 06:30:00', 1100.00, 'AC Sleeper'],
    ['KPN Travels', 'TN-01-C-1111', 'Chennai', 'Bangalore', '2026-03-23 23:00:00', '2026-03-24 05:45:00', 850.00, 'Semi-Sleeper']
];

foreach ($buses as $b) {
    db_query($conn, "INSERT INTO buses (operator_name, bus_number, from_location, to_location, departure_time, arrival_time, price, bus_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", $b);
    echo "Added Bus: {$b[0]} ({$b[2]} to {$b[3]})<br>";
}

// 2. TRAINS
$trains = [
    ['Rajdhani Express', '12431', 'Delhi', 'Mumbai', '2026-03-23 16:30:00', '2026-03-24 08:30:00', 3500.00, 50],
    ['Shatabdi Express', '12002', 'Delhi', 'Bhopal', '2026-03-23 06:15:00', '2026-03-23 14:10:00', 1500.00, 80],
    ['Duronto Express', '12267', 'Mumbai', 'Ahmedabad', '2026-03-23 23:25:00', '2026-03-24 05:55:00', 2200.00, 60],
    ['Chennai Express', '12163', 'Mumbai', 'Chennai', '2026-03-23 20:35:00', '2026-03-24 16:45:00', 1800.00, 100],
    ['Brindavan Express', '12640', 'Bangalore', 'Chennai', '2026-03-23 15:10:00', '2026-03-23 21:10:00', 700.00, 120]
];

foreach ($trains as $t) {
    db_query($conn, "INSERT INTO trains (train_name, train_number, from_station, to_station, departure_time, arrival_time, price, available_seats) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", $t);
    echo "Added Train: {$t[0]} ({$t[2]} to {$t[3]})<br>";
}

// 3. HOTELS
$hotels = [
    ['The Taj Mahal Palace', 'Mumbai', 'Apollo Bunder, Mumbai', 'Wifi, Pool, Gym, Spa', 15000.00, 5.0],
    ['Oberoi Grand', 'Kolkata', 'Chowringhee Road, Kolkata', 'Wifi, Restaurant, Bar', 8000.00, 4.8],
    ['JW Marriott', 'Bangalore', 'Vittal Mallya Rd, Bangalore', 'Wifi, Pool, Lounge', 12000.00, 4.7],
    ['Radisson Blu', 'Delhi', 'Mahipalpur, New Delhi', 'Wifi, Airport Shuttle', 6000.00, 4.5],
    ['Novotel Goa', 'Goa', 'Candolim, Goa', 'Pool, Beach Access, Wifi', 5500.00, 4.3]
];

foreach ($hotels as $h) {
    db_query($conn, "INSERT INTO hotels (name, city, address, amenities, price_per_night, rating) VALUES (?, ?, ?, ?, ?, ?)", $h);
    echo "Added Hotel: {$h[0]} in {$h[1]}<br>";
}

echo "<br>Done!";
?>
