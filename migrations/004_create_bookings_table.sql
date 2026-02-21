-- Migration: 004
-- Description: Create bookings table
-- Date: 2026-02-12

-- Updated Migration: 004
CREATE TABLE IF NOT EXISTS bookings (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    service_type VARCHAR(50), 
    destination VARCHAR(255),
    starting_point VARCHAR(255),
    booking_date DATE DEFAULT CURRENT_DATE,
    travel_date DATE,
    status VARCHAR(50) DEFAULT 'pending',
    total_amount DECIMAL(10, 2), -- Use this name for Admin panel compatibility
    booking_details JSONB,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_bookings_user_id ON bookings(user_id);
CREATE INDEX IF NOT EXISTS idx_bookings_status ON bookings(status);
