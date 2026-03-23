<?php
include 'database/db.php';

echo "<h2>Fixing 'bookings' table schema:</h2>";

// Check if bookings table exists at all
$tableExists = db_query($conn, "SHOW TABLES LIKE 'bookings'");
if (db_num_rows($tableExists) == 0) {
    echo "Table 'bookings' does not exist. Creating it now...<br>";
    $createSql = "CREATE TABLE bookings (
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
    if (db_query($conn, $createSql)) {
        echo "<p style='color: green;'>✓ Successfully created 'bookings' table.</p>";
    }
}

// Check if total_amount exists
$columnCheck = db_query($conn, "SHOW COLUMNS FROM bookings LIKE 'total_amount'");
if (db_num_rows($columnCheck) == 0) {
    echo "Column 'total_amount' missing. Adding it now...<br>";
    $alterSql = "ALTER TABLE bookings ADD COLUMN total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER status";
    if (db_query($conn, $alterSql)) {
        echo "<p style='color: green;'>✓ Successfully added 'total_amount' column to 'bookings' table.</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding 'total_amount' column: " . db_last_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: blue;'>i 'total_amount' column already exists in 'bookings' table.</p>";
}

// Ensure booking_type exists
$columnCheck = db_query($conn, "SHOW COLUMNS FROM bookings LIKE 'booking_type'");
if (db_num_rows($columnCheck) == 0) {
    $columnCheck2 = db_query($conn, "SHOW COLUMNS FROM bookings LIKE 'service_type'");
    if (db_num_rows($columnCheck2) > 0) {
        echo "Renaming 'service_type' to 'booking_type'...<br>";
        $alterSql = "ALTER TABLE bookings CHANGE service_type booking_type VARCHAR(50)";
        if (db_query($conn, $alterSql)) {
            echo "<p style='color: green;'>✓ Successfully renamed 'service_type' to 'booking_type'.</p>";
        }
    } else {
        echo "Column 'booking_type' missing. Adding it now...<br>";
        $alterSql = "ALTER TABLE bookings ADD COLUMN booking_type VARCHAR(50) AFTER user_id";
        if (db_query($conn, $alterSql)) {
            echo "<p style='color: green;'>✓ Successfully added 'booking_type' column.</p>";
        }
    }
} else {
    echo "<p style='color: blue;'>i 'booking_type' column already exists.</p>";
}

// Ensure reference_id exists
$columnCheck = db_query($conn, "SHOW COLUMNS FROM bookings LIKE 'reference_id'");
if (db_num_rows($columnCheck) == 0) {
    echo "Column 'reference_id' missing. Adding it now...<br>";
    $alterSql = "ALTER TABLE bookings ADD COLUMN reference_id INT AFTER booking_type";
    if (db_query($conn, $alterSql)) {
        echo "<p style='color: green;'>✓ Successfully added 'reference_id' column.</p>";
    }
} else {
    echo "<p style='color: blue;'>i 'reference_id' column already exists.</p>";
}

// Ensure booking_date exists (rename created_at if it exists)
$columnCheck = db_query($conn, "SHOW COLUMNS FROM bookings LIKE 'booking_date'");
if (db_num_rows($columnCheck) == 0) {
    $columnCheck2 = db_query($conn, "SHOW COLUMNS FROM bookings LIKE 'created_at'");
    if (db_num_rows($columnCheck2) > 0) {
        echo "Renaming 'created_at' to 'booking_date'...<br>";
        $alterSql = "ALTER TABLE bookings CHANGE created_at booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        if (db_query($conn, $alterSql)) {
            echo "<p style='color: green;'>✓ Successfully renamed 'created_at' to 'booking_date'.</p>";
        }
    } else {
        echo "Column 'booking_date' missing. Adding it now...<br>";
        $alterSql = "ALTER TABLE bookings ADD COLUMN booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER reference_id";
        if (db_query($conn, $alterSql)) {
            echo "<p style='color: green;'>✓ Successfully added 'booking_date' column.</p>";
        }
    }
} else {
    echo "<p style='color: blue;'>i 'booking_date' column already exists.</p>";
}

// Ensure travel_date exists
$columnCheck = db_query($conn, "SHOW COLUMNS FROM bookings LIKE 'travel_date'");
if (db_num_rows($columnCheck) == 0) {
    echo "Column 'travel_date' missing. Adding it now...<br>";
    $alterSql = "ALTER TABLE bookings ADD COLUMN travel_date TIMESTAMP NULL AFTER booking_date";
    if (db_query($conn, $alterSql)) {
        echo "<p style='color: green;'>✓ Successfully added 'travel_date' column.</p>";
    }
} else {
    echo "<p style='color: blue;'>i 'travel_date' column already exists.</p>";
}

echo "<h3>Current schema of 'bookings':</h3>";
$res = db_query($conn, "DESCRIBE bookings");
if ($res) {
    echo "<table border='1'><tr><th>Field</th><th>Type</th></tr>";
    while ($row = db_fetch_assoc($res)) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td></tr>";
    }
    echo "</table>";
}

echo "<p><a href='user/my_booking_standlone.php'>Return to My Bookings</a></p>";
?>