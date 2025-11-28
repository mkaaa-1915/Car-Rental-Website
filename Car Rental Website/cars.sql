-- updated cars table: renamed machine -> gear, added engine and description
-- Run this on a new DB or adapt into ALTER TABLE statements if you want an in-place migration.

CREATE TABLE IF NOT EXISTS cars (
  id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  type VARCHAR(100) DEFAULT '',
  gear VARCHAR(100) DEFAULT '',        -- was machine, now gear (Automatic/Manual)
  engine VARCHAR(100) DEFAULT '',      -- new: engine type (Hybrid, Gasoline, Diesel, Electric, ...)
  price INT(11) NOT NULL DEFAULT 0,
  image VARCHAR(255) DEFAULT '',
  description TEXT DEFAULT '',         -- new: description text
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Example seed rows (adjust as needed)
INSERT IGNORE INTO cars (name, type, gear, engine, price, image, description) VALUES
('Porsche 911','Sedan','Automatic','Gasoline',299,'img/trend-1.png','Iconic sports car.'),
('Audi R8','Sedan','Automatic','Gasoline',299,'img/trend-4.png','High performance.'),
('Audi A8 L','Sedan','Automatic','Gasoline',299,'img/rental-3.png','Luxury sedan.'),
('BMW M4 Competition','Sedan','Manual','Gasoline',299,'img/rental-4.png','Performance oriented.'),
('Mercedes GLE','SUV','Automatic','Diesel',299,'img/rental-3.png','Comfort & space.'),
('Rolls-Royce','Sedan','Automatic','Gasoline',299,'img/rental-1.png','Luxury experience.'),
('Macan 4','SUV','Automatic','Gasoline',299,'img/rental-2.png','Compact SUV.'),
('Cayenne S E-Hybrid','SUV','Automatic','Hybrid',299,'img/rental-3.png','Plug-in hybrid.'),
('Nissan GT-R','Sedan','Manual','Gasoline',299,'img/rental-4.png','High performance.'),
('Panamera Turbo','Sedan','Automatic','Gasoline',299,'img/rental-5.png','Sport-luxury.'),
('Tesla Model 3','Sedan','Automatic','Electric',299,'img/rental-2.png','Electric sedan.'),
('Cayenne Turbo','SUV','Automatic','Gasoline',299,'img/rental-7.png','Powerful SUV.'),
('718 Boxster S','Convertible','Manual','Gasoline',299,'img/rental-8.png','Convertible fun.'),
('Nissan Ariya','SUV','Automatic','Electric',299,'img/rental-6.png','Electric crossover.');