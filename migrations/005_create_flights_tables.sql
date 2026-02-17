-- Migration: 005
-- Description: Create airport, airline, and flights tables for travel management
-- Based on SQL_QUERY.txt schema for Supabase
-- Date: 2026-02-12

-- Create airports table
CREATE TABLE IF NOT EXISTS airports (
    airport_code VARCHAR(3) PRIMARY KEY, -- e.g., 'BOM', 'DEL'
    airport_name VARCHAR(100) NOT NULL,
    city VARCHAR(50) NOT NULL,
    country VARCHAR(50) NOT NULL
);

-- Create airlines table
CREATE TABLE IF NOT EXISTS airlines (
    airline_id SERIAL PRIMARY KEY,
    airline_name VARCHAR(100) NOT NULL,
    airline_logo TEXT -- Using TEXT for image URLs/paths
);

-- Create flights table
CREATE TABLE IF NOT EXISTS flights (
    flight_id SERIAL PRIMARY KEY,
    flight_number VARCHAR(10) UNIQUE NOT NULL,
    airline_id INT REFERENCES airlines(airline_id),
    departure_airport VARCHAR(3) REFERENCES airports(airport_code),
    arrival_airport VARCHAR(3) REFERENCES airports(airport_code),
    departure_time TIMESTAMPTZ NOT NULL, -- 'TIMESTAMPTZ' is best practice for timezones
    arrival_time TIMESTAMPTZ NOT NULL,
    base_price DECIMAL(10, 2) NOT NULL,
    total_seats INT DEFAULT 60,
    available_seats INT NOT NULL,
    status VARCHAR(20) DEFAULT 'scheduled' CHECK (status IN ('scheduled', 'boarding', 'departed', 'landed', 'cancelled')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Ensuring seats don't go below zero
    CONSTRAINT check_seats CHECK (available_seats >= 0)
);

-- Insert sample data into airports table (4 airports)
INSERT INTO airports (airport_code, airport_name, city, country) VALUES
('BOM', 'Bombay International Airport', 'Mumbai', 'India'),
('DEL', 'Indira Gandhi International Airport', 'Delhi', 'India'),
('BLR', 'Kempegowda International Airport', 'Bangalore', 'India'),
('HYD', 'Rajiv Gandhi International Airport', 'Hyderabad', 'India')
ON CONFLICT (airport_code) DO NOTHING;

-- Insert sample data into airlines table (4 airlines)
INSERT INTO airlines (airline_name, airline_logo) VALUES
('Air India', 'https://example.com/logos/airindia.png'),
('IndiGo', 'https://example.com/logos/indigo.png'),
('Spice Jet', 'https://example.com/logos/spicejet.png'),
('Vistara', 'https://example.com/logos/vistara.png')
ON CONFLICT DO NOTHING;

-- Insert sample data into flights table (4 flights)
INSERT INTO flights (flight_number, airline_id, departure_airport, arrival_airport, departure_time, arrival_time, base_price, total_seats, available_seats, status) VALUES
('AI101', 1, 'BOM', 'DEL', '2026-02-15 08:00:00+05:30', '2026-02-15 10:15:00+05:30', 4500.00, 60, 45, 'scheduled'),
('6E202', 2, 'DEL', 'BLR', '2026-02-15 14:30:00+05:30', '2026-02-15 17:45:00+05:30', 3800.00, 60, 32, 'scheduled'),
('SG303', 3, 'BLR', 'HYD', '2026-02-16 09:00:00+05:30', '2026-02-16 10:30:00+05:30', 2500.00, 60, 50, 'scheduled'),
('UK404', 4, 'HYD', 'BOM', '2026-02-16 18:00:00+05:30', '2026-02-16 20:00:00+05:30', 3200.00, 60, 55, 'scheduled')
ON CONFLICT DO NOTHING;
