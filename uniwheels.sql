-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 27, 2026 at 07:25 AM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `uniwheels`
--

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `brand` varchar(50) DEFAULT 'Toyota',
  `price` decimal(12,2) DEFAULT 0.00,
  `listed_price` decimal(12,2) DEFAULT NULL,
  `old_price` decimal(12,2) DEFAULT NULL,
  `stock_id` varchar(40) DEFAULT NULL,
  `mileage` varchar(50) DEFAULT 'Low Mileage',
  `transmission` varchar(50) DEFAULT 'Automatic',
  `body_type` varchar(50) DEFAULT 'Sedan',
  `fuel_type` varchar(100) DEFAULT 'Gasoline',
  `seating` varchar(20) DEFAULT '5 Seats',
  `status` varchar(50) NOT NULL DEFAULT 'AVAILABLE',
  `is_recommended` tinyint(1) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `wheel_name` varchar(100) NOT NULL,
  `front_fitment` varchar(100) NOT NULL,
  `rear_fitment` varchar(100) NOT NULL,
  `finish` varchar(100) NOT NULL,
  `alloy_type` varchar(100) NOT NULL,
  `weight` varchar(50) NOT NULL,
  `image_file` varchar(255) NOT NULL,
  `gallery_images` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sold_at` datetime DEFAULT NULL,
  `traded_at` datetime DEFAULT NULL,
  `trade_car_name` varchar(255) DEFAULT NULL,
  `trade_stock_id` varchar(64) DEFAULT NULL,
  `reserved_at` datetime DEFAULT NULL,
  `reserve_expires_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `model_year` smallint(5) UNSIGNED DEFAULT NULL,
  `ownership_history` varchar(255) DEFAULT NULL,
  `damage_status` varchar(12) DEFAULT NULL,
  `damage_report` text DEFAULT NULL,
  `photo_labels` text DEFAULT NULL,
  `listing_type` varchar(4) NOT NULL DEFAULT 'SALE',
  `rental_rate` decimal(12,2) DEFAULT NULL,
  `booking_deposit` decimal(12,2) DEFAULT NULL,
  `booking_policy` text DEFAULT NULL,
  `approved_booking_id` int(10) UNSIGNED DEFAULT NULL,
  `year_model` int(11) DEFAULT 2024,
  `plate_number` varchar(60) DEFAULT NULL,
  `original_price` decimal(12,2) DEFAULT NULL,
  `price_note` varchar(100) DEFAULT 'Negotiable',
  `deposit_amount` decimal(12,2) DEFAULT 10000.00,
  `damage_disclosure` text DEFAULT NULL,
  `damage_photos` text DEFAULT NULL,
  `photo_metadata` text DEFAULT NULL,
  `vehicle_policies` text DEFAULT NULL,
  `agent_owner` varchar(100) DEFAULT 'Boss',
  `reserved_until` datetime DEFAULT NULL,
  `active_reservation_ref` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`id`, `name`, `brand`, `price`, `listed_price`, `old_price`, `stock_id`, `mileage`, `transmission`, `body_type`, `fuel_type`, `seating`, `status`, `is_recommended`, `is_featured`, `wheel_name`, `front_fitment`, `rear_fitment`, `finish`, `alloy_type`, `weight`, `image_file`, `gallery_images`, `created_at`, `sold_at`, `traded_at`, `trade_car_name`, `trade_stock_id`, `reserved_at`, `reserve_expires_at`, `deleted_at`, `model_year`, `ownership_history`, `damage_status`, `damage_report`, `photo_labels`, `listing_type`, `rental_rate`, `booking_deposit`, `booking_policy`, `approved_booking_id`, `year_model`, `plate_number`, `original_price`, `price_note`, `deposit_amount`, `damage_disclosure`, `damage_photos`, `photo_metadata`, `vehicle_policies`, `agent_owner`, `reserved_until`, `active_reservation_ref`) VALUES
