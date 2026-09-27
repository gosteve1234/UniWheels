-- UniWheels Complete Database Schema & Seed
-- Database: `uniwheels`
USE `uniwheels`;

-- 1. Cars Table (Fleet Management & Wheel Fitments)
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

-- 3. Users Table (Registered Supercar Customers & Client Accounts)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(50) DEFAULT '',
  `status` VARCHAR(50) NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Default Admin Employee (admin / admin123)
INSERT IGNORE INTO `employees` (`id`, `username`, `password`, `name`, `email`, `phone`, `role`, `department`, `status`) VALUES
(1, 'admin', '$2y$12$B6ImRZ/DnYxulGNLMp52BuS/JB81ORAKbgW4tTzYwhQ6e1wKI7LL2', 'Administrator', 'admin@uniwheels.com', '+1 (555) 019-2831', 'admin', 'Executive Management', 'active');

-- Seed 8 Initial High-Performance Vehicles
INSERT IGNORE INTO `cars` (`id`, `name`, `wheel_name`, `status`, `is_recommended`, `is_featured`, `front_fitment`, `rear_fitment`, `finish`, `alloy_type`, `weight`, `image_file`) VALUES
(1, 'Ferrari SF90 Stradale Crimson Edition', 'UW-F01 Monoblock Forged', 'AVAILABLE', 1, 1, '21\" x 9.5J ET28', '22\" x 12.5J ET38', 'Candy Dark Red', '6061-T6 Aerospace Forged', '9.8 kg / wheel', 'car-1.png'),
(2, 'Apollo Intensa Emozione Carbon/Crimson', 'UW-R09 Carbon-Forged Track', 'BESPOKE', 1, 1, '20\" x 10.0J ET22', '21\" x 13.0J ET30', 'Dark Crimson Tint', 'Carbon Barrel + Forged Center', '8.4 kg / wheel', 'car-2.png'),
(3, 'Nissan GT-R Nismo Widebody Edition', 'UW-DD3 Deep Lip 3-Piece', 'RECOMMENDED', 1, 1, '20\" x 11.0J ET-5', '20\" x 12.5J ET-15', 'Anodized Dark Red Lip', '3-Piece Modular Forged', '10.5 kg / wheel', 'car-3.png'),
(4, 'Porsche 911 GT3 RS Track Edition', 'UW-RS01 Centerlock Forged', 'RECOMMENDED', 1, 0, '20\" x 9.5J Centerlock', '21\" x 12.5J Centerlock', 'Brushed Dark Red', '6061-T6 Monoblock Forged', '8.9 kg / wheel', 'car-4.png'),
(5, 'Lamborghini Revuelto V12 Hybrid', 'UW-SV7 Carbon-Forged Monoblock', 'AVAILABLE', 0, 0, '20\" x 9.5J ET25', '21\" x 12.5J ET35', 'Candy Crimson Metallic', 'Aero-Forged Monoblock', '9.2 kg / wheel', 'car-5.png'),
(6, 'Chevrolet Corvette C8 Z06 Carbon Aero', 'UW-Z06 Concave Forged', 'AVAILABLE', 1, 0, '20\" x 10.0J ET30', '21\" x 13.0J ET42', 'Metallic Dark Wine Red', 'Deep Concave Forged Monoblock', '9.6 kg / wheel', 'car-6.png'),
(7, 'BMW M4 Competition G82 Widebody', 'UW-M4 Deep Dish Modular', 'AVAILABLE', 0, 0, '20\" x 10.5J ET12', '20\" x 11.5J ET08', 'Gloss Red Chrome Lip', '2-Piece Welded Modular', '10.2 kg / wheel', 'car-7.png'),
(8, 'McLaren 765LT Carbon Longtail', 'UW-LT9 Ultra-Light Track', 'BESPOKE', 1, 0, '19\" x 9.0J ET18', '20\" x 11.5J ET26', 'Candy Crimson Satin', 'Forged Magnesium/Alloy', '7.9 kg / wheel', 'car-8.png');

-- 4. Roles Table (Operational Roles, Descriptions & Capabilities)
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `role_key` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `badge` VARCHAR(50) NOT NULL DEFAULT 'admin',
  `description` TEXT NOT NULL,
  `permissions` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Default Roles
INSERT IGNORE INTO `roles` (`id`, `role_key`, `name`, `badge`, `description`, `permissions`) VALUES
(1, 'admin', 'Super Administrator', 'admin', 'Full root privileges. Holds full control over database schemas, vehicle inventory, direct server file uploads, employee accounts, and role permissions.', 'Create, Edit & Delete Vehicles\nDirect Image Uploads to assets/images/cars/\nToggle Recommended & Featured Showcases\nRegister & Revoke Employee Accounts\nEdit System Roles & Permission Rules\nAccess Database Diagnostics'),
(2, 'manager', 'Fleet Manager', 'manager', 'Operational vehicle coordinator. Can adjust vehicle specifications, pricing, stock availability, and upload vehicle cutout assets for new stock arrivals.', 'Create & Edit Vehicles in Fleet\nUpload Vehicle & Wheel Photos\nToggle Stock & Status (Available / Bespoke)\nView Employee Directory'),
(3, 'employee', 'Sales Representative', 'employee', 'Showroom sales and customer service staff. Read-only inventory consultation tool to assist supercar buyers with vehicle availability and acquisition.', 'Query Vehicle Inventory & Stock\nInspect Vehicle Specs & Pricing\nReal-Time Stock Status Check\nProcess Customer Inquiries'),
(4, 'visitor', 'Customer / Visitor', 'visitor', 'Public storefront visitors accessing the dynamic showroom with spotlight hover blurring, 3-car showcase, and vehicle inquiry submission.', 'Browse Interactive Vehicle Showcase\nFilter By Stock & Series\nCreate Customer Account\nSubmit Vehicle Acquisition Inquiries');

-- 5. Inquiries & Showroom Consultations Table
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

INSERT IGNORE INTO `inquiries` (`id`, `name`, `email`, `phone`, `vehicle_name`, `inquiry_type`, `preferred_date`, `message`, `status`, `staff_notes`) VALUES
(1, 'Julian Thorne', 'jthorne@vanceholdings.com', '+1 (555) 489-2104', 'Ferrari SF90 Stradale Crimson Edition', 'Showroom Visit (Face-to-Face)', 'Tomorrow, 2:00 PM', 'Looking to inspect the SF90 Crimson edition in person. Would like to test drive and discuss cash purchase and enclosed transport options.', 'ongoing', 'VIP client. Assigned to Sales Rep Marcus. Test drive prep scheduled.'),
(2, 'Elena Rostova', 'elena.rostova@monacofund.mc', '+1 (555) 732-9012', 'Apollo Intensa Emozione Carbon/Crimson', 'Executive VIP Consultation', 'This Saturday, 11:00 AM', 'Interested in bespoke Apollo IE allocation and custom wheel finish options for track use. Please arrange private showroom slot.', 'new', 'Requires Super Admin confirmation for track spec clearance.'),
(3, 'Marcus Sterling', 'msterling@apexcapital.org', '+1 (555) 219-8840', 'Porsche 911 GT3 RS Track Edition', 'Vehicle Acquisition & Inspection', 'Yesterday', 'Visited showroom, verified centerlock fitments, vehicle acquisition contract signed.', 'completed', 'Acquisition completed. Delivery scheduled via enclosed transporter next Tuesday.');

