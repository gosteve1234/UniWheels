-- UniWheels Motors - Database Schema & Seed Data
-- Database: `uniwheels`
CREATE DATABASE IF NOT EXISTS `uniwheels` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `uniwheels`;

-- 1. Cars Table (Certified Pre-Owned & Secondhand Inventory)
CREATE TABLE IF NOT EXISTS `cars` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `brand` VARCHAR(50) DEFAULT 'Toyota',
  `price` DECIMAL(12,2) DEFAULT 0.00,
  `stock_id` VARCHAR(20) DEFAULT NULL,
  `mileage` VARCHAR(50) DEFAULT 'Low Mileage',
  `transmission` VARCHAR(50) DEFAULT 'Automatic',
  `body_type` VARCHAR(50) DEFAULT 'Sedan',
  `fuel_type` VARCHAR(100) DEFAULT 'Gasoline',
  `seating` VARCHAR(20) DEFAULT '5 Seats',
  `status` VARCHAR(50) NOT NULL DEFAULT 'AVAILABLE',
  `is_recommended` TINYINT(1) NOT NULL DEFAULT 0,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `wheel_name` VARCHAR(100) NOT NULL,
  `front_fitment` VARCHAR(100) NOT NULL,
  `rear_fitment` VARCHAR(100) NOT NULL,
  `finish` VARCHAR(100) NOT NULL,
  `alloy_type` VARCHAR(100) NOT NULL,
  `weight` VARCHAR(50) NOT NULL,
  `image_file` VARCHAR(255) NOT NULL,
  `gallery_images` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Employees Table (Showroom Staff, Managers & Super Administrator)
CREATE TABLE IF NOT EXISTS `employees` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(50) DEFAULT '',
  `role` VARCHAR(50) NOT NULL DEFAULT 'employee',
  `department` VARCHAR(100) DEFAULT 'Sales',
  `status` VARCHAR(50) NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Roles Table
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `role_key` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `badge` VARCHAR(50) NOT NULL DEFAULT 'admin',
  `description` TEXT NOT NULL,
  `permissions` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Inquiries Table
CREATE TABLE IF NOT EXISTS `inquiries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `vehicle_name` VARCHAR(150) NOT NULL,
  `inquiry_type` VARCHAR(100) NOT NULL DEFAULT 'Showroom Visit (Face-to-Face)',
  `preferred_date` VARCHAR(100) DEFAULT '',
  `message` TEXT,
  `status` VARCHAR(50) NOT NULL DEFAULT 'new',
  `staff_notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Default Admin Account (admin / admin123)
INSERT IGNORE INTO `employees` (`id`, `username`, `password`, `name`, `email`, `phone`, `role`, `department`, `status`) VALUES
(1, 'admin', '$2y$12$B6ImRZ/DnYxulGNLMp52BuS/JB81ORAKbgW4tTzYwhQ6e1wKI7LL2', 'Administrator', 'admin@uniwheels.com', '+1 (555) 019-2831', 'admin', 'Executive Management', 'active');

-- Seed Default Certified Pre-Owned Inventory
INSERT INTO `cars` (`id`, `name`, `brand`, `price`, `stock_id`, `mileage`, `transmission`, `body_type`, `fuel_type`, `seating`, `status`, `is_recommended`, `is_featured`, `wheel_name`, `front_fitment`, `rear_fitment`, `finish`, `alloy_type`, `weight`, `image_file`, `gallery_images`) VALUES
(1, '2021 Toyota Vios 1.3 XLE', 'Toyota', 545000.00, 'UW-TOY01', '28,400 km', 'Automatic', 'Sedan', 'Gasoline', '5 Seats', 'AVAILABLE', 1, 1, 'OEM 15" Alloy Wheels (85% Tires)', '185/60 R15 Tires', '1st Owner / Complete OR/CR', 'Silver Metallic', 'Original Factory Paint & Condition', '3-Month Warranty Included', 'car-1.jpg', '["car-1.jpg","car-1-rear.jpg","car-1-interior.jpg"]'),
(2, '2020 Honda Civic 1.8 E CVT', 'Honda', 785000.00, 'UW-HND02', '34,200 km', 'Automatic', 'Sedan', 'Gasoline', '5 Seats', 'AVAILABLE', 1, 1, '16" Factory Alloy Wheels', '215/55 R16 Bridgestone', 'Casa Maintained / Fresh In & Out', 'Taffeta White', 'Clean Papers / LTO Verified', 'Free Change Oil & Detailing', 'car-2.jpg', '["car-2.jpg","car-2-rear.jpg","car-2-interior.jpg"]'),
(3, '2019 Toyota Fortuner 2.4 G 4x2', 'Toyota', 1050000.00, 'UW-FTN03', '52,800 km', 'Automatic', 'SUV', 'Diesel', '7 Seats', 'AVAILABLE', 1, 1, '17" Rugged Factory Alloys', '265/65 R17 All-Terrain', 'Diesel Turbo / High Fuel Efficiency', 'Pearl White', 'Inspected & Certified / Trade-In Ready', 'Comprehensive History Checked', 'car-3.jpg', NULL),
(4, '2022 Suzuki Ertiga 1.5 GL', 'Suzuki', 625000.00, 'UW-ERT04', '19,500 km', 'Automatic', 'MPV', 'Gasoline', '7 Seats', 'AVAILABLE', 0, 0, '15" Multi-Spoke Alloys', '185/65 R15', 'Low Mileage Family Van / Ice Cold AC', 'Titanium Grey Metallic', 'Single Owner / No Accident History', 'Ready for Long Family Trips', 'car-4.jpg', NULL),
(5, '2021 Ford Ranger 2.2 XLS 4x2', 'Ford', 840000.00, 'UW-RNG05', '38,100 km', 'Automatic', 'Pickup', 'Diesel', '5 Seats', 'AVAILABLE', 1, 0, '16" Dark Gunmetal All-Terrain', '255/70 R16', 'Tough Utility Bed / Clean Interior', 'Meteor Dark Grey', 'Heavy Duty & Ready to Work', 'Tested & Ready to Work', 'car-5.jpg', NULL),
(6, '2022 Suzuki Swift 1.2 GL', 'Suzuki', 510000.00, 'UW-SWF06', '16,300 km', 'Automatic', 'Hatchback', 'Gasoline', '5 Seats', 'AVAILABLE', 1, 0, '15" Factory Sport Steelies', '175/65 R15', 'Great Fuel Mileage (20 km/L)', 'Burning Red Pearl', 'City Friendly Commuter / Easy Parking', 'Full Service Records Available', 'car-6.jpg', NULL),
(7, '2021 Toyota RAV4 2.5 Hybrid', 'Toyota', 1280000.00, 'UW-RAV07', '31,000 km', 'Automatic', 'SUV', 'Hybrid', '5 Seats', 'AVAILABLE', 1, 0, '18" Diamond Cut Two-Tone Alloys', '225/60 R18 Michelin', 'Hybrid Electric / Ultra Smooth Ride', 'Silver Sky Metallic', 'Original Factory Paint / 1st Owner', 'Battery Health Tested 100%', 'car-7.jpg', NULL),
(8, '2020 Nissan Almera 1.5 E', 'Nissan', 415000.00, 'UW-ALM08', '41,500 km', 'Automatic', 'Sedan', 'Gasoline', '5 Seats', 'AVAILABLE', 0, 0, '15" Multi-Spoke Alloy Wheels', '185/65 R15', 'Huge Trunk Space / Comfortable Legroom', 'Diamond Black', 'Affordable Reliable Family Sedan', 'Complete Spare Tire & Tools', 'car-8.jpg', NULL)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);
