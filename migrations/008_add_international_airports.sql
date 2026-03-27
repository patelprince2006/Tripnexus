-- -------------------------------------------------------------------
-- Add Major International Airports
-- -------------------------------------------------------------------

INSERT IGNORE INTO `airports` (`airport_code`, `airport_name`, `city`, `country`) VALUES
('DXB', 'Dubai International Airport', 'Dubai', 'UAE'),
('LHR', 'London Heathrow Airport', 'London', 'UK'),
('SIN', 'Singapore Changi Airport', 'Singapore', 'Singapore'),
('JFK', 'John F. Kennedy International Airport', 'New York', 'USA'),
('CDG', 'Charles de Gaulle Airport', 'Paris', 'France'),
('HND', 'Tokyo Haneda Airport', 'Tokyo', 'Japan'),
('SYD', 'Sydney Airport', 'Sydney', 'Australia'),
('YYZ', 'Toronto Pearson International Airport', 'Toronto', 'Canada'),
('FRA', 'Frankfurt Airport', 'Frankfurt', 'Germany'),
('BKK', 'Suvarnabhumi Airport', 'Bangkok', 'Thailand');
