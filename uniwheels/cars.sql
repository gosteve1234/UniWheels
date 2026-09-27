-- UniWheels Database - Cars Table Schema
USE `uniwheels`;

CREATE TABLE IF NOT EXISTS `cars` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `wheel_name` VARCHAR(100) NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'AVAILABLE',
  `is_recommended` TINYINT(1) NOT NULL DEFAULT 0,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `front_fitment` VARCHAR(100) NOT NULL,
  `rear_fitment` VARCHAR(100) NOT NULL,
  `finish` VARCHAR(100) NOT NULL,
  `alloy_type` VARCHAR(100) NOT NULL,
  `weight` VARCHAR(50) NOT NULL,
  `image_file` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Clean existing if any
TRUNCATE TABLE `cars`;

-- Insert Initial 8 Cars
INSERT INTO `cars` (`id`, `name`, `wheel_name`, `status`, `is_recommended`, `is_featured`, `front_fitment`, `rear_fitment`, `finish`, `alloy_type`, `weight`, `image_file`) VALUES
(1, 'Ferrari SF90 Stradale Crimson Edition', 'UW-F01 Monoblock Forged', 'AVAILABLE', 1, 1, '21\" x 9.5J ET28', '22\" x 12.5J ET38', 'Candy Dark Red', '6061-T6 Aerospace Forged', '9.8 kg / wheel', 'car-1.png'),
(2, 'Apollo Intensa Emozione Carbon/Crimson', 'UW-R09 Carbon-Forged Track', 'BESPOKE', 1, 1, '20\" x 10.0J ET22', '21\" x 13.0J ET30', 'Dark Crimson Tint', 'Carbon Barrel + Forged Center', '8.4 kg / wheel', 'car-2.png'),
(3, 'Nissan GT-R Nismo Widebody Edition', 'UW-DD3 Deep Lip 3-Piece', 'RECOMMENDED', 1, 1, '20\" x 11.0J ET-5', '20\" x 12.5J ET-15', 'Anodized Dark Red Lip', '3-Piece Modular Forged', '10.5 kg / wheel', 'car-3.png'),
(4, 'Porsche 911 GT3 RS Track Edition', 'UW-RS01 Centerlock Forged', 'RECOMMENDED', 1, 0, '20\" x 9.5J Centerlock', '21\" x 12.5J Centerlock', 'Brushed Dark Red', '6061-T6 Monoblock Forged', '8.9 kg / wheel', 'car-4.png'),
(5, 'Lamborghini Revuelto V12 Hybrid', 'UW-SV7 Carbon-Forged Monoblock', 'AVAILABLE', 0, 0, '20\" x 9.5J ET25', '21\" x 12.5J ET35', 'Candy Crimson Metallic', 'Aero-Forged Monoblock', '9.2 kg / wheel', 'car-5.png'),
(6, 'Chevrolet Corvette C8 Z06 Carbon Aero', 'UW-Z06 Concave Forged', 'AVAILABLE', 1, 0, '20\" x 10.0J ET30', '21\" x 13.0J ET42', 'Metallic Dark Wine Red', 'Deep Concave Forged Monoblock', '9.6 kg / wheel', 'car-6.png'),
(7, 'BMW M4 Competition G82 Widebody', 'UW-M4 Deep Dish Modular', 'AVAILABLE', 0, 0, '20\" x 10.5J ET12', '20\" x 11.5J ET08', 'Gloss Red Chrome Lip', '2-Piece Welded Modular', '10.2 kg / wheel', 'car-7.png'),
(8, 'McLaren 765LT Carbon Longtail', 'UW-LT9 Ultra-Light Track', 'BESPOKE', 1, 0, '19\" x 9.0J ET18', '20\" x 11.5J ET26', 'Candy Crimson Satin', 'Forged Magnesium/Alloy', '7.9 kg / wheel', 'car-8.png');
