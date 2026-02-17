-- Insert sample data into airports table (4 airports)
INSERT INTO airports (airport_code, airport_name, city, country) VALUES
('BOM', 'Bombay International Airport', 'Mumbai', 'India'),
('DEL', 'Indira Gandhi International Airport', 'Delhi', 'India'),
('BLR', 'Kempegowda International Airport', 'Bangalore', 'India'),
('HYD', 'Rajiv Gandhi International Airport', 'Hyderabad', 'India')
ON CONFLICT (airport_code) DO NOTHING;

-- Insert two additional airports used by new flights
INSERT INTO airports (airport_code, airport_name, city, country) VALUES
('MAA', 'Chennai International Airport', 'Chennai', 'India'),
('COK', 'Cochin International Airport', 'Kochi', 'India')
ON CONFLICT (airport_code) DO NOTHING;

-- Flights for airlines with ids 5-8 (JET AIRWAYS, Indian Airlines, Air Asia, Akasa Air)
INSERT INTO flights (flight_number, airline_id, departure_airport, arrival_airport, departure_time, arrival_time, base_price, total_seats, available_seats, status)
SELECT 'JA505', airline_id, 'MAA', 'DEL', '2026-02-17 06:30:00+05:30'::timestamptz, '2026-02-17 08:45:00+05:30'::timestamptz, 5200.00, 180, 120, 'scheduled'
FROM airlines WHERE airline_name = 'JET AIRWAYS'
ON CONFLICT (flight_number) DO NOTHING;

INSERT INTO flights (flight_number, airline_id, departure_airport, arrival_airport, departure_time, arrival_time, base_price, total_seats, available_seats, status)
SELECT 'IN606', airline_id, 'DEL', 'COK', '2026-02-17 11:00:00+05:30'::timestamptz, '2026-02-17 14:15:00+05:30'::timestamptz, 6100.00, 150, 80, 'scheduled'
FROM airlines WHERE airline_name = 'Indian Airlines'
ON CONFLICT (flight_number) DO NOTHING;

INSERT INTO flights (flight_number, airline_id, departure_airport, arrival_airport, departure_time, arrival_time, base_price, total_seats, available_seats, status)
SELECT 'AK707', airline_id, 'COK', 'BLR', '2026-02-18 07:45:00+05:30'::timestamptz, '2026-02-18 09:00:00+05:30'::timestamptz, 1800.00, 180, 160, 'scheduled'
FROM airlines WHERE airline_name = 'Air Asia'
ON CONFLICT (flight_number) DO NOTHING;

INSERT INTO flights (flight_number, airline_id, departure_airport, arrival_airport, departure_time, arrival_time, base_price, total_seats, available_seats, status)
SELECT 'AKS808', airline_id, 'BLR', 'MAA', '2026-02-18 19:00:00+05:30'::timestamptz, '2026-02-18 20:30:00+05:30'::timestamptz, 1950.00, 180, 170, 'scheduled'
FROM airlines WHERE airline_name = 'Akasa Air'
ON CONFLICT (flight_number) DO NOTHING;

-- Additional flights using same airports (BOM, DEL, BLR, HYD) with different airlines and times
INSERT INTO flights (flight_number, airline_id, departure_airport, arrival_airport, departure_time, arrival_time, base_price, total_seats, available_seats, status)
SELECT 'JA510', airline_id, 'BOM', 'DEL', '2026-02-19 09:00:00+05:30'::timestamptz, '2026-02-19 11:15:00+05:30'::timestamptz, 4800.00, 180, 140, 'scheduled'
FROM airlines WHERE airline_name = 'JET AIRWAYS'
ON CONFLICT (flight_number) DO NOTHING;

INSERT INTO flights (flight_number, airline_id, departure_airport, arrival_airport, departure_time, arrival_time, base_price, total_seats, available_seats, status)
SELECT 'IN612', airline_id, 'DEL', 'BLR', '2026-02-19 15:00:00+05:30'::timestamptz, '2026-02-19 17:30:00+05:30'::timestamptz, 3950.00, 150, 90, 'scheduled'
FROM airlines WHERE airline_name = 'Indian Airlines'
ON CONFLICT (flight_number) DO NOTHING;

INSERT INTO flights (flight_number, airline_id, departure_airport, arrival_airport, departure_time, arrival_time, base_price, total_seats, available_seats, status)
SELECT 'AA713', airline_id, 'BLR', 'HYD', '2026-02-20 08:30:00+05:30'::timestamptz, '2026-02-20 09:45:00+05:30'::timestamptz, 1900.00, 180, 150, 'scheduled'
FROM airlines WHERE airline_name = 'Air Asia'
ON CONFLICT (flight_number) DO NOTHING;

INSERT INTO flights (flight_number, airline_id, departure_airport, arrival_airport, departure_time, arrival_time, base_price, total_seats, available_seats, status)
SELECT 'AK809', airline_id, 'HYD', 'BOM', '2026-02-20 20:30:00+05:30'::timestamptz, '2026-02-20 22:30:00+05:30'::timestamptz, 3350.00, 180, 160, 'scheduled'
FROM airlines WHERE airline_name = 'Akasa Air'
ON CONFLICT (flight_number) DO NOTHING;

-- Insert sample data into flights table (4 flights)
-- Insert sample data into flights table (4 flights)
-- Use SELECT from airlines to map airline_name -> airline_id so this works with existing airlines

INSERT INTO flights (flight_number, airline_id, departure_airport, arrival_airport, departure_time, arrival_time, base_price, total_seats, available_seats, status)
SELECT 'AI101', airline_id, 'BOM', 'DEL', '2026-02-15 08:00:00+05:30'::timestamptz, '2026-02-15 10:15:00+05:30'::timestamptz, 4500.00, 60, 45, 'scheduled'
FROM airlines WHERE airline_name = 'Air India'
ON CONFLICT (flight_number) DO NOTHING;

INSERT INTO flights (flight_number, airline_id, departure_airport, arrival_airport, departure_time, arrival_time, base_price, total_seats, available_seats, status)
SELECT '6E202', airline_id, 'DEL', 'BLR', '2026-02-15 14:30:00+05:30'::timestamptz, '2026-02-15 17:45:00+05:30'::timestamptz, 3800.00, 60, 32, 'scheduled'
FROM airlines WHERE airline_name = 'IndiGo'
ON CONFLICT (flight_number) DO NOTHING;

INSERT INTO flights (flight_number, airline_id, departure_airport, arrival_airport, departure_time, arrival_time, base_price, total_seats, available_seats, status)
SELECT 'SG303', airline_id, 'BLR', 'HYD', '2026-02-16 09:00:00+05:30'::timestamptz, '2026-02-16 10:30:00+05:30'::timestamptz, 2500.00, 60, 50, 'scheduled'
FROM airlines WHERE airline_name = 'Spice Jet'
ON CONFLICT (flight_number) DO NOTHING;

INSERT INTO flights (flight_number, airline_id, departure_airport, arrival_airport, departure_time, arrival_time, base_price, total_seats, available_seats, status)
SELECT 'UK404', airline_id, 'HYD', 'BOM', '2026-02-16 18:00:00+05:30'::timestamptz, '2026-02-16 20:00:00+05:30'::timestamptz, 3200.00, 60, 55, 'scheduled'
FROM airlines WHERE airline_name = 'Vistara'
ON CONFLICT (flight_number) DO NOTHING;