(1, '2021 Toyota Vios 1.3 XLE', 'Toyota', 545000.00, NULL, NULL, 'UW-TOY01', '28,400 km', 'Automatic', 'Sedan', 'Gasoline', '5 Seats', 'AVAILABLE', 1, 1, 'OEM 15\" Alloy Wheels (85% Tires)', '185/60 R15 Tires', '1st Owner / Complete OR/CR', 'Silver Metallic', 'Original Factory Paint & Condition', '3-Month Warranty Included', 'car-1.jpg', '[\"car-1.jpg\",\"car-1-rear.jpg\",\"car-1-interior.jpg\"]', '2026-10-28 03:20:20', NULL, NULL, NULL, NULL, NULL, NULL, '2026-11-27 07:23:44', 2021, '1 previous owner', 'MINOR', 'Minor light scuff on rear bumper corner — photographed and documented before booking.', '{\"car-1.jpg\":\"Exterior\",\"car-1-rear.jpg\":\"Damage Close-up\",\"car-1-interior.jpg\":\"Interior\"}', 'BOTH', 2200.00, 5000.00, NULL, NULL, 2024, NULL, NULL, 'Negotiable', 10000.00, NULL, NULL, NULL, NULL, 'Boss', NULL, NULL),
(2, '2020 Honda Civic 1.8 E CVT', 'Honda', 785000.00, NULL, NULL, 'UW-HND02', '34,200 km', 'Automatic', 'Sedan', 'Gasoline', '5 Seats', 'AVAILABLE', 1, 1, '16\" Factory Alloy Wheels', '215/55 R16 Bridgestone', 'Casa Maintained / Fresh In & Out', 'Taffeta White', 'Clean Papers / LTO Verified', 'Free Change Oil & Detailing', 'car-2.jpg', '[\"car-2-rear.jpg\",\"car-2-interior.jpg\"]', '2026-10-28 03:20:20', NULL, NULL, NULL, NULL, NULL, NULL, '2026-11-27 07:23:39', 2020, '1 previous owner', 'NONE', 'No dents or major scratches. Original factory paint verified.', '{\"car-2.jpg\":\"Exterior\",\"car-2-rear.jpg\":\"Trunk\",\"car-2-interior.jpg\":\"Dashboard\"}', 'BOTH', 3200.00, 5000.00, NULL, NULL, 2024, NULL, NULL, 'Negotiable', 10000.00, NULL, NULL, NULL, NULL, 'Boss', NULL, NULL),
(3, '2019 Toyota Fortuner 2.4 G 4x2', 'Toyota', 1050000.00, NULL, NULL, 'UW-FTN03', '52,800 km', 'Automatic', 'SUV', 'Diesel', '7 Seats', 'AVAILABLE', 1, 1, '17\" Rugged Factory Alloys', '265/65 R17 All-Terrain', 'Diesel Turbo / High Fuel Efficiency', 'Pearl White', 'Inspected & Certified / Trade-In Ready', 'Comprehensive History Checked', 'car-3.jpg', NULL, '2026-10-28 03:20:20', NULL, NULL, NULL, NULL, NULL, NULL, '2026-11-27 07:23:34', 2019, '1 previous owner', 'MINOR', 'Light stone chips on front hood from highway driving; no structural repairs.', '{\"car-3.jpg\":\"Exterior\"}', 'BOTH', 4000.00, 8000.00, NULL, NULL, 2024, NULL, NULL, 'Negotiable', 10000.00, NULL, NULL, NULL, NULL, 'Boss', NULL, NULL),
(4, '2022 Suzuki Ertiga 1.5 GL', 'Suzuki', 625000.00, NULL, NULL, 'UW-ERT04', '19,500 km', 'Automatic', 'MPV', 'Gasoline', '7 Seats', 'AVAILABLE', 0, 0, '15\" Multi-Spoke Alloys', '185/65 R15', 'Low Mileage Family Van / Ice Cold AC', 'Titanium Grey Metallic', 'Single Owner / No Accident History', 'Ready for Long Family Trips', 'car-4.jpg', NULL, '2026-10-28 03:20:20', NULL, NULL, NULL, NULL, NULL, NULL, '2026-11-27 07:23:25', 2022, '1 previous owner', 'NONE', 'Clean exterior and interior; no prior accident or body repairs.', '{\"car-4.jpg\":\"Exterior\"}', 'BOTH', 2600.00, 5000.00, NULL, NULL, 2024, NULL, NULL, 'Negotiable', 10000.00, NULL, NULL, NULL, NULL, 'Boss', NULL, NULL),
(5, '2021 Ford Ranger Raptor 4X4', 'Ford', 900000.00, 840000.00, NULL, 'UW-RNG05', '38,100 km', 'Automatic', 'Pickup', 'Diesel', '5 Seats', 'SOLD', 1, 0, '16\" Dark Gunmetal All-Terrain', '255/70 R16', 'Tough Utility Bed / Clean Interior', 'Meteor Dark Grey', 'Heavy Duty & Ready to Work', 'Tested & Ready to Work', '518275072-676404038772996-17279697677145-20261127072240-48483c.jpg', '[\"516193774-676404045439662-80522312448513-20261127072240-dc0f15.jpg\",\"517118130-676404105439656-10413145417777-20261127072240-62c1ad.jpg\"]', '2026-10-28 03:20:20', '2026-11-27 12:29:58', NULL, NULL, NULL, NULL, NULL, NULL, 2021, 'Company-owned since 2021', 'MINOR', 'Minor bed liner wear from light cargo; exterior body panels intact.', '{\"518275072-676404038772996-17279697677145-20261127072240-48483c.jpg\":\"Exterior\",\"516193774-676404045439662-80522312448513-20261127072240-dc0f15.jpg\":\"Exterior\",\"517118130-676404105439656-10413145417777-20261127072240-62c1ad.jpg\":\"Interior\"}', 'SALE', NULL, 5000.00, 'Valid government ID and driver\'s license required upon viewing/pickup. Reservation holds automatically release if payment is not completed before the deadline. All deposits are applied toward final payment or refunded per inspection terms.', NULL, 2024, NULL, NULL, 'Negotiable', 10000.00, NULL, NULL, NULL, NULL, 'Boss', NULL, NULL),
(6, '2022 Suzuki XL7', 'Suzuki', 0.00, NULL, NULL, 'UW-SWF06', '39,000 km', 'Automatic', 'Hatchback', 'Gasoline', '5 Seats', 'MAINTENANCE', 1, 0, '15\" Factory Sport Steelies', '175/65 R15', 'Great Fuel Mileage (20 km/L)', 'Sunrise Orange Pearl Metallic', 'City Friendly Commuter / Easy Parking', 'Full Service Records Available', '821123799-3475211326019298-7066073226767-20261127072038-74ea98.jpg', '[\"807284890-1750042849538158-8241564731426-20261127072038-ab19a4.jpg\",\"823647978-846625391846077-11182998672250-20261127072038-badce9.jpg\"]', '2026-10-28 03:20:20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2022, '1 previous owner', 'NONE', 'Clean condition inside and out; tires at 85% tread.', '{\"821123799-3475211326019298-7066073226767-20261127072038-74ea98.jpg\":\"Exterior\",\"807284890-1750042849538158-8241564731426-20261127072038-ab19a4.jpg\":\"Exterior\",\"823647978-846625391846077-11182998672250-20261127072038-badce9.jpg\":\"Interior\"}', 'SALE', NULL, NULL, 'Valid government ID and driver\'s license required upon viewing/pickup. Reservation holds automatically release if payment is not completed before the deadline. All deposits are applied toward final payment or refunded per inspection terms.', NULL, 2024, NULL, NULL, 'Negotiable', 10000.00, NULL, NULL, NULL, NULL, 'Boss', NULL, NULL),
(7, '2021 Toyota Vios XLE', 'Toyota', 560000.00, NULL, NULL, 'UW-RAV07', '45,000 km', 'Automatic', 'SUV', 'Hybrid', '5 Seats', 'TRADED', 1, 0, '18\" Diamond Cut Two-Tone Alloys', '225/60 R18 Michelin', 'Hybrid Electric / Ultra Smooth Ride', 'Silver Sky Metallic', 'Original Factory Paint / 1st Owner', 'Battery Health Tested 100%', '906289458-1451581573757015-5721820078948-20261127071632-99ef29.jpg', '[\"904517879-1163669052864969-1628288376993-20261127071632-e5b669.jpg\",\"905030438-4977627902465389-9649973114222-20261127071632-50c914.jpg\"]', '2026-10-28 03:20:20', NULL, '2026-11-27 12:34:21', '2021 Honda', 'UW-H678', NULL, NULL, NULL, 2021, '3 previous owner', 'NONE', 'Original factory paint; hybrid battery and body inspected clean.', '{\"906289458-1451581573757015-5721820078948-20261127071632-99ef29.jpg\":\"Exterior\",\"904517879-1163669052864969-1628288376993-20261127071632-e5b669.jpg\":\"Exterior\",\"905030438-4977627902465389-9649973114222-20261127071632-50c914.jpg\":\"Interior\"}', 'SALE', NULL, 10000.00, 'Valid government ID and driver\'s license required upon viewing/pickup. Reservation holds automatically release if payment is not completed before the deadline. All deposits are applied toward final payment or refunded per inspection terms.', NULL, 2024, NULL, NULL, 'Negotiable', 10000.00, NULL, NULL, NULL, NULL, 'Boss', NULL, NULL),
(8, '2024 Nissan Terra VE', 'Nissan', 940000.00, NULL, 1540000.00, 'CCG 3574', '13,000 km', 'Automatic', 'Sedan', 'Gasoline', '5 Seats', 'RESERVED', 0, 0, '15\" Multi-Spoke Alloy Wheels', '185/65 R15', 'Huge Trunk Space / Comfortable Legroom', 'Lunar Metallic Black', 'Affordable Reliable Family Sedan', 'Complete Spare Tire & Tools', '903684460-1794503819343093-4983372071454-20261127071245-536356.jpg', '[\"902979174-1946591233386514-8581049774993-20261127071245-3e653a.jpg\"]', '2026-10-28 03:20:20', NULL, NULL, NULL, NULL, '2026-11-27 13:38:22', '2026-11-28 13:38:22', NULL, 2024, '2 previous owners', 'REPAIRED', 'Right rear door repainted due to minor parking scrape; fully restored.', '{\"903684460-1794503819343093-4983372071454-20261127071245-536356.jpg\":\"Exterior\",\"902979174-1946591233386514-8581049774993-20261127071245-3e653a.jpg\":\"Exterior\"}', 'SALE', NULL, 100000.00, 'Valid government ID and driver\'s license required upon viewing/pickup. Reservation holds automatically release if payment is not completed before the deadline. All deposits are applied toward final payment or refunded per inspection terms.', NULL, 2024, NULL, NULL, 'Negotiable', 10000.00, NULL, NULL, NULL, NULL, 'Boss', NULL, NULL),
(16, '2026 Toyota Vios XLE', 'Toyota', 740000.00, NULL, NULL, 'DCD 7701', '10,000 km', 'Automatic', 'Sedan', 'Gasoline', '5 Seats', 'AVAILABLE', 0, 0, '', '', '', 'Silver Metallic', '', '', '929595700-1131954503059987-8383345773320-20261127134150-f32c71.jpg', '[\"923007341-1854882875512809-2174799586414-20261127134150-a15d84.jpg\",\"926564040-1481656794029781-4475537576649-20261127134150-2abc35.jpg\",\"925679321-1821248868995864-1630867424223-20261127134150-8f1987.jpg\"]', '2026-11-27 05:41:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2026, '1 previous owner', 'NONE', 'perfect condition', '{\"929595700-1131954503059987-8383345773320-20261127134150-f32c71.jpg\":\"Exterior\",\"923007341-1854882875512809-2174799586414-20261127134150-a15d84.jpg\":\"Interior\",\"926564040-1481656794029781-4475537576649-20261127134150-2abc35.jpg\":\"Interior\",\"925679321-1821248868995864-1630867424223-20261127134150-8f1987.jpg\":\"Exterior\"}', 'SALE', NULL, 200000.00, 'Valid government ID and driver\'s license required upon viewing/pickup. Reservation holds automatically release if payment is not completed before the deadline. All deposits are applied toward final payment or refunded per inspection terms.', NULL, 2024, NULL, NULL, 'Negotiable', 10000.00, NULL, NULL, NULL, NULL, 'Boss', NULL, NULL),
(17, '2021 Nissan Navara VL 4X4', 'Nissan', 900000.00, 980000.00, NULL, 'GAT 8533', '33,000 km', 'Automatic', 'MPV', 'Diesel', '5 Seats', 'SOLD', 0, 0, '', '', '', 'Forged Metallic Copper', '', '', '927531620-1140240652306023-5751639719405-20261127134538-d1e95d.jpg', '[\"926368308-1634181675388831-8679791847837-20261127134538-5cc2a8.jpg\",\"922251431-1159637496525107-1075865070689-20261127134538-bb0ea7.jpg\"]', '2026-11-27 05:45:38', '2026-11-27 13:56:58', NULL, NULL, NULL, NULL, NULL, NULL, 2021, '2 previous owners', 'NONE', 'good condition', '{\"927531620-1140240652306023-5751639719405-20261127134538-d1e95d.jpg\":\"Exterior\",\"926368308-1634181675388831-8679791847837-20261127134538-5cc2a8.jpg\":\"Exterior\",\"922251431-1159637496525107-1075865070689-20261127134538-bb0ea7.jpg\":\"Interior\"}', 'SALE', NULL, 7000.00, 'Valid government ID and driver\'s license required upon viewing/pickup. Reservation holds automatically release if payment is not completed before the deadline. All deposits are applied toward final payment or refunded per inspection terms.', NULL, 2024, NULL, NULL, 'Negotiable', 10000.00, NULL, NULL, NULL, NULL, 'Boss', NULL, NULL),
(18, '2024 Toyota Avanza E.', 'Toyota', 880000.00, NULL, NULL, 'FAQ 6107', '21,000 km', 'Automatic', 'SUV', 'Gasoline', '5 Seats', 'AVAILABLE', 0, 0, '', '', '', 'Dark Red Mica Metallic', '', '', '925950403-1162391016174534-2821189245807-20261127134807-674767.jpg', '[\"933120975-1719542729695293-8005198839928-20261127134807-91d177.jpg\",\"923592081-1930462861273704-7672850730833-20261127134807-ff1ee6.jpg\"]', '2026-11-27 05:48:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2024, '1 previous owner', 'NONE', 'perfect condition', '{\"925950403-1162391016174534-2821189245807-20261127134807-674767.jpg\":\"Exterior\",\"933120975-1719542729695293-8005198839928-20261127134807-91d177.jpg\":\"Interior\",\"923592081-1930462861273704-7672850730833-20261127134807-ff1ee6.jpg\":\"Exterior\"}', 'SALE', NULL, 6000.00, 'Valid government ID and driver\'s license required upon viewing/pickup. Reservation holds automatically release if payment is not completed before the deadline. All deposits are applied toward final payment or refunded per inspection terms.', NULL, 2024, NULL, NULL, 'Negotiable', 10000.00, NULL, NULL, NULL, NULL, 'Boss', NULL, NULL),
(19, '2023 Geely Coolray SE Sport', 'Greely Auto', 680000.00, NULL, NULL, 'FAO 4866', '46,000 km', 'Automatic', 'SUV', 'Gasoline', '5 Seats', 'AVAILABLE', 0, 0, '', '', '', 'Pearl White', '', '', '926661241-1822986258768167-7784572486901-20261127135058-81f1b5.jpg', '[\"926503030-2222841575292117-2415607628230-20261127135058-669c41.jpg\",\"923089574-1126273550195288-4341963838104-20261127135058-139c2f.jpg\",\"917342919-2397204301024940-9219223280326-20261127135058-f14390.jpg\"]', '2026-11-27 05:50:58', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2023, '3 previous owner', 'NONE', 'great condition', '{\"926661241-1822986258768167-7784572486901-20261127135058-81f1b5.jpg\":\"Exterior\",\"926503030-2222841575292117-2415607628230-20261127135058-669c41.jpg\":\"Exterior\",\"923089574-1126273550195288-4341963838104-20261127135058-139c2f.jpg\":\"Exterior\",\"917342919-2397204301024940-9219223280326-20261127135058-f14390.jpg\":\"Exterior\"}', 'SALE', NULL, 6000.00, 'Valid government ID and driver\'s license required upon viewing/pickup. Reservation holds automatically release if payment is not completed before the deadline. All deposits are applied toward final payment or refunded per inspection terms.', NULL, 2024, NULL, NULL, 'Negotiable', 10000.00, NULL, NULL, NULL, NULL, 'Boss', NULL, NULL),
(20, '2026 Mitsubishi L300 FB', 'Mitsubishi', 840000.00, NULL, NULL, 'NKT 9388', '17,000 km', 'Manual', 'Pickup', 'Diesel', '12 Seats', 'AVAILABLE', 0, 0, '', '', '', 'Polar White', '', '', '921965047-1489035546502598-1849434788978-20261127135330-40cd26.jpg', '[\"921424674-1133153129578875-6039982588693-20261127135330-9fd3b5.jpg\"]', '2026-11-27 05:53:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2026, 'No owner', 'NONE', 'mint condition', '{\"921965047-1489035546502598-1849434788978-20261127135330-40cd26.jpg\":\"Exterior\",\"921424674-1133153129578875-6039982588693-20261127135330-9fd3b5.jpg\":\"Exterior\"}', 'SALE', NULL, 5000.00, 'Valid government ID and driver\'s license required upon viewing/pickup. Reservation holds automatically release if payment is not completed before the deadline. All deposits are applied toward final payment or refunded per inspection terms.', NULL, 2024, NULL, NULL, 'Negotiable', 10000.00, NULL, NULL, NULL, NULL, 'Boss', NULL, NULL),
(21, '2025 Hyundai Stargazer GL', 'Hyundai', 800000.00, 860000.00, NULL, 'FAS 3132', '13,000 km', 'Automatic', 'Sedan', 'Gasoline', '5 Seats', 'SOLD', 0, 0, '', '', '', 'Pearl White', '', '', '921076874-1154771020640435-2591280224388-20261127135532-7f9e8e.jpg', '[\"921130358-1378733730866933-3644634542505-20261127135532-b55d95.jpg\",\"926527455-1085176564564720-2196561089596-20261127135532-859322.jpg\"]', '2026-11-27 05:55:32', '2026-11-27 14:05:57', NULL, NULL, NULL, NULL, NULL, NULL, 2025, '1 previous owner', 'REPAIRED', 'minor scratch', '{\"921076874-1154771020640435-2591280224388-20261127135532-7f9e8e.jpg\":\"Exterior\",\"921130358-1378733730866933-3644634542505-20261127135532-b55d95.jpg\":\"Exterior\",\"926527455-1085176564564720-2196561089596-20261127135532-859322.jpg\":\"Exterior\"}', 'SALE', NULL, 6000.00, 'Valid government ID and driver\'s license required upon viewing/pickup. Reservation holds automatically release if payment is not completed before the deadline. All deposits are applied toward final payment or refunded per inspection terms.', NULL, 2024, NULL, NULL, 'Negotiable', 10000.00, NULL, NULL, NULL, NULL, 'Boss', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(50) DEFAULT '',
  `role` varchar(50) NOT NULL DEFAULT 'employee',
  `department` varchar(100) DEFAULT 'Sales',
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `username`, `password`, `name`, `email`, `phone`, `role`, `department`, `status`, `created_at`) VALUES
(1, 'admin', '$2y$12$B6ImRZ/DnYxulGNLMp52BuS/JB81ORAKbgW4tTzYwhQ6e1wKI7LL2', 'Administrator', 'admin@uniwheels.com', '+1 (555) 019-2831', 'admin', 'Executive Management', 'active', '2026-10-28 03:20:20');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `booking_ref` varchar(32) NOT NULL,
  `car_id` int(11) NOT NULL,
  `customer_name` varchar(120) NOT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `customer_email` varchar(120) NOT NULL,
  `hold_days` int(11) NOT NULL DEFAULT 2,
  `car_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `deposit_amount` decimal(12,2) NOT NULL DEFAULT 10000.00,
  `reserved_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'ACTIVE',
  `notes` text DEFAULT NULL,
  `notification_sent` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `setting_key` varchar(64) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('default_deposit', '5000', '2026-11-26 16:09:15'),
('default_policy', 'Valid government ID and driver\'s license required upon viewing/pickup. Reservation holds automatically release if payment is not completed before the deadline. All deposits are applied toward final payment or refunded per inspection terms.', '2026-11-26 16:09:15'),
('footer_address', 'UniWheels Auto Mall, Main Highway Showroom Strip', '2026-11-26 16:09:15'),
('hero_subtitle', 'Inspected, complete with original LTO papers, and transparently documented before you book.', '2026-11-26 16:09:15'),
('hero_title', 'Quality secondhand cars you can trust', '2026-11-26 16:09:15'),
('max_hold_days', '7', '2026-11-26 16:09:15'),
('messenger_url', 'https://www.messenger.com/t/110204505385093', '2026-11-27 05:37:11'),
('showroom_address', '123 Car City Commercial Highway', '2026-11-26 16:09:15'),
('showroom_email', 'sales@uniwheelsmotors.com', '2026-11-26 16:09:15'),
('showroom_hours', 'Open Everyday: 8:30 AM – 6:30 PM', '2026-11-26 16:09:15'),
('showroom_phone', '(02) 8123-4567 / 0917-123-4567', '2026-11-26 16:09:15');

-- --------------------------------------------------------

--
-- Table structure for table `uw_reservation_holds`
--

CREATE TABLE `uw_reservation_holds` (
  `id` int(10) UNSIGNED NOT NULL,
  `car_id` int(11) NOT NULL,
  `reference` varchar(24) NOT NULL,
  `owner_hash` char(64) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_contact` varchar(150) NOT NULL,
  `rental_start` date DEFAULT NULL,
  `rental_end` date DEFAULT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `deposit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `state` varchar(20) NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `uw_reservation_holds`
--

INSERT INTO `uw_reservation_holds` (`id`, `car_id`, `reference`, `owner_hash`, `customer_name`, `customer_contact`, `rental_start`, `rental_end`, `total`, `deposit`, `expires_at`, `created_at`, `state`) VALUES
(5, 6, 'UW-H261127-MQWU', 'eee6d9f81635e71f6fba535d0471c6d5576994ed74d3fc76ccc962eaa625c6bb', 'Juan Miguel', 'miguel@gmail.com', NULL, NULL, 840000.00, 5000.00, '2026-12-01 11:09:58', '2026-11-27 11:09:58', 'cancelled'),
(6, 7, 'UW-H261127-SZR3', 'eee6d9f81635e71f6fba535d0471c6d5576994ed74d3fc76ccc962eaa625c6bb', 'Juan Miguel', 'miguel@gmail.com', NULL, NULL, 560000.00, 10000.00, '2026-11-28 12:33:35', '2026-11-27 12:33:35', 'cancelled'),
(7, 8, 'UW-H261127-UKD3', 'eee6d9f81635e71f6fba535d0471c6d5576994ed74d3fc76ccc962eaa625c6bb', 'Juan Miguel', 'miguel@gmail.com', NULL, NULL, 1540000.00, 1100000.00, '2026-11-28 13:38:22', '2026-11-27 13:38:22', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_bookings`
--

CREATE TABLE `vehicle_bookings` (
  `id` int(10) UNSIGNED NOT NULL,
  `car_id` int(11) NOT NULL,
  `reference` char(15) NOT NULL,
  `owner_token` char(64) NOT NULL,
  `booking_type` varchar(4) NOT NULL,
  `state` varchar(10) NOT NULL,
  `rental_start` date DEFAULT NULL,
  `rental_end` date DEFAULT NULL,
  `total` decimal(12,2) NOT NULL,
  `deposit` decimal(12,2) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_contact` varchar(150) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicle_bookings`
--

INSERT INTO `vehicle_bookings` (`id`, `car_id`, `reference`, `owner_token`, `booking_type`, `state`, `rental_start`, `rental_end`, `total`, `deposit`, `customer_name`, `customer_contact`, `expires_at`, `created_at`) VALUES
(12, 7, 'UW-B261127-9TGQ', 'e1bf299186bada6e5d9f2f69ed4fbb880670309baaf0abbb4aee5dc97934eed9', 'BUY', 'cancelled', '2026-11-27', '2026-11-27', 560000.00, 10000.00, 'Maria Santos', 'maria@gmail.com', '2026-11-28 09:43:58', '2026-11-27 09:43:58');

-- --------------------------------------------------------

--
-- Table structure for table `wp_commentmeta`
--

CREATE TABLE `wp_commentmeta` (
  `meta_id` bigint(20) UNSIGNED NOT NULL,
  `comment_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wp_comments`
--

CREATE TABLE `wp_comments` (
  `comment_ID` bigint(20) UNSIGNED NOT NULL,
  `comment_post_ID` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `comment_author` tinytext NOT NULL,
  `comment_author_email` varchar(100) NOT NULL DEFAULT '',
  `comment_author_url` varchar(200) NOT NULL DEFAULT '',
  `comment_author_IP` varchar(100) NOT NULL DEFAULT '',
  `comment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_content` text NOT NULL,
  `comment_karma` int(11) NOT NULL DEFAULT 0,
  `comment_approved` varchar(20) NOT NULL DEFAULT '1',
  `comment_agent` varchar(255) NOT NULL DEFAULT '',
  `comment_type` varchar(20) NOT NULL DEFAULT 'comment',
  `comment_parent` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `user_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wp_comments`
--

INSERT INTO `wp_comments` (`comment_ID`, `comment_post_ID`, `comment_author`, `comment_author_email`, `comment_author_url`, `comment_author_IP`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_karma`, `comment_approved`, `comment_agent`, `comment_type`, `comment_parent`, `user_id`) VALUES
(1, 1, 'A WordPress Commenter', 'wapuu@wordpress.example', 'https://wordpress.org/', '', '2026-09-27 13:09:50', '2026-09-27 13:09:50', 'Hi, this is a comment.\nTo get started with moderating, editing, and deleting comments, please visit the Comments screen in the dashboard.\nCommenter avatars come from <a href=\"https://gravatar.com/\">Gravatar</a>.', 0, '1', '', 'comment', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `wp_links`
--

CREATE TABLE `wp_links` (
  `link_id` bigint(20) UNSIGNED NOT NULL,
  `link_url` varchar(255) NOT NULL DEFAULT '',
  `link_name` varchar(255) NOT NULL DEFAULT '',
  `link_image` varchar(255) NOT NULL DEFAULT '',
  `link_target` varchar(25) NOT NULL DEFAULT '',
  `link_description` varchar(255) NOT NULL DEFAULT '',
  `link_visible` varchar(20) NOT NULL DEFAULT 'Y',
  `link_owner` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `link_rating` int(11) NOT NULL DEFAULT 0,
  `link_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `link_rel` varchar(255) NOT NULL DEFAULT '',
  `link_notes` mediumtext NOT NULL,
  `link_rss` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wp_options`
--

CREATE TABLE `wp_options` (
  `option_id` bigint(20) UNSIGNED NOT NULL,
  `option_name` varchar(191) NOT NULL DEFAULT '',
  `option_value` longtext NOT NULL,
  `autoload` varchar(20) NOT NULL DEFAULT 'yes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wp_options`
--

INSERT INTO `wp_options` (`option_id`, `option_name`, `option_value`, `autoload`) VALUES
(1, 'cron', 'a:10:{i:1795763390;a:1:{s:34:\"wp_privacy_delete_old_export_files\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:6:\"hourly\";s:4:\"args\";a:0:{}s:8:\"interval\";i:3600;}}}i:1795784990;a:2:{s:32:\"recovery_mode_clean_expired_keys\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}s:41:\"wp_privacy_personal_data_cleanup_requests\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1795785001;a:3:{s:19:\"wp_scheduled_delete\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}s:25:\"delete_expired_transients\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}s:21:\"wp_update_user_counts\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}}i:1795785006;a:1:{s:30:\"wp_scheduled_auto_draft_delete\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1795788590;a:1:{s:16:\"wp_version_check\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}}i:1795790390;a:1:{s:17:\"wp_update_plugins\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}}i:1795792190;a:1:{s:16:\"wp_update_themes\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}}i:1795958114;a:1:{s:30:\"wp_delete_temp_updater_backups\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:6:\"weekly\";s:4:\"args\";a:0:{}s:8:\"interval\";i:604800;}}}i:1796044190;a:1:{s:30:\"wp_site_health_scheduled_check\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:6:\"weekly\";s:4:\"args\";a:0:{}s:8:\"interval\";i:604800;}}}s:7:\"version\";i:2;}', 'on'),
(2, 'siteurl', 'http://localhost/uniwheels', 'on'),
(3, 'home', 'http://localhost/uniwheels', 'on'),
(4, 'blogname', 'UniWheels', 'on'),
(5, 'blogdescription', '', 'on'),
(6, 'users_can_register', '0', 'on'),
(7, 'admin_email', 'ciedeejyrho.avelino@wvsu.edu.ph', 'on'),
(8, 'start_of_week', '1', 'on'),
(9, 'use_balanceTags', '0', 'on'),
(10, 'use_smilies', '1', 'on'),
(11, 'require_name_email', '1', 'on'),
(12, 'comments_notify', '1', 'on'),
(13, 'posts_per_rss', '10', 'on'),
(14, 'rss_use_excerpt', '0', 'on'),
(15, 'mailserver_url', 'mail.example.com', 'on'),
(16, 'mailserver_login', 'login@example.com', 'on'),
(17, 'mailserver_pass', '', 'on'),
(18, 'mailserver_port', '110', 'on'),
(19, 'default_category', '1', 'on'),
(20, 'default_comment_status', 'open', 'on'),
(21, 'default_ping_status', 'open', 'on'),
(22, 'default_pingback_flag', '0', 'on'),
(23, 'posts_per_page', '10', 'on'),
(24, 'date_format', 'F j, Y', 'on'),
(25, 'time_format', 'g:i a', 'on'),
(26, 'links_updated_date_format', 'F j, Y g:i a', 'on'),
(27, 'comment_moderation', '0', 'on'),
(28, 'moderation_notify', '1', 'on'),
(29, 'permalink_structure', '/%year%/%monthnum%/%day%/%postname%/', 'on'),
(30, 'rewrite_rules', 'a:94:{s:11:\"^wp-json/?$\";s:22:\"index.php?rest_route=/\";s:14:\"^wp-json/(.*)?\";s:33:\"index.php?rest_route=/$matches[1]\";s:21:\"^index.php/wp-json/?$\";s:22:\"index.php?rest_route=/\";s:24:\"^index.php/wp-json/(.*)?\";s:33:\"index.php?rest_route=/$matches[1]\";s:17:\"^wp-sitemap\\.xml$\";s:23:\"index.php?sitemap=index\";s:17:\"^wp-sitemap\\.xsl$\";s:36:\"index.php?sitemap-stylesheet=sitemap\";s:23:\"^wp-sitemap-index\\.xsl$\";s:34:\"index.php?sitemap-stylesheet=index\";s:48:\"^wp-sitemap-([a-z]+?)-([a-z\\d_-]+?)-(\\d+?)\\.xml$\";s:75:\"index.php?sitemap=$matches[1]&sitemap-subtype=$matches[2]&paged=$matches[3]\";s:34:\"^wp-sitemap-([a-z]+?)-(\\d+?)\\.xml$\";s:47:\"index.php?sitemap=$matches[1]&paged=$matches[2]\";s:47:\"category/(.+?)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:52:\"index.php?category_name=$matches[1]&feed=$matches[2]\";s:42:\"category/(.+?)/(feed|rdf|rss|rss2|atom)/?$\";s:52:\"index.php?category_name=$matches[1]&feed=$matches[2]\";s:23:\"category/(.+?)/embed/?$\";s:46:\"index.php?category_name=$matches[1]&embed=true\";s:35:\"category/(.+?)/page/?([0-9]{1,})/?$\";s:53:\"index.php?category_name=$matches[1]&paged=$matches[2]\";s:17:\"category/(.+?)/?$\";s:35:\"index.php?category_name=$matches[1]\";s:44:\"tag/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?tag=$matches[1]&feed=$matches[2]\";s:39:\"tag/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?tag=$matches[1]&feed=$matches[2]\";s:20:\"tag/([^/]+)/embed/?$\";s:36:\"index.php?tag=$matches[1]&embed=true\";s:32:\"tag/([^/]+)/page/?([0-9]{1,})/?$\";s:43:\"index.php?tag=$matches[1]&paged=$matches[2]\";s:14:\"tag/([^/]+)/?$\";s:25:\"index.php?tag=$matches[1]\";s:45:\"type/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?post_format=$matches[1]&feed=$matches[2]\";s:40:\"type/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?post_format=$matches[1]&feed=$matches[2]\";s:21:\"type/([^/]+)/embed/?$\";s:44:\"index.php?post_format=$matches[1]&embed=true\";s:33:\"type/([^/]+)/page/?([0-9]{1,})/?$\";s:51:\"index.php?post_format=$matches[1]&paged=$matches[2]\";s:15:\"type/([^/]+)/?$\";s:33:\"index.php?post_format=$matches[1]\";s:48:\".*wp-(atom|rdf|rss|rss2|feed|commentsrss2)\\.php$\";s:18:\"index.php?feed=old\";s:20:\".*wp-app\\.php(/.*)?$\";s:19:\"index.php?error=403\";s:18:\".*wp-register.php$\";s:23:\"index.php?register=true\";s:32:\"feed/(feed|rdf|rss|rss2|atom)/?$\";s:27:\"index.php?&feed=$matches[1]\";s:27:\"(feed|rdf|rss|rss2|atom)/?$\";s:27:\"index.php?&feed=$matches[1]\";s:8:\"embed/?$\";s:21:\"index.php?&embed=true\";s:20:\"page/?([0-9]{1,})/?$\";s:28:\"index.php?&paged=$matches[1]\";s:41:\"comments/feed/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?&feed=$matches[1]&withcomments=1\";s:36:\"comments/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?&feed=$matches[1]&withcomments=1\";s:17:\"comments/embed/?$\";s:21:\"index.php?&embed=true\";s:44:\"search/(.+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:40:\"index.php?s=$matches[1]&feed=$matches[2]\";s:39:\"search/(.+)/(feed|rdf|rss|rss2|atom)/?$\";s:40:\"index.php?s=$matches[1]&feed=$matches[2]\";s:20:\"search/(.+)/embed/?$\";s:34:\"index.php?s=$matches[1]&embed=true\";s:32:\"search/(.+)/page/?([0-9]{1,})/?$\";s:41:\"index.php?s=$matches[1]&paged=$matches[2]\";s:14:\"search/(.+)/?$\";s:23:\"index.php?s=$matches[1]\";s:47:\"author/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?author_name=$matches[1]&feed=$matches[2]\";s:42:\"author/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?author_name=$matches[1]&feed=$matches[2]\";s:23:\"author/([^/]+)/embed/?$\";s:44:\"index.php?author_name=$matches[1]&embed=true\";s:35:\"author/([^/]+)/page/?([0-9]{1,})/?$\";s:51:\"index.php?author_name=$matches[1]&paged=$matches[2]\";s:17:\"author/([^/]+)/?$\";s:33:\"index.php?author_name=$matches[1]\";s:69:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$\";s:80:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]\";s:64:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$\";s:80:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]\";s:45:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/embed/?$\";s:74:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&embed=true\";s:57:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/page/?([0-9]{1,})/?$\";s:81:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&paged=$matches[4]\";s:39:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/?$\";s:63:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]\";s:56:\"([0-9]{4})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$\";s:64:\"index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]\";s:51:\"([0-9]{4})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$\";s:64:\"index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]\";s:32:\"([0-9]{4})/([0-9]{1,2})/embed/?$\";s:58:\"index.php?year=$matches[1]&monthnum=$matches[2]&embed=true\";s:44:\"([0-9]{4})/([0-9]{1,2})/page/?([0-9]{1,})/?$\";s:65:\"index.php?year=$matches[1]&monthnum=$matches[2]&paged=$matches[3]\";s:26:\"([0-9]{4})/([0-9]{1,2})/?$\";s:47:\"index.php?year=$matches[1]&monthnum=$matches[2]\";s:43:\"([0-9]{4})/feed/(feed|rdf|rss|rss2|atom)/?$\";s:43:\"index.php?year=$matches[1]&feed=$matches[2]\";s:38:\"([0-9]{4})/(feed|rdf|rss|rss2|atom)/?$\";s:43:\"index.php?year=$matches[1]&feed=$matches[2]\";s:19:\"([0-9]{4})/embed/?$\";s:37:\"index.php?year=$matches[1]&embed=true\";s:31:\"([0-9]{4})/page/?([0-9]{1,})/?$\";s:44:\"index.php?year=$matches[1]&paged=$matches[2]\";s:13:\"([0-9]{4})/?$\";s:26:\"index.php?year=$matches[1]\";s:58:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:68:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:88:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:83:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:83:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:64:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:53:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/embed/?$\";s:91:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&embed=true\";s:57:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/trackback/?$\";s:85:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&tb=1\";s:77:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:97:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&feed=$matches[5]\";s:72:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:97:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&feed=$matches[5]\";s:65:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/page/?([0-9]{1,})/?$\";s:98:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&paged=$matches[5]\";s:72:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/comment-page-([0-9]{1,})/?$\";s:98:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&cpage=$matches[5]\";s:61:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)(?:/([0-9]+))?/?$\";s:97:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&page=$matches[5]\";s:47:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:57:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:77:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:72:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:72:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:53:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:64:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/comment-page-([0-9]{1,})/?$\";s:81:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&cpage=$matches[4]\";s:51:\"([0-9]{4})/([0-9]{1,2})/comment-page-([0-9]{1,})/?$\";s:65:\"index.php?year=$matches[1]&monthnum=$matches[2]&cpage=$matches[3]\";s:38:\"([0-9]{4})/comment-page-([0-9]{1,})/?$\";s:44:\"index.php?year=$matches[1]&cpage=$matches[2]\";s:27:\".?.+?/attachment/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:37:\".?.+?/attachment/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:57:\".?.+?/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:52:\".?.+?/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:52:\".?.+?/attachment/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:33:\".?.+?/attachment/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:16:\"(.?.+?)/embed/?$\";s:41:\"index.php?pagename=$matches[1]&embed=true\";s:20:\"(.?.+?)/trackback/?$\";s:35:\"index.php?pagename=$matches[1]&tb=1\";s:40:\"(.?.+?)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:47:\"index.php?pagename=$matches[1]&feed=$matches[2]\";s:35:\"(.?.+?)/(feed|rdf|rss|rss2|atom)/?$\";s:47:\"index.php?pagename=$matches[1]&feed=$matches[2]\";s:28:\"(.?.+?)/page/?([0-9]{1,})/?$\";s:48:\"index.php?pagename=$matches[1]&paged=$matches[2]\";s:35:\"(.?.+?)/comment-page-([0-9]{1,})/?$\";s:48:\"index.php?pagename=$matches[1]&cpage=$matches[2]\";s:24:\"(.?.+?)(?:/([0-9]+))?/?$\";s:47:\"index.php?pagename=$matches[1]&page=$matches[2]\";}', 'on'),
(31, 'hack_file', '0', 'on'),
(32, 'blog_charset', 'UTF-8', 'on'),
(33, 'moderation_keys', '', 'off'),
(34, 'active_plugins', 'a:0:{}', 'on'),
(35, 'category_base', '', 'on'),
(36, 'ping_sites', 'https://rpc.pingomatic.com/', 'on'),
(37, 'comment_max_links', '2', 'on'),
(38, 'gmt_offset', '0', 'on'),
(39, 'default_email_category', '1', 'on'),
(40, 'recently_edited', '', 'off'),
(41, 'template', 'uniwheels', 'on'),
(42, 'stylesheet', 'uniwheels', 'on'),
(43, 'comment_registration', '0', 'on'),
(44, 'html_type', 'text/html', 'on'),
(45, 'use_trackback', '0', 'on'),
(46, 'default_role', 'subscriber', 'on'),
(47, 'db_version', '61833', 'on'),
(48, 'uploads_use_yearmonth_folders', '1', 'on'),
(49, 'upload_path', '', 'on'),
(50, 'blog_public', '0', 'on'),
(51, 'default_link_category', '2', 'on'),
(52, 'show_on_front', 'posts', 'on'),
(53, 'tag_base', '', 'on'),
(54, 'show_avatars', '1', 'on'),
(55, 'avatar_rating', 'G', 'on'),
(56, 'upload_url_path', '', 'on'),
(57, 'thumbnail_size_w', '150', 'on'),
(58, 'thumbnail_size_h', '150', 'on'),
(59, 'thumbnail_crop', '1', 'on'),
(60, 'medium_size_w', '300', 'on'),
(61, 'medium_size_h', '300', 'on'),
(62, 'avatar_default', 'mystery', 'on'),
(63, 'large_size_w', '1024', 'on'),
(64, 'large_size_h', '1024', 'on'),
(65, 'image_default_link_type', 'none', 'on'),
(66, 'image_default_size', '', 'on'),
(67, 'image_default_align', '', 'on'),
(68, 'close_comments_for_old_posts', '0', 'on'),
(69, 'close_comments_days_old', '14', 'on'),
(70, 'thread_comments', '1', 'on'),
(71, 'thread_comments_depth', '5', 'on'),
(72, 'page_comments', '0', 'on'),
(73, 'comments_per_page', '50', 'on'),
(74, 'default_comments_page', 'newest', 'on'),
(75, 'comment_order', 'asc', 'on'),
(76, 'sticky_posts', 'a:0:{}', 'on'),
(77, 'widget_categories', 'a:2:{i:1;a:0:{}s:12:\"_multiwidget\";i:1;}', 'auto'),
(78, 'widget_text', 'a:2:{i:1;a:0:{}s:12:\"_multiwidget\";i:1;}', 'auto'),
(79, 'widget_rss', 'a:2:{i:1;a:0:{}s:12:\"_multiwidget\";i:1;}', 'auto'),
(80, 'uninstall_plugins', 'a:0:{}', 'off'),
(81, 'timezone_string', '', 'on'),
(82, 'page_for_posts', '0', 'on'),
(83, 'page_on_front', '0', 'on'),
(84, 'default_post_format', '0', 'on'),
(85, 'link_manager_enabled', '0', 'on'),
(86, 'finished_splitting_shared_terms', '1', 'on'),
(87, 'site_icon', '0', 'on'),
(88, 'medium_large_size_w', '768', 'on'),
(89, 'medium_large_size_h', '0', 'on'),
(90, 'wp_page_for_privacy_policy', '3', 'on'),
(91, 'show_comments_cookies_opt_in', '1', 'on'),
(92, 'admin_email_lifespan', '1806066590', 'on'),
(93, 'disallowed_keys', '', 'off'),
(94, 'comment_previously_approved', '1', 'on'),
(95, 'auto_plugin_theme_update_emails', 'a:0:{}', 'off'),
(96, 'auto_update_core_dev', 'enabled', 'on'),
(97, 'auto_update_core_minor', 'enabled', 'on'),
(98, 'auto_update_core_major', 'enabled', 'on'),
(99, 'wp_force_deactivated_plugins', 'a:0:{}', 'on'),
(100, 'wp_attachment_pages_enabled', '0', 'on'),
(101, 'wp_notes_notify', '1', 'on'),
(102, 'initial_db_version', '61833', 'on'),
(103, 'wp_user_roles', 'a:5:{s:13:\"administrator\";a:2:{s:4:\"name\";s:13:\"Administrator\";s:12:\"capabilities\";a:61:{s:13:\"switch_themes\";b:1;s:11:\"edit_themes\";b:1;s:16:\"activate_plugins\";b:1;s:12:\"edit_plugins\";b:1;s:10:\"edit_users\";b:1;s:10:\"edit_files\";b:1;s:14:\"manage_options\";b:1;s:17:\"moderate_comments\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:12:\"upload_files\";b:1;s:6:\"import\";b:1;s:15:\"unfiltered_html\";b:1;s:10:\"edit_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:10:\"edit_pages\";b:1;s:4:\"read\";b:1;s:8:\"level_10\";b:1;s:7:\"level_9\";b:1;s:7:\"level_8\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:17:\"edit_others_pages\";b:1;s:20:\"edit_published_pages\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_pages\";b:1;s:19:\"delete_others_pages\";b:1;s:22:\"delete_published_pages\";b:1;s:12:\"delete_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:20:\"delete_private_posts\";b:1;s:18:\"edit_private_posts\";b:1;s:18:\"read_private_posts\";b:1;s:20:\"delete_private_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"read_private_pages\";b:1;s:12:\"delete_users\";b:1;s:12:\"create_users\";b:1;s:17:\"unfiltered_upload\";b:1;s:14:\"edit_dashboard\";b:1;s:14:\"update_plugins\";b:1;s:14:\"delete_plugins\";b:1;s:15:\"install_plugins\";b:1;s:13:\"update_themes\";b:1;s:14:\"install_themes\";b:1;s:11:\"update_core\";b:1;s:10:\"list_users\";b:1;s:12:\"remove_users\";b:1;s:13:\"promote_users\";b:1;s:18:\"edit_theme_options\";b:1;s:13:\"delete_themes\";b:1;s:6:\"export\";b:1;}}s:6:\"editor\";a:2:{s:4:\"name\";s:6:\"Editor\";s:12:\"capabilities\";a:34:{s:17:\"moderate_comments\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:12:\"upload_files\";b:1;s:15:\"unfiltered_html\";b:1;s:10:\"edit_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:10:\"edit_pages\";b:1;s:4:\"read\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:17:\"edit_others_pages\";b:1;s:20:\"edit_published_pages\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_pages\";b:1;s:19:\"delete_others_pages\";b:1;s:22:\"delete_published_pages\";b:1;s:12:\"delete_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:20:\"delete_private_posts\";b:1;s:18:\"edit_private_posts\";b:1;s:18:\"read_private_posts\";b:1;s:20:\"delete_private_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"read_private_pages\";b:1;}}s:6:\"author\";a:2:{s:4:\"name\";s:6:\"Author\";s:12:\"capabilities\";a:10:{s:12:\"upload_files\";b:1;s:10:\"edit_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:4:\"read\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:12:\"delete_posts\";b:1;s:22:\"delete_published_posts\";b:1;}}s:11:\"contributor\";a:2:{s:4:\"name\";s:11:\"Contributor\";s:12:\"capabilities\";a:5:{s:10:\"edit_posts\";b:1;s:4:\"read\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:12:\"delete_posts\";b:1;}}s:10:\"subscriber\";a:2:{s:4:\"name\";s:10:\"Subscriber\";s:12:\"capabilities\";a:2:{s:4:\"read\";b:1;s:7:\"level_0\";b:1;}}}', 'on'),
(104, 'fresh_site', '0', 'off'),
(105, 'user_count', '1', 'off'),
(106, 'widget_block', 'a:6:{i:2;a:1:{s:7:\"content\";s:19:\"<!-- wp:search /-->\";}i:3;a:1:{s:7:\"content\";s:154:\"<!-- wp:group --><div class=\"wp-block-group\"><!-- wp:heading --><h2>Recent Posts</h2><!-- /wp:heading --><!-- wp:latest-posts /--></div><!-- /wp:group -->\";}i:4;a:1:{s:7:\"content\";s:227:\"<!-- wp:group --><div class=\"wp-block-group\"><!-- wp:heading --><h2>Recent Comments</h2><!-- /wp:heading --><!-- wp:latest-comments {\"displayAvatar\":false,\"displayDate\":false,\"displayExcerpt\":false} /--></div><!-- /wp:group -->\";}i:5;a:1:{s:7:\"content\";s:146:\"<!-- wp:group --><div class=\"wp-block-group\"><!-- wp:heading --><h2>Archives</h2><!-- /wp:heading --><!-- wp:archives /--></div><!-- /wp:group -->\";}i:6;a:1:{s:7:\"content\";s:150:\"<!-- wp:group --><div class=\"wp-block-group\"><!-- wp:heading --><h2>Categories</h2><!-- /wp:heading --><!-- wp:categories /--></div><!-- /wp:group -->\";}s:12:\"_multiwidget\";i:1;}', 'auto'),
(107, 'sidebars_widgets', 'a:4:{s:19:\"wp_inactive_widgets\";a:0:{}s:9:\"sidebar-1\";a:3:{i:0;s:7:\"block-2\";i:1;s:7:\"block-3\";i:2;s:7:\"block-4\";}s:9:\"sidebar-2\";a:2:{i:0;s:7:\"block-5\";i:1;s:7:\"block-6\";}s:13:\"array_version\";i:3;}', 'auto'),
(108, 'widget_pages', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(109, 'widget_calendar', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(110, 'widget_archives', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(111, 'widget_media_audio', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(112, 'widget_media_image', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(113, 'widget_media_gallery', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(114, 'widget_media_video', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(115, 'widget_meta', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(116, 'widget_search', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(117, 'widget_recent-posts', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(118, 'widget_recent-comments', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(119, 'widget_tag_cloud', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(120, 'widget_nav_menu', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(121, 'widget_custom_html', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'auto'),
(122, '_transient_wp_core_block_css_files', 'a:2:{s:7:\"version\";s:5:\"7.1.2\";s:5:\"files\";a:624:{i:0;s:31:\"accordion-heading/style-rtl.css\";i:1;s:35:\"accordion-heading/style-rtl.min.css\";i:2;s:27:\"accordion-heading/style.css\";i:3;s:31:\"accordion-heading/style.min.css\";i:4;s:28:\"accordion-item/style-rtl.css\";i:5;s:32:\"accordion-item/style-rtl.min.css\";i:6;s:24:\"accordion-item/style.css\";i:7;s:28:\"accordion-item/style.min.css\";i:8;s:29:\"accordion-panel/style-rtl.css\";i:9;s:33:\"accordion-panel/style-rtl.min.css\";i:10;s:25:\"accordion-panel/style.css\";i:11;s:29:\"accordion-panel/style.min.css\";i:12;s:23:\"accordion/style-rtl.css\";i:13;s:27:\"accordion/style-rtl.min.css\";i:14;s:19:\"accordion/style.css\";i:15;s:23:\"accordion/style.min.css\";i:16;s:22:\"archives/style-rtl.css\";i:17;s:26:\"archives/style-rtl.min.css\";i:18;s:18:\"archives/style.css\";i:19;s:22:\"archives/style.min.css\";i:20;s:20:\"audio/editor-rtl.css\";i:21;s:24:\"audio/editor-rtl.min.css\";i:22;s:16:\"audio/editor.css\";i:23;s:20:\"audio/editor.min.css\";i:24;s:19:\"audio/style-rtl.css\";i:25;s:23:\"audio/style-rtl.min.css\";i:26;s:15:\"audio/style.css\";i:27;s:19:\"audio/style.min.css\";i:28;s:19:\"audio/theme-rtl.css\";i:29;s:23:\"audio/theme-rtl.min.css\";i:30;s:15:\"audio/theme.css\";i:31;s:19:\"audio/theme.min.css\";i:32;s:21:\"avatar/editor-rtl.css\";i:33;s:25:\"avatar/editor-rtl.min.css\";i:34;s:17:\"avatar/editor.css\";i:35;s:21:\"avatar/editor.min.css\";i:36;s:20:\"avatar/style-rtl.css\";i:37;s:24:\"avatar/style-rtl.min.css\";i:38;s:16:\"avatar/style.css\";i:39;s:20:\"avatar/style.min.css\";i:40;s:25:\"breadcrumbs/style-rtl.css\";i:41;s:29:\"breadcrumbs/style-rtl.min.css\";i:42;s:21:\"breadcrumbs/style.css\";i:43;s:25:\"breadcrumbs/style.min.css\";i:44;s:21:\"button/editor-rtl.css\";i:45;s:25:\"button/editor-rtl.min.css\";i:46;s:17:\"button/editor.css\";i:47;s:21:\"button/editor.min.css\";i:48;s:20:\"button/style-rtl.css\";i:49;s:24:\"button/style-rtl.min.css\";i:50;s:16:\"button/style.css\";i:51;s:20:\"button/style.min.css\";i:52;s:22:\"buttons/editor-rtl.css\";i:53;s:26:\"buttons/editor-rtl.min.css\";i:54;s:18:\"buttons/editor.css\";i:55;s:22:\"buttons/editor.min.css\";i:56;s:21:\"buttons/style-rtl.css\";i:57;s:25:\"buttons/style-rtl.min.css\";i:58;s:17:\"buttons/style.css\";i:59;s:21:\"buttons/style.min.css\";i:60;s:22:\"calendar/style-rtl.css\";i:61;s:26:\"calendar/style-rtl.min.css\";i:62;s:18:\"calendar/style.css\";i:63;s:22:\"calendar/style.min.css\";i:64;s:25:\"categories/editor-rtl.css\";i:65;s:29:\"categories/editor-rtl.min.css\";i:66;s:21:\"categories/editor.css\";i:67;s:25:\"categories/editor.min.css\";i:68;s:24:\"categories/style-rtl.css\";i:69;s:28:\"categories/style-rtl.min.css\";i:70;s:20:\"categories/style.css\";i:71;s:24:\"categories/style.min.css\";i:72;s:19:\"code/editor-rtl.css\";i:73;s:23:\"code/editor-rtl.min.css\";i:74;s:15:\"code/editor.css\";i:75;s:19:\"code/editor.min.css\";i:76;s:18:\"code/style-rtl.css\";i:77;s:22:\"code/style-rtl.min.css\";i:78;s:14:\"code/style.css\";i:79;s:18:\"code/style.min.css\";i:80;s:18:\"code/theme-rtl.css\";i:81;s:22:\"code/theme-rtl.min.css\";i:82;s:14:\"code/theme.css\";i:83;s:18:\"code/theme.min.css\";i:84;s:22:\"columns/editor-rtl.css\";i:85;s:26:\"columns/editor-rtl.min.css\";i:86;s:18:\"columns/editor.css\";i:87;s:22:\"columns/editor.min.css\";i:88;s:21:\"columns/style-rtl.css\";i:89;s:25:\"columns/style-rtl.min.css\";i:90;s:17:\"columns/style.css\";i:91;s:21:\"columns/style.min.css\";i:92;s:33:\"comment-author-name/style-rtl.css\";i:93;s:37:\"comment-author-name/style-rtl.min.css\";i:94;s:29:\"comment-author-name/style.css\";i:95;s:33:\"comment-author-name/style.min.css\";i:96;s:29:\"comment-content/style-rtl.css\";i:97;s:33:\"comment-content/style-rtl.min.css\";i:98;s:25:\"comment-content/style.css\";i:99;s:29:\"comment-content/style.min.css\";i:100;s:26:\"comment-date/style-rtl.css\";i:101;s:30:\"comment-date/style-rtl.min.css\";i:102;s:22:\"comment-date/style.css\";i:103;s:26:\"comment-date/style.min.css\";i:104;s:31:\"comment-edit-link/style-rtl.css\";i:105;s:35:\"comment-edit-link/style-rtl.min.css\";i:106;s:27:\"comment-edit-link/style.css\";i:107;s:31:\"comment-edit-link/style.min.css\";i:108;s:32:\"comment-reply-link/style-rtl.css\";i:109;s:36:\"comment-reply-link/style-rtl.min.css\";i:110;s:28:\"comment-reply-link/style.css\";i:111;s:32:\"comment-reply-link/style.min.css\";i:112;s:30:\"comment-template/style-rtl.css\";i:113;s:34:\"comment-template/style-rtl.min.css\";i:114;s:26:\"comment-template/style.css\";i:115;s:30:\"comment-template/style.min.css\";i:116;s:42:\"comments-pagination-numbers/editor-rtl.css\";i:117;s:46:\"comments-pagination-numbers/editor-rtl.min.css\";i:118;s:38:\"comments-pagination-numbers/editor.css\";i:119;s:42:\"comments-pagination-numbers/editor.min.css\";i:120;s:34:\"comments-pagination/editor-rtl.css\";i:121;s:38:\"comments-pagination/editor-rtl.min.css\";i:122;s:30:\"comments-pagination/editor.css\";i:123;s:34:\"comments-pagination/editor.min.css\";i:124;s:33:\"comments-pagination/style-rtl.css\";i:125;s:37:\"comments-pagination/style-rtl.min.css\";i:126;s:29:\"comments-pagination/style.css\";i:127;s:33:\"comments-pagination/style.min.css\";i:128;s:29:\"comments-title/editor-rtl.css\";i:129;s:33:\"comments-title/editor-rtl.min.css\";i:130;s:25:\"comments-title/editor.css\";i:131;s:29:\"comments-title/editor.min.css\";i:132;s:23:\"comments/editor-rtl.css\";i:133;s:27:\"comments/editor-rtl.min.css\";i:134;s:19:\"comments/editor.css\";i:135;s:23:\"comments/editor.min.css\";i:136;s:22:\"comments/style-rtl.css\";i:137;s:26:\"comments/style-rtl.min.css\";i:138;s:18:\"comments/style.css\";i:139;s:22:\"comments/style.min.css\";i:140;s:20:\"cover/editor-rtl.css\";i:141;s:24:\"cover/editor-rtl.min.css\";i:142;s:16:\"cover/editor.css\";i:143;s:20:\"cover/editor.min.css\";i:144;s:19:\"cover/style-rtl.css\";i:145;s:23:\"cover/style-rtl.min.css\";i:146;s:15:\"cover/style.css\";i:147;s:19:\"cover/style.min.css\";i:148;s:22:\"details/editor-rtl.css\";i:149;s:26:\"details/editor-rtl.min.css\";i:150;s:18:\"details/editor.css\";i:151;s:22:\"details/editor.min.css\";i:152;s:21:\"details/style-rtl.css\";i:153;s:25:\"details/style-rtl.min.css\";i:154;s:17:\"details/style.css\";i:155;s:21:\"details/style.min.css\";i:156;s:20:\"embed/editor-rtl.css\";i:157;s:24:\"embed/editor-rtl.min.css\";i:158;s:16:\"embed/editor.css\";i:159;s:20:\"embed/editor.min.css\";i:160;s:19:\"embed/style-rtl.css\";i:161;s:23:\"embed/style-rtl.min.css\";i:162;s:15:\"embed/style.css\";i:163;s:19:\"embed/style.min.css\";i:164;s:19:\"embed/theme-rtl.css\";i:165;s:23:\"embed/theme-rtl.min.css\";i:166;s:15:\"embed/theme.css\";i:167;s:19:\"embed/theme.min.css\";i:168;s:19:\"file/editor-rtl.css\";i:169;s:23:\"file/editor-rtl.min.css\";i:170;s:15:\"file/editor.css\";i:171;s:19:\"file/editor.min.css\";i:172;s:18:\"file/style-rtl.css\";i:173;s:22:\"file/style-rtl.min.css\";i:174;s:14:\"file/style.css\";i:175;s:18:\"file/style.min.css\";i:176;s:23:\"footnotes/style-rtl.css\";i:177;s:27:\"footnotes/style-rtl.min.css\";i:178;s:19:\"footnotes/style.css\";i:179;s:23:\"footnotes/style.min.css\";i:180;s:23:\"freeform/editor-rtl.css\";i:181;s:27:\"freeform/editor-rtl.min.css\";i:182;s:19:\"freeform/editor.css\";i:183;s:23:\"freeform/editor.min.css\";i:184;s:22:\"gallery/editor-rtl.css\";i:185;s:26:\"gallery/editor-rtl.min.css\";i:186;s:18:\"gallery/editor.css\";i:187;s:22:\"gallery/editor.min.css\";i:188;s:21:\"gallery/style-rtl.css\";i:189;s:25:\"gallery/style-rtl.min.css\";i:190;s:17:\"gallery/style.css\";i:191;s:21:\"gallery/style.min.css\";i:192;s:21:\"gallery/theme-rtl.css\";i:193;s:25:\"gallery/theme-rtl.min.css\";i:194;s:17:\"gallery/theme.css\";i:195;s:21:\"gallery/theme.min.css\";i:196;s:20:\"group/editor-rtl.css\";i:197;s:24:\"group/editor-rtl.min.css\";i:198;s:16:\"group/editor.css\";i:199;s:20:\"group/editor.min.css\";i:200;s:19:\"group/style-rtl.css\";i:201;s:23:\"group/style-rtl.min.css\";i:202;s:15:\"group/style.css\";i:203;s:19:\"group/style.min.css\";i:204;s:19:\"group/theme-rtl.css\";i:205;s:23:\"group/theme-rtl.min.css\";i:206;s:15:\"group/theme.css\";i:207;s:19:\"group/theme.min.css\";i:208;s:21:\"heading/style-rtl.css\";i:209;s:25:\"heading/style-rtl.min.css\";i:210;s:17:\"heading/style.css\";i:211;s:21:\"heading/style.min.css\";i:212;s:19:\"html/editor-rtl.css\";i:213;s:23:\"html/editor-rtl.min.css\";i:214;s:15:\"html/editor.css\";i:215;s:19:\"html/editor.min.css\";i:216;s:19:\"icon/editor-rtl.css\";i:217;s:23:\"icon/editor-rtl.min.css\";i:218;s:15:\"icon/editor.css\";i:219;s:19:\"icon/editor.min.css\";i:220;s:18:\"icon/style-rtl.css\";i:221;s:22:\"icon/style-rtl.min.css\";i:222;s:14:\"icon/style.css\";i:223;s:18:\"icon/style.min.css\";i:224;s:20:\"image/editor-rtl.css\";i:225;s:24:\"image/editor-rtl.min.css\";i:226;s:16:\"image/editor.css\";i:227;s:20:\"image/editor.min.css\";i:228;s:19:\"image/style-rtl.css\";i:229;s:23:\"image/style-rtl.min.css\";i:230;s:15:\"image/style.css\";i:231;s:19:\"image/style.min.css\";i:232;s:19:\"image/theme-rtl.css\";i:233;s:23:\"image/theme-rtl.min.css\";i:234;s:15:\"image/theme.css\";i:235;s:19:\"image/theme.min.css\";i:236;s:29:\"latest-comments/style-rtl.css\";i:237;s:33:\"latest-comments/style-rtl.min.css\";i:238;s:25:\"latest-comments/style.css\";i:239;s:29:\"latest-comments/style.min.css\";i:240;s:27:\"latest-posts/editor-rtl.css\";i:241;s:31:\"latest-posts/editor-rtl.min.css\";i:242;s:23:\"latest-posts/editor.css\";i:243;s:27:\"latest-posts/editor.min.css\";i:244;s:26:\"latest-posts/style-rtl.css\";i:245;s:30:\"latest-posts/style-rtl.min.css\";i:246;s:22:\"latest-posts/style.css\";i:247;s:26:\"latest-posts/style.min.css\";i:248;s:18:\"list/style-rtl.css\";i:249;s:22:\"list/style-rtl.min.css\";i:250;s:14:\"list/style.css\";i:251;s:18:\"list/style.min.css\";i:252;s:22:\"loginout/style-rtl.css\";i:253;s:26:\"loginout/style-rtl.min.css\";i:254;s:18:\"loginout/style.css\";i:255;s:22:\"loginout/style.min.css\";i:256;s:19:\"math/editor-rtl.css\";i:257;s:23:\"math/editor-rtl.min.css\";i:258;s:15:\"math/editor.css\";i:259;s:19:\"math/editor.min.css\";i:260;s:18:\"math/style-rtl.css\";i:261;s:22:\"math/style-rtl.min.css\";i:262;s:14:\"math/style.css\";i:263;s:18:\"math/style.min.css\";i:264;s:25:\"media-text/editor-rtl.css\";i:265;s:29:\"media-text/editor-rtl.min.css\";i:266;s:21:\"media-text/editor.css\";i:267;s:25:\"media-text/editor.min.css\";i:268;s:24:\"media-text/style-rtl.css\";i:269;s:28:\"media-text/style-rtl.min.css\";i:270;s:20:\"media-text/style.css\";i:271;s:24:\"media-text/style.min.css\";i:272;s:19:\"more/editor-rtl.css\";i:273;s:23:\"more/editor-rtl.min.css\";i:274;s:15:\"more/editor.css\";i:275;s:19:\"more/editor.min.css\";i:276;s:30:\"navigation-link/editor-rtl.css\";i:277;s:34:\"navigation-link/editor-rtl.min.css\";i:278;s:26:\"navigation-link/editor.css\";i:279;s:30:\"navigation-link/editor.min.css\";i:280;s:29:\"navigation-link/style-rtl.css\";i:281;s:33:\"navigation-link/style-rtl.min.css\";i:282;s:25:\"navigation-link/style.css\";i:283;s:29:\"navigation-link/style.min.css\";i:284;s:38:\"navigation-overlay-close/style-rtl.css\";i:285;s:42:\"navigation-overlay-close/style-rtl.min.css\";i:286;s:34:\"navigation-overlay-close/style.css\";i:287;s:38:\"navigation-overlay-close/style.min.css\";i:288;s:33:\"navigation-submenu/editor-rtl.css\";i:289;s:37:\"navigation-submenu/editor-rtl.min.css\";i:290;s:29:\"navigation-submenu/editor.css\";i:291;s:33:\"navigation-submenu/editor.min.css\";i:292;s:25:\"navigation/editor-rtl.css\";i:293;s:29:\"navigation/editor-rtl.min.css\";i:294;s:21:\"navigation/editor.css\";i:295;s:25:\"navigation/editor.min.css\";i:296;s:24:\"navigation/style-rtl.css\";i:297;s:28:\"navigation/style-rtl.min.css\";i:298;s:20:\"navigation/style.css\";i:299;s:24:\"navigation/style.min.css\";i:300;s:23:\"nextpage/editor-rtl.css\";i:301;s:27:\"nextpage/editor-rtl.min.css\";i:302;s:19:\"nextpage/editor.css\";i:303;s:23:\"nextpage/editor.min.css\";i:304;s:24:\"page-list/editor-rtl.css\";i:305;s:28:\"page-list/editor-rtl.min.css\";i:306;s:20:\"page-list/editor.css\";i:307;s:24:\"page-list/editor.min.css\";i:308;s:23:\"page-list/style-rtl.css\";i:309;s:27:\"page-list/style-rtl.min.css\";i:310;s:19:\"page-list/style.css\";i:311;s:23:\"page-list/style.min.css\";i:312;s:24:\"paragraph/editor-rtl.css\";i:313;s:28:\"paragraph/editor-rtl.min.css\";i:314;s:20:\"paragraph/editor.css\";i:315;s:24:\"paragraph/editor.min.css\";i:316;s:23:\"paragraph/style-rtl.css\";i:317;s:27:\"paragraph/style-rtl.min.css\";i:318;s:19:\"paragraph/style.css\";i:319;s:23:\"paragraph/style.min.css\";i:320;s:28:\"playlist-track/style-rtl.css\";i:321;s:32:\"playlist-track/style-rtl.min.css\";i:322;s:24:\"playlist-track/style.css\";i:323;s:28:\"playlist-track/style.min.css\";i:324;s:23:\"playlist/editor-rtl.css\";i:325;s:27:\"playlist/editor-rtl.min.css\";i:326;s:19:\"playlist/editor.css\";i:327;s:23:\"playlist/editor.min.css\";i:328;s:22:\"playlist/style-rtl.css\";i:329;s:26:\"playlist/style-rtl.min.css\";i:330;s:18:\"playlist/style.css\";i:331;s:22:\"playlist/style.min.css\";i:332;s:35:\"post-author-biography/style-rtl.css\";i:333;s:39:\"post-author-biography/style-rtl.min.css\";i:334;s:31:\"post-author-biography/style.css\";i:335;s:35:\"post-author-biography/style.min.css\";i:336;s:30:\"post-author-name/style-rtl.css\";i:337;s:34:\"post-author-name/style-rtl.min.css\";i:338;s:26:\"post-author-name/style.css\";i:339;s:30:\"post-author-name/style.min.css\";i:340;s:26:\"post-author/editor-rtl.css\";i:341;s:30:\"post-author/editor-rtl.min.css\";i:342;s:22:\"post-author/editor.css\";i:343;s:26:\"post-author/editor.min.css\";i:344;s:25:\"post-author/style-rtl.css\";i:345;s:29:\"post-author/style-rtl.min.css\";i:346;s:21:\"post-author/style.css\";i:347;s:25:\"post-author/style.min.css\";i:348;s:33:\"post-comments-count/style-rtl.css\";i:349;s:37:\"post-comments-count/style-rtl.min.css\";i:350;s:29:\"post-comments-count/style.css\";i:351;s:33:\"post-comments-count/style.min.css\";i:352;s:33:\"post-comments-form/editor-rtl.css\";i:353;s:37:\"post-comments-form/editor-rtl.min.css\";i:354;s:29:\"post-comments-form/editor.css\";i:355;s:33:\"post-comments-form/editor.min.css\";i:356;s:32:\"post-comments-form/style-rtl.css\";i:357;s:36:\"post-comments-form/style-rtl.min.css\";i:358;s:28:\"post-comments-form/style.css\";i:359;s:32:\"post-comments-form/style.min.css\";i:360;s:32:\"post-comments-link/style-rtl.css\";i:361;s:36:\"post-comments-link/style-rtl.min.css\";i:362;s:28:\"post-comments-link/style.css\";i:363;s:32:\"post-comments-link/style.min.css\";i:364;s:26:\"post-content/style-rtl.css\";i:365;s:30:\"post-content/style-rtl.min.css\";i:366;s:22:\"post-content/style.css\";i:367;s:26:\"post-content/style.min.css\";i:368;s:23:\"post-date/style-rtl.css\";i:369;s:27:\"post-date/style-rtl.min.css\";i:370;s:19:\"post-date/style.css\";i:371;s:23:\"post-date/style.min.css\";i:372;s:27:\"post-excerpt/editor-rtl.css\";i:373;s:31:\"post-excerpt/editor-rtl.min.css\";i:374;s:23:\"post-excerpt/editor.css\";i:375;s:27:\"post-excerpt/editor.min.css\";i:376;s:26:\"post-excerpt/style-rtl.css\";i:377;s:30:\"post-excerpt/style-rtl.min.css\";i:378;s:22:\"post-excerpt/style.css\";i:379;s:26:\"post-excerpt/style.min.css\";i:380;s:34:\"post-featured-image/editor-rtl.css\";i:381;s:38:\"post-featured-image/editor-rtl.min.css\";i:382;s:30:\"post-featured-image/editor.css\";i:383;s:34:\"post-featured-image/editor.min.css\";i:384;s:33:\"post-featured-image/style-rtl.css\";i:385;s:37:\"post-featured-image/style-rtl.min.css\";i:386;s:29:\"post-featured-image/style.css\";i:387;s:33:\"post-featured-image/style.min.css\";i:388;s:34:\"post-navigation-link/style-rtl.css\";i:389;s:38:\"post-navigation-link/style-rtl.min.css\";i:390;s:30:\"post-navigation-link/style.css\";i:391;s:34:\"post-navigation-link/style.min.css\";i:392;s:27:\"post-template/style-rtl.css\";i:393;s:31:\"post-template/style-rtl.min.css\";i:394;s:23:\"post-template/style.css\";i:395;s:27:\"post-template/style.min.css\";i:396;s:24:\"post-terms/style-rtl.css\";i:397;s:28:\"post-terms/style-rtl.min.css\";i:398;s:20:\"post-terms/style.css\";i:399;s:24:\"post-terms/style.min.css\";i:400;s:31:\"post-time-to-read/style-rtl.css\";i:401;s:35:\"post-time-to-read/style-rtl.min.css\";i:402;s:27:\"post-time-to-read/style.css\";i:403;s:31:\"post-time-to-read/style.min.css\";i:404;s:24:\"post-title/style-rtl.css\";i:405;s:28:\"post-title/style-rtl.min.css\";i:406;s:20:\"post-title/style.css\";i:407;s:24:\"post-title/style.min.css\";i:408;s:26:\"preformatted/style-rtl.css\";i:409;s:30:\"preformatted/style-rtl.min.css\";i:410;s:22:\"preformatted/style.css\";i:411;s:26:\"preformatted/style.min.css\";i:412;s:24:\"pullquote/editor-rtl.css\";i:413;s:28:\"pullquote/editor-rtl.min.css\";i:414;s:20:\"pullquote/editor.css\";i:415;s:24:\"pullquote/editor.min.css\";i:416;s:23:\"pullquote/style-rtl.css\";i:417;s:27:\"pullquote/style-rtl.min.css\";i:418;s:19:\"pullquote/style.css\";i:419;s:23:\"pullquote/style.min.css\";i:420;s:23:\"pullquote/theme-rtl.css\";i:421;s:27:\"pullquote/theme-rtl.min.css\";i:422;s:19:\"pullquote/theme.css\";i:423;s:23:\"pullquote/theme.min.css\";i:424;s:39:\"query-pagination-numbers/editor-rtl.css\";i:425;s:43:\"query-pagination-numbers/editor-rtl.min.css\";i:426;s:35:\"query-pagination-numbers/editor.css\";i:427;s:39:\"query-pagination-numbers/editor.min.css\";i:428;s:31:\"query-pagination/editor-rtl.css\";i:429;s:35:\"query-pagination/editor-rtl.min.css\";i:430;s:27:\"query-pagination/editor.css\";i:431;s:31:\"query-pagination/editor.min.css\";i:432;s:30:\"query-pagination/style-rtl.css\";i:433;s:34:\"query-pagination/style-rtl.min.css\";i:434;s:26:\"query-pagination/style.css\";i:435;s:30:\"query-pagination/style.min.css\";i:436;s:25:\"query-title/style-rtl.css\";i:437;s:29:\"query-title/style-rtl.min.css\";i:438;s:21:\"query-title/style.css\";i:439;s:25:\"query-title/style.min.css\";i:440;s:25:\"query-total/style-rtl.css\";i:441;s:29:\"query-total/style-rtl.min.css\";i:442;s:21:\"query-total/style.css\";i:443;s:25:\"query-total/style.min.css\";i:444;s:20:\"query/editor-rtl.css\";i:445;s:24:\"query/editor-rtl.min.css\";i:446;s:16:\"query/editor.css\";i:447;s:20:\"query/editor.min.css\";i:448;s:19:\"quote/style-rtl.css\";i:449;s:23:\"quote/style-rtl.min.css\";i:450;s:15:\"quote/style.css\";i:451;s:19:\"quote/style.min.css\";i:452;s:19:\"quote/theme-rtl.css\";i:453;s:23:\"quote/theme-rtl.min.css\";i:454;s:15:\"quote/theme.css\";i:455;s:19:\"quote/theme.min.css\";i:456;s:23:\"read-more/style-rtl.css\";i:457;s:27:\"read-more/style-rtl.min.css\";i:458;s:19:\"read-more/style.css\";i:459;s:23:\"read-more/style.min.css\";i:460;s:18:\"rss/editor-rtl.css\";i:461;s:22:\"rss/editor-rtl.min.css\";i:462;s:14:\"rss/editor.css\";i:463;s:18:\"rss/editor.min.css\";i:464;s:17:\"rss/style-rtl.css\";i:465;s:21:\"rss/style-rtl.min.css\";i:466;s:13:\"rss/style.css\";i:467;s:17:\"rss/style.min.css\";i:468;s:21:\"search/editor-rtl.css\";i:469;s:25:\"search/editor-rtl.min.css\";i:470;s:17:\"search/editor.css\";i:471;s:21:\"search/editor.min.css\";i:472;s:20:\"search/style-rtl.css\";i:473;s:24:\"search/style-rtl.min.css\";i:474;s:16:\"search/style.css\";i:475;s:20:\"search/style.min.css\";i:476;s:20:\"search/theme-rtl.css\";i:477;s:24:\"search/theme-rtl.min.css\";i:478;s:16:\"search/theme.css\";i:479;s:20:\"search/theme.min.css\";i:480;s:24:\"separator/editor-rtl.css\";i:481;s:28:\"separator/editor-rtl.min.css\";i:482;s:20:\"separator/editor.css\";i:483;s:24:\"separator/editor.min.css\";i:484;s:23:\"separator/style-rtl.css\";i:485;s:27:\"separator/style-rtl.min.css\";i:486;s:19:\"separator/style.css\";i:487;s:23:\"separator/style.min.css\";i:488;s:23:\"separator/theme-rtl.css\";i:489;s:27:\"separator/theme-rtl.min.css\";i:490;s:19:\"separator/theme.css\";i:491;s:23:\"separator/theme.min.css\";i:492;s:24:\"shortcode/editor-rtl.css\";i:493;s:28:\"shortcode/editor-rtl.min.css\";i:494;s:20:\"shortcode/editor.css\";i:495;s:24:\"shortcode/editor.min.css\";i:496;s:24:\"site-logo/editor-rtl.css\";i:497;s:28:\"site-logo/editor-rtl.min.css\";i:498;s:20:\"site-logo/editor.css\";i:499;s:24:\"site-logo/editor.min.css\";i:500;s:23:\"site-logo/style-rtl.css\";i:501;s:27:\"site-logo/style-rtl.min.css\";i:502;s:19:\"site-logo/style.css\";i:503;s:23:\"site-logo/style.min.css\";i:504;s:27:\"site-tagline/editor-rtl.css\";i:505;s:31:\"site-tagline/editor-rtl.min.css\";i:506;s:23:\"site-tagline/editor.css\";i:507;s:27:\"site-tagline/editor.min.css\";i:508;s:26:\"site-tagline/style-rtl.css\";i:509;s:30:\"site-tagline/style-rtl.min.css\";i:510;s:22:\"site-tagline/style.css\";i:511;s:26:\"site-tagline/style.min.css\";i:512;s:25:\"site-title/editor-rtl.css\";i:513;s:29:\"site-title/editor-rtl.min.css\";i:514;s:21:\"site-title/editor.css\";i:515;s:25:\"site-title/editor.min.css\";i:516;s:24:\"site-title/style-rtl.css\";i:517;s:28:\"site-title/style-rtl.min.css\";i:518;s:20:\"site-title/style.css\";i:519;s:24:\"site-title/style.min.css\";i:520;s:26:\"social-link/editor-rtl.css\";i:521;s:30:\"social-link/editor-rtl.min.css\";i:522;s:22:\"social-link/editor.css\";i:523;s:26:\"social-link/editor.min.css\";i:524;s:27:\"social-links/editor-rtl.css\";i:525;s:31:\"social-links/editor-rtl.min.css\";i:526;s:23:\"social-links/editor.css\";i:527;s:27:\"social-links/editor.min.css\";i:528;s:26:\"social-links/style-rtl.css\";i:529;s:30:\"social-links/style-rtl.min.css\";i:530;s:22:\"social-links/style.css\";i:531;s:26:\"social-links/style.min.css\";i:532;s:21:\"spacer/editor-rtl.css\";i:533;s:25:\"spacer/editor-rtl.min.css\";i:534;s:17:\"spacer/editor.css\";i:535;s:21:\"spacer/editor.min.css\";i:536;s:20:\"spacer/style-rtl.css\";i:537;s:24:\"spacer/style-rtl.min.css\";i:538;s:16:\"spacer/style.css\";i:539;s:20:\"spacer/style.min.css\";i:540;s:23:\"tab-list/editor-rtl.css\";i:541;s:27:\"tab-list/editor-rtl.min.css\";i:542;s:19:\"tab-list/editor.css\";i:543;s:23:\"tab-list/editor.min.css\";i:544;s:22:\"tab-list/style-rtl.css\";i:545;s:26:\"tab-list/style-rtl.min.css\";i:546;s:18:\"tab-list/style.css\";i:547;s:22:\"tab-list/style.min.css\";i:548;s:23:\"tab-panel/style-rtl.css\";i:549;s:27:\"tab-panel/style-rtl.min.css\";i:550;s:19:\"tab-panel/style.css\";i:551;s:23:\"tab-panel/style.min.css\";i:552;s:20:\"table/editor-rtl.css\";i:553;s:24:\"table/editor-rtl.min.css\";i:554;s:16:\"table/editor.css\";i:555;s:20:\"table/editor.min.css\";i:556;s:19:\"table/style-rtl.css\";i:557;s:23:\"table/style-rtl.min.css\";i:558;s:15:\"table/style.css\";i:559;s:19:\"table/style.min.css\";i:560;s:19:\"table/theme-rtl.css\";i:561;s:23:\"table/theme-rtl.min.css\";i:562;s:15:\"table/theme.css\";i:563;s:19:\"table/theme.min.css\";i:564;s:18:\"tabs/style-rtl.css\";i:565;s:22:\"tabs/style-rtl.min.css\";i:566;s:14:\"tabs/style.css\";i:567;s:18:\"tabs/style.min.css\";i:568;s:23:\"tag-cloud/style-rtl.css\";i:569;s:27:\"tag-cloud/style-rtl.min.css\";i:570;s:19:\"tag-cloud/style.css\";i:571;s:23:\"tag-cloud/style.min.css\";i:572;s:28:\"template-part/editor-rtl.css\";i:573;s:32:\"template-part/editor-rtl.min.css\";i:574;s:24:\"template-part/editor.css\";i:575;s:28:\"template-part/editor.min.css\";i:576;s:27:\"template-part/theme-rtl.css\";i:577;s:31:\"template-part/theme-rtl.min.css\";i:578;s:23:\"template-part/theme.css\";i:579;s:27:\"template-part/theme.min.css\";i:580;s:24:\"term-count/style-rtl.css\";i:581;s:28:\"term-count/style-rtl.min.css\";i:582;s:20:\"term-count/style.css\";i:583;s:24:\"term-count/style.min.css\";i:584;s:30:\"term-description/style-rtl.css\";i:585;s:34:\"term-description/style-rtl.min.css\";i:586;s:26:\"term-description/style.css\";i:587;s:30:\"term-description/style.min.css\";i:588;s:23:\"term-name/style-rtl.css\";i:589;s:27:\"term-name/style-rtl.min.css\";i:590;s:19:\"term-name/style.css\";i:591;s:23:\"term-name/style.min.css\";i:592;s:28:\"term-template/editor-rtl.css\";i:593;s:32:\"term-template/editor-rtl.min.css\";i:594;s:24:\"term-template/editor.css\";i:595;s:28:\"term-template/editor.min.css\";i:596;s:27:\"term-template/style-rtl.css\";i:597;s:31:\"term-template/style-rtl.min.css\";i:598;s:23:\"term-template/style.css\";i:599;s:27:\"term-template/style.min.css\";i:600;s:27:\"text-columns/editor-rtl.css\";i:601;s:31:\"text-columns/editor-rtl.min.css\";i:602;s:23:\"text-columns/editor.css\";i:603;s:27:\"text-columns/editor.min.css\";i:604;s:26:\"text-columns/style-rtl.css\";i:605;s:30:\"text-columns/style-rtl.min.css\";i:606;s:22:\"text-columns/style.css\";i:607;s:26:\"text-columns/style.min.css\";i:608;s:19:\"verse/style-rtl.css\";i:609;s:23:\"verse/style-rtl.min.css\";i:610;s:15:\"verse/style.css\";i:611;s:19:\"verse/style.min.css\";i:612;s:20:\"video/editor-rtl.css\";i:613;s:24:\"video/editor-rtl.min.css\";i:614;s:16:\"video/editor.css\";i:615;s:20:\"video/editor.min.css\";i:616;s:19:\"video/style-rtl.css\";i:617;s:23:\"video/style-rtl.min.css\";i:618;s:15:\"video/style.css\";i:619;s:19:\"video/style.min.css\";i:620;s:19:\"video/theme-rtl.css\";i:621;s:23:\"video/theme-rtl.min.css\";i:622;s:15:\"video/theme.css\";i:623;s:19:\"video/theme.min.css\";}}', 'on'),
(125, 'theme_mods_twentytwentyfive', 'a:1:{s:18:\"custom_css_post_id\";i:-1;}', 'auto'),
(126, '_transient_wp_styles_for_blocks', 'a:2:{s:4:\"hash\";s:32:\"94289267f68ccac1fefa45915ab93347\";s:6:\"blocks\";a:9:{s:32:\"832dc2d864d79097d8b8b493ad93453b\";s:0:\"\";s:32:\"45d3e0c4afcbd8cf25cb1ba51abfb3d7\";s:46:\":root :where(.wp-block-icon svg){width: 24px;}\";s:32:\"feca6e996f694be2d29599793228e0d7\";s:0:\"\";s:32:\"5eef131663eddaf830554df656fc2968\";s:324:\":where(.wp-block-gallery.is-layout-flex){gap: var( --wp--style--gallery-gap-default, var( --gallery-block--gutter-size, var( --wp--style--block-gap, 0.5em ) ) );}:where(.wp-block-gallery.is-layout-grid){gap: var( --wp--style--gallery-gap-default, var( --gallery-block--gutter-size, var( --wp--style--block-gap, 0.5em ) ) );}\";s:32:\"c99c05932c6685777ec5b856698fcc7d\";s:118:\":where(.wp-block-latest-posts.is-layout-flex){gap: 1.25em;}:where(.wp-block-latest-posts.is-layout-grid){gap: 1.25em;}\";s:32:\"dec8d648f30b13caec8e61374591787d\";s:120:\":where(.wp-block-post-template.is-layout-flex){gap: 1.25em;}:where(.wp-block-post-template.is-layout-grid){gap: 1.25em;}\";s:32:\"6c35533f7a92cce94808323603db9fc8\";s:120:\":where(.wp-block-term-template.is-layout-flex){gap: 1.25em;}:where(.wp-block-term-template.is-layout-grid){gap: 1.25em;}\";s:32:\"6a0505cd5c78a87ed77570cda43c1132\";s:102:\":where(.wp-block-columns.is-layout-flex){gap: 2em;}:where(.wp-block-columns.is-layout-grid){gap: 2em;}\";s:32:\"25a66f156386551185570f72a9f7d44e\";s:69:\":root :where(.wp-block-pullquote){font-size: 1.5em;line-height: 1.6;}\";}}', 'on'),
(128, 'recovery_keys', 'a:0:{}', 'off');
INSERT INTO `wp_options` (`option_id`, `option_name`, `option_value`, `autoload`) VALUES
(133, '_site_transient_update_themes', 'O:8:\"stdClass\":5:{s:12:\"last_checked\";i:1795747702;s:7:\"checked\";a:4:{s:16:\"twentytwentyfive\";s:3:\"1.5\";s:16:\"twentytwentyfour\";s:3:\"1.6\";s:17:\"twentytwentythree\";s:3:\"1.7\";s:9:\"uniwheels\";s:0:\"\";}s:8:\"response\";a:0:{}s:9:\"no_update\";a:3:{s:16:\"twentytwentyfive\";a:6:{s:5:\"theme\";s:16:\"twentytwentyfive\";s:11:\"new_version\";s:3:\"1.5\";s:3:\"url\";s:46:\"https://wordpress.org/themes/twentytwentyfive/\";s:7:\"package\";s:62:\"https://downloads.wordpress.org/theme/twentytwentyfive.1.5.zip\";s:8:\"requires\";s:3:\"6.7\";s:12:\"requires_php\";s:3:\"7.2\";}s:16:\"twentytwentyfour\";a:6:{s:5:\"theme\";s:16:\"twentytwentyfour\";s:11:\"new_version\";s:3:\"1.6\";s:3:\"url\";s:46:\"https://wordpress.org/themes/twentytwentyfour/\";s:7:\"package\";s:62:\"https://downloads.wordpress.org/theme/twentytwentyfour.1.6.zip\";s:8:\"requires\";s:3:\"6.4\";s:12:\"requires_php\";s:3:\"7.0\";}s:17:\"twentytwentythree\";a:6:{s:5:\"theme\";s:17:\"twentytwentythree\";s:11:\"new_version\";s:3:\"1.7\";s:3:\"url\";s:47:\"https://wordpress.org/themes/twentytwentythree/\";s:7:\"package\";s:63:\"https://downloads.wordpress.org/theme/twentytwentythree.1.7.zip\";s:8:\"requires\";s:3:\"6.1\";s:12:\"requires_php\";s:3:\"5.6\";}}s:12:\"translations\";a:0:{}}', 'off'),
(139, 'can_compress_scripts', '1', 'on'),
(142, '_site_transient_wp_plugin_dependencies_plugin_data', 'a:0:{}', 'off'),
(143, 'recently_activated', 'a:2:{s:27:\"mcp-adapter/mcp-adapter.php\";i:1790515458;s:33:\"ai-provider-for-google/plugin.php\";i:1790515197;}', 'off'),
(158, 'finished_updating_comment_type', '1', 'auto'),
(162, '_transient_jetpack_autoloader_plugin_paths', 'a:0:{}', 'on'),
(167, '_site_transient_update_plugins', 'O:8:\"stdClass\":5:{s:12:\"last_checked\";i:1795747701;s:8:\"response\";a:0:{}s:12:\"translations\";a:0:{}s:9:\"no_update\";a:2:{s:19:\"akismet/akismet.php\";O:8:\"stdClass\":10:{s:2:\"id\";s:21:\"w.org/plugins/akismet\";s:4:\"slug\";s:7:\"akismet\";s:6:\"plugin\";s:19:\"akismet/akismet.php\";s:11:\"new_version\";s:5:\"5.7.2\";s:3:\"url\";s:38:\"https://wordpress.org/plugins/akismet/\";s:7:\"package\";s:56:\"https://downloads.wordpress.org/plugin/akismet.5.7.2.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:60:\"https://ps.w.org/akismet/assets/icon-256x256.png?rev=2818463\";s:2:\"1x\";s:60:\"https://ps.w.org/akismet/assets/icon-128x128.png?rev=2818463\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:63:\"https://ps.w.org/akismet/assets/banner-1544x500.png?rev=2900731\";s:2:\"1x\";s:62:\"https://ps.w.org/akismet/assets/banner-772x250.png?rev=2900731\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"5.8\";}s:9:\"hello.php\";O:8:\"stdClass\":10:{s:2:\"id\";s:25:\"w.org/plugins/hello-dolly\";s:4:\"slug\";s:11:\"hello-dolly\";s:6:\"plugin\";s:9:\"hello.php\";s:11:\"new_version\";s:5:\"1.7.2\";s:3:\"url\";s:42:\"https://wordpress.org/plugins/hello-dolly/\";s:7:\"package\";s:60:\"https://downloads.wordpress.org/plugin/hello-dolly.1.7.2.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:64:\"https://ps.w.org/hello-dolly/assets/icon-256x256.jpg?rev=2052855\";s:2:\"1x\";s:64:\"https://ps.w.org/hello-dolly/assets/icon-128x128.jpg?rev=2052855\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:67:\"https://ps.w.org/hello-dolly/assets/banner-1544x500.jpg?rev=2645582\";s:2:\"1x\";s:66:\"https://ps.w.org/hello-dolly/assets/banner-772x250.jpg?rev=2052855\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"4.6\";}}s:7:\"checked\";a:2:{s:19:\"akismet/akismet.php\";s:5:\"5.7.2\";s:9:\"hello.php\";s:5:\"1.7.2\";}}', 'off'),
(177, 'theme_mods_uniwheels', 'a:1:{s:18:\"custom_css_post_id\";i:-1;}', 'auto'),
(213, '_transient_health-check-site-status-result', '{\"good\":19,\"recommended\":6,\"critical\":1}', 'on'),
(259, 'wp_calendar_block_has_published_posts', '1', 'auto'),
(265, '_site_transient_update_core', 'O:8:\"stdClass\":4:{s:7:\"updates\";a:1:{i:0;O:8:\"stdClass\":10:{s:8:\"response\";s:6:\"latest\";s:8:\"download\";s:59:\"https://downloads.wordpress.org/release/wordpress-7.1.5.zip\";s:6:\"locale\";s:5:\"en_US\";s:8:\"packages\";O:8:\"stdClass\":5:{s:4:\"full\";s:59:\"https://downloads.wordpress.org/release/wordpress-7.1.5.zip\";s:10:\"no_content\";s:70:\"https://downloads.wordpress.org/release/wordpress-7.1.5-no-content.zip\";s:11:\"new_bundled\";s:71:\"https://downloads.wordpress.org/release/wordpress-7.1.5-new-bundled.zip\";s:7:\"partial\";s:0:\"\";s:8:\"rollback\";s:0:\"\";}s:7:\"current\";s:5:\"7.1.5\";s:7:\"version\";s:5:\"7.1.5\";s:11:\"php_version\";s:3:\"7.4\";s:13:\"mysql_version\";s:5:\"5.5.5\";s:11:\"new_bundled\";s:3:\"6.7\";s:15:\"partial_version\";s:0:\"\";}}s:12:\"last_checked\";i:1795747696;s:15:\"version_checked\";s:5:\"7.1.5\";s:12:\"translations\";a:0:{}}', 'off'),
(266, 'auto_core_update_notified', 'a:4:{s:4:\"type\";s:7:\"success\";s:5:\"email\";s:31:\"ciedeejyrho.avelino@wvsu.edu.ph\";s:7:\"version\";s:5:\"7.1.3\";s:9:\"timestamp\";i:1793159258;}', 'off'),
(269, 'category_children', 'a:0:{}', 'auto'),
(293, '_site_transient_timeout_php_check_ee1e43d8f825d70a4b56f5957c7ccdad', '1796339078', 'off'),
(294, '_site_transient_php_check_ee1e43d8f825d70a4b56f5957c7ccdad', 'a:5:{s:19:\"recommended_version\";s:3:\"8.3\";s:15:\"minimum_version\";s:3:\"7.4\";s:12:\"is_supported\";b:1;s:9:\"is_secure\";b:1;s:13:\"is_acceptable\";b:1;}', 'off'),
(311, '_site_transient_timeout_theme_roots', '1795749501', 'off'),
(312, '_site_transient_theme_roots', 'a:4:{s:16:\"twentytwentyfive\";s:7:\"/themes\";s:16:\"twentytwentyfour\";s:7:\"/themes\";s:17:\"twentytwentythree\";s:7:\"/themes\";s:9:\"uniwheels\";s:7:\"/themes\";}', 'off'),
(359, '_site_transient_timeout_wp_theme_files_patterns-131d72e5b6398571addf1c6360e90b65', '1795761943', 'off'),
(360, '_site_transient_wp_theme_files_patterns-131d72e5b6398571addf1c6360e90b65', 'a:2:{s:7:\"version\";s:0:\"\";s:8:\"patterns\";a:0:{}}', 'off'),
(362, '_site_transient_timeout_wp_theme_files_patterns-8c4dd8bf85c9aec284ce8a088785d797', '1795761943', 'off'),
(363, '_site_transient_wp_theme_files_patterns-8c4dd8bf85c9aec284ce8a088785d797', 'a:2:{s:7:\"version\";s:0:\"\";s:8:\"patterns\";a:0:{}}', 'off');

-- --------------------------------------------------------

--
-- Table structure for table `wp_postmeta`
--

CREATE TABLE `wp_postmeta` (
  `meta_id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wp_postmeta`
--

INSERT INTO `wp_postmeta` (`meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(2, 3, '_wp_page_template', 'default'),
(5, 8, '_edit_lock', '1790521751:1'),
(6, 14, '_edit_lock', '1793159626:1'),
(7, 11, '_edit_lock', '1790522233:1'),
(8, 3, '_edit_lock', '1790521998:1'),
(9, 13, '_edit_lock', '1790522120:1'),
(10, 12, '_edit_lock', '1793159586:1'),
(13, 19, '_uniwheels_car_id', '1'),
(14, 20, '_wp_attached_file', '2026/10/car-1.jpg'),
(15, 20, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1376;s:6:\"height\";i:768;s:4:\"file\";s:17:\"2026/10/car-1.jpg\";s:8:\"filesize\";i:510275;s:5:\"sizes\";a:0:{}s:10:\"image_meta\";a:13:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}s:3:\"alt\";s:0:\"\";}}'),
(16, 20, '_uniwheels_photo', 'car-1.jpg'),
(17, 19, '_thumbnail_id', '20'),
(19, 21, '_uniwheels_car_id', '2'),
(20, 22, '_wp_attached_file', '2026/10/car-2.jpg'),
(21, 22, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1376;s:6:\"height\";i:768;s:4:\"file\";s:17:\"2026/10/car-2.jpg\";s:8:\"filesize\";i:498516;s:5:\"sizes\";a:0:{}s:10:\"image_meta\";a:13:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}s:3:\"alt\";s:0:\"\";}}'),
(22, 22, '_uniwheels_photo', 'car-2.jpg'),
(23, 21, '_thumbnail_id', '22'),
(25, 23, '_uniwheels_car_id', '3'),
(26, 24, '_wp_attached_file', '2026/10/car-3.jpg'),
(27, 24, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1376;s:6:\"height\";i:768;s:4:\"file\";s:17:\"2026/10/car-3.jpg\";s:8:\"filesize\";i:544396;s:5:\"sizes\";a:0:{}s:10:\"image_meta\";a:13:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}s:3:\"alt\";s:0:\"\";}}'),
(28, 24, '_uniwheels_photo', 'car-3.jpg'),
(29, 23, '_thumbnail_id', '24'),
(31, 25, '_uniwheels_car_id', '4'),
(32, 26, '_wp_attached_file', '2026/10/car-4.jpg'),
(33, 26, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1376;s:6:\"height\";i:768;s:4:\"file\";s:17:\"2026/10/car-4.jpg\";s:8:\"filesize\";i:542958;s:5:\"sizes\";a:0:{}s:10:\"image_meta\";a:13:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}s:3:\"alt\";s:0:\"\";}}'),
(34, 26, '_uniwheels_photo', 'car-4.jpg'),
(35, 25, '_thumbnail_id', '26'),
(37, 27, '_uniwheels_car_id', '5'),
(38, 28, '_wp_attached_file', '2026/10/car-5.jpg'),
(39, 28, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1376;s:6:\"height\";i:768;s:4:\"file\";s:17:\"2026/10/car-5.jpg\";s:8:\"filesize\";i:550942;s:5:\"sizes\";a:0:{}s:10:\"image_meta\";a:13:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}s:3:\"alt\";s:0:\"\";}}'),
(40, 28, '_uniwheels_photo', 'car-5.jpg'),
(41, 27, '_thumbnail_id', '57'),
(43, 29, '_uniwheels_car_id', '6'),
(44, 30, '_wp_attached_file', '2026/10/car-6.jpg'),
(45, 30, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1376;s:6:\"height\";i:768;s:4:\"file\";s:17:\"2026/10/car-6.jpg\";s:8:\"filesize\";i:520831;s:5:\"sizes\";a:0:{}s:10:\"image_meta\";a:13:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}s:3:\"alt\";s:0:\"\";}}'),
(46, 30, '_uniwheels_photo', 'car-6.jpg'),
(47, 29, '_thumbnail_id', '55'),
(49, 31, '_uniwheels_car_id', '7'),
(50, 32, '_wp_attached_file', '2026/10/car-7.jpg'),
(51, 32, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1376;s:6:\"height\";i:768;s:4:\"file\";s:17:\"2026/10/car-7.jpg\";s:8:\"filesize\";i:553018;s:5:\"sizes\";a:0:{}s:10:\"image_meta\";a:13:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}s:3:\"alt\";s:0:\"\";}}'),
(52, 32, '_uniwheels_photo', 'car-7.jpg'),
(53, 31, '_thumbnail_id', '53'),
(55, 33, '_uniwheels_car_id', '8'),
(56, 34, '_wp_attached_file', '2026/10/car-8.jpg'),
(57, 34, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1376;s:6:\"height\";i:768;s:4:\"file\";s:17:\"2026/10/car-8.jpg\";s:8:\"filesize\";i:541745;s:5:\"sizes\";a:0:{}s:10:\"image_meta\";a:13:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}s:3:\"alt\";s:0:\"\";}}'),
(58, 34, '_uniwheels_photo', 'car-8.jpg'),
(59, 33, '_thumbnail_id', '51'),
(61, 11, '_wp_trash_meta_status', 'publish'),
(62, 11, '_wp_trash_meta_time', '1793159587'),
(63, 11, '_wp_desired_post_slug', 'home'),
(64, 12, '_wp_trash_meta_status', 'publish'),
(65, 12, '_wp_trash_meta_time', '1793159587'),
(66, 12, '_wp_desired_post_slug', 'inventory'),
(67, 13, '_wp_trash_meta_status', 'publish'),
(68, 13, '_wp_trash_meta_time', '1793159587'),
(69, 13, '_wp_desired_post_slug', 'inquiries'),
(70, 15, '_wp_trash_meta_status', 'publish'),
(71, 15, '_wp_trash_meta_time', '1793159587'),
(72, 15, '_wp_desired_post_slug', 'services'),
(73, 16, '_edit_lock', '1793159819:1'),
(80, 51, '_wp_attached_file', '2026/11/903684460-1794503819343093-4983372071454-20261127071245-536356.jpg'),
(81, 51, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1200;s:6:\"height\";i:1600;s:4:\"file\";s:74:\"2026/11/903684460-1794503819343093-4983372071454-20261127071245-536356.jpg\";s:8:\"filesize\";i:359756;s:5:\"sizes\";a:0:{}s:10:\"image_meta\";a:13:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}s:3:\"alt\";s:0:\"\";}}'),
(82, 51, '_uniwheels_photo', '903684460-1794503819343093-4983372071454-20261127071245-536356.jpg'),
(84, 53, '_wp_attached_file', '2026/11/906289458-1451581573757015-5721820078948-20261127071632-99ef29.jpg'),
(85, 53, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1200;s:6:\"height\";i:1600;s:4:\"file\";s:74:\"2026/11/906289458-1451581573757015-5721820078948-20261127071632-99ef29.jpg\";s:8:\"filesize\";i:374334;s:5:\"sizes\";a:0:{}s:10:\"image_meta\";a:13:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}s:3:\"alt\";s:0:\"\";}}'),
(86, 53, '_uniwheels_photo', '906289458-1451581573757015-5721820078948-20261127071632-99ef29.jpg'),
(88, 55, '_wp_attached_file', '2026/11/821123799-3475211326019298-7066073226767-20261127072038-74ea98.jpg'),
(89, 55, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1536;s:6:\"height\";i:2048;s:4:\"file\";s:74:\"2026/11/821123799-3475211326019298-7066073226767-20261127072038-74ea98.jpg\";s:8:\"filesize\";i:347680;s:5:\"sizes\";a:0:{}s:10:\"image_meta\";a:13:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}s:3:\"alt\";s:0:\"\";}}'),
(90, 55, '_uniwheels_photo', '821123799-3475211326019298-7066073226767-20261127072038-74ea98.jpg'),
(92, 57, '_wp_attached_file', '2026/11/518275072-676404038772996-17279697677145-20261127072240-48483c.jpg'),
(93, 57, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1536;s:6:\"height\";i:2048;s:4:\"file\";s:74:\"2026/11/518275072-676404038772996-17279697677145-20261127072240-48483c.jpg\";s:8:\"filesize\";i:320572;s:5:\"sizes\";a:0:{}s:10:\"image_meta\";a:13:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}s:3:\"alt\";s:0:\"\";}}'),
(94, 57, '_uniwheels_photo', '518275072-676404038772996-17279697677145-20261127072240-48483c.jpg'),
(96, 25, '_wp_trash_meta_status', 'publish'),
(97, 25, '_wp_trash_meta_time', '1795735405'),
(98, 25, '_wp_desired_post_slug', '2022-suzuki-ertiga-1-5-gl'),
(99, 23, '_wp_trash_meta_status', 'publish'),
(100, 23, '_wp_trash_meta_time', '1795735414'),
(101, 23, '_wp_desired_post_slug', '2019-toyota-fortuner-2-4-g-4x2'),
(102, 21, '_wp_trash_meta_status', 'publish'),
(103, 21, '_wp_trash_meta_time', '1795735419'),
(104, 21, '_wp_desired_post_slug', '2020-honda-civic-1-8-e-cvt'),
(105, 19, '_wp_trash_meta_status', 'publish'),
(106, 19, '_wp_trash_meta_time', '1795735424'),
(107, 19, '_wp_desired_post_slug', '2021-toyota-vios-1-3-xle'),
(124, 31, '_wp_trash_meta_status', 'publish'),
(125, 31, '_wp_trash_meta_time', '1795754061'),
(126, 31, '_wp_desired_post_slug', '2021-toyota-rav4-2-5-hybrid'),
(129, 76, '_uniwheels_car_id', '16'),
(130, 77, '_wp_attached_file', '2026/11/923007341-1854882875512809-2174799586414-20261127134150-a15d84.jpg'),
(131, 77, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1536;s:6:\"height\";i:2048;s:4:\"file\";s:74:\"2026/11/923007341-1854882875512809-2174799586414-20261127134150-a15d84.jpg\";s:8:\"filesize\";i:195634;s:5:\"sizes\";a:0:{}s:10:\"image_meta\";a:13:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}s:3:\"alt\";s:0:\"\";}}'),
(132, 77, '_uniwheels_photo', '923007341-1854882875512809-2174799586414-20261127134150-a15d84.jpg'),
(133, 76, '_thumbnail_id', '79'),
(135, 79, '_wp_attached_file', '2026/11/929595700-1131954503059987-8383345773320-20261127134150-f32c71.jpg'),
(136, 79, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1536;s:6:\"height\";i:2048;s:4:\"file\";s:74:\"2026/11/929595700-1131954503059987-8383345773320-20261127134150-f32c71.jpg\";s:8:\"filesize\";i:322855;s:5:\"sizes\";a:0:{}s:10:\"image_meta\";a:13:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}s:3:\"alt\";s:0:\"\";}}'),
(137, 79, '_uniwheels_photo', '929595700-1131954503059987-8383345773320-20261127134150-f32c71.jpg'),
(139, 80, '_uniwheels_car_id', '17'),
(140, 81, '_wp_attached_file', '2026/11/926368308-1634181675388831-8679791847837-20261127134538-5cc2a8.jpg'),
(141, 81, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1200;s:6:\"height\";i:1600;s:4:\"file\";s:74:\"2026/11/926368308-1634181675388831-8679791847837-20261127134538-5cc2a8.jpg\";s:8:\"filesize\";i:432829;s:5:\"sizes\";a:0:{}s:10:\"image_meta\";a:13:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}s:3:\"alt\";s:0:\"\";}}'),
(142, 81, '_uniwheels_photo', '926368308-1634181675388831-8679791847837-20261127134538-5cc2a8.jpg'),
(143, 80, '_thumbnail_id', '83'),
(145, 83, '_wp_attached_file', '2026/11/927531620-1140240652306023-5751639719405-20261127134538-d1e95d.jpg'),
(146, 83, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1200;s:6:\"height\";i:1600;s:4:\"file\";s:74:\"2026/11/927531620-1140240652306023-5751639719405-20261127134538-d1e95d.jpg\";s:8:\"filesize\";i:550676;s:5:\"sizes\";a:0:{}s:10:\"image_meta\";a:13:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}s:3:\"alt\";s:0:\"\";}}'),
(147, 83, '_uniwheels_photo', '927531620-1140240652306023-5751639719405-20261127134538-d1e95d.jpg'),
(149, 84, '_uniwheels_car_id', '18'),
(150, 85, '_wp_attached_file', '2026/11/933120975-1719542729695293-8005198839928-20261127134807-91d177.jpg'),
(151, 85, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1200;s:6:\"height\";i:1600;s:4:\"file\";s:74:\"2026/11/933120975-1719542729695293-8005198839928-20261127134807-91d177.jpg\";s:8:\"filesize\";i:269000;s:5:\"sizes\";a:0:{}s:10:\"image_meta\";a:13:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}s:3:\"alt\";s:0:\"\";}}'),
(152, 85, '_uniwheels_photo', '933120975-1719542729695293-8005198839928-20261127134807-91d177.jpg'),
(153, 84, '_thumbnail_id', '87'),
(155, 87, '_wp_attached_file', '2026/11/925950403-1162391016174534-2821189245807-20261127134807-674767.jpg'),
(156, 87, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1200;s:6:\"height\";i:1600;s:4:\"file\";s:74:\"2026/11/925950403-1162391016174534-2821189245807-20261127134807-674767.jpg\";s:8:\"filesize\";i:411762;s:5:\"sizes\";a:0:{}s:10:\"image_meta\";a:13:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}s:3:\"alt\";s:0:\"\";}}'),
(157, 87, '_uniwheels_photo', '925950403-1162391016174534-2821189245807-20261127134807-674767.jpg'),
(159, 88, '_uniwheels_car_id', '19'),
(160, 89, '_wp_attached_file', '2026/11/926503030-2222841575292117-2415607628230-20261127135058-669c41.jpg'),
(161, 89, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1536;s:6:\"height\";i:2048;s:4:\"file\";s:74:\"2026/11/926503030-2222841575292117-2415607628230-20261127135058-669c41.jpg\";s:8:\"filesize\";i:243252;s:5:\"sizes\";a:0:{}s:10:\"image_meta\";a:13:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}s:3:\"alt\";s:0:\"\";}}'),
(162, 89, '_uniwheels_photo', '926503030-2222841575292117-2415607628230-20261127135058-669c41.jpg'),
(163, 88, '_thumbnail_id', '91'),
(165, 91, '_wp_attached_file', '2026/11/926661241-1822986258768167-7784572486901-20261127135058-81f1b5.jpg'),
(166, 91, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1536;s:6:\"height\";i:2048;s:4:\"file\";s:74:\"2026/11/926661241-1822986258768167-7784572486901-20261127135058-81f1b5.jpg\";s:8:\"filesize\";i:315013;s:5:\"sizes\";a:0:{}s:10:\"image_meta\";a:13:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}s:3:\"alt\";s:0:\"\";}}'),
(167, 91, '_uniwheels_photo', '926661241-1822986258768167-7784572486901-20261127135058-81f1b5.jpg'),
(169, 92, '_uniwheels_car_id', '20'),
(170, 93, '_wp_attached_file', '2026/11/921965047-1489035546502598-1849434788978-20261127135330-40cd26.jpg'),
(171, 93, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1200;s:6:\"height\";i:1600;s:4:\"file\";s:74:\"2026/11/921965047-1489035546502598-1849434788978-20261127135330-40cd26.jpg\";s:8:\"filesize\";i:395431;s:5:\"sizes\";a:0:{}s:10:\"image_meta\";a:13:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}s:3:\"alt\";s:0:\"\";}}'),
(172, 93, '_uniwheels_photo', '921965047-1489035546502598-1849434788978-20261127135330-40cd26.jpg'),
(173, 92, '_thumbnail_id', '93'),
(175, 95, '_wp_attached_file', '2026/11/921424674-1133153129578875-6039982588693-20261127135330-9fd3b5.jpg'),
(176, 95, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1536;s:6:\"height\";i:2048;s:4:\"file\";s:74:\"2026/11/921424674-1133153129578875-6039982588693-20261127135330-9fd3b5.jpg\";s:8:\"filesize\";i:310402;s:5:\"sizes\";a:0:{}s:10:\"image_meta\";a:13:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}s:3:\"alt\";s:0:\"\";}}'),
(177, 95, '_uniwheels_photo', '921424674-1133153129578875-6039982588693-20261127135330-9fd3b5.jpg'),
(179, 96, '_uniwheels_car_id', '21'),
(180, 97, '_wp_attached_file', '2026/11/921130358-1378733730866933-3644634542505-20261127135532-b55d95.jpg'),
(181, 97, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1536;s:6:\"height\";i:2048;s:4:\"file\";s:74:\"2026/11/921130358-1378733730866933-3644634542505-20261127135532-b55d95.jpg\";s:8:\"filesize\";i:225829;s:5:\"sizes\";a:0:{}s:10:\"image_meta\";a:13:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}s:3:\"alt\";s:0:\"\";}}'),
(182, 97, '_uniwheels_photo', '921130358-1378733730866933-3644634542505-20261127135532-b55d95.jpg'),
(183, 96, '_thumbnail_id', '99'),
(185, 99, '_wp_attached_file', '2026/11/921076874-1154771020640435-2591280224388-20261127135532-7f9e8e.jpg'),
(186, 99, '_wp_attachment_metadata', 'a:6:{s:5:\"width\";i:1536;s:6:\"height\";i:2048;s:4:\"file\";s:74:\"2026/11/921076874-1154771020640435-2591280224388-20261127135532-7f9e8e.jpg\";s:8:\"filesize\";i:368323;s:5:\"sizes\";a:0:{}s:10:\"image_meta\";a:13:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}s:3:\"alt\";s:0:\"\";}}'),
(187, 99, '_uniwheels_photo', '921076874-1154771020640435-2591280224388-20261127135532-7f9e8e.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `wp_posts`
--

CREATE TABLE `wp_posts` (
  `ID` bigint(20) UNSIGNED NOT NULL,
  `post_author` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content` longtext NOT NULL,
  `post_title` text NOT NULL,
  `post_excerpt` text NOT NULL,
  `post_status` varchar(20) NOT NULL DEFAULT 'publish',
  `comment_status` varchar(20) NOT NULL DEFAULT 'open',
  `ping_status` varchar(20) NOT NULL DEFAULT 'open',
  `post_password` varchar(255) NOT NULL DEFAULT '',
  `post_name` varchar(200) NOT NULL DEFAULT '',
  `to_ping` text NOT NULL,
  `pinged` text NOT NULL,
  `post_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_modified_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content_filtered` longtext NOT NULL,
  `post_parent` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `guid` varchar(255) NOT NULL DEFAULT '',
  `menu_order` int(11) NOT NULL DEFAULT 0,
  `post_type` varchar(20) NOT NULL DEFAULT 'post',
  `post_mime_type` varchar(100) NOT NULL DEFAULT '',
  `comment_count` bigint(20) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wp_posts`
--

INSERT INTO `wp_posts` (`ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES
(1, 1, '2026-09-27 13:09:50', '2026-09-27 13:09:50', '<!-- wp:paragraph -->\n<p>Welcome to WordPress. This is your first post. Edit or delete it, then start writing!</p>\n<!-- /wp:paragraph -->', 'Hello world!', '', 'publish', 'open', 'open', '', 'hello-world', '', '', '2026-09-27 13:09:50', '2026-09-27 13:09:50', '', 0, 'http://localhost/uniwheels/?p=1', 0, 'post', '', 1),
(3, 1, '2026-09-27 13:09:50', '2026-09-27 13:09:50', '<p>UniWheels Motors respects your privacy and follows the Philippine Data Privacy Act of 2012 (Republic Act No. 10173).</p>\n\n<h2>What we collect</h2>\n<p>When you message, call or visit us, we may keep your name, phone number, email, and the car you are interested in or want to trade in.</p>\n\n<h2>How we use it</h2>\n<ul>\n<li>To answer your questions and arrange viewings and test drives</li>\n<li>To prepare your reservation, sale and transfer papers</li>\n</ul>\n<p>We do not sell or share your details with other companies for marketing.</p>\n\n<h2>Messenger</h2>\n<p>Chats on Facebook Messenger are also covered by Meta\'s own privacy policy.</p>\n\n<h2>Your choices</h2>\n<p>You can ask us at any time to see, correct or delete your details. Email sales@uniwheelsmotors.com or tell our staff at the showroom.</p>', 'Privacy Policy', '', 'publish', 'closed', 'open', '', 'privacy-policy', '', '', '2026-10-28 03:53:07', '2026-10-28 03:53:07', '', 0, 'http://localhost/uniwheels/?page_id=3', 0, 'page', '', 0),
(4, 0, '2026-09-27 13:09:52', '2026-09-27 13:09:52', '<!-- wp:page-list /-->', 'Navigation', '', 'publish', 'closed', 'closed', '', 'navigation', '', '', '2026-09-27 13:09:52', '2026-09-27 13:09:52', '', 0, 'http://localhost/uniwheels/2026/09/27/navigation/', 0, 'wp_navigation', '', 0),
(7, 1, '2026-09-27 13:18:28', '2026-09-27 13:18:28', '{\"version\": 3, \"isGlobalStylesUserThemeJSON\": true }', 'Custom Styles', '', 'publish', 'closed', 'closed', '', 'wp-global-styles-twentytwentyfive', '', '', '2026-09-27 13:18:28', '2026-09-27 13:18:28', '', 0, 'http://localhost/uniwheels/2026/09/27/wp-global-styles-twentytwentyfive/', 0, 'wp_global_styles', '', 0),
(9, 1, '2026-09-27 15:09:11', '2026-09-27 15:09:11', '{\"version\": 3, \"isGlobalStylesUserThemeJSON\": true }', 'Custom Styles', '', 'publish', 'closed', 'closed', '', 'wp-global-styles-uniwheels', '', '', '2026-09-27 15:09:11', '2026-09-27 15:09:11', '', 0, 'http://localhost/uniwheels/2026/09/27/wp-global-styles-uniwheels/', 0, 'wp_global_styles', '', 0),
(10, 0, '2026-09-27 15:12:30', '2026-09-27 15:12:30', '<h2>Client Confidentiality &amp; Privacy Protocol</h2>\n<p>At <strong>UniWheels Performance &amp; Exotic Motors</strong>, client privacy and transaction confidentiality are paramount. We respect the privacy of our high-net-worth clientele, collectors, and automotive enthusiasts worldwide.</p>\n\n<div class=\"luxury-feature-grid\">\n  <div class=\"luxury-feature-box\">\n    <h4>🔒 Discreet Transactions</h4>\n    <p>All private vehicle acquisitions, bespoke wheel engineering commissions, and financial arrangements remain strictly confidential between the client and senior acquisition directors.</p>\n  </div>\n  <div class=\"luxury-feature-box\">\n    <h4>🛡️ Secure Inquiry Processing</h4>\n    <p>Contact information submitted through our private consultation booking form is encrypted and never sold, leased, or distributed to third-party marketing entities.</p>\n  </div>\n  <div class=\"luxury-feature-box\">\n    <h4>📋 Transparent Data Retention</h4>\n    <p>Client records are stored securely within our encrypted dealership telemetry system exclusively for appointment confirmations, warranty records, and transport logistics.</p>\n  </div>\n</div>\n\n<h3>Information We Collect</h3>\n<p>When you schedule a private showroom consultation or submit an inquiry for a fleet vehicle, we collect:</p>\n<ul>\n  <li>Full Name and preferred salutation</li>\n  <li>Direct telephone or WhatsApp contact number</li>\n  <li>Email address for appointment itineraries and transport confirmations</li>\n  <li>Target vehicle of interest and custom build specifications</li>\n  <li>Trade-in vehicle VIN, year, make, model, and valuation preferences</li>\n</ul>\n\n<h3>Data Security &amp; Protection</h3>\n<p>Our executive dealer infrastructure complies with international data privacy standards. All inquiries submitted to our database are safeguarded with enterprise-grade firewalls, bcrypt credential hashing, and restricted staff privilege controls.</p>', 'Privacy Policy', '', 'inherit', 'closed', 'closed', '', '3-revision-v1', '', '', '2026-09-27 15:12:30', '2026-09-27 15:12:30', '', 3, 'http://localhost/uniwheels/?p=10', 0, 'revision', '', 0),
(11, 0, '2026-09-27 15:12:30', '2026-09-27 15:12:30', '<h2>Welcome to UniWheels Performance</h2>\n<p class=\"lead\">UniWheels is an elite high-performance automotive dealership and bespoke forged wheel atelier dedicated to supercars, hypercars, and track-engineered platforms.</p>\n\n<div class=\"luxury-feature-grid\">\n  <div class=\"luxury-feature-box\">\n    <h4>🏎️ Hand-Curated Exotic Fleet</h4>\n    <p>From the Ferrari SF90 Stradale to the McLaren 765LT, our showroom hosts only the world\'s most capable and impeccably documented vehicles.</p>\n  </div>\n  <div class=\"luxury-feature-box\">\n    <h4>⚙️ Aerospace 6061-T6 Forged Wheels</h4>\n    <p>Every wheel is custom-engineered using 10,000+ tons of hydraulic forging pressure for millimeter-perfect caliper clearances and flush fender stance.</p>\n  </div>\n  <div class=\"luxury-feature-box\">\n    <h4>🤝 Face-to-Face VIP Consultations</h4>\n    <p>Skip impersonal forms and online guesswork. Experience our private showroom with dedicated 1-on-1 consultations, cold starts, and immediate trade appraisals.</p>\n  </div>\n</div>\n\n<p style=\"text-align:center;margin-top:2.5rem\">\n  <a href=\"#showcase\" class=\"btn-nav-inquire\" style=\"padding:14px 30px;font-size:0.9rem\">Explore Showroom Fleet →</a>\n</p>', 'Home — Showroom &amp; Exotic Fleet', '', 'trash', 'closed', 'closed', '', 'home__trashed', '', '', '2026-10-28 03:53:07', '2026-10-28 03:53:07', '', 0, 'http://localhost/uniwheels/home/', 0, 'page', '', 0),
(12, 0, '2026-09-27 15:12:30', '2026-09-27 15:12:30', '<h2>Curated High-Performance Inventory</h2>\n<p>Each exotic vehicle in the UniWheels collection represents the pinnacle of contemporary automotive engineering, certified by our master technicians with 100-point mechanical and structural verification.</p>\n\n<div class=\"luxury-feature-grid\">\n  <div class=\"luxury-feature-box\">\n    <div style=\"font-size:0.75rem;color:var(--wheels-neon);font-family:var(--font-mono);font-weight:700\">★ RECOMMENDED SHOWCASE</div>\n    <h4>Ferrari SF90 Stradale Crimson Edition</h4>\n    <p><strong>Wheels:</strong> UW-F01 Monoblock Forged (21\" F / 22\" R)<br>\n    <strong>Finish:</strong> Brushed Candy Apple Crimson<br>\n    <strong>Status:</strong> Immediate Showroom Delivery Available</p>\n  </div>\n\n  <div class=\"luxury-feature-box\">\n    <div style=\"font-size:0.75rem;color:#ffab00;font-family:var(--font-mono);font-weight:700\">🔥 BESPOKE ALLOCATION</div>\n    <h4>Apollo Intensa Emozione Carbon/Crimson</h4>\n    <p><strong>Wheels:</strong> UW-R09 Carbon-Forged Track Spec (20\" F / 21\" R)<br>\n    <strong>Finish:</strong> Dark Crimson Tint / Gloss Carbon Lip<br>\n    <strong>Status:</strong> Centerlock Track Build (Bespoke)</p>\n  </div>\n\n  <div class=\"luxury-feature-box\">\n    <div style=\"font-size:0.75rem;color:#2ed573;font-family:var(--font-mono);font-weight:700\">● IN STOCK</div>\n    <h4>Porsche 911 GT3 RS Track Edition</h4>\n    <p><strong>Wheels:</strong> UW-GT3 Magnesium Monoblock (20\" F / 21\" R)<br>\n    <strong>Finish:</strong> Liquid Bronze / Crimson Outer Lip<br>\n    <strong>Status:</strong> Immediate Showroom Delivery Available</p>\n  </div>\n\n  <div class=\"luxury-feature-box\">\n    <div style=\"font-size:0.75rem;color:#38bdf8;font-family:var(--font-mono);font-weight:700\">🔥 BESPOKE ALLOCATION</div>\n    <h4>McLaren 765LT Carbon Longtail</h4>\n    <p><strong>Wheels:</strong> UW-LT9 Ultra-Light Track (19\" F / 20\" R)<br>\n    <strong>Finish:</strong> Candy Crimson Satin<br>\n    <strong>Status:</strong> Dual-Clutch Bespoke Longtail</p>\n  </div>\n\n  <div class=\"luxury-feature-box\">\n    <div style=\"font-size:0.75rem;color:#2ed573;font-family:var(--font-mono);font-weight:700\">● IN STOCK</div>\n    <h4>Lamborghini Huracán STO Squadra Corse</h4>\n    <p><strong>Wheels:</strong> UW-STO Aero-Forged Centerlock (20\" F / 20\" R)<br>\n    <strong>Finish:</strong> Matte Velvet Crimson<br>\n    <strong>Status:</strong> Naturally Aspirated V10 Track Weapon</p>\n  </div>\n\n  <div class=\"luxury-feature-box\">\n    <div style=\"font-size:0.75rem;color:#2ed573;font-family:var(--font-mono);font-weight:700\">● IN STOCK</div>\n    <h4>Chevrolet Corvette C8 Z06 Widebody</h4>\n    <p><strong>Wheels:</strong> UW-Z06 Concave Forged Monoblock (20\" F / 21\" R)<br>\n    <strong>Finish:</strong> Metallic Dark Wine Red<br>\n    <strong>Status:</strong> Flat-Plane Crank 8,600 RPM</p>\n  </div>\n</div>\n\n<p style=\"text-align:center;margin-top:2.5rem\">\n  <a href=\"#inquirySection\" class=\"btn-nav-inquire\" style=\"padding:14px 30px;font-size:0.9rem\">Schedule Private Inspection &amp; Test Drive →</a>\n</p>', 'Showroom Fleet &amp; Inventory', '', 'trash', 'closed', 'closed', '', 'inventory__trashed', '', '', '2026-10-28 03:53:07', '2026-10-28 03:53:07', '', 0, 'http://localhost/uniwheels/inventory/', 0, 'page', '', 0),
(13, 0, '2026-09-27 15:12:30', '2026-09-27 15:12:30', '<h2>Private Showroom Appointments &amp; Consultations</h2>\n<p>Acquiring an exotic vehicle or custom forged wheel set should be personal, thorough, and effortless. We believe luxury clients should never have to guess technical wheel offsets or vehicle specifications online.</p>\n\n<div class=\"luxury-feature-grid\">\n  <div class=\"luxury-feature-box\">\n    <h4>1. Dedicated Showroom Host</h4>\n    <p>Enjoy exclusive private access to our showroom floor with zero crowds. Inspect your target vehicle, test cold starts, and examine paintwork under precision lighting.</p>\n  </div>\n\n  <div class=\"luxury-feature-box\">\n    <h4>2. Instant Physical Trade Appraisal</h4>\n    <p>Bring your existing vehicle for an immediate physical evaluation, diagnostic scan, and guaranteed buy-in figure while you consult with our team.</p>\n  </div>\n\n  <div class=\"luxury-feature-box\">\n    <h4>3. Enclosed Transport Logistics</h4>\n    <p>Receive comprehensive door-to-door white-glove transport quotes with full marine and road transit insurance included worldwide.</p>\n  </div>\n</div>\n\n<h3>Available Consultation Formats</h3>\n<ul>\n  <li><strong>Showroom Visit (Face-to-Face):</strong> 60-minute private walkthrough of our vehicle collection.</li>\n  <li><strong>Private VIP Viewing &amp; Test Drive:</strong> Supervised road testing and dynamic performance evaluation.</li>\n  <li><strong>Vehicle Acquisition &amp; Trade-In:</strong> Contract negotiation, cash purchase terms, or vehicle trade valuation.</li>\n  <li><strong>Custom Fleet Sourcing:</strong> Global allocation locating for limited-series exotics not currently on display.</li>\n</ul>\n\n<p style=\"text-align:center;margin-top:2.5rem\">\n  <a href=\"#inquirySection\" class=\"btn-nav-inquire\" style=\"padding:14px 30px;font-size:0.9rem\">Book Face-to-Face Consultation →</a>\n</p>', 'VIP Inquiries &amp; Consultations', '', 'trash', 'closed', 'closed', '', 'inquiries__trashed', '', '', '2026-10-28 03:53:07', '2026-10-28 03:53:07', '', 0, 'http://localhost/uniwheels/inquiries/', 0, 'page', '', 0),
(14, 0, '2026-09-27 15:12:30', '2026-09-27 15:12:30', '<p>UniWheels Motors is a neighborhood pre-owned car dealer. We sell clean, checked secondhand cars for everyday families and drivers: sedans, hatchbacks, SUVs and pickups.</p>\n\n<h2>What we check on every car</h2>\n<ul>\n<li>Engine, transmission, brakes and electrical system, road-tested by our mechanics</li>\n<li>Real mileage and no hidden flood or accident damage</li>\n<li>Complete original OR/CR, LTO registration and HPG clearance, ready for transfer</li>\n</ul>\n\n<h2>Trade in your car</h2>\n<p>Bring your current car to the showroom for an honest trade-in quote. If you find a car you like, you can trade in and drive home your upgrade the same day.</p>\n\n<h2>Visit us</h2>\n<p>Walk-ins are always welcome. See our <a href=\"page.php?p=contact\">address and opening hours</a>, or message us on Messenger to reserve a viewing.</p>', 'About Us', '', 'publish', 'closed', 'closed', '', 'about', '', '', '2026-10-28 03:53:07', '2026-10-28 03:53:07', '', 0, 'http://localhost/uniwheels/about/', 0, 'page', '', 0),
(15, 0, '2026-09-27 15:12:30', '2026-09-27 15:12:30', '<h2>Comprehensive Dealership Services</h2>\n<p>UniWheels offers end-to-end bespoke automotive services tailored for supercar owners and discerning collectors.</p>\n\n<div class=\"luxury-feature-grid\">\n  <div class=\"luxury-feature-box\">\n    <h4>🔄 Exotic Vehicle Trade-In &amp; Consignment</h4>\n    <p>Trade in your current exotic vehicle or let our senior brokers manage private consignment to verified buyers worldwide with guaranteed settlement.</p>\n  </div>\n\n  <div class=\"luxury-feature-box\">\n    <h4>⚡ Bespoke Forged Wheel Engineering</h4>\n    <p>Custom 1-piece Monoblock and 3-piece modular forged wheels tailored to your vehicle\'s exact suspension geometry and brake caliper dimensions.</p>\n  </div>\n\n  <div class=\"luxury-feature-box\">\n    <h4>🏁 Track Preparation &amp; Centerlock Conversions</h4>\n    <p>High-performance brake upgrades, centerlock hub conversions, and lightweight track wheel balancing with Michelin Cup 2R tires.</p>\n  </div>\n\n  <div class=\"luxury-feature-box\">\n    <h4>🚚 Enclosed White-Glove Transport</h4>\n    <p>Door-to-door climate-controlled enclosed transit across North America, Europe, and the Middle East with comprehensive insurance.</p>\n  </div>\n</div>\n\n<h3>Trade-In Evaluation Process</h3>\n<ol>\n  <li><strong>Initial Consultation:</strong> Submit your vehicle details via our VIP inquiry form or discuss directly in person.</li>\n  <li><strong>Physical Vehicle Inspection:</strong> Multi-point diagnostic scan, paint meter readings, and mechanical check in our private bay.</li>\n  <li><strong>Guaranteed Valuation:</strong> Immediate trade-in credit applied toward any vehicle in our showroom fleet.</li>\n</ol>', 'Dealership Services &amp; Trade-Ins', '', 'trash', 'closed', 'closed', '', 'services__trashed', '', '', '2026-10-28 03:53:07', '2026-10-28 03:53:07', '', 0, 'http://localhost/uniwheels/services/', 0, 'page', '', 0),
(16, 0, '2026-09-27 15:12:30', '2026-09-27 15:12:30', '<h2>Showroom</h2>\n<p>UniWheels Auto Mall, Main Highway Showroom Strip</p>\n\n<h2>Opening hours</h2>\n<ul>\n<li>Monday to Saturday: 8:30 AM to 6:30 PM</li>\n<li>Sunday: 9:00 AM to 5:00 PM</li>\n</ul>\n<p>Walk-ins are always welcome. No appointment needed.</p>\n\n<h2>Call or message us</h2>\n<ul>\n<li>Phone: (02) 8123-4567 / 0917-123-4567</li>\n<li>Email: sales@uniwheelsmotors.com</li>\n<li>Messenger: <a href=\"https://m.me/uniwheels\">m.me/uniwheels</a> (fastest reply)</li>\n</ul>', 'Contact Us', '', 'publish', 'closed', 'closed', '', 'contact', '', '', '2026-10-28 03:53:07', '2026-10-28 03:53:07', '', 0, 'http://localhost/uniwheels/contact/', 0, 'page', '', 0),
(17, 0, '2026-09-27 15:12:30', '2026-09-27 15:12:30', '<h2>Prices and availability</h2>\n<p>Prices on this website are in Philippine pesos and may change without notice. A car shown as available may already be reserved or sold, so please message or call us to confirm before visiting.</p>\n\n<h2>Reservations</h2>\n<p>You can reserve a car with a reservation fee. Our staff will explain how long the reservation holds and whether the fee is refundable before you pay.</p>\n\n<h2>Inspection and test drive</h2>\n<p>You are welcome to inspect and test-drive any car at our showroom, and to bring your own mechanic.</p>\n\n<h2>Warranty</h2>\n<p>Warranty coverage depends on the car. Our staff will tell you what is covered and for how long, and it will be written in your sales agreement.</p>\n\n<h2>Papers</h2>\n<p>Every car comes with its original OR/CR. We assist with the transfer of ownership.</p>', 'Terms &amp; Warranty', '', 'publish', 'closed', 'closed', '', 'terms-warranty', '', '', '2026-10-28 03:53:07', '2026-10-28 03:53:07', '', 0, 'http://localhost/uniwheels/terms-warranty/', 0, 'page', '', 0),
(19, 0, '2026-10-28 03:47:30', '2026-10-28 03:47:30', '<img src=\"http://localhost/uniwheels/assets/images/cars/car-1.jpg\" alt=\"2021 Toyota Vios 1.3 XLE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/car-1-rear.jpg\" alt=\"2021 Toyota Vios 1.3 XLE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/car-1-interior.jpg\" alt=\"2021 Toyota Vios 1.3 XLE\" />\n<p><strong>₱545,000</strong></p>\n<ul>\n<li>28,400 km</li>\n<li>Automatic</li>\n<li>Sedan</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Silver Metallic</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2021 Toyota Vios 1.3 XLE', '', 'trash', 'open', 'open', '', '2021-toyota-vios-1-3-xle__trashed', '', '', '2026-11-26 23:23:44', '2026-11-26 23:23:44', '', 0, 'http://localhost/uniwheels/2026/10/28/2021-toyota-vios-1-3-xle/', 0, 'post', '', 0),
(20, 0, '2026-10-28 03:47:30', '2026-10-28 03:47:30', '', 'car-1', '', 'inherit', 'open', 'closed', '', 'car-1', '', '', '2026-10-28 03:47:30', '2026-10-28 03:47:30', '', 19, 'http://localhost/uniwheels/2026/10/28/2021-toyota-vios-1-3-xle/car-1/', 0, 'attachment', 'image/jpeg', 0),
(21, 0, '2026-10-28 03:47:31', '2026-10-28 03:47:31', '<img src=\"http://localhost/uniwheels/assets/images/cars/car-2.jpg\" alt=\"2020 Honda Civic 1.8 E CVT\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/car-2-rear.jpg\" alt=\"2020 Honda Civic 1.8 E CVT\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/car-2-interior.jpg\" alt=\"2020 Honda Civic 1.8 E CVT\" />\n<p><strong>₱785,000</strong></p>\n<ul>\n<li>34,200 km</li>\n<li>Automatic</li>\n<li>Sedan</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Taffeta White</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2020 Honda Civic 1.8 E CVT', '', 'trash', 'open', 'open', '', '2020-honda-civic-1-8-e-cvt__trashed', '', '', '2026-11-26 23:23:39', '2026-11-26 23:23:39', '', 0, 'http://localhost/uniwheels/2026/10/28/2020-honda-civic-1-8-e-cvt/', 0, 'post', '', 0),
(22, 0, '2026-10-28 03:47:31', '2026-10-28 03:47:31', '', 'car-2', '', 'inherit', 'open', 'closed', '', 'car-2', '', '', '2026-10-28 03:47:31', '2026-10-28 03:47:31', '', 21, 'http://localhost/uniwheels/2026/10/28/2020-honda-civic-1-8-e-cvt/car-2/', 0, 'attachment', 'image/jpeg', 0),
(23, 0, '2026-10-28 03:47:31', '2026-10-28 03:47:31', '<img src=\"http://localhost/uniwheels/assets/images/cars/car-3.jpg\" alt=\"2019 Toyota Fortuner 2.4 G 4x2\" />\n<p><strong>₱1,050,000</strong></p>\n<ul>\n<li>52,800 km</li>\n<li>Automatic</li>\n<li>SUV</li>\n<li>Diesel</li>\n<li>7 Seats</li>\n<li>Pearl White</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2019 Toyota Fortuner 2.4 G 4x2', '', 'trash', 'open', 'open', '', '2019-toyota-fortuner-2-4-g-4x2__trashed', '', '', '2026-11-26 23:23:34', '2026-11-26 23:23:34', '', 0, 'http://localhost/uniwheels/2026/10/28/2019-toyota-fortuner-2-4-g-4x2/', 0, 'post', '', 0),
(24, 0, '2026-10-28 03:47:31', '2026-10-28 03:47:31', '', 'car-3', '', 'inherit', 'open', 'closed', '', 'car-3', '', '', '2026-10-28 03:47:31', '2026-10-28 03:47:31', '', 23, 'http://localhost/uniwheels/2026/10/28/2019-toyota-fortuner-2-4-g-4x2/car-3/', 0, 'attachment', 'image/jpeg', 0),
(25, 0, '2026-10-28 03:47:31', '2026-10-28 03:47:31', '<img src=\"http://localhost/uniwheels/assets/images/cars/car-4.jpg\" alt=\"2022 Suzuki Ertiga 1.5 GL\" />\n<p><strong>₱625,000</strong></p>\n<ul>\n<li>19,500 km</li>\n<li>Automatic</li>\n<li>MPV</li>\n<li>Gasoline</li>\n<li>7 Seats</li>\n<li>Titanium Grey Metallic</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2022 Suzuki Ertiga 1.5 GL', '', 'trash', 'open', 'open', '', '2022-suzuki-ertiga-1-5-gl__trashed', '', '', '2026-11-26 23:23:25', '2026-11-26 23:23:25', '', 0, 'http://localhost/uniwheels/2026/10/28/2022-suzuki-ertiga-1-5-gl/', 0, 'post', '', 0),
(26, 0, '2026-10-28 03:47:31', '2026-10-28 03:47:31', '', 'car-4', '', 'inherit', 'open', 'closed', '', 'car-4', '', '', '2026-10-28 03:47:31', '2026-10-28 03:47:31', '', 25, 'http://localhost/uniwheels/2026/10/28/2022-suzuki-ertiga-1-5-gl/car-4/', 0, 'attachment', 'image/jpeg', 0),
(27, 0, '2026-10-28 03:47:31', '2026-10-28 03:47:31', '<img src=\"http://localhost/uniwheels/assets/images/cars/518275072-676404038772996-17279697677145-20261127072240-48483c.jpg\" alt=\"2021 Ford Ranger Raptor 4X4\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/516193774-676404045439662-80522312448513-20261127072240-dc0f15.jpg\" alt=\"2021 Ford Ranger Raptor 4X4\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/517118130-676404105439656-10413145417777-20261127072240-62c1ad.jpg\" alt=\"2021 Ford Ranger Raptor 4X4\" />\n<p><strong>SOLD</strong></p>\n<ul>\n<li>38,100 km</li>\n<li>Automatic</li>\n<li>Pickup</li>\n<li>Diesel</li>\n<li>5 Seats</li>\n<li>Meteor Dark Grey</li>\n</ul>\n', 'SOLD: 2021 Ford Ranger Raptor 4X4', '', 'publish', 'open', 'open', '', '2021-ford-ranger-2-2-xls-4x2', '', '', '2026-11-27 04:29:58', '2026-11-27 04:29:58', '', 0, 'http://localhost/uniwheels/2026/10/28/2021-ford-ranger-2-2-xls-4x2/', 0, 'post', '', 0),
(28, 0, '2026-10-28 03:47:31', '2026-10-28 03:47:31', '', 'car-5', '', 'inherit', 'open', 'closed', '', 'car-5', '', '', '2026-10-28 03:47:31', '2026-10-28 03:47:31', '', 27, 'http://localhost/uniwheels/2026/10/28/2021-ford-ranger-2-2-xls-4x2/car-5/', 0, 'attachment', 'image/jpeg', 0),
(29, 0, '2026-10-28 03:47:31', '2026-10-28 03:47:31', '<img src=\"http://localhost/uniwheels/assets/images/cars/821123799-3475211326019298-7066073226767-20261127072038-74ea98.jpg\" alt=\"2022 Suzuki XL7\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/807284890-1750042849538158-8241564731426-20261127072038-ab19a4.jpg\" alt=\"2022 Suzuki XL7\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/823647978-846625391846077-11182998672250-20261127072038-badce9.jpg\" alt=\"2022 Suzuki XL7\" />\n<p><strong>No price yet</strong></p>\n<ul>\n<li>39,000 km</li>\n<li>Automatic</li>\n<li>Hatchback</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Sunrise Orange Pearl Metallic</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2022 Suzuki XL7', '', 'publish', 'open', 'open', '', '2022-suzuki-swift-1-2-gl', '', '', '2026-11-27 04:28:34', '2026-11-27 04:28:34', '', 0, 'http://localhost/uniwheels/2026/10/28/2022-suzuki-swift-1-2-gl/', 0, 'post', '', 0),
(30, 0, '2026-10-28 03:47:31', '2026-10-28 03:47:31', '', 'car-6', '', 'inherit', 'open', 'closed', '', 'car-6', '', '', '2026-10-28 03:47:31', '2026-10-28 03:47:31', '', 29, 'http://localhost/uniwheels/2026/10/28/2022-suzuki-swift-1-2-gl/car-6/', 0, 'attachment', 'image/jpeg', 0),
(31, 0, '2026-10-28 03:47:31', '2026-10-28 03:47:31', '<img src=\"http://localhost/uniwheels/assets/images/cars/906289458-1451581573757015-5721820078948-20261127071632-99ef29.jpg\" alt=\"2021 Toyota Vios XLE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/904517879-1163669052864969-1628288376993-20261127071632-e5b669.jpg\" alt=\"2021 Toyota Vios XLE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/905030438-4977627902465389-9649973114222-20261127071632-50c914.jpg\" alt=\"2021 Toyota Vios XLE\" />\n<p><strong>₱560,000</strong> · Reserved</p>\n<ul>\n<li>45,000 km</li>\n<li>Automatic</li>\n<li>SUV</li>\n<li>Hybrid</li>\n<li>5 Seats</li>\n<li>Silver Sky Metallic</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2021 Toyota Vios XLE', '', 'trash', 'open', 'open', '', '2021-toyota-rav4-2-5-hybrid__trashed', '', '', '2026-11-27 04:34:21', '2026-11-27 04:34:21', '', 0, 'http://localhost/uniwheels/2026/10/28/2021-toyota-rav4-2-5-hybrid/', 0, 'post', '', 0),
(32, 0, '2026-10-28 03:47:31', '2026-10-28 03:47:31', '', 'car-7', '', 'inherit', 'open', 'closed', '', 'car-7', '', '', '2026-10-28 03:47:31', '2026-10-28 03:47:31', '', 31, 'http://localhost/uniwheels/2026/10/28/2021-toyota-rav4-2-5-hybrid/car-7/', 0, 'attachment', 'image/jpeg', 0),
(33, 0, '2026-10-28 03:47:31', '2026-10-28 03:47:31', '<img src=\"http://localhost/uniwheels/assets/images/cars/903684460-1794503819343093-4983372071454-20261127071245-536356.jpg\" alt=\"2024 Nissan Terra VE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/902979174-1946591233386514-8581049774993-20261127071245-3e653a.jpg\" alt=\"2024 Nissan Terra VE\" />\n<p><strong>₱940,000</strong> <del>₱1,540,000</del> (price reduced) · Reserved</p>\n<ul>\n<li>13,000 km</li>\n<li>Automatic</li>\n<li>Sedan</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Lunar Metallic Black</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2024 Nissan Terra VE', '', 'publish', 'open', 'open', '', '2020-nissan-almera-1-5-e', '', '', '2026-11-27 05:56:23', '2026-11-27 05:56:23', '', 0, 'http://localhost/uniwheels/2026/10/28/2020-nissan-almera-1-5-e/', 0, 'post', '', 0),
(34, 0, '2026-10-28 03:47:31', '2026-10-28 03:47:31', '', 'car-8', '', 'inherit', 'open', 'closed', '', 'car-8', '', '', '2026-10-28 03:47:31', '2026-10-28 03:47:31', '', 33, 'http://localhost/uniwheels/2026/10/28/2020-nissan-almera-1-5-e/car-8/', 0, 'attachment', 'image/jpeg', 0),
(35, 0, '2026-10-28 03:47:47', '2026-10-28 03:47:47', '<img src=\"http://localhost/uniwheels/assets/images/cars/car-8.jpg\" alt=\"2020 Nissan Almera 1.5 E\" />\n<p><strong>SOLD</strong></p>\n<ul>\n<li>41,500 km</li>\n<li>Automatic</li>\n<li>Sedan</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Diamond Black</li>\n</ul>\n', 'SOLD: 2020 Nissan Almera 1.5 E', '', 'inherit', 'closed', 'closed', '', '33-revision-v1', '', '', '2026-10-28 03:47:47', '2026-10-28 03:47:47', '', 33, 'http://localhost/uniwheels/?p=35', 0, 'revision', '', 0),
(36, 0, '2026-10-28 03:47:47', '2026-10-28 03:47:47', '<img src=\"http://localhost/uniwheels/assets/images/cars/car-8.jpg\" alt=\"2020 Nissan Almera 1.5 E\" />\n<p><strong>₱415,000</strong></p>\n<ul>\n<li>41,500 km</li>\n<li>Automatic</li>\n<li>Sedan</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Diamond Black</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2020 Nissan Almera 1.5 E', '', 'inherit', 'closed', 'closed', '', '33-revision-v1', '', '', '2026-10-28 03:47:47', '2026-10-28 03:47:47', '', 33, 'http://localhost/uniwheels/?p=36', 0, 'revision', '', 0),
(38, 0, '2026-10-28 03:53:07', '2026-10-28 03:53:07', '<p>UniWheels Motors is a neighborhood pre-owned car dealer. We sell clean, checked secondhand cars for everyday families and drivers: sedans, hatchbacks, SUVs and pickups.</p>\n\n<h2>What we check on every car</h2>\n<ul>\n<li>Engine, transmission, brakes and electrical system, road-tested by our mechanics</li>\n<li>Real mileage and no hidden flood or accident damage</li>\n<li>Complete original OR/CR, LTO registration and HPG clearance, ready for transfer</li>\n</ul>\n\n<h2>Trade in your car</h2>\n<p>Bring your current car to the showroom for an honest trade-in quote. If you find a car you like, you can trade in and drive home your upgrade the same day.</p>\n\n<h2>Visit us</h2>\n<p>Walk-ins are always welcome. See our <a href=\"page.php?p=contact\">address and opening hours</a>, or message us on Messenger to reserve a viewing.</p>', 'About Us', '', 'inherit', 'closed', 'closed', '', '14-revision-v1', '', '', '2026-10-28 03:53:07', '2026-10-28 03:53:07', '', 14, 'http://localhost/uniwheels/?p=38', 0, 'revision', '', 0),
(39, 0, '2026-10-28 03:53:07', '2026-10-28 03:53:07', '<h2>Showroom</h2>\n<p>UniWheels Auto Mall, Main Highway Showroom Strip</p>\n\n<h2>Opening hours</h2>\n<ul>\n<li>Monday to Saturday: 8:30 AM to 6:30 PM</li>\n<li>Sunday: 9:00 AM to 5:00 PM</li>\n</ul>\n<p>Walk-ins are always welcome. No appointment needed.</p>\n\n<h2>Call or message us</h2>\n<ul>\n<li>Phone: (02) 8123-4567 / 0917-123-4567</li>\n<li>Email: sales@uniwheelsmotors.com</li>\n<li>Messenger: <a href=\"https://m.me/uniwheels\">m.me/uniwheels</a> (fastest reply)</li>\n</ul>', 'Contact Us', '', 'inherit', 'closed', 'closed', '', '16-revision-v1', '', '', '2026-10-28 03:53:07', '2026-10-28 03:53:07', '', 16, 'http://localhost/uniwheels/?p=39', 0, 'revision', '', 0),
(40, 0, '2026-10-28 03:53:07', '2026-10-28 03:53:07', '<h2>Prices and availability</h2>\n<p>Prices on this website are in Philippine pesos and may change without notice. A car shown as available may already be reserved or sold, so please message or call us to confirm before visiting.</p>\n\n<h2>Reservations</h2>\n<p>You can reserve a car with a reservation fee. Our staff will explain how long the reservation holds and whether the fee is refundable before you pay.</p>\n\n<h2>Inspection and test drive</h2>\n<p>You are welcome to inspect and test-drive any car at our showroom, and to bring your own mechanic.</p>\n\n<h2>Warranty</h2>\n<p>Warranty coverage depends on the car. Our staff will tell you what is covered and for how long, and it will be written in your sales agreement.</p>\n\n<h2>Papers</h2>\n<p>Every car comes with its original OR/CR. We assist with the transfer of ownership.</p>', 'Terms &amp; Warranty', '', 'inherit', 'closed', 'closed', '', '17-revision-v1', '', '', '2026-10-28 03:53:07', '2026-10-28 03:53:07', '', 17, 'http://localhost/uniwheels/?p=40', 0, 'revision', '', 0),
(41, 0, '2026-10-28 03:53:07', '2026-10-28 03:53:07', '<p>UniWheels Motors respects your privacy and follows the Philippine Data Privacy Act of 2012 (Republic Act No. 10173).</p>\n\n<h2>What we collect</h2>\n<p>When you message, call or visit us, we may keep your name, phone number, email, and the car you are interested in or want to trade in.</p>\n\n<h2>How we use it</h2>\n<ul>\n<li>To answer your questions and arrange viewings and test drives</li>\n<li>To prepare your reservation, sale and transfer papers</li>\n</ul>\n<p>We do not sell or share your details with other companies for marketing.</p>\n\n<h2>Messenger</h2>\n<p>Chats on Facebook Messenger are also covered by Meta\'s own privacy policy.</p>\n\n<h2>Your choices</h2>\n<p>You can ask us at any time to see, correct or delete your details. Email sales@uniwheelsmotors.com or tell our staff at the showroom.</p>', 'Privacy Policy', '', 'inherit', 'closed', 'closed', '', '3-revision-v1', '', '', '2026-10-28 03:53:07', '2026-10-28 03:53:07', '', 3, 'http://localhost/uniwheels/?p=41', 0, 'revision', '', 0),
(42, 0, '2026-10-28 03:53:07', '2026-10-28 03:53:07', '<h2>Welcome to UniWheels Performance</h2>\n<p class=\"lead\">UniWheels is an elite high-performance automotive dealership and bespoke forged wheel atelier dedicated to supercars, hypercars, and track-engineered platforms.</p>\n\n<div class=\"luxury-feature-grid\">\n  <div class=\"luxury-feature-box\">\n    <h4>🏎️ Hand-Curated Exotic Fleet</h4>\n    <p>From the Ferrari SF90 Stradale to the McLaren 765LT, our showroom hosts only the world\'s most capable and impeccably documented vehicles.</p>\n  </div>\n  <div class=\"luxury-feature-box\">\n    <h4>⚙️ Aerospace 6061-T6 Forged Wheels</h4>\n    <p>Every wheel is custom-engineered using 10,000+ tons of hydraulic forging pressure for millimeter-perfect caliper clearances and flush fender stance.</p>\n  </div>\n  <div class=\"luxury-feature-box\">\n    <h4>🤝 Face-to-Face VIP Consultations</h4>\n    <p>Skip impersonal forms and online guesswork. Experience our private showroom with dedicated 1-on-1 consultations, cold starts, and immediate trade appraisals.</p>\n  </div>\n</div>\n\n<p style=\"text-align:center;margin-top:2.5rem\">\n  <a href=\"#showcase\" class=\"btn-nav-inquire\" style=\"padding:14px 30px;font-size:0.9rem\">Explore Showroom Fleet →</a>\n</p>', 'Home — Showroom &amp; Exotic Fleet', '', 'inherit', 'closed', 'closed', '', '11-revision-v1', '', '', '2026-10-28 03:53:07', '2026-10-28 03:53:07', '', 11, 'http://localhost/uniwheels/?p=42', 0, 'revision', '', 0),
(43, 0, '2026-10-28 03:53:07', '2026-10-28 03:53:07', '<h2>Curated High-Performance Inventory</h2>\n<p>Each exotic vehicle in the UniWheels collection represents the pinnacle of contemporary automotive engineering, certified by our master technicians with 100-point mechanical and structural verification.</p>\n\n<div class=\"luxury-feature-grid\">\n  <div class=\"luxury-feature-box\">\n    <div style=\"font-size:0.75rem;color:var(--wheels-neon);font-family:var(--font-mono);font-weight:700\">★ RECOMMENDED SHOWCASE</div>\n    <h4>Ferrari SF90 Stradale Crimson Edition</h4>\n    <p><strong>Wheels:</strong> UW-F01 Monoblock Forged (21\" F / 22\" R)<br>\n    <strong>Finish:</strong> Brushed Candy Apple Crimson<br>\n    <strong>Status:</strong> Immediate Showroom Delivery Available</p>\n  </div>\n\n  <div class=\"luxury-feature-box\">\n    <div style=\"font-size:0.75rem;color:#ffab00;font-family:var(--font-mono);font-weight:700\">🔥 BESPOKE ALLOCATION</div>\n    <h4>Apollo Intensa Emozione Carbon/Crimson</h4>\n    <p><strong>Wheels:</strong> UW-R09 Carbon-Forged Track Spec (20\" F / 21\" R)<br>\n    <strong>Finish:</strong> Dark Crimson Tint / Gloss Carbon Lip<br>\n    <strong>Status:</strong> Centerlock Track Build (Bespoke)</p>\n  </div>\n\n  <div class=\"luxury-feature-box\">\n    <div style=\"font-size:0.75rem;color:#2ed573;font-family:var(--font-mono);font-weight:700\">● IN STOCK</div>\n    <h4>Porsche 911 GT3 RS Track Edition</h4>\n    <p><strong>Wheels:</strong> UW-GT3 Magnesium Monoblock (20\" F / 21\" R)<br>\n    <strong>Finish:</strong> Liquid Bronze / Crimson Outer Lip<br>\n    <strong>Status:</strong> Immediate Showroom Delivery Available</p>\n  </div>\n\n  <div class=\"luxury-feature-box\">\n    <div style=\"font-size:0.75rem;color:#38bdf8;font-family:var(--font-mono);font-weight:700\">🔥 BESPOKE ALLOCATION</div>\n    <h4>McLaren 765LT Carbon Longtail</h4>\n    <p><strong>Wheels:</strong> UW-LT9 Ultra-Light Track (19\" F / 20\" R)<br>\n    <strong>Finish:</strong> Candy Crimson Satin<br>\n    <strong>Status:</strong> Dual-Clutch Bespoke Longtail</p>\n  </div>\n\n  <div class=\"luxury-feature-box\">\n    <div style=\"font-size:0.75rem;color:#2ed573;font-family:var(--font-mono);font-weight:700\">● IN STOCK</div>\n    <h4>Lamborghini Huracán STO Squadra Corse</h4>\n    <p><strong>Wheels:</strong> UW-STO Aero-Forged Centerlock (20\" F / 20\" R)<br>\n    <strong>Finish:</strong> Matte Velvet Crimson<br>\n    <strong>Status:</strong> Naturally Aspirated V10 Track Weapon</p>\n  </div>\n\n  <div class=\"luxury-feature-box\">\n    <div style=\"font-size:0.75rem;color:#2ed573;font-family:var(--font-mono);font-weight:700\">● IN STOCK</div>\n    <h4>Chevrolet Corvette C8 Z06 Widebody</h4>\n    <p><strong>Wheels:</strong> UW-Z06 Concave Forged Monoblock (20\" F / 21\" R)<br>\n    <strong>Finish:</strong> Metallic Dark Wine Red<br>\n    <strong>Status:</strong> Flat-Plane Crank 8,600 RPM</p>\n  </div>\n</div>\n\n<p style=\"text-align:center;margin-top:2.5rem\">\n  <a href=\"#inquirySection\" class=\"btn-nav-inquire\" style=\"padding:14px 30px;font-size:0.9rem\">Schedule Private Inspection &amp; Test Drive →</a>\n</p>', 'Showroom Fleet &amp; Inventory', '', 'inherit', 'closed', 'closed', '', '12-revision-v1', '', '', '2026-10-28 03:53:07', '2026-10-28 03:53:07', '', 12, 'http://localhost/uniwheels/?p=43', 0, 'revision', '', 0),
(44, 0, '2026-10-28 03:53:07', '2026-10-28 03:53:07', '<h2>Private Showroom Appointments &amp; Consultations</h2>\n<p>Acquiring an exotic vehicle or custom forged wheel set should be personal, thorough, and effortless. We believe luxury clients should never have to guess technical wheel offsets or vehicle specifications online.</p>\n\n<div class=\"luxury-feature-grid\">\n  <div class=\"luxury-feature-box\">\n    <h4>1. Dedicated Showroom Host</h4>\n    <p>Enjoy exclusive private access to our showroom floor with zero crowds. Inspect your target vehicle, test cold starts, and examine paintwork under precision lighting.</p>\n  </div>\n\n  <div class=\"luxury-feature-box\">\n    <h4>2. Instant Physical Trade Appraisal</h4>\n    <p>Bring your existing vehicle for an immediate physical evaluation, diagnostic scan, and guaranteed buy-in figure while you consult with our team.</p>\n  </div>\n\n  <div class=\"luxury-feature-box\">\n    <h4>3. Enclosed Transport Logistics</h4>\n    <p>Receive comprehensive door-to-door white-glove transport quotes with full marine and road transit insurance included worldwide.</p>\n  </div>\n</div>\n\n<h3>Available Consultation Formats</h3>\n<ul>\n  <li><strong>Showroom Visit (Face-to-Face):</strong> 60-minute private walkthrough of our vehicle collection.</li>\n  <li><strong>Private VIP Viewing &amp; Test Drive:</strong> Supervised road testing and dynamic performance evaluation.</li>\n  <li><strong>Vehicle Acquisition &amp; Trade-In:</strong> Contract negotiation, cash purchase terms, or vehicle trade valuation.</li>\n  <li><strong>Custom Fleet Sourcing:</strong> Global allocation locating for limited-series exotics not currently on display.</li>\n</ul>\n\n<p style=\"text-align:center;margin-top:2.5rem\">\n  <a href=\"#inquirySection\" class=\"btn-nav-inquire\" style=\"padding:14px 30px;font-size:0.9rem\">Book Face-to-Face Consultation →</a>\n</p>', 'VIP Inquiries &amp; Consultations', '', 'inherit', 'closed', 'closed', '', '13-revision-v1', '', '', '2026-10-28 03:53:07', '2026-10-28 03:53:07', '', 13, 'http://localhost/uniwheels/?p=44', 0, 'revision', '', 0),
(45, 0, '2026-10-28 03:53:07', '2026-10-28 03:53:07', '<h2>Comprehensive Dealership Services</h2>\n<p>UniWheels offers end-to-end bespoke automotive services tailored for supercar owners and discerning collectors.</p>\n\n<div class=\"luxury-feature-grid\">\n  <div class=\"luxury-feature-box\">\n    <h4>🔄 Exotic Vehicle Trade-In &amp; Consignment</h4>\n    <p>Trade in your current exotic vehicle or let our senior brokers manage private consignment to verified buyers worldwide with guaranteed settlement.</p>\n  </div>\n\n  <div class=\"luxury-feature-box\">\n    <h4>⚡ Bespoke Forged Wheel Engineering</h4>\n    <p>Custom 1-piece Monoblock and 3-piece modular forged wheels tailored to your vehicle\'s exact suspension geometry and brake caliper dimensions.</p>\n  </div>\n\n  <div class=\"luxury-feature-box\">\n    <h4>🏁 Track Preparation &amp; Centerlock Conversions</h4>\n    <p>High-performance brake upgrades, centerlock hub conversions, and lightweight track wheel balancing with Michelin Cup 2R tires.</p>\n  </div>\n\n  <div class=\"luxury-feature-box\">\n    <h4>🚚 Enclosed White-Glove Transport</h4>\n    <p>Door-to-door climate-controlled enclosed transit across North America, Europe, and the Middle East with comprehensive insurance.</p>\n  </div>\n</div>\n\n<h3>Trade-In Evaluation Process</h3>\n<ol>\n  <li><strong>Initial Consultation:</strong> Submit your vehicle details via our VIP inquiry form or discuss directly in person.</li>\n  <li><strong>Physical Vehicle Inspection:</strong> Multi-point diagnostic scan, paint meter readings, and mechanical check in our private bay.</li>\n  <li><strong>Guaranteed Valuation:</strong> Immediate trade-in credit applied toward any vehicle in our showroom fleet.</li>\n</ol>', 'Dealership Services &amp; Trade-Ins', '', 'inherit', 'closed', 'closed', '', '15-revision-v1', '', '', '2026-10-28 03:53:07', '2026-10-28 03:53:07', '', 15, 'http://localhost/uniwheels/?p=45', 0, 'revision', '', 0),
(46, 0, '2026-11-26 23:04:05', '2026-11-26 23:04:05', '<img src=\"http://localhost/uniwheels/assets/images/cars/car-8.jpg\" alt=\"2024 NISSAN TERRA VE\" />\n<p><strong>SOLD</strong></p>\n<ul>\n<li>13,000 km</li>\n<li>Automatic</li>\n<li>Sedan</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Lunar Metallic Black</li>\n</ul>\n', 'SOLD: 2024 NISSAN TERRA VE', '', 'inherit', 'closed', 'closed', '', '33-revision-v1', '', '', '2026-11-26 23:04:05', '2026-11-26 23:04:05', '', 33, 'http://localhost/uniwheels/?p=46', 0, 'revision', '', 0),
(47, 0, '2026-11-26 23:05:40', '2026-11-26 23:05:40', '<img src=\"http://localhost/uniwheels/assets/images/cars/car-7.jpg\" alt=\"2021 Toyota RAV4 2.5 Hybrid\" />\n<p><strong>₱1,280,000</strong></p>\n<ul>\n<li>31,000 km</li>\n<li>Automatic</li>\n<li>SUV</li>\n<li>Hybrid</li>\n<li>5 Seats</li>\n<li>Silver Sky Metallic</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2021 Toyota RAV4 2.5 Hybrid', '', 'inherit', 'closed', 'closed', '', '31-revision-v1', '', '', '2026-11-26 23:05:40', '2026-11-26 23:05:40', '', 31, 'http://localhost/uniwheels/?p=47', 0, 'revision', '', 0),
(48, 0, '2026-11-26 23:08:36', '2026-11-26 23:08:36', '<img src=\"http://localhost/uniwheels/assets/images/cars/car-8.jpg\" alt=\"2024 NISSAN TERRA VE\" />\n<p><strong>₱1,540,000</strong></p>\n<ul>\n<li>13,000 km</li>\n<li>Automatic</li>\n<li>Sedan</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Lunar Metallic Black</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2024 NISSAN TERRA VE', '', 'inherit', 'closed', 'closed', '', '33-revision-v1', '', '', '2026-11-26 23:08:36', '2026-11-26 23:08:36', '', 33, 'http://localhost/uniwheels/?p=48', 0, 'revision', '', 0),
(49, 0, '2026-11-26 23:12:45', '2026-11-26 23:12:45', '<img src=\"http://localhost/uniwheels/assets/images/cars/car-8.jpg\" alt=\"2024 NISSAN TERRA VE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/903684460-1794503819343093-4983372071454-20261127071245-536356.jpg\" alt=\"2024 NISSAN TERRA VE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/902979174-1946591233386514-8581049774993-20261127071245-3e653a.jpg\" alt=\"2024 NISSAN TERRA VE\" />\n<p><strong>₱1,540,000</strong></p>\n<ul>\n<li>13,000 km</li>\n<li>Automatic</li>\n<li>Sedan</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Lunar Metallic Black</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2024 NISSAN TERRA VE', '', 'inherit', 'closed', 'closed', '', '33-revision-v1', '', '', '2026-11-26 23:12:45', '2026-11-26 23:12:45', '', 33, 'http://localhost/uniwheels/?p=49', 0, 'revision', '', 0),
(50, 0, '2026-11-26 23:13:05', '2026-11-26 23:13:05', '<img src=\"http://localhost/uniwheels/assets/images/cars/903684460-1794503819343093-4983372071454-20261127071245-536356.jpg\" alt=\"2024 NISSAN TERRA VE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/902979174-1946591233386514-8581049774993-20261127071245-3e653a.jpg\" alt=\"2024 NISSAN TERRA VE\" />\n<p><strong>₱1,540,000</strong></p>\n<ul>\n<li>13,000 km</li>\n<li>Automatic</li>\n<li>Sedan</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Lunar Metallic Black</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2024 NISSAN TERRA VE', '', 'inherit', 'closed', 'closed', '', '33-revision-v1', '', '', '2026-11-26 23:13:05', '2026-11-26 23:13:05', '', 33, 'http://localhost/uniwheels/?p=50', 0, 'revision', '', 0),
(51, 0, '2026-11-26 23:13:05', '2026-11-26 23:13:05', '', '903684460-1794503819343093-4983372071454-20261127071245-536356', '', 'inherit', 'open', 'closed', '', '903684460-1794503819343093-4983372071454-20261127071245-536356', '', '', '2026-11-26 23:13:05', '2026-11-26 23:13:05', '', 33, 'http://localhost/uniwheels/2026/10/28/2020-nissan-almera-1-5-e/903684460-1794503819343093-4983372071454-20261127071245-536356/', 0, 'attachment', 'image/jpeg', 0),
(52, 0, '2026-11-26 23:16:32', '2026-11-26 23:16:32', '<img src=\"http://localhost/uniwheels/assets/images/cars/906289458-1451581573757015-5721820078948-20261127071632-99ef29.jpg\" alt=\"2021 Toyota Vios XLE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/904517879-1163669052864969-1628288376993-20261127071632-e5b669.jpg\" alt=\"2021 Toyota Vios XLE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/905030438-4977627902465389-9649973114222-20261127071632-50c914.jpg\" alt=\"2021 Toyota Vios XLE\" />\n<p><strong>₱560,000</strong> <del>₱1,280,000</del> (price reduced)</p>\n<ul>\n<li>45,000 km</li>\n<li>Automatic</li>\n<li>SUV</li>\n<li>Hybrid</li>\n<li>5 Seats</li>\n<li>Silver Sky Metallic</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2021 Toyota Vios XLE', '', 'inherit', 'closed', 'closed', '', '31-revision-v1', '', '', '2026-11-26 23:16:32', '2026-11-26 23:16:32', '', 31, 'http://localhost/uniwheels/?p=52', 0, 'revision', '', 0),
(53, 0, '2026-11-26 23:16:32', '2026-11-26 23:16:32', '', '906289458-1451581573757015-5721820078948-20261127071632-99ef29', '', 'inherit', 'open', 'closed', '', '906289458-1451581573757015-5721820078948-20261127071632-99ef29', '', '', '2026-11-26 23:16:32', '2026-11-26 23:16:32', '', 31, 'http://localhost/uniwheels/2026/10/28/2021-toyota-rav4-2-5-hybrid/906289458-1451581573757015-5721820078948-20261127071632-99ef29/', 0, 'attachment', 'image/jpeg', 0),
(54, 0, '2026-11-26 23:20:38', '2026-11-26 23:20:38', '<img src=\"http://localhost/uniwheels/assets/images/cars/821123799-3475211326019298-7066073226767-20261127072038-74ea98.jpg\" alt=\"2022 Suzuki XL7\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/807284890-1750042849538158-8241564731426-20261127072038-ab19a4.jpg\" alt=\"2022 Suzuki XL7\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/823647978-846625391846077-11182998672250-20261127072038-badce9.jpg\" alt=\"2022 Suzuki XL7\" />\n<p><strong>₱840,000</strong></p>\n<ul>\n<li>39,000 km</li>\n<li>Automatic</li>\n<li>Hatchback</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Sunrise Orange Pearl Metallic</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2022 Suzuki XL7', '', 'inherit', 'closed', 'closed', '', '29-revision-v1', '', '', '2026-11-26 23:20:38', '2026-11-26 23:20:38', '', 29, 'http://localhost/uniwheels/?p=54', 0, 'revision', '', 0),
(55, 0, '2026-11-26 23:20:38', '2026-11-26 23:20:38', '', '821123799-3475211326019298-7066073226767-20261127072038-74ea98', '', 'inherit', 'open', 'closed', '', '821123799-3475211326019298-7066073226767-20261127072038-74ea98', '', '', '2026-11-26 23:20:38', '2026-11-26 23:20:38', '', 29, 'http://localhost/uniwheels/2026/10/28/2022-suzuki-swift-1-2-gl/821123799-3475211326019298-7066073226767-20261127072038-74ea98/', 0, 'attachment', 'image/jpeg', 0),
(56, 0, '2026-11-26 23:22:40', '2026-11-26 23:22:40', '<img src=\"http://localhost/uniwheels/assets/images/cars/518275072-676404038772996-17279697677145-20261127072240-48483c.jpg\" alt=\"2021 Ford Ranger Raptor 4X4\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/516193774-676404045439662-80522312448513-20261127072240-dc0f15.jpg\" alt=\"2021 Ford Ranger Raptor 4X4\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/517118130-676404105439656-10413145417777-20261127072240-62c1ad.jpg\" alt=\"2021 Ford Ranger Raptor 4X4\" />\n<p><strong>₱840,000</strong></p>\n<ul>\n<li>38,100 km</li>\n<li>Automatic</li>\n<li>Pickup</li>\n<li>Diesel</li>\n<li>5 Seats</li>\n<li>Meteor Dark Grey</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2021 Ford Ranger Raptor 4X4', '', 'inherit', 'closed', 'closed', '', '27-revision-v1', '', '', '2026-11-26 23:22:40', '2026-11-26 23:22:40', '', 27, 'http://localhost/uniwheels/?p=56', 0, 'revision', '', 0),
(57, 0, '2026-11-26 23:22:40', '2026-11-26 23:22:40', '', '518275072-676404038772996-17279697677145-20261127072240-48483c', '', 'inherit', 'open', 'closed', '', '518275072-676404038772996-17279697677145-20261127072240-48483c', '', '', '2026-11-26 23:22:40', '2026-11-26 23:22:40', '', 27, 'http://localhost/uniwheels/2026/10/28/2021-ford-ranger-2-2-xls-4x2/518275072-676404038772996-17279697677145-20261127072240-48483c/', 0, 'attachment', 'image/jpeg', 0),
(58, 0, '2026-11-26 23:23:07', '2026-11-26 23:23:07', '<img src=\"http://localhost/uniwheels/assets/images/cars/903684460-1794503819343093-4983372071454-20261127071245-536356.jpg\" alt=\"2024 Nissan Terra VE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/902979174-1946591233386514-8581049774993-20261127071245-3e653a.jpg\" alt=\"2024 Nissan Terra VE\" />\n<p><strong>₱1,540,000</strong></p>\n<ul>\n<li>13,000 km</li>\n<li>Automatic</li>\n<li>Sedan</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Lunar Metallic Black</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2024 Nissan Terra VE', '', 'inherit', 'closed', 'closed', '', '33-revision-v1', '', '', '2026-11-26 23:23:07', '2026-11-26 23:23:07', '', 33, 'http://localhost/uniwheels/?p=58', 0, 'revision', '', 0);
INSERT INTO `wp_posts` (`ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES
(59, 0, '2026-11-26 23:23:25', '2026-11-26 23:23:25', '<img src=\"http://localhost/uniwheels/assets/images/cars/car-4.jpg\" alt=\"2022 Suzuki Ertiga 1.5 GL\" />\n<p><strong>₱625,000</strong></p>\n<ul>\n<li>19,500 km</li>\n<li>Automatic</li>\n<li>MPV</li>\n<li>Gasoline</li>\n<li>7 Seats</li>\n<li>Titanium Grey Metallic</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2022 Suzuki Ertiga 1.5 GL', '', 'inherit', 'closed', 'closed', '', '25-revision-v1', '', '', '2026-11-26 23:23:25', '2026-11-26 23:23:25', '', 25, 'http://localhost/uniwheels/?p=59', 0, 'revision', '', 0),
(60, 0, '2026-11-26 23:23:34', '2026-11-26 23:23:34', '<img src=\"http://localhost/uniwheels/assets/images/cars/car-3.jpg\" alt=\"2019 Toyota Fortuner 2.4 G 4x2\" />\n<p><strong>₱1,050,000</strong></p>\n<ul>\n<li>52,800 km</li>\n<li>Automatic</li>\n<li>SUV</li>\n<li>Diesel</li>\n<li>7 Seats</li>\n<li>Pearl White</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2019 Toyota Fortuner 2.4 G 4x2', '', 'inherit', 'closed', 'closed', '', '23-revision-v1', '', '', '2026-11-26 23:23:34', '2026-11-26 23:23:34', '', 23, 'http://localhost/uniwheels/?p=60', 0, 'revision', '', 0),
(61, 0, '2026-11-26 23:23:39', '2026-11-26 23:23:39', '<img src=\"http://localhost/uniwheels/assets/images/cars/car-2.jpg\" alt=\"2020 Honda Civic 1.8 E CVT\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/car-2-rear.jpg\" alt=\"2020 Honda Civic 1.8 E CVT\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/car-2-interior.jpg\" alt=\"2020 Honda Civic 1.8 E CVT\" />\n<p><strong>₱785,000</strong></p>\n<ul>\n<li>34,200 km</li>\n<li>Automatic</li>\n<li>Sedan</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Taffeta White</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2020 Honda Civic 1.8 E CVT', '', 'inherit', 'closed', 'closed', '', '21-revision-v1', '', '', '2026-11-26 23:23:39', '2026-11-26 23:23:39', '', 21, 'http://localhost/uniwheels/?p=61', 0, 'revision', '', 0),
(62, 0, '2026-11-26 23:23:44', '2026-11-26 23:23:44', '<img src=\"http://localhost/uniwheels/assets/images/cars/car-1.jpg\" alt=\"2021 Toyota Vios 1.3 XLE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/car-1-rear.jpg\" alt=\"2021 Toyota Vios 1.3 XLE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/car-1-interior.jpg\" alt=\"2021 Toyota Vios 1.3 XLE\" />\n<p><strong>₱545,000</strong></p>\n<ul>\n<li>28,400 km</li>\n<li>Automatic</li>\n<li>Sedan</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Silver Metallic</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2021 Toyota Vios 1.3 XLE', '', 'inherit', 'closed', 'closed', '', '19-revision-v1', '', '', '2026-11-26 23:23:44', '2026-11-26 23:23:44', '', 19, 'http://localhost/uniwheels/?p=62', 0, 'revision', '', 0),
(63, 0, '2026-11-26 23:26:16', '2026-11-26 23:26:16', '<img src=\"http://localhost/uniwheels/assets/images/cars/906289458-1451581573757015-5721820078948-20261127071632-99ef29.jpg\" alt=\"2021 Toyota Vios XLE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/904517879-1163669052864969-1628288376993-20261127071632-e5b669.jpg\" alt=\"2021 Toyota Vios XLE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/905030438-4977627902465389-9649973114222-20261127071632-50c914.jpg\" alt=\"2021 Toyota Vios XLE\" />\n<p><strong>₱560,000</strong></p>\n<ul>\n<li>45,000 km</li>\n<li>Automatic</li>\n<li>SUV</li>\n<li>Hybrid</li>\n<li>5 Seats</li>\n<li>Silver Sky Metallic</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2021 Toyota Vios XLE', '', 'inherit', 'closed', 'closed', '', '31-revision-v1', '', '', '2026-11-26 23:26:16', '2026-11-26 23:26:16', '', 31, 'http://localhost/uniwheels/?p=63', 0, 'revision', '', 0),
(64, 0, '2026-11-27 02:47:53', '2026-11-27 02:47:53', '<img src=\"http://localhost/uniwheels/assets/images/cars/903684460-1794503819343093-4983372071454-20261127071245-536356.jpg\" alt=\"2024 Nissan Terra VE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/902979174-1946591233386514-8581049774993-20261127071245-3e653a.jpg\" alt=\"2024 Nissan Terra VE\" />\n<p><strong>₱1,540,000</strong> · Reserved</p>\n<ul>\n<li>13,000 km</li>\n<li>Automatic</li>\n<li>Sedan</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Lunar Metallic Black</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2024 Nissan Terra VE', '', 'inherit', 'closed', 'closed', '', '33-revision-v1', '', '', '2026-11-27 02:47:53', '2026-11-27 02:47:53', '', 33, 'http://localhost/uniwheels/?p=64', 0, 'revision', '', 0),
(65, 0, '2026-11-27 03:09:58', '2026-11-27 03:09:58', '<img src=\"http://localhost/uniwheels/assets/images/cars/821123799-3475211326019298-7066073226767-20261127072038-74ea98.jpg\" alt=\"2022 Suzuki XL7\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/807284890-1750042849538158-8241564731426-20261127072038-ab19a4.jpg\" alt=\"2022 Suzuki XL7\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/823647978-846625391846077-11182998672250-20261127072038-badce9.jpg\" alt=\"2022 Suzuki XL7\" />\n<p><strong>₱840,000</strong> · Reserved</p>\n<ul>\n<li>39,000 km</li>\n<li>Automatic</li>\n<li>Hatchback</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Sunrise Orange Pearl Metallic</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2022 Suzuki XL7', '', 'inherit', 'closed', 'closed', '', '29-revision-v1', '', '', '2026-11-27 03:09:58', '2026-11-27 03:09:58', '', 29, 'http://localhost/uniwheels/?p=65', 0, 'revision', '', 0),
(66, 0, '2026-11-27 03:10:08', '2026-11-27 03:10:08', '<img src=\"http://localhost/uniwheels/assets/images/cars/903684460-1794503819343093-4983372071454-20261127071245-536356.jpg\" alt=\"2024 Nissan Terra VE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/902979174-1946591233386514-8581049774993-20261127071245-3e653a.jpg\" alt=\"2024 Nissan Terra VE\" />\n<p><strong>₱1,540,000</strong></p>\n<ul>\n<li>13,000 km</li>\n<li>Automatic</li>\n<li>Sedan</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Lunar Metallic Black</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2024 Nissan Terra VE', '', 'inherit', 'closed', 'closed', '', '33-revision-v1', '', '', '2026-11-27 03:10:08', '2026-11-27 03:10:08', '', 33, 'http://localhost/uniwheels/?p=66', 0, 'revision', '', 0),
(67, 0, '2026-11-27 03:33:13', '2026-11-27 03:33:13', '<img src=\"http://localhost/uniwheels/assets/images/cars/903684460-1794503819343093-4983372071454-20261127071245-536356.jpg\" alt=\"2024 Nissan Terra VE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/902979174-1946591233386514-8581049774993-20261127071245-3e653a.jpg\" alt=\"2024 Nissan Terra VE\" />\n<p><strong>SOLD</strong></p>\n<ul>\n<li>13,000 km</li>\n<li>Automatic</li>\n<li>Sedan</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Lunar Metallic Black</li>\n</ul>\n', 'SOLD: 2024 Nissan Terra VE', '', 'inherit', 'closed', 'closed', '', '33-revision-v1', '', '', '2026-11-27 03:33:13', '2026-11-27 03:33:13', '', 33, 'http://localhost/uniwheels/?p=67', 0, 'revision', '', 0),
(68, 0, '2026-11-27 03:59:25', '2026-11-27 03:59:25', '<img src=\"http://localhost/uniwheels/assets/images/cars/821123799-3475211326019298-7066073226767-20261127072038-74ea98.jpg\" alt=\"2022 Suzuki XL7\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/807284890-1750042849538158-8241564731426-20261127072038-ab19a4.jpg\" alt=\"2022 Suzuki XL7\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/823647978-846625391846077-11182998672250-20261127072038-badce9.jpg\" alt=\"2022 Suzuki XL7\" />\n<p><strong>₱840,000</strong></p>\n<ul>\n<li>39,000 km</li>\n<li>Automatic</li>\n<li>Hatchback</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Sunrise Orange Pearl Metallic</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2022 Suzuki XL7', '', 'inherit', 'closed', 'closed', '', '29-revision-v1', '', '', '2026-11-27 03:59:25', '2026-11-27 03:59:25', '', 29, 'http://localhost/uniwheels/?p=68', 0, 'revision', '', 0),
(69, 0, '2026-11-27 04:01:21', '2026-11-27 04:01:21', '<img src=\"http://localhost/uniwheels/assets/images/cars/906289458-1451581573757015-5721820078948-20261127071632-99ef29.jpg\" alt=\"2021 Toyota Vios XLE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/904517879-1163669052864969-1628288376993-20261127071632-e5b669.jpg\" alt=\"2021 Toyota Vios XLE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/905030438-4977627902465389-9649973114222-20261127071632-50c914.jpg\" alt=\"2021 Toyota Vios XLE\" />\n<p><strong>SOLD</strong></p>\n<ul>\n<li>45,000 km</li>\n<li>Automatic</li>\n<li>SUV</li>\n<li>Hybrid</li>\n<li>5 Seats</li>\n<li>Silver Sky Metallic</li>\n</ul>\n', 'SOLD: 2021 Toyota Vios XLE', '', 'inherit', 'closed', 'closed', '', '31-revision-v1', '', '', '2026-11-27 04:01:21', '2026-11-27 04:01:21', '', 31, 'http://localhost/uniwheels/?p=69', 0, 'revision', '', 0),
(70, 0, '2026-11-27 04:28:34', '2026-11-27 04:28:34', '<img src=\"http://localhost/uniwheels/assets/images/cars/821123799-3475211326019298-7066073226767-20261127072038-74ea98.jpg\" alt=\"2022 Suzuki XL7\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/807284890-1750042849538158-8241564731426-20261127072038-ab19a4.jpg\" alt=\"2022 Suzuki XL7\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/823647978-846625391846077-11182998672250-20261127072038-badce9.jpg\" alt=\"2022 Suzuki XL7\" />\n<p><strong>No price yet</strong></p>\n<ul>\n<li>39,000 km</li>\n<li>Automatic</li>\n<li>Hatchback</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Sunrise Orange Pearl Metallic</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2022 Suzuki XL7', '', 'inherit', 'closed', 'closed', '', '29-revision-v1', '', '', '2026-11-27 04:28:34', '2026-11-27 04:28:34', '', 29, 'http://localhost/uniwheels/?p=70', 0, 'revision', '', 0),
(71, 0, '2026-11-27 04:28:52', '2026-11-27 04:28:52', '<img src=\"http://localhost/uniwheels/assets/images/cars/903684460-1794503819343093-4983372071454-20261127071245-536356.jpg\" alt=\"2024 Nissan Terra VE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/902979174-1946591233386514-8581049774993-20261127071245-3e653a.jpg\" alt=\"2024 Nissan Terra VE\" />\n<p><strong>₱1,540,000</strong></p>\n<ul>\n<li>13,000 km</li>\n<li>Automatic</li>\n<li>Sedan</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Lunar Metallic Black</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2024 Nissan Terra VE', '', 'inherit', 'closed', 'closed', '', '33-revision-v1', '', '', '2026-11-27 04:28:52', '2026-11-27 04:28:52', '', 33, 'http://localhost/uniwheels/?p=71', 0, 'revision', '', 0),
(72, 0, '2026-11-27 04:28:54', '2026-11-27 04:28:54', '<img src=\"http://localhost/uniwheels/assets/images/cars/906289458-1451581573757015-5721820078948-20261127071632-99ef29.jpg\" alt=\"2021 Toyota Vios XLE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/904517879-1163669052864969-1628288376993-20261127071632-e5b669.jpg\" alt=\"2021 Toyota Vios XLE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/905030438-4977627902465389-9649973114222-20261127071632-50c914.jpg\" alt=\"2021 Toyota Vios XLE\" />\n<p><strong>₱560,000</strong></p>\n<ul>\n<li>45,000 km</li>\n<li>Automatic</li>\n<li>SUV</li>\n<li>Hybrid</li>\n<li>5 Seats</li>\n<li>Silver Sky Metallic</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2021 Toyota Vios XLE', '', 'inherit', 'closed', 'closed', '', '31-revision-v1', '', '', '2026-11-27 04:28:54', '2026-11-27 04:28:54', '', 31, 'http://localhost/uniwheels/?p=72', 0, 'revision', '', 0),
(73, 0, '2026-11-27 04:29:58', '2026-11-27 04:29:58', '<img src=\"http://localhost/uniwheels/assets/images/cars/518275072-676404038772996-17279697677145-20261127072240-48483c.jpg\" alt=\"2021 Ford Ranger Raptor 4X4\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/516193774-676404045439662-80522312448513-20261127072240-dc0f15.jpg\" alt=\"2021 Ford Ranger Raptor 4X4\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/517118130-676404105439656-10413145417777-20261127072240-62c1ad.jpg\" alt=\"2021 Ford Ranger Raptor 4X4\" />\n<p><strong>SOLD</strong></p>\n<ul>\n<li>38,100 km</li>\n<li>Automatic</li>\n<li>Pickup</li>\n<li>Diesel</li>\n<li>5 Seats</li>\n<li>Meteor Dark Grey</li>\n</ul>\n', 'SOLD: 2021 Ford Ranger Raptor 4X4', '', 'inherit', 'closed', 'closed', '', '27-revision-v1', '', '', '2026-11-27 04:29:58', '2026-11-27 04:29:58', '', 27, 'http://localhost/uniwheels/?p=73', 0, 'revision', '', 0),
(74, 0, '2026-11-27 04:33:36', '2026-11-27 04:33:36', '<img src=\"http://localhost/uniwheels/assets/images/cars/906289458-1451581573757015-5721820078948-20261127071632-99ef29.jpg\" alt=\"2021 Toyota Vios XLE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/904517879-1163669052864969-1628288376993-20261127071632-e5b669.jpg\" alt=\"2021 Toyota Vios XLE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/905030438-4977627902465389-9649973114222-20261127071632-50c914.jpg\" alt=\"2021 Toyota Vios XLE\" />\n<p><strong>₱560,000</strong> · Reserved</p>\n<ul>\n<li>45,000 km</li>\n<li>Automatic</li>\n<li>SUV</li>\n<li>Hybrid</li>\n<li>5 Seats</li>\n<li>Silver Sky Metallic</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2021 Toyota Vios XLE', '', 'inherit', 'closed', 'closed', '', '31-revision-v1', '', '', '2026-11-27 04:33:36', '2026-11-27 04:33:36', '', 31, 'http://localhost/uniwheels/?p=74', 0, 'revision', '', 0),
(75, 0, '2026-11-27 05:38:22', '2026-11-27 05:38:22', '<img src=\"http://localhost/uniwheels/assets/images/cars/903684460-1794503819343093-4983372071454-20261127071245-536356.jpg\" alt=\"2024 Nissan Terra VE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/902979174-1946591233386514-8581049774993-20261127071245-3e653a.jpg\" alt=\"2024 Nissan Terra VE\" />\n<p><strong>₱1,540,000</strong> · Reserved</p>\n<ul>\n<li>13,000 km</li>\n<li>Automatic</li>\n<li>Sedan</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Lunar Metallic Black</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2024 Nissan Terra VE', '', 'inherit', 'closed', 'closed', '', '33-revision-v1', '', '', '2026-11-27 05:38:22', '2026-11-27 05:38:22', '', 33, 'http://localhost/uniwheels/?p=75', 0, 'revision', '', 0),
(76, 0, '2026-11-27 05:41:50', '2026-11-27 05:41:50', '<img src=\"http://localhost/uniwheels/assets/images/cars/929595700-1131954503059987-8383345773320-20261127134150-f32c71.jpg\" alt=\"2026 Toyota Vios XLE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/923007341-1854882875512809-2174799586414-20261127134150-a15d84.jpg\" alt=\"2026 Toyota Vios XLE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/926564040-1481656794029781-4475537576649-20261127134150-2abc35.jpg\" alt=\"2026 Toyota Vios XLE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/925679321-1821248868995864-1630867424223-20261127134150-8f1987.jpg\" alt=\"2026 Toyota Vios XLE\" />\n<p><strong>₱740,000</strong></p>\n<ul>\n<li>10,000 km</li>\n<li>Automatic</li>\n<li>Sedan</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Silver Metallic</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2026 Toyota Vios XLE', '', 'publish', 'open', 'open', '', '2026-toyota-vios-xle', '', '', '2026-11-27 05:41:57', '2026-11-27 05:41:57', '', 0, 'http://localhost/uniwheels/2026/11/27/2026-toyota-vios-xle/', 0, 'post', '', 0),
(77, 0, '2026-11-27 05:41:50', '2026-11-27 05:41:50', '', '923007341-1854882875512809-2174799586414-20261127134150-a15d84', '', 'inherit', 'open', 'closed', '', '923007341-1854882875512809-2174799586414-20261127134150-a15d84', '', '', '2026-11-27 05:41:50', '2026-11-27 05:41:50', '', 76, 'http://localhost/uniwheels/2026/11/27/2026-toyota-vios-xle/923007341-1854882875512809-2174799586414-20261127134150-a15d84/', 0, 'attachment', 'image/jpeg', 0),
(78, 0, '2026-11-27 05:41:57', '2026-11-27 05:41:57', '<img src=\"http://localhost/uniwheels/assets/images/cars/929595700-1131954503059987-8383345773320-20261127134150-f32c71.jpg\" alt=\"2026 Toyota Vios XLE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/923007341-1854882875512809-2174799586414-20261127134150-a15d84.jpg\" alt=\"2026 Toyota Vios XLE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/926564040-1481656794029781-4475537576649-20261127134150-2abc35.jpg\" alt=\"2026 Toyota Vios XLE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/925679321-1821248868995864-1630867424223-20261127134150-8f1987.jpg\" alt=\"2026 Toyota Vios XLE\" />\n<p><strong>₱740,000</strong></p>\n<ul>\n<li>10,000 km</li>\n<li>Automatic</li>\n<li>Sedan</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Silver Metallic</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2026 Toyota Vios XLE', '', 'inherit', 'closed', 'closed', '', '76-revision-v1', '', '', '2026-11-27 05:41:57', '2026-11-27 05:41:57', '', 76, 'http://localhost/uniwheels/?p=78', 0, 'revision', '', 0),
(79, 0, '2026-11-27 05:41:57', '2026-11-27 05:41:57', '', '929595700-1131954503059987-8383345773320-20261127134150-f32c71', '', 'inherit', 'open', 'closed', '', '929595700-1131954503059987-8383345773320-20261127134150-f32c71', '', '', '2026-11-27 05:41:57', '2026-11-27 05:41:57', '', 76, 'http://localhost/uniwheels/2026/11/27/2026-toyota-vios-xle/929595700-1131954503059987-8383345773320-20261127134150-f32c71/', 0, 'attachment', 'image/jpeg', 0),
(80, 0, '2026-11-27 05:45:38', '2026-11-27 05:45:38', '<img src=\"http://localhost/uniwheels/assets/images/cars/927531620-1140240652306023-5751639719405-20261127134538-d1e95d.jpg\" alt=\"2021 Nissan Navara VL 4X4\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/926368308-1634181675388831-8679791847837-20261127134538-5cc2a8.jpg\" alt=\"2021 Nissan Navara VL 4X4\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/922251431-1159637496525107-1075865070689-20261127134538-bb0ea7.jpg\" alt=\"2021 Nissan Navara VL 4X4\" />\n<p><strong>SOLD</strong></p>\n<ul>\n<li>33,000 km</li>\n<li>Automatic</li>\n<li>MPV</li>\n<li>Diesel</li>\n<li>5 Seats</li>\n<li>Forged Metallic Copper</li>\n</ul>\n', 'SOLD: 2021 Nissan Navara VL 4X4', '', 'publish', 'open', 'open', '', '2021-nissan-navara-vl-4x4', '', '', '2026-11-27 05:56:58', '2026-11-27 05:56:58', '', 0, 'http://localhost/uniwheels/2026/11/27/2021-nissan-navara-vl-4x4/', 0, 'post', '', 0),
(81, 0, '2026-11-27 05:45:38', '2026-11-27 05:45:38', '', '926368308-1634181675388831-8679791847837-20261127134538-5cc2a8', '', 'inherit', 'open', 'closed', '', '926368308-1634181675388831-8679791847837-20261127134538-5cc2a8', '', '', '2026-11-27 05:45:38', '2026-11-27 05:45:38', '', 80, 'http://localhost/uniwheels/2026/11/27/2021-nissan-navara-vl-4x4/926368308-1634181675388831-8679791847837-20261127134538-5cc2a8/', 0, 'attachment', 'image/jpeg', 0),
(82, 0, '2026-11-27 05:45:46', '2026-11-27 05:45:46', '<img src=\"http://localhost/uniwheels/assets/images/cars/927531620-1140240652306023-5751639719405-20261127134538-d1e95d.jpg\" alt=\"2021 Nissan Navara VL 4X4\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/926368308-1634181675388831-8679791847837-20261127134538-5cc2a8.jpg\" alt=\"2021 Nissan Navara VL 4X4\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/922251431-1159637496525107-1075865070689-20261127134538-bb0ea7.jpg\" alt=\"2021 Nissan Navara VL 4X4\" />\n<p><strong>₱980,000</strong></p>\n<ul>\n<li>33,000 km</li>\n<li>Automatic</li>\n<li>MPV</li>\n<li>Diesel</li>\n<li>5 Seats</li>\n<li>Forged Metallic Copper</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2021 Nissan Navara VL 4X4', '', 'inherit', 'closed', 'closed', '', '80-revision-v1', '', '', '2026-11-27 05:45:46', '2026-11-27 05:45:46', '', 80, 'http://localhost/uniwheels/?p=82', 0, 'revision', '', 0),
(83, 0, '2026-11-27 05:45:46', '2026-11-27 05:45:46', '', '927531620-1140240652306023-5751639719405-20261127134538-d1e95d', '', 'inherit', 'open', 'closed', '', '927531620-1140240652306023-5751639719405-20261127134538-d1e95d', '', '', '2026-11-27 05:45:46', '2026-11-27 05:45:46', '', 80, 'http://localhost/uniwheels/2026/11/27/2021-nissan-navara-vl-4x4/927531620-1140240652306023-5751639719405-20261127134538-d1e95d/', 0, 'attachment', 'image/jpeg', 0),
(84, 0, '2026-11-27 05:48:07', '2026-11-27 05:48:07', '<img src=\"http://localhost/uniwheels/assets/images/cars/925950403-1162391016174534-2821189245807-20261127134807-674767.jpg\" alt=\"2024 Toyota Avanza E.\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/933120975-1719542729695293-8005198839928-20261127134807-91d177.jpg\" alt=\"2024 Toyota Avanza E.\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/923592081-1930462861273704-7672850730833-20261127134807-ff1ee6.jpg\" alt=\"2024 Toyota Avanza E.\" />\n<p><strong>₱880,000</strong></p>\n<ul>\n<li>21,000 km</li>\n<li>Automatic</li>\n<li>SUV</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Dark Red Mica Metallic</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2024 Toyota Avanza E.', '', 'publish', 'open', 'open', '', '2024-toyota-avanza-e', '', '', '2026-11-27 05:48:12', '2026-11-27 05:48:12', '', 0, 'http://localhost/uniwheels/2026/11/27/2024-toyota-avanza-e/', 0, 'post', '', 0),
(85, 0, '2026-11-27 05:48:07', '2026-11-27 05:48:07', '', '933120975-1719542729695293-8005198839928-20261127134807-91d177', '', 'inherit', 'open', 'closed', '', '933120975-1719542729695293-8005198839928-20261127134807-91d177', '', '', '2026-11-27 05:48:07', '2026-11-27 05:48:07', '', 84, 'http://localhost/uniwheels/2026/11/27/2024-toyota-avanza-e/933120975-1719542729695293-8005198839928-20261127134807-91d177/', 0, 'attachment', 'image/jpeg', 0),
(86, 0, '2026-11-27 05:48:12', '2026-11-27 05:48:12', '<img src=\"http://localhost/uniwheels/assets/images/cars/925950403-1162391016174534-2821189245807-20261127134807-674767.jpg\" alt=\"2024 Toyota Avanza E.\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/933120975-1719542729695293-8005198839928-20261127134807-91d177.jpg\" alt=\"2024 Toyota Avanza E.\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/923592081-1930462861273704-7672850730833-20261127134807-ff1ee6.jpg\" alt=\"2024 Toyota Avanza E.\" />\n<p><strong>₱880,000</strong></p>\n<ul>\n<li>21,000 km</li>\n<li>Automatic</li>\n<li>SUV</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Dark Red Mica Metallic</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2024 Toyota Avanza E.', '', 'inherit', 'closed', 'closed', '', '84-revision-v1', '', '', '2026-11-27 05:48:12', '2026-11-27 05:48:12', '', 84, 'http://localhost/uniwheels/?p=86', 0, 'revision', '', 0),
(87, 0, '2026-11-27 05:48:12', '2026-11-27 05:48:12', '', '925950403-1162391016174534-2821189245807-20261127134807-674767', '', 'inherit', 'open', 'closed', '', '925950403-1162391016174534-2821189245807-20261127134807-674767', '', '', '2026-11-27 05:48:12', '2026-11-27 05:48:12', '', 84, 'http://localhost/uniwheels/2026/11/27/2024-toyota-avanza-e/925950403-1162391016174534-2821189245807-20261127134807-674767/', 0, 'attachment', 'image/jpeg', 0),
(88, 0, '2026-11-27 05:50:59', '2026-11-27 05:50:59', '<img src=\"http://localhost/uniwheels/assets/images/cars/926661241-1822986258768167-7784572486901-20261127135058-81f1b5.jpg\" alt=\"2023 Geely Coolray SE Sport\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/926503030-2222841575292117-2415607628230-20261127135058-669c41.jpg\" alt=\"2023 Geely Coolray SE Sport\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/923089574-1126273550195288-4341963838104-20261127135058-139c2f.jpg\" alt=\"2023 Geely Coolray SE Sport\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/917342919-2397204301024940-9219223280326-20261127135058-f14390.jpg\" alt=\"2023 Geely Coolray SE Sport\" />\n<p><strong>₱680,000</strong></p>\n<ul>\n<li>46,000 km</li>\n<li>Automatic</li>\n<li>SUV</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Pearl White</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2023 Geely Coolray SE Sport', '', 'publish', 'open', 'open', '', '2023-geely-coolray-se-sport', '', '', '2026-11-27 05:51:06', '2026-11-27 05:51:06', '', 0, 'http://localhost/uniwheels/2026/11/27/2023-geely-coolray-se-sport/', 0, 'post', '', 0),
(89, 0, '2026-11-27 05:50:59', '2026-11-27 05:50:59', '', '926503030-2222841575292117-2415607628230-20261127135058-669c41', '', 'inherit', 'open', 'closed', '', '926503030-2222841575292117-2415607628230-20261127135058-669c41', '', '', '2026-11-27 05:50:59', '2026-11-27 05:50:59', '', 88, 'http://localhost/uniwheels/2026/11/27/2023-geely-coolray-se-sport/926503030-2222841575292117-2415607628230-20261127135058-669c41/', 0, 'attachment', 'image/jpeg', 0),
(90, 0, '2026-11-27 05:51:06', '2026-11-27 05:51:06', '<img src=\"http://localhost/uniwheels/assets/images/cars/926661241-1822986258768167-7784572486901-20261127135058-81f1b5.jpg\" alt=\"2023 Geely Coolray SE Sport\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/926503030-2222841575292117-2415607628230-20261127135058-669c41.jpg\" alt=\"2023 Geely Coolray SE Sport\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/923089574-1126273550195288-4341963838104-20261127135058-139c2f.jpg\" alt=\"2023 Geely Coolray SE Sport\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/917342919-2397204301024940-9219223280326-20261127135058-f14390.jpg\" alt=\"2023 Geely Coolray SE Sport\" />\n<p><strong>₱680,000</strong></p>\n<ul>\n<li>46,000 km</li>\n<li>Automatic</li>\n<li>SUV</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Pearl White</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2023 Geely Coolray SE Sport', '', 'inherit', 'closed', 'closed', '', '88-revision-v1', '', '', '2026-11-27 05:51:06', '2026-11-27 05:51:06', '', 88, 'http://localhost/uniwheels/?p=90', 0, 'revision', '', 0),
(91, 0, '2026-11-27 05:51:06', '2026-11-27 05:51:06', '', '926661241-1822986258768167-7784572486901-20261127135058-81f1b5', '', 'inherit', 'open', 'closed', '', '926661241-1822986258768167-7784572486901-20261127135058-81f1b5', '', '', '2026-11-27 05:51:06', '2026-11-27 05:51:06', '', 88, 'http://localhost/uniwheels/2026/11/27/2023-geely-coolray-se-sport/926661241-1822986258768167-7784572486901-20261127135058-81f1b5/', 0, 'attachment', 'image/jpeg', 0),
(92, 0, '2026-11-27 05:53:30', '2026-11-27 05:53:30', '<img src=\"http://localhost/uniwheels/assets/images/cars/921965047-1489035546502598-1849434788978-20261127135330-40cd26.jpg\" alt=\"2026 Mitsubishi L300 FB\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/921424674-1133153129578875-6039982588693-20261127135330-9fd3b5.jpg\" alt=\"2026 Mitsubishi L300 FB\" />\n<p><strong>₱840,000</strong></p>\n<ul>\n<li>17,000 km</li>\n<li>Manual</li>\n<li>Pickup</li>\n<li>Diesel</li>\n<li>12 Seats</li>\n<li>Polar White</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2026 Mitsubishi L300 FB', '', 'publish', 'open', 'open', '', '2026-mitsubishi-l300-fb', '', '', '2026-11-27 06:15:43', '2026-11-27 06:15:43', '', 0, 'http://localhost/uniwheels/2026/11/27/2026-mitsubishi-l300-fb/', 0, 'post', '', 0),
(93, 0, '2026-11-27 05:53:30', '2026-11-27 05:53:30', '', '921965047-1489035546502598-1849434788978-20261127135330-40cd26', '', 'inherit', 'open', 'closed', '', '921965047-1489035546502598-1849434788978-20261127135330-40cd26', '', '', '2026-11-27 05:53:30', '2026-11-27 05:53:30', '', 92, 'http://localhost/uniwheels/2026/11/27/2026-mitsubishi-l300-fb/921965047-1489035546502598-1849434788978-20261127135330-40cd26/', 0, 'attachment', 'image/jpeg', 0),
(94, 0, '2026-11-27 05:53:35', '2026-11-27 05:53:35', '<img src=\"http://localhost/uniwheels/assets/images/cars/921424674-1133153129578875-6039982588693-20261127135330-9fd3b5.jpg\" alt=\"2026 Mitsubishi L300 FB\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/921965047-1489035546502598-1849434788978-20261127135330-40cd26.jpg\" alt=\"2026 Mitsubishi L300 FB\" />\n<p><strong>₱840,000</strong></p>\n<ul>\n<li>17,000 km</li>\n<li>Manual</li>\n<li>Pickup</li>\n<li>Diesel</li>\n<li>12 Seats</li>\n<li>Polar White</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2026 Mitsubishi L300 FB', '', 'inherit', 'closed', 'closed', '', '92-revision-v1', '', '', '2026-11-27 05:53:35', '2026-11-27 05:53:35', '', 92, 'http://localhost/uniwheels/?p=94', 0, 'revision', '', 0),
(95, 0, '2026-11-27 05:53:35', '2026-11-27 05:53:35', '', '921424674-1133153129578875-6039982588693-20261127135330-9fd3b5', '', 'inherit', 'open', 'closed', '', '921424674-1133153129578875-6039982588693-20261127135330-9fd3b5', '', '', '2026-11-27 05:53:35', '2026-11-27 05:53:35', '', 92, 'http://localhost/uniwheels/2026/11/27/2026-mitsubishi-l300-fb/921424674-1133153129578875-6039982588693-20261127135330-9fd3b5/', 0, 'attachment', 'image/jpeg', 0),
(96, 0, '2026-11-27 05:55:32', '2026-11-27 05:55:32', '<img src=\"http://localhost/uniwheels/assets/images/cars/921076874-1154771020640435-2591280224388-20261127135532-7f9e8e.jpg\" alt=\"2025 Hyundai Stargazer GL\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/921130358-1378733730866933-3644634542505-20261127135532-b55d95.jpg\" alt=\"2025 Hyundai Stargazer GL\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/926527455-1085176564564720-2196561089596-20261127135532-859322.jpg\" alt=\"2025 Hyundai Stargazer GL\" />\n<p><strong>SOLD</strong></p>\n<ul>\n<li>13,000 km</li>\n<li>Automatic</li>\n<li>Sedan</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Pearl White</li>\n</ul>\n', 'SOLD: 2025 Hyundai Stargazer GL', '', 'publish', 'open', 'open', '', '2025-hyundai-stargazer-gl', '', '', '2026-11-27 06:05:57', '2026-11-27 06:05:57', '', 0, 'http://localhost/uniwheels/2026/11/27/2025-hyundai-stargazer-gl/', 0, 'post', '', 0),
(97, 0, '2026-11-27 05:55:32', '2026-11-27 05:55:32', '', '921130358-1378733730866933-3644634542505-20261127135532-b55d95', '', 'inherit', 'open', 'closed', '', '921130358-1378733730866933-3644634542505-20261127135532-b55d95', '', '', '2026-11-27 05:55:32', '2026-11-27 05:55:32', '', 96, 'http://localhost/uniwheels/2026/11/27/2025-hyundai-stargazer-gl/921130358-1378733730866933-3644634542505-20261127135532-b55d95/', 0, 'attachment', 'image/jpeg', 0),
(98, 0, '2026-11-27 05:55:37', '2026-11-27 05:55:37', '<img src=\"http://localhost/uniwheels/assets/images/cars/921076874-1154771020640435-2591280224388-20261127135532-7f9e8e.jpg\" alt=\"2025 Hyundai Stargazer GL\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/921130358-1378733730866933-3644634542505-20261127135532-b55d95.jpg\" alt=\"2025 Hyundai Stargazer GL\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/926527455-1085176564564720-2196561089596-20261127135532-859322.jpg\" alt=\"2025 Hyundai Stargazer GL\" />\n<p><strong>₱860,000</strong></p>\n<ul>\n<li>13,000 km</li>\n<li>Automatic</li>\n<li>Sedan</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Pearl White</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2025 Hyundai Stargazer GL', '', 'inherit', 'closed', 'closed', '', '96-revision-v1', '', '', '2026-11-27 05:55:37', '2026-11-27 05:55:37', '', 96, 'http://localhost/uniwheels/?p=98', 0, 'revision', '', 0),
(99, 0, '2026-11-27 05:55:37', '2026-11-27 05:55:37', '', '921076874-1154771020640435-2591280224388-20261127135532-7f9e8e', '', 'inherit', 'open', 'closed', '', '921076874-1154771020640435-2591280224388-20261127135532-7f9e8e', '', '', '2026-11-27 05:55:37', '2026-11-27 05:55:37', '', 96, 'http://localhost/uniwheels/2026/11/27/2025-hyundai-stargazer-gl/921076874-1154771020640435-2591280224388-20261127135532-7f9e8e/', 0, 'attachment', 'image/jpeg', 0),
(100, 0, '2026-11-27 05:56:23', '2026-11-27 05:56:23', '<img src=\"http://localhost/uniwheels/assets/images/cars/903684460-1794503819343093-4983372071454-20261127071245-536356.jpg\" alt=\"2024 Nissan Terra VE\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/902979174-1946591233386514-8581049774993-20261127071245-3e653a.jpg\" alt=\"2024 Nissan Terra VE\" />\n<p><strong>₱940,000</strong> <del>₱1,540,000</del> (price reduced) · Reserved</p>\n<ul>\n<li>13,000 km</li>\n<li>Automatic</li>\n<li>Sedan</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Lunar Metallic Black</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2024 Nissan Terra VE', '', 'inherit', 'closed', 'closed', '', '33-revision-v1', '', '', '2026-11-27 05:56:23', '2026-11-27 05:56:23', '', 33, 'http://localhost/uniwheels/?p=100', 0, 'revision', '', 0),
(101, 0, '2026-11-27 05:56:58', '2026-11-27 05:56:58', '<img src=\"http://localhost/uniwheels/assets/images/cars/927531620-1140240652306023-5751639719405-20261127134538-d1e95d.jpg\" alt=\"2021 Nissan Navara VL 4X4\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/926368308-1634181675388831-8679791847837-20261127134538-5cc2a8.jpg\" alt=\"2021 Nissan Navara VL 4X4\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/922251431-1159637496525107-1075865070689-20261127134538-bb0ea7.jpg\" alt=\"2021 Nissan Navara VL 4X4\" />\n<p><strong>SOLD</strong></p>\n<ul>\n<li>33,000 km</li>\n<li>Automatic</li>\n<li>MPV</li>\n<li>Diesel</li>\n<li>5 Seats</li>\n<li>Forged Metallic Copper</li>\n</ul>\n', 'SOLD: 2021 Nissan Navara VL 4X4', '', 'inherit', 'closed', 'closed', '', '80-revision-v1', '', '', '2026-11-27 05:56:58', '2026-11-27 05:56:58', '', 80, 'http://localhost/uniwheels/?p=101', 0, 'revision', '', 0),
(102, 0, '2026-11-27 06:04:49', '2026-11-27 06:04:49', '<img src=\"http://localhost/uniwheels/assets/images/cars/921130358-1378733730866933-3644634542505-20261127135532-b55d95.jpg\" alt=\"2025 Hyundai Stargazer GL\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/921076874-1154771020640435-2591280224388-20261127135532-7f9e8e.jpg\" alt=\"2025 Hyundai Stargazer GL\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/926527455-1085176564564720-2196561089596-20261127135532-859322.jpg\" alt=\"2025 Hyundai Stargazer GL\" />\n<p><strong>₱860,000</strong></p>\n<ul>\n<li>13,000 km</li>\n<li>Automatic</li>\n<li>Sedan</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Pearl White</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2025 Hyundai Stargazer GL', '', 'inherit', 'closed', 'closed', '', '96-revision-v1', '', '', '2026-11-27 06:04:49', '2026-11-27 06:04:49', '', 96, 'http://localhost/uniwheels/?p=102', 0, 'revision', '', 0),
(103, 0, '2026-11-27 06:05:01', '2026-11-27 06:05:01', '<img src=\"http://localhost/uniwheels/assets/images/cars/921076874-1154771020640435-2591280224388-20261127135532-7f9e8e.jpg\" alt=\"2025 Hyundai Stargazer GL\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/921130358-1378733730866933-3644634542505-20261127135532-b55d95.jpg\" alt=\"2025 Hyundai Stargazer GL\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/926527455-1085176564564720-2196561089596-20261127135532-859322.jpg\" alt=\"2025 Hyundai Stargazer GL\" />\n<p><strong>₱860,000</strong></p>\n<ul>\n<li>13,000 km</li>\n<li>Automatic</li>\n<li>Sedan</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Pearl White</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2025 Hyundai Stargazer GL', '', 'inherit', 'closed', 'closed', '', '96-revision-v1', '', '', '2026-11-27 06:05:01', '2026-11-27 06:05:01', '', 96, 'http://localhost/uniwheels/?p=103', 0, 'revision', '', 0),
(104, 0, '2026-11-27 06:05:57', '2026-11-27 06:05:57', '<img src=\"http://localhost/uniwheels/assets/images/cars/921076874-1154771020640435-2591280224388-20261127135532-7f9e8e.jpg\" alt=\"2025 Hyundai Stargazer GL\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/921130358-1378733730866933-3644634542505-20261127135532-b55d95.jpg\" alt=\"2025 Hyundai Stargazer GL\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/926527455-1085176564564720-2196561089596-20261127135532-859322.jpg\" alt=\"2025 Hyundai Stargazer GL\" />\n<p><strong>SOLD</strong></p>\n<ul>\n<li>13,000 km</li>\n<li>Automatic</li>\n<li>Sedan</li>\n<li>Gasoline</li>\n<li>5 Seats</li>\n<li>Pearl White</li>\n</ul>\n', 'SOLD: 2025 Hyundai Stargazer GL', '', 'inherit', 'closed', 'closed', '', '96-revision-v1', '', '', '2026-11-27 06:05:57', '2026-11-27 06:05:57', '', 96, 'http://localhost/uniwheels/?p=104', 0, 'revision', '', 0),
(105, 0, '2026-11-27 06:15:43', '2026-11-27 06:15:43', '<img src=\"http://localhost/uniwheels/assets/images/cars/921965047-1489035546502598-1849434788978-20261127135330-40cd26.jpg\" alt=\"2026 Mitsubishi L300 FB\" />\n<img src=\"http://localhost/uniwheels/assets/images/cars/921424674-1133153129578875-6039982588693-20261127135330-9fd3b5.jpg\" alt=\"2026 Mitsubishi L300 FB\" />\n<p><strong>₱840,000</strong></p>\n<ul>\n<li>17,000 km</li>\n<li>Manual</li>\n<li>Pickup</li>\n<li>Diesel</li>\n<li>12 Seats</li>\n<li>Polar White</li>\n</ul>\n<p><a href=\"https://m.me/uniwheels\">Message us on Messenger to view or reserve this car</a></p>\n', '2026 Mitsubishi L300 FB', '', 'inherit', 'closed', 'closed', '', '92-revision-v1', '', '', '2026-11-27 06:15:43', '2026-11-27 06:15:43', '', 92, 'http://localhost/uniwheels/?p=105', 0, 'revision', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `wp_termmeta`
--

CREATE TABLE `wp_termmeta` (
  `meta_id` bigint(20) UNSIGNED NOT NULL,
  `term_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wp_terms`
--

CREATE TABLE `wp_terms` (
  `term_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL DEFAULT '',
  `slug` varchar(200) NOT NULL DEFAULT '',
  `term_group` bigint(10) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wp_terms`
--

INSERT INTO `wp_terms` (`term_id`, `name`, `slug`, `term_group`) VALUES
(1, 'Uncategorized', 'uncategorized', 0),
(2, 'twentytwentyfive', 'twentytwentyfive', 0),
(3, 'uniwheels', 'uniwheels', 0),
(4, 'Cars for sale', 'cars-for-sale', 0),
(5, 'Sold cars', 'sold-cars', 0);

-- --------------------------------------------------------

--
-- Table structure for table `wp_term_relationships`
--

CREATE TABLE `wp_term_relationships` (
  `object_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `term_taxonomy_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `term_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wp_term_relationships`
--

INSERT INTO `wp_term_relationships` (`object_id`, `term_taxonomy_id`, `term_order`) VALUES
(1, 1, 0),
(7, 2, 0),
(9, 3, 0),
(19, 4, 0),
(21, 4, 0),
(23, 4, 0),
(25, 4, 0),
(27, 5, 0),
(29, 4, 0),
(31, 4, 0),
(33, 4, 0),
(76, 4, 0),
(80, 5, 0),
(84, 4, 0),
(88, 4, 0),
(92, 4, 0),
(96, 5, 0);

-- --------------------------------------------------------

--
-- Table structure for table `wp_term_taxonomy`
--

CREATE TABLE `wp_term_taxonomy` (
  `term_taxonomy_id` bigint(20) UNSIGNED NOT NULL,
  `term_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `taxonomy` varchar(32) NOT NULL DEFAULT '',
  `description` longtext NOT NULL,
  `parent` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `count` bigint(20) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wp_term_taxonomy`
--

INSERT INTO `wp_term_taxonomy` (`term_taxonomy_id`, `term_id`, `taxonomy`, `description`, `parent`, `count`) VALUES
(1, 1, 'category', '', 0, 1),
(2, 2, 'wp_theme', '', 0, 1),
(3, 3, 'wp_theme', '', 0, 1),
(4, 4, 'category', '', 0, 6),
(5, 5, 'category', '', 0, 3);

-- --------------------------------------------------------

--
-- Table structure for table `wp_usermeta`
--

CREATE TABLE `wp_usermeta` (
  `umeta_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wp_usermeta`
--

INSERT INTO `wp_usermeta` (`umeta_id`, `user_id`, `meta_key`, `meta_value`) VALUES
(1, 1, 'nickname', 'root'),
(2, 1, 'first_name', ''),
(3, 1, 'last_name', ''),
(4, 1, 'description', ''),
(5, 1, 'rich_editing', 'true'),
(6, 1, 'syntax_highlighting', 'true'),
(7, 1, 'infinite_scrolling', 'true'),
(8, 1, 'comment_shortcuts', 'false'),
(9, 1, 'admin_color', 'modern'),
(10, 1, 'use_ssl', '0'),
(11, 1, 'show_admin_bar_front', 'true'),
(12, 1, 'locale', ''),
(13, 1, 'wp_capabilities', 'a:1:{s:13:\"administrator\";b:1;}'),
(14, 1, 'wp_user_level', '10'),
(15, 1, 'dismissed_wp_pointers', ''),
(16, 1, 'show_welcome_panel', '1'),
(17, 1, 'session_tokens', 'a:1:{s:64:\"912315ca51a41e9e30576ed2e964257ac4f2e8dd4678e47154e3079a108c4aff\";a:4:{s:10:\"expiration\";i:1793332221;s:2:\"ip\";s:9:\"127.0.0.1\";s:2:\"ua\";s:80:\"Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:159.0) Gecko/20100101 Firefox/159.0\";s:5:\"login\";i:1793159421;}}'),
(18, 1, 'wp_dashboard_quick_press_last_post_id', '37'),
(19, 1, 'community-events-location', 'a:1:{s:2:\"ip\";s:9:\"127.0.0.0\";}'),
(20, 1, 'wp_user-settings', 'mfold=o'),
(21, 1, 'wp_user-settings-time', '1790515189'),
(22, 1, 'wp_persisted_preferences', 'a:4:{s:4:\"core\";a:1:{s:26:\"isComplementaryAreaVisible\";b:0;}s:14:\"core/edit-post\";a:1:{s:12:\"welcomeGuide\";b:0;}s:9:\"_modified\";s:24:\"2026-09-27T15:15:57.741Z\";s:14:\"core/edit-site\";a:1:{s:12:\"welcomeGuide\";b:0;}}');

-- --------------------------------------------------------

--
-- Table structure for table `wp_users`
--

CREATE TABLE `wp_users` (
  `ID` bigint(20) UNSIGNED NOT NULL,
  `user_login` varchar(60) NOT NULL DEFAULT '',
  `user_pass` varchar(255) NOT NULL DEFAULT '',
  `user_nicename` varchar(50) NOT NULL DEFAULT '',
  `user_email` varchar(100) NOT NULL DEFAULT '',
  `user_url` varchar(100) NOT NULL DEFAULT '',
  `user_registered` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_activation_key` varchar(255) NOT NULL DEFAULT '',
  `user_status` int(11) NOT NULL DEFAULT 0,
  `display_name` varchar(250) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wp_users`
--

INSERT INTO `wp_users` (`ID`, `user_login`, `user_pass`, `user_nicename`, `user_email`, `user_url`, `user_registered`, `user_activation_key`, `user_status`, `display_name`) VALUES
(1, 'root', '$wp$2y$10$prWd5nK9FQk2BAP/tov0dOd8Ztkl5uo6gf4mQgWDpHMPtHiFyjbjK', 'root', 'ciedeejyrho.avelino@wvsu.edu.ph', 'http://localhost/uniwheels', '2026-09-27 13:09:50', '', 0, 'root');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_ref` (`booking_ref`),
  ADD KEY `idx_car_status` (`car_id`,`status`),
  ADD KEY `idx_booking_ref` (`booking_ref`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `uw_reservation_holds`
--
ALTER TABLE `uw_reservation_holds`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference` (`reference`),
  ADD KEY `active_car` (`car_id`,`state`,`expires_at`),
  ADD KEY `owner_lookup` (`owner_hash`,`state`);

--
-- Indexes for table `vehicle_bookings`
--
ALTER TABLE `vehicle_bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference` (`reference`),
  ADD KEY `car_hold` (`car_id`,`state`,`expires_at`),
  ADD KEY `owner_booking` (`owner_token`,`car_id`);

--
-- Indexes for table `wp_commentmeta`
--
ALTER TABLE `wp_commentmeta`
  ADD PRIMARY KEY (`meta_id`),
  ADD KEY `comment_id` (`comment_id`),
  ADD KEY `meta_key` (`meta_key`(191));

--
-- Indexes for table `wp_comments`
--
ALTER TABLE `wp_comments`
  ADD PRIMARY KEY (`comment_ID`),
  ADD KEY `comment_post_ID` (`comment_post_ID`),
  ADD KEY `comment_approved_date_gmt` (`comment_approved`,`comment_date_gmt`),
  ADD KEY `comment_date_gmt` (`comment_date_gmt`),
  ADD KEY `comment_parent` (`comment_parent`),
  ADD KEY `comment_author_email` (`comment_author_email`(10));

--
-- Indexes for table `wp_links`
--
ALTER TABLE `wp_links`
  ADD PRIMARY KEY (`link_id`),
  ADD KEY `link_visible` (`link_visible`);

--
-- Indexes for table `wp_options`
--
ALTER TABLE `wp_options`
  ADD PRIMARY KEY (`option_id`),
  ADD UNIQUE KEY `option_name` (`option_name`),
  ADD KEY `autoload` (`autoload`);

--
-- Indexes for table `wp_postmeta`
--
ALTER TABLE `wp_postmeta`
  ADD PRIMARY KEY (`meta_id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `meta_key` (`meta_key`(191));

--
-- Indexes for table `wp_posts`
--
ALTER TABLE `wp_posts`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `post_name` (`post_name`(191)),
  ADD KEY `type_status_date` (`post_type`,`post_status`,`post_date`,`ID`),
  ADD KEY `post_parent` (`post_parent`),
  ADD KEY `post_author` (`post_author`),
  ADD KEY `type_status_author` (`post_type`,`post_status`,`post_author`);

--
-- Indexes for table `wp_termmeta`
--
ALTER TABLE `wp_termmeta`
  ADD PRIMARY KEY (`meta_id`),
  ADD KEY `term_id` (`term_id`),
  ADD KEY `meta_key` (`meta_key`(191));

--
-- Indexes for table `wp_terms`
--
ALTER TABLE `wp_terms`
  ADD PRIMARY KEY (`term_id`),
  ADD KEY `slug` (`slug`(191)),
  ADD KEY `name` (`name`(191));

--
-- Indexes for table `wp_term_relationships`
--
ALTER TABLE `wp_term_relationships`
  ADD PRIMARY KEY (`object_id`,`term_taxonomy_id`),
  ADD KEY `term_taxonomy_id` (`term_taxonomy_id`);

--
-- Indexes for table `wp_term_taxonomy`
--
ALTER TABLE `wp_term_taxonomy`
  ADD PRIMARY KEY (`term_taxonomy_id`),
  ADD UNIQUE KEY `term_id_taxonomy` (`term_id`,`taxonomy`),
  ADD KEY `taxonomy` (`taxonomy`);

--
-- Indexes for table `wp_usermeta`
--
ALTER TABLE `wp_usermeta`
  ADD PRIMARY KEY (`umeta_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `meta_key` (`meta_key`(191));

--
-- Indexes for table `wp_users`
--
ALTER TABLE `wp_users`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `user_login_key` (`user_login`),
  ADD KEY `user_nicename` (`user_nicename`),
  ADD KEY `user_email` (`user_email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `uw_reservation_holds`
--
ALTER TABLE `uw_reservation_holds`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `vehicle_bookings`
--
ALTER TABLE `vehicle_bookings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `wp_commentmeta`
--
ALTER TABLE `wp_commentmeta`
  MODIFY `meta_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wp_comments`
--
ALTER TABLE `wp_comments`
  MODIFY `comment_ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wp_links`
--
ALTER TABLE `wp_links`
  MODIFY `link_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wp_options`
--
ALTER TABLE `wp_options`
  MODIFY `option_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=364;

--
-- AUTO_INCREMENT for table `wp_postmeta`
--
ALTER TABLE `wp_postmeta`
  MODIFY `meta_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=194;

--
-- AUTO_INCREMENT for table `wp_posts`
--
ALTER TABLE `wp_posts`
  MODIFY `ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `wp_termmeta`
--
ALTER TABLE `wp_termmeta`
  MODIFY `meta_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wp_terms`
--
ALTER TABLE `wp_terms`
  MODIFY `term_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `wp_term_taxonomy`
--
ALTER TABLE `wp_term_taxonomy`
  MODIFY `term_taxonomy_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `wp_usermeta`
--
ALTER TABLE `wp_usermeta`
  MODIFY `umeta_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `wp_users`
--
ALTER TABLE `wp_users`
  MODIFY `ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `uw_reservation_holds`
--
ALTER TABLE `uw_reservation_holds`
  ADD CONSTRAINT `uw_reservation_holds_ibfk_1` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`);

--
-- Constraints for table `vehicle_bookings`
--
ALTER TABLE `vehicle_bookings`
  ADD CONSTRAINT `vehicle_booking_car` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
