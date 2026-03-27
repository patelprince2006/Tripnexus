-- Migration: 010
-- Description: Create train_stations and bus_locations tables and populate with sample data
-- Date: 2026-03-27

-- Create train_stations table
CREATE TABLE IF NOT EXISTS train_stations (
    station_code VARCHAR(10) PRIMARY KEY,
    station_name VARCHAR(100) NOT NULL,
    city VARCHAR(50) NOT NULL,
    state VARCHAR(50) DEFAULT NULL
) ENGINE=InnoDB;

-- Create bus_locations table
CREATE TABLE IF NOT EXISTS bus_locations (
    location_id INT AUTO_INCREMENT PRIMARY KEY,
    city_name VARCHAR(100) NOT NULL UNIQUE,
    state VARCHAR(50) DEFAULT NULL
) ENGINE=InnoDB;

-- Insert sample data into train_stations
INSERT IGNORE INTO train_stations (station_code, station_name, city, state) VALUES
('NDLS', 'New Delhi Railway Station', 'Delhi', 'Delhi'),
('MMCT', 'Mumbai Central', 'Mumbai', 'Maharashtra'),
('CSMT', 'Chhatrapati Shivaji Maharaj Terminus', 'Mumbai', 'Maharashtra'),
('MAS', 'Chennai Central', 'Chennai', 'Tamil Nadu'),
('SBC', 'KSR Bengaluru City Junction', 'Bangalore', 'Karnataka'),
('HWH', 'Howrah Junction', 'Kolkata', 'West Bengal'),
('PUNE', 'Pune Junction', 'Pune', 'Maharashtra'),
('HYB', 'Hyderabad Deccan Nampally', 'Hyderabad', 'Telangana'),
('JP', 'Jaipur Junction', 'Jaipur', 'Rajasthan'),
('LKO', 'Lucknow Charbagh', 'Lucknow', 'Uttar Pradesh'),
('ADI', 'Ahmedabad Junction', 'Ahmedabad', 'Gujarat'),
('BPL', 'Bhopal Junction', 'Bhopal', 'Madhya Pradesh'),
('PNBE', 'Patna Junction', 'Patna', 'Bihar'),
('INDB', 'Indore Junction', 'Indore', 'Madhya Pradesh'),
('VSKP', 'Visakhapatnam Junction', 'Visakhapatnam', 'Andhra Pradesh'),
('GHY', 'Guwahati Railway Station', 'Guwahati', 'Assam'),
('AGC', 'Agra Cantt.', 'Agra', 'Uttar Pradesh'),
('BSB', 'Varanasi Junction', 'Varanasi', 'Uttar Pradesh'),
('CNB', 'Kanpur Central', 'Kanpur', 'Uttar Pradesh'),
('MYS', 'Mysuru Junction', 'Mysuru', 'Karnataka'),
('CBE', 'Coimbatore Junction', 'Coimbatore', 'Tamil Nadu'),
('MDU', 'Madurai Junction', 'Madurai', 'Tamil Nadu'),
('BZA', 'Vijayawada Junction', 'Vijayawada', 'Andhra Pradesh'),
('ERS', 'Ernakulam Junction', 'Kochi', 'Kerala'),
('TVC', 'Thiruvananthapuram Central', 'Thiruvananthapuram', 'Kerala');

-- Insert sample data into bus_locations
INSERT IGNORE INTO bus_locations (city_name, state) VALUES
('Bangalore', 'Karnataka'),
('Hyderabad', 'Telangana'),
('Mumbai', 'Maharashtra'),
('Pune', 'Maharashtra'),
('Chennai', 'Tamil Nadu'),
('Vijayawada', 'Andhra Pradesh'),
('Goa', 'Goa'),
('Delhi', 'Delhi'),
('Ahmedabad', 'Gujarat'),
('Jaipur', 'Rajasthan'),
('Lucknow', 'Uttar Pradesh'),
('Kochi', 'Kerala'),
('Coimbatore', 'Tamil Nadu'),
('Madurai', 'Tamil Nadu'),
('Mysore', 'Karnataka'),
('Mangalore', 'Karnataka'),
('Visakhapatnam', 'Andhra Pradesh'),
('Tirupati', 'Andhra Pradesh'),
('Indore', 'Madhya Pradesh'),
('Bhopal', 'Madhya Pradesh'),
('Nagpur', 'Maharashtra'),
('Surat', 'Gujarat'),
('Rajkot', 'Gujarat'),
('Vadodara', 'Gujarat'),
('Chandigarh', 'Chandigarh');
