-- Migration: 004
-- Description: Create bookings table
-- Date: 2026-02-12

-- Updated Migration: 004
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_type VARCHAR(50),
    destination VARCHAR(255),
    starting_point VARCHAR(255),
    booking_date DATE DEFAULT CURRENT_DATE,
    travel_date DATE,
    status VARCHAR(50) DEFAULT 'pending',
    total_amount DECIMAL(10, 2),
    booking_details JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_bookings_user_id ON bookings(user_id);
CREATE INDEX idx_bookings_status ON bookings(status);
