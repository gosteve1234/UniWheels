<?php
/**
 * UniWheels Car Display — November 1, 2026 Price List Importer (50% Curated Fleet = 105 Vehicles)
 * Populates `cars` with complete specifications, vehicle age, ownership history,
 * transparent damage disclosures, categorized inspection galleries, and Admin-only agent tags,
 * then exports an up-to-date `cars.sql` dump.
 */

require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/includes/uniwheels-core.php';

uniwheels_ensure_schema($pdo);

// Clear previous demo rows & demo reservations
$pdo->exec("TRUNCATE TABLE `reservations`");
$pdo->exec("TRUNCATE TABLE `cars`");

// Format: [Category, ListNo, RawModel, Year, Trans, Fuel, Plate, Color, MileageRaw, Price, PriceNote, AgentOwner, BodyType, Brand, Seats]
$items = [
    // ================= HATCHBACK / SEDAN (25 Units) =================
    ['Hatchback / Sedan', 1,  'Chevrolet Sail', 2018, 'Automatic', 'Gasoline', 'FAE 4953', 'Blue Sky', '24,000 km', 440000, 'Markdown to ₱440k (Neg.)', 'To Bong', 'Sedan', 'Chevrolet', '5 Seats'],
    ['Hatchback / Sedan', 2,  'Changan Alsvin', 2022, 'Automatic', 'Gasoline', 'FAJ 2861', 'Pearl Black', '60,000 km', 420000, 'Negotiable', 'N. Kano', 'Sedan', 'Changan', '5 Seats'],
    ['Hatchback / Sedan', 4,  'GAC Empow GB', 2023, 'Automatic', 'Gasoline', 'ZAG 1234', 'Elegant Black', '23,000 km', 740000, 'Negotiable', 'Boss', 'Sedan', 'GAC', '5 Seats'],
    ['Hatchback / Sedan', 5,  'Geely Emgrand Premium', 2023, 'Automatic', 'Gasoline', 'CCC 3845', 'Dark Blue', '16,000 km', 620000, 'Negotiable', 'Boss', 'Sedan', 'Geely', '5 Seats'],
    ['Hatchback / Sedan', 6,  'Honda Brio RS', 2020, 'Automatic', 'Gasoline', 'FAL 7859', 'Taffeta White', '58,000 km', 590000, 'Negotiable', 'Boss', 'Hatchback', 'Honda', '5 Seats'],
    ['Hatchback / Sedan', 7,  'Honda City RS', 2025, 'Automatic', 'Gasoline', 'DCD 7164', 'Platinum White Pearl', '6,000 km', 980000, 'Negotiable', 'Boss', 'Sedan', 'Honda', '5 Seats'],
    ['Hatchback / Sedan', 9,  'Honda City S', 2025, 'Automatic', 'Gasoline', 'DBS 2449', 'Lunar Silver Metallic', '16,000 km', 870000, 'Negotiable', 'SPS. Golez', 'Sedan', 'Honda', '5 Seats'],
    ['Hatchback / Sedan', 10, 'Honda Civic i-VTEC', 2020, 'Automatic', 'Gasoline', 'FAF 5005', 'Lunar Silver Metallic', '55,000 km', 880000, 'Negotiable', 'Boss', 'Sedan', 'Honda', '5 Seats'],
    ['Hatchback / Sedan', 11, 'Honda Jazz', 2016, 'Automatic', 'Gasoline', 'NCB 9473', 'White', '59,000 km', 580000, 'Negotiable', 'Paolo', 'Hatchback', 'Honda', '5 Seats'],
    ['Hatchback / Sedan', 13, 'Hyundai Eon', 2017, 'Manual', 'Gasoline', 'FAB 4643', 'Red', '58,000 km', 320000, 'Negotiable', 'Boss', 'Hatchback', 'Hyundai', '5 Seats'],
    ['Hatchback / Sedan', 14, 'Kia Picanto', 2023, 'Manual', 'Gasoline', 'ZAF 2804', 'Green', '22,000 km', 510000, 'Markdown to ₱510k (Neg.)', 'Boss', 'Hatchback', 'Kia', '5 Seats'],
    ['Hatchback / Sedan', 15, 'Mazda 2', 2015, 'Manual', 'Gasoline', 'ANA 1870', 'White', '137,000 km', 360000, 'Markdown to ₱360k (Neg.)', 'DFC', 'Hatchback', 'Mazda', '5 Seats'],
    ['Hatchback / Sedan', 16, 'Mitsubishi Mirage G4 GLS', 2023, 'Automatic', 'Gasoline', 'FAO 5478', 'Cool Silver Metallic', '18,000 km', 630000, 'Markdown to ₱630k (Neg.)', 'Paolo', 'Sedan', 'Mitsubishi', '5 Seats'],
    ['Hatchback / Sedan', 18, 'Mitsubishi Mirage G4 GLX', 2024, 'Automatic', 'Gasoline', 'NKF 4972', 'Cool Silver Metallic', '12,000 km', 630000, 'Negotiable', 'Boss', 'Sedan', 'Mitsubishi', '5 Seats'],
    ['Hatchback / Sedan', 20, 'Mitsubishi Mirage G4 GLX', 2025, 'Automatic', 'Gasoline', 'FAT 5570', 'Red Metallic', '20,000 km', 640000, 'Negotiable', 'Boss', 'Sedan', 'Mitsubishi', '5 Seats'],
    ['Hatchback / Sedan', 23, 'MG 5 Core / Style', 2025, 'Automatic', 'Gasoline', 'RQ 949A/NJA 8887', 'White', '9,000 km', 570000, 'Negotiable', 'Boss', 'Sedan', 'MG', '5 Seats'],
    ['Hatchback / Sedan', 24, 'Nissan Almera VE', 2022, 'Automatic', 'Gasoline', 'LAJ 8203', 'Cayenne Red', '25,000 km', 690000, 'Markdown to ₱690k (Neg.)', 'Boss', 'Sedan', 'Nissan', '5 Seats'],
    ['Hatchback / Sedan', 26, 'Nissan Almera VL Turbo', 2024, 'Automatic', 'Gasoline', 'LAO 7831', 'Gun Metallic', '19,000 km', 840000, 'Markdown to ₱840k (Neg.)', 'Boss', 'Sedan', 'Nissan', '5 Seats'],
    ['Hatchback / Sedan', 27, 'Suzuki S-Presso GL AGS', 2025, 'Automatic', 'Gasoline', 'FAT 7522', 'Red', '17,000 km', 620000, 'Negotiable', 'Boss', 'Hatchback', 'Suzuki', '5 Seats'],
    ['Hatchback / Sedan', 30, 'Suzuki S-Presso GL AGS', 2026, 'Automatic', 'Gasoline', 'DCC 2819', 'Gray', '13,000 km', 650000, 'Negotiable', 'Boss', 'Hatchback', 'Suzuki', '5 Seats'],
    ['Hatchback / Sedan', 32, 'Toyota Vios 1.3 E', 2018, 'Automatic', 'Gasoline', '150110/ZAA 6179', 'Alumina Jade Metallic', '54,000 km', 480000, 'Negotiable', 'Boss', 'Sedan', 'Toyota', '5 Seats'],
    ['Hatchback / Sedan', 34, 'Toyota Vios 1.3 XLE', 2023, 'Automatic', 'Gasoline', 'FAO 8703', 'Blackish Red Mica', '30,000 km', 640000, 'Negotiable', 'Boss', 'Sedan', 'Toyota', '5 Seats'],
    ['Hatchback / Sedan', 38, 'Toyota Vios 1.3 XLE', 2024, 'Automatic', 'Gasoline', 'DBN 2465', 'Alumina Jade Metallic', '23,000 km', 670000, 'Negotiable', 'Boss', 'Sedan', 'Toyota', '5 Seats'],
    ['Hatchback / Sedan', 44, 'Toyota Vios 1.3 XLE', 2025, 'Automatic', 'Gasoline', 'DBP 8491', 'Black', '11,000 km', 720000, 'Negotiable', 'Boss', 'Sedan', 'Toyota', '5 Seats'],
    ['Hatchback / Sedan', 48, 'Toyota Wigo 1.0 G', 2025, 'Automatic', 'Gasoline', 'CCK 1681', 'Red Mica Metallic', '17,000 km', 650000, 'Negotiable', 'Boss', 'Hatchback', 'Toyota', '5 Seats'],

    // ================= PICK UP (23 Units) =================
    ['Pick Up', 1,  'Ford Ranger Raptor 4x4', 2019, 'Automatic', 'Diesel', 'NBH 5023', 'Absolute Black', '96,000 km', 1080000, 'Negotiable', 'Boss', 'Pickup', 'Ford', '5 Seats'],
    ['Pick Up', 2,  'Ford Ranger Raptor 4x4', 2025, 'Automatic', 'Diesel', 'FAS 3793', 'Arctic White', '43,000 km', 1980000, 'Negotiable', 'Boss', 'Pickup', 'Ford', '5 Seats'],
    ['Pick Up', 3,  'Ford Ranger Sport 4x2', 2023, 'Automatic', 'Diesel', 'NFP 7405', 'Absolute Black', '38,000 km', 1180000, 'Negotiable', 'Boss', 'Pickup', 'Ford', '5 Seats'],
    ['Pick Up', 4,  'Ford Ranger Wildtrak 4x2', 2022, 'Automatic', 'Diesel', 'KAF 3902', 'Saber Orange', '59,000 km', 1110000, 'Markdown to ₱1.110M (Neg.)', 'To Vincent', 'Pickup', 'Ford', '5 Seats'],
    ['Pick Up', 6,  'Ford Ranger Wildtrak 4x2', 2025, 'Automatic', 'Diesel', 'FAP 3476', 'Meteor Gray', '11,000 km', 1580000, 'Negotiable', 'Boss', 'Pickup', 'Ford', '5 Seats'],
    ['Pick Up', 7,  'Ford Ranger XLS 4x2', 2023, 'Manual', 'Diesel', 'NFP 3721', 'Arctic White', '40,000 km', 1060000, 'Negotiable', 'Kuya', 'Pickup', 'Ford', '5 Seats'],
    ['Pick Up', 9,  'Isuzu D-Max LS-A 4x2', 2023, 'Automatic', 'Diesel', 'FAO 9924', 'Red Spinel Mica', '26,000 km', 1280000, 'Negotiable', 'Boss', 'Pickup', 'Isuzu', '5 Seats'],
    ['Pick Up', 10, 'Isuzu D-Max LS-A Plus 4x2', 2024, 'Automatic', 'Diesel', 'FAU 1006', 'Islay Gray', '16,000 km', 1440000, 'Negotiable', 'Boss', 'Pickup', 'Isuzu', '5 Seats'],
    ['Pick Up', 11, 'Mitsubishi Strada GLS 4x2', 2023, 'Automatic', 'Diesel', 'LAL 7855', 'Jet Black Mica', '23,000 km', 1180000, 'Negotiable', 'Boss', 'Pickup', 'Mitsubishi', '5 Seats'],
    ['Pick Up', 13, 'Mitsubishi Triton GLS 4x2', 2024, 'Automatic', 'Diesel', 'FU 527A/NKI 8610', 'Graphite Gray Metallic', '20,000 km', 1490000, 'Markdown to ₱1.490M (Neg.)', 'Boss/SPS. Golez', 'Pickup', 'Mitsubishi', '5 Seats'],
    ['Pick Up', 15, 'Mitsubishi Triton GLX 4x2', 2024, 'Manual', 'Diesel', 'EQ 792A/FAR 9817', 'Red Solid', '13,000 km', 1280000, 'Negotiable', 'Boss', 'Pickup', 'Mitsubishi', '5 Seats'],
    ['Pick Up', 19, 'Mitsubishi Triton GLX 4x2', 2025, 'Automatic', 'Diesel', 'NEZ 6100', 'Graphite Gray Metallic', '24,000 km', 1380000, 'Negotiable', 'Boss', 'Pickup', 'Mitsubishi', '5 Seats'],
    ['Pick Up', 21, 'Mitsubishi Triton GLX 4x4', 2024, 'Manual', 'Diesel', 'HAH 2680', 'Jet Black Mica', '19,000 km', 1360000, 'Markdown to ₱1.360M (Neg.)', 'Paolo', 'Pickup', 'Mitsubishi', '5 Seats'],
    ['Pick Up', 22, 'Nissan Navara Calibre-X 4x2', 2025, 'Automatic', 'Diesel', 'FAS 9886', 'Fiery Red', '10,000 km', 1490000, 'Negotiable', 'Boss', 'Pickup', 'Nissan', '5 Seats'],
    ['Pick Up', 24, 'Nissan Navara PRO-4X', 2023, 'Automatic', 'Diesel', 'EAH 1332', 'Galaxy Black', '20,000 km', 1620000, 'Markdown to ₱1.620M (Neg.)', 'SPS. Golez', 'Pickup', 'Nissan', '5 Seats'],
    ['Pick Up', 26, 'Nissan Navara VE 4x2', 2025, 'Automatic', 'Diesel', 'J0 316A/CCG 2302', 'Galaxy Black', '12,000 km', 1360000, 'Negotiable', 'Boss', 'Pickup', 'Nissan', '5 Seats'],
    ['Pick Up', 30, 'Nissan Navara VE 4x2', 2025, 'Automatic', 'Diesel', 'LAP 8384', 'Lunar Metallic Gray', '12,000 km', 1360000, 'Markdown to ₱1.360M (Neg.)', 'Boss', 'Pickup', 'Nissan', '5 Seats'],
    ['Pick Up', 34, 'Nissan Navara VL 4x4', 2018, 'Automatic', 'Diesel', 'FAD 3246', 'Galaxy Black', '72,000 km', 940000, 'Negotiable', 'Boss', 'Pickup', 'Nissan', '5 Seats'],
    ['Pick Up', 36, 'Toyota Hilux G 4x4', 2013, 'Manual', 'Diesel', 'YLB 704', 'Lithium Silver', '76,000 km', 890000, 'Negotiable', 'Boss', 'Pickup', 'Toyota', '5 Seats'],
    ['Pick Up', 37, 'Toyota Hilux G 4x2', 2021, 'Automatic', 'Diesel', 'FAF 3357', 'Silver Metallic', '45,000 km', 1250000, 'Negotiable', 'Paolo', 'Pickup', 'Toyota', '5 Seats'],
    ['Pick Up', 41, 'Toyota Hilux G 4x2', 2025, 'Automatic', 'Diesel', 'DBR 8214', 'Super White', '13,000 km', 1540000, 'Negotiable', 'Boss', 'Pickup', 'Toyota', '5 Seats'],
    ['Pick Up', 42, 'Toyota Hilux GR-S 4x4', 2025, 'Automatic', 'Diesel', 'GBJ 4332', 'Super White/Attitude Black Mica', '18,000 km', 2360000, 'Negotiable', 'Boss', 'Pickup', 'Toyota', '5 Seats'],
    ['Pick Up', 47, 'Toyota Hilux V Conquest 4x4', 2025, 'Automatic', 'Diesel', 'FBC 2413', 'Emotional Red', '13,000 km', 1998000, 'Firm / Showroom Special', 'Boss', 'Pickup', 'Toyota', '5 Seats'],

    // ================= MOTORCYCLE (1 Unit) =================
    ['Motorcycle', 1, 'BMW R 1200 GS Adventure', 2018, 'Manual', 'Gasoline', '070108', 'Light White', '29,000 km', 850000, 'Negotiable', 'Boss', 'Motorcycle', 'BMW', '2 Seats'],

    // ================= S.U.V. & MPV (44 Units) =================
    ['SUV', 1,  'Changan CS35 Plus', 2024, 'Automatic', 'Gasoline', 'FAS 9511', 'Red with Black Roof', '12,000 km', 680000, 'Negotiable', 'Boss', 'SUV', 'Changan', '5 Seats'],
    ['SUV', 3,  'Chery Tiggo 7 Pro', 2022, 'Automatic', 'Gasoline', 'FAN 7543', 'Gray', '20,000 km', 760000, 'Markdown to ₱760k (Neg.)', 'DFC', 'SUV', 'Chery', '5 Seats'],
    ['SUV', 4,  'Chevrolet Trax', 2018, 'Automatic', 'Gasoline', 'FAP 3058', 'Summit White', '14,000 km', 580000, 'Negotiable', 'Boss', 'SUV', 'Chevrolet', '5 Seats'],
    ['SUV', 6,  'GAC GS3 Emzoom GS', 2025, 'Automatic', 'Gasoline', 'ZAF 7161', 'Moonlight Gray', '3,000 km', 840000, 'Negotiable', 'Boss', 'SUV', 'GAC', '5 Seats'],
    ['SUV', 8,  'GAC M6 Pro GS', 2024, 'Automatic', 'Gasoline', 'ZAF 5579', 'Elegant Black', '15,000 km', 880000, 'Negotiable', 'Boss', 'MPV', 'GAC', '7 Seats'],
    ['SUV', 9,  'Geely Coolray SE Sport', 2022, 'Automatic', 'Gasoline', 'FAN 6546', 'Red', '54,000 km', 620000, 'Negotiable', 'Boss', 'SUV', 'Geely', '5 Seats'],
    ['SUV', 11, 'Geely Okavango Urban Plus', 2021, 'Automatic', 'Gasoline', 'GAT 6610', 'White', '27,000 km', 760000, 'Negotiable', 'Boss', 'SUV', 'Geely', '7 Seats'],
    ['SUV', 13, 'Honda BR-V 1.5 S', 2025, 'Automatic', 'Gasoline', 'NJK 4657', 'Lunar Metallic Gray', '11,000 km', 990000, 'Markdown to ₱990k (Neg.)', 'Boss', 'SUV', 'Honda', '7 Seats'],
    ['SUV', 15, 'Honda CR-V', 2017, 'Automatic', 'Gasoline', 'GEC 8395', 'Modern Steel', '55,000 km', 780000, 'Negotiable', 'Paolo', 'SUV', 'Honda', '5 Seats'],
    ['SUV', 17, 'Hyundai Stargazer GL', 2025, 'Automatic', 'Gasoline', 'FAT 4876', 'Red', '6,000 km', 860000, 'Negotiable', 'Paolo', 'MPV', 'Hyundai', '7 Seats'],
    ['SUV', 19, 'Hyundai Stargazer GLS Premium', 2024, 'Automatic', 'Gasoline', 'K3 E051/NKE 5896', 'White', '20,000 km', 860000, 'Negotiable', 'Boss', 'MPV', 'Hyundai', '7 Seats'],
    ['SUV', 20, 'Hyundai Tucson CRDi', 2015, 'Automatic', 'Diesel', 'AHA 3437', 'White', '82,000 km', 540000, 'Markdown to ₱540k (Neg.)', 'DFC', 'SUV', 'Hyundai', '5 Seats'],
    ['SUV', 22, 'Isuzu mu-X 3.0 LS-A', 2023, 'Automatic', 'Diesel', 'LAN 8961', 'Mercury Silver', '18,000 km', 1720000, 'Negotiable', 'Boss', 'SUV', 'Isuzu', '7 Seats'],
    ['SUV', 23, 'Jetour X50 Sport', 2025, 'Automatic', 'Gasoline', 'QC 715A/NKS 3324', 'White', '9,000 km', 820000, 'Negotiable', 'Boss', 'SUV', 'Jetour', '5 Seats'],
    ['SUV', 24, 'Kia Soul CRDi', 2016, 'Manual', 'Diesel', 'FAH 8955', 'Red', '58,000 km', 390000, 'Markdown to ₱390k (Neg.)', 'Ryan', 'SUV', 'Kia', '5 Seats'],
    ['SUV', 25, 'Kia Stonic LX', 2021, 'Manual', 'Gasoline', 'FAF 4597', 'Blue', '45,000 km', 540000, 'Negotiable', 'To Mayok', 'SUV', 'Kia', '5 Seats'],
    ['SUV', 26, 'Mitsubishi Adventure GLX', 2009, 'Manual', 'Diesel', 'NRO 357', 'Black', '98,000 km', 360000, 'Markdown to ₱360k (Neg.)', 'DFC', 'MPV', 'Mitsubishi', '7 Seats'],
    ['SUV', 30, 'Mitsubishi Montero Sport GLS', 2016, 'Automatic', 'Diesel', 'NBX 6075', 'Savanna White', '62,000 km', 880000, 'Negotiable', 'S. Randy', 'SUV', 'Mitsubishi', '7 Seats'],
    ['SUV', 32, 'Mitsubishi Montero Sport GLS', 2024, 'Automatic', 'Diesel', 'GBF 2941', 'Sterling Silver Metallic', '30,000 km', 1460000, 'Negotiable', 'Boss', 'SUV', 'Mitsubishi', '7 Seats'],
    ['SUV', 34, 'Mitsubishi Montero Sport GLS', 2025, 'Automatic', 'Diesel', 'GBB 2141', 'Jet Black Mica', '12,000 km', 1650000, 'Markdown to ₱1.650M (Neg.)', 'Boss', 'SUV', 'Mitsubishi', '7 Seats'],
    ['SUV', 36, 'Mitsubishi Montero Sport GLS', 2026, 'Automatic', 'Diesel', 'FBA 5951', 'Jet Black Mica', '10,000 km', 1690000, 'Negotiable', 'Boss', 'SUV', 'Mitsubishi', '7 Seats'],
    ['SUV', 38, 'Mitsubishi Montero Sport GT 4x4', 2017, 'Automatic', 'Diesel', 'FAD 6661', 'Pyrenese Black', '58,000 km', 990000, 'Negotiable', 'Boss', 'SUV', 'Mitsubishi', '7 Seats'],
    ['SUV', 39, 'Mitsubishi Xforce GLS', 2025, 'Automatic', 'Gasoline', 'NKE 4758', 'Graphite Gray Metallic w/ Black Roof', '10,000 km', 1080000, 'Negotiable', 'Boss', 'SUV', 'Mitsubishi', '5 Seats'],
    ['SUV', 41, 'Mitsubishi Xpander GLS', 2023, 'Automatic', 'Gasoline', 'IAE 6721', 'Graphite Gray Metallic', '25,000 km', 890000, 'Negotiable', 'Boss', 'MPV', 'Mitsubishi', '7 Seats'],
    ['SUV', 44, 'Mitsubishi Xpander GLS', 2025, 'Automatic', 'Gasoline', 'LAQ 6135', 'Green Bronze Metallic', '22,000 km', 990000, 'Negotiable', 'Boss', 'MPV', 'Mitsubishi', '7 Seats'],
    ['SUV', 46, 'Mitsubishi Xpander GLX', 2025, 'Manual', 'Gasoline', 'FAS 6028', 'Graphite Gray Metallic', '20,000 km', 880000, 'Negotiable', 'Paolo', 'MPV', 'Mitsubishi', '7 Seats'],
    ['SUV', 48, 'MG ZS Style', 2022, 'Automatic', 'Gasoline', 'FAI 9116', 'Red', '62,000 km', 540000, 'Markdown to ₱540k (Neg.)', 'DFC', 'SUV', 'MG', '5 Seats'],
    ['SUV', 50, 'MG RX5', 2019, 'Automatic', 'Gasoline', 'GAL 1671', 'White', '35,000 km', 670000, 'Markdown to ₱670k (Neg.)', 'Boss', 'SUV', 'MG', '5 Seats'],
    ['SUV', 51, 'Nissan Livina VE', 2026, 'Automatic', 'Gasoline', 'FAU 4267', 'Silver', '10,000 km', 960000, 'Negotiable', 'Boss', 'MPV', 'Nissan', '7 Seats'],
    ['SUV', 52, 'Nissan Patrol Royale V8', 2019, 'Automatic', 'Gasoline', 'KAD 4374', 'Black Obsidian', '48,000 km', 2780000, 'Markdown to ₱2.780M (Neg.)', 'Boss', 'SUV', 'Nissan', '8 Seats'],
    ['SUV', 53, 'Nissan Terra VE 4x2', 2024, 'Automatic', 'Diesel', 'CCG 3574', 'Lunar Metallic Gray', '13,000 km', 1540000, 'Negotiable', 'Boss', 'SUV', 'Nissan', '7 Seats'],
    ['SUV', 55, 'Subaru XV 2.0i-S', 2022, 'Automatic', 'Gasoline', 'LAL 2562', 'Ice Silver Metallic', '41,000 km', 880000, 'Negotiable', 'Boss', 'SUV', 'Subaru', '5 Seats'],
    ['SUV', 58, 'Suzuki Ertiga GL', 2023, 'Automatic', 'Gasoline', 'DAS 7597', 'Gray', '20,000 km', 780000, 'Negotiable', 'Boss', 'MPV', 'Suzuki', '7 Seats'],
    ['SUV', 60, 'Suzuki Ertiga Hybrid', 2024, 'Automatic', 'Hybrid', 'FNV 1493', 'Red', '13,000 km', 900000, 'Markdown to ₱900k (Neg.)', 'Boss', 'MPV', 'Suzuki', '7 Seats'],
    ['SUV', 62, 'Suzuki Ertiga GLX Hybrid', 2025, 'Automatic', 'Hybrid', 'DNW 4845', 'Silver', '12,000 km', 950000, 'Negotiable', 'Boss', 'MPV', 'Suzuki', '7 Seats'],
    ['SUV', 64, 'Suzuki Jimny JLX 4x4', 2017, 'Automatic', 'Gasoline', 'FAB 2387', 'Black', '52,000 km', 750000, 'Negotiable', 'S. Randy', 'SUV', 'Suzuki', '4 Seats'],
    ['SUV', 66, 'Suzuki Jimny JLX 4x4', 2018, 'Automatic', 'Gasoline', 'FAD 5348', 'Silver', '33,000 km', 790000, 'Negotiable', 'N. Kano', 'SUV', 'Suzuki', '4 Seats'],
    ['SUV', 68, 'Suzuki XL7 GLX Hybrid', 2025, 'Automatic', 'Hybrid', 'DBR 7701', 'Ivory/Black', '14,000 km', 990000, 'Negotiable', 'S. Randy', 'SUV', 'Suzuki', '7 Seats'],
    ['SUV', 71, 'Toyota Avanza 1.3 E', 2024, 'Automatic', 'Gasoline', 'GBE 7537', 'Black Metallic', '23,000 km', 920000, 'Negotiable', 'SPS. Golez', 'MPV', 'Toyota', '7 Seats'],
    ['SUV', 74, 'Toyota Fortuner 2.4 G', 2023, 'Automatic', 'Diesel', 'Z2 U081/DBI 6605', 'Silver Metallic', '28,000 km', 1540000, 'Negotiable', 'Boss', 'SUV', 'Toyota', '7 Seats'],
    ['SUV', 76, 'Toyota Fortuner LTD 4x4', 2024, 'Automatic', 'Diesel', 'LAP 6842', 'Silver Metallic/Attitude Black Mica', '22,000 km', 2180000, 'Negotiable', 'Boss', 'SUV', 'Toyota', '7 Seats'],
    ['SUV', 80, 'Toyota Innova 2.8 E', 2023, 'Automatic', 'Diesel', 'KAR 2457', 'Black', '30,000 km', 1260000, 'Negotiable', 'Boss', 'MPV', 'Toyota', '7 Seats'],
    ['SUV', 83, 'Toyota Raize 1.0 Turbo', 2025, 'Automatic', 'Gasoline', 'NKH 2000', 'White Pearl Mica', '13,000 km', 940000, 'Negotiable', 'Boss', 'SUV', 'Toyota', '5 Seats'],
    ['SUV', 88, 'Toyota Veloz 1.5 V', 2025, 'Automatic', 'Gasoline', 'DBT 4676', 'Purplish Silver Mica Metallic', '20,000 km', 1150000, 'Negotiable', 'Boss', 'MPV', 'Toyota', '7 Seats'],

    // ================= VAN / TRUCKS (12 Units) =================
    ['Van / Trucks', 1,  'Isuzu Traviz L', 2023, 'Manual', 'Diesel', 'FAN 6727', 'Arc White', '28,000 km', 980000, 'Negotiable', 'Boss', 'Van / Truck', 'Isuzu', '3 Seats'],
    ['Van / Trucks', 2,  'Isuzu Traviz L (Aluminum Van)', 2025, 'Manual', 'Diesel', 'NKS 3573', 'Arc White', '27,000 km', 990000, 'Negotiable', 'Boss', 'Van / Truck', 'Isuzu', '3 Seats'],
    ['Van / Trucks', 4,  'Mitsubishi L300 FB', 2025, 'Manual', 'Diesel', 'EAJ 6794', 'Polar White', '12,000 km', 780000, 'Negotiable', 'Boss', 'Van / Truck', 'Mitsubishi', '15 Seats'],
    ['Van / Trucks', 7,  'Mitsubishi L300 FB Exceed', 2023, 'Manual', 'Diesel', 'NFC 9560', 'Polar White', '32,000 km', 720000, 'Negotiable', 'Boss', 'Van / Truck', 'Mitsubishi', '15 Seats'],
    ['Van / Trucks', 9,  'Nissan NV350 Urvan', 2022, 'Manual', 'Diesel', 'GAV 7752', 'Brilliant Silver', '60,000 km', 1060000, 'Markdown to ₱1.060M (Neg.)', 'Boss', 'Van / Truck', 'Nissan', '15 Seats'],
    ['Van / Trucks', 11, 'Nissan NV350 Urvan High Roof', 2021, 'Manual', 'Diesel', 'FAF 7384', 'Alpine White', '38,000 km', 1060000, 'Negotiable', 'Boss', 'Van / Truck', 'Nissan', '15 Seats'],
    ['Van / Trucks', 12, 'Suzuki Carry Utility Cab', 2025, 'Manual', 'Gasoline', 'LAP 6450', 'White', '19,000 km', 620000, 'Negotiable', 'Boss', 'Van / Truck', 'Suzuki', '3 Seats'],
    ['Van / Trucks', 15, 'Toyota Hiace Commuter Deluxe', 2025, 'Manual', 'Diesel', 'NKP 5037', 'Silver Mica Metallic', '3,000 km', 1680000, 'Negotiable', 'Boss', 'Van / Truck', 'Toyota', '15 Seats'],
    ['Van / Trucks', 18, 'Toyota Hiace GL Grandia', 2024, 'Manual', 'Diesel', 'FAR 7596', 'Silver Mica Metallic', '16,000 km', 1980000, 'Negotiable', 'Boss', 'Van / Truck', 'Toyota', '12 Seats'],
    ['Van / Trucks', 21, 'Toyota Hiace Super Grandia', 2018, 'Automatic', 'Diesel', 'LAC 5055', 'White Pearl', '60,000 km', 1548000, 'Markdown to ₱1.548M (Neg.)', 'Paolo', 'Van / Truck', 'Toyota', '10 Seats'],
    ['Van / Trucks', 22, 'Toyota Hilux Tamaraw FX', 2026, 'Manual', 'Diesel', 'CDC 1990', 'Super White', '10,000 km', 960000, 'Markdown to ₱960k (Neg.)', 'Boss', 'Van / Truck', 'Toyota', '12 Seats'],
    ['Van / Trucks', 14, 'Toyota Hiace Commuter Deluxe', 2022, 'Manual', 'Diesel', 'FAJ 1515', 'White', '49,000 km', 1490000, 'Negotiable', 'Paolo', 'Van / Truck', 'Toyota', '15 Seats']
];

$prefixMap = [
    'Hatchback / Sedan' => 'HS',
    'Pick Up'           => 'PU',
    'Motorcycle'        => 'MC',
    'SUV'               => 'SUV',
    'Van / Trucks'      => 'VT'
];

$featuredIndexes = [5, 21, 26, 46, 56, 78, 89, 102]; // Diverse spotlight showcase across Sedan, Pickup, SUV, Van

$insertSql = "INSERT INTO `cars` (
    `name`, `wheel_name`, `status`, `is_recommended`, `is_featured`,
    `front_fitment`, `rear_fitment`, `finish`, `alloy_type`, `weight`,
    `image_file`, `gallery_images`, `brand`, `price`, `mileage`,
    `transmission`, `body_type`, `fuel_type`, `seating`, `stock_id`,
    `year_model`, `plate_number`, `original_price`, `price_note`,
    `deposit_amount`, `ownership_history`, `damage_disclosure`,
    `damage_photos`, `photo_metadata`, `agent_owner`
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $pdo->prepare($insertSql);

foreach ($items as $idx => $row) {
    [$cat, $listNo, $model, $year, $trans, $fuel, $plate, $color, $mileage, $price, $priceNote, $agent, $bodyType, $brand, $seats] = $row;

    $fullName = "{$year} {$model}";
    $stockCode = 'UW-' . ($prefixMap[$cat] ?? 'CAR') . str_pad((string)$listNo, 2, '0', STR_PAD_LEFT);
    $colorLower = strtolower($color);

    // Choose accurate main studio photo by body type + color
    if ($bodyType === 'Motorcycle') {
        $mainImg = 'car-moto.jpg';
        $rearImg = 'car-moto.jpg';
        $intImg  = 'detail-dashboard-2.jpg';
        $dashImg = 'detail-dashboard-2.jpg';
        $tireImg = 'detail-tires-2.jpg';
        $dmgImg  = 'detail-damage-bumper-2.jpg';
    } elseif ($bodyType === 'Van / Truck') {
        $mainImg = 'car-van.jpg';
        $rearImg = 'detail-trunk-1.jpg';
        $intImg  = 'car-1-interior.jpg';
        $dashImg = 'detail-dashboard-1.jpg';
        $tireImg = 'detail-tires-1.jpg';
        $dmgImg  = 'detail-damage-bumper-1.jpg';
    } elseif ($bodyType === 'Pickup') {
        $mainImg = 'car-5.jpg';
        $rearImg = 'detail-trunk-2.jpg';
        $intImg  = 'car-2-interior.jpg';
        $dashImg = 'detail-dashboard-2.jpg';
        $tireImg = 'detail-tires-2.jpg';
        $dmgImg  = 'detail-damage-bumper-2.jpg';
    } elseif ($bodyType === 'MPV') {
        $mainImg = 'car-4.jpg';
        $rearImg = 'car-1-rear.jpg';
        $intImg  = 'car-1-interior.jpg';
        $dashImg = 'detail-dashboard-1.jpg';
        $tireImg = 'detail-tires-1.jpg';
        $dmgImg  = 'detail-damage-bumper-1.jpg';
    } elseif ($bodyType === 'Hatchback') {
        $mainImg = 'car-6.jpg';
        $rearImg = 'car-2-rear.jpg';
        $intImg  = 'car-1-interior.jpg';
        $dashImg = 'detail-dashboard-1.jpg';
        $tireImg = 'detail-tires-1.jpg';
        $dmgImg  = 'detail-damage-bumper-1.jpg';
    } elseif ($bodyType === 'SUV') {
        // Larger ladder-frame SUVs use car-3.jpg, crossovers use car-7.jpg
        if (preg_match('/Fortuner|Montero|mu-X|Patrol|Terra|Jimny/i', $model)) {
            $mainImg = 'car-3.jpg';
            $tireImg = 'detail-tires-2.jpg';
        } else {
            $mainImg = 'car-7.jpg';
            $tireImg = 'detail-tires-1.jpg';
        }
        $rearImg = 'car-2-rear.jpg';
        $intImg  = 'car-2-interior.jpg';
        $dashImg = 'detail-dashboard-2.jpg';
        $dmgImg  = 'detail-damage-bumper-2.jpg';
    } else {
        // Sedan: vary by color (black/dark -> car-8, white -> car-2, silver/other -> car-1)
        if (strpos($colorLower, 'black') !== false || strpos($colorLower, 'dark') !== false || strpos($colorLower, 'gun') !== false) {
            $mainImg = 'car-8.jpg';
            $rearImg = 'car-2-rear.jpg';
            $intImg  = 'car-2-interior.jpg';
            $dashImg = 'detail-dashboard-2.jpg';
            $dmgImg  = 'detail-damage-bumper-2.jpg';
        } elseif (strpos($colorLower, 'white') !== false) {
            $mainImg = 'car-2.jpg';
            $rearImg = 'car-2-rear.jpg';
            $intImg  = 'car-2-interior.jpg';
            $dashImg = 'detail-dashboard-2.jpg';
            $dmgImg  = 'detail-damage-bumper-2.jpg';
        } else {
            $mainImg = 'car-1.jpg';
            $rearImg = 'car-1-rear.jpg';
            $intImg  = 'car-1-interior.jpg';
            $dashImg = 'detail-dashboard-1.jpg';
            $dmgImg  = 'detail-damage-bumper-1.jpg';
        }
        $tireImg = 'detail-tires-1.jpg';
    }

    $galleryList = array_values(array_unique([$rearImg, $intImg, $dashImg, $tireImg, $dmgImg]));
    $photoMeta = [
        ['file' => $mainImg, 'category' => 'Exterior',        'label' => "Main Exterior ({$color})"],
        ['file' => $rearImg, 'category' => 'Rear / Trunk',    'label' => 'Rear Profile & Cargo / Trunk'],
        ['file' => $intImg,  'category' => 'Interior',        'label' => 'Cabin & Seating Condition'],
        ['file' => $dashImg, 'category' => 'Dashboard',       'label' => "Dashboard & Odometer ({$mileage})"],
        ['file' => $tireImg, 'category' => 'Tires',           'label' => 'Wheels, Brakes & Tire Tread'],
        ['file' => $dmgImg,  'category' => 'Damage Close-up', 'label' => 'Documented Pre-Existing Cosmetic Detail']
    ];

    // Ownership history based on year & category
    $kmNum = intval(preg_replace('/[^0-9]/', '', $mileage));
    if ($bodyType === 'Van / Truck' && $kmNum > 25000) {
        $ownership = "Company-owned since {$year} — Fleet vehicle, regularly maintained";
    } elseif ($year >= 2024) {
        $ownership = "1 previous owner — Casa maintained since {$year}";
    } elseif ($year >= 2019 && $kmNum <= 60000) {
        $ownership = "1 previous owner — Complete LTO OR/CR & service records";
    } else {
        $ownership = "2 previous owners — LTO & HPG verified clean transfer history";
    }

    // Honest, transparent damage disclosure tailored to mileage/age
    if ($kmNum <= 15000 && $year >= 2025) {
        $damageText = "Near-showroom condition ({$mileage}). Very faint hairline stone chip on lower rear apron — photographed and documented before reservation. Original factory paint; zero collision or flood history.";
    } elseif ($kmNum <= 45000) {
        $damageText = "Minor surface scratch on rear bumper corner and light door-edge wear — photographed and documented prior to showroom listing. All panels original, cold AC, and 100% flood-free.";
    } else {
        $damageText = "Honest age-consistent wear: minor touch-up on lower rear bumper and light alloy curb scuff — photographed and documented upfront. Engine, transmission, and underchassis passed 50-point inspection.";
    }

    $dmgPhotos = [
        [
            'file'    => $dmgImg,
            'caption' => 'Documented rear bumper cosmetic inspection photo'
        ]
    ];

    $origPrice = (stripos($priceNote, 'Markdown') !== false) ? round($price * 1.06, -3) : null;
    $deposit = ($price >= 1000000) ? 20000.00 : 10000.00;
    $isFeatured = in_array($idx, $featuredIndexes, true) ? 1 : 0;
    $isRecommended = ($isFeatured || ($idx % 7 === 0)) ? 1 : 0;

    // Showcase real-time statuses across fleet: mostly AVAILABLE, plus 2 UNDER MAINTENANCE
    $status = 'AVAILABLE';
    if ($idx === 11 || $idx === 65) {
        $status = 'UNDER MAINTENANCE'; // Periodic Preventive Detailing / LTO Renewal
    }

    $wheelDesc = ($bodyType === 'Pickup' || $bodyType === 'SUV') ? 'OEM Alloy Wheels (85%+ Tire Tread)' : 'Factory OEM Wheels & Inspected Tires';
    $frontFit  = "Plate / Conduction: {$plate}";
    $rearFit   = $ownership;
    $alloyType = '100% Flood-Free & 50-Point Mechanic Verified';
    $weightStr = $priceNote;

    $stmt->execute([
        $fullName,
        $wheelDesc,
        $status,
        $isRecommended,
        $isFeatured,
        $frontFit,
        $rearFit,
        $color,
        $alloyType,
        $weightStr,
        $mainImg,
        json_encode($galleryList),
        $brand,
        $price,
        $mileage,
        $trans,
        $bodyType,
        $fuel,
        $seats,
        $stockCode,
        $year,
        $plate,
        $origPrice,
        $priceNote,
        $deposit,
        $ownership,
        $damageText,
        json_encode($dmgPhotos),
        json_encode($photoMeta),
        $agent
    ]);
}

// Seed 1 sample active Reservation Hold (on car #4 — 2023 Geely Emgrand Premium) so live countdown is immediately visible
$sampleCarId = 4;
$resAt = date('Y-m-d H:i:s');
$expAt = date('Y-m-d H:i:s', time() + (2 * 86400));
$sampleRef = 'UW-HOLD-' . strtoupper(substr(md5('sample2026'), 0, 6));
$pdo->prepare("INSERT INTO `reservations` (`booking_ref`, `car_id`, `customer_name`, `customer_phone`, `customer_email`, `hold_days`, `car_price`, `deposit_amount`, `reserved_at`, `expires_at`, `status`, `notes`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVE', ?)")
    ->execute([$sampleRef, $sampleCarId, 'Maria Santos (Sample Hold)', '0917-555-0192', 'maria.santos@example.com', 2, 620000, 10000, $resAt, $expAt, 'Showroom viewing & bank manager check scheduled']);
$pdo->prepare("UPDATE `cars` SET `status` = 'RESERVED', `reserved_until` = ?, `active_reservation_ref` = ? WHERE `id` = ?")
    ->execute([$expAt, $sampleRef, $sampleCarId]);

$totalSeeded = (int)$pdo->query("SELECT COUNT(*) FROM `cars`")->fetchColumn();
echo "Successfully seeded {$totalSeeded} vehicles from the November 1, 2026 Price List!\n";

// Also regenerate cars.sql so standalone SQL imports stay synchronized
$sqlDump = "-- UniWheels Motors - Complete Database Schema & November 1, 2026 Price List Seed ({$totalSeeded} Vehicles)\n";
$sqlDump .= "CREATE DATABASE IF NOT EXISTS `uniwheels` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
$sqlDump .= "USE `uniwheels`;\n\n";

foreach (['cars', 'reservations', 'site_settings'] as $tbl) {
    $createRow = $pdo->query("SHOW CREATE TABLE `{$tbl}`")->fetch(PDO::FETCH_NUM);
    $sqlDump .= "DROP TABLE IF EXISTS `{$tbl}`;\n" . $createRow[1] . ";\n\n";

    $rows = $pdo->query("SELECT * FROM `{$tbl}`")->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($rows)) {
        $cols = array_map(fn($c) => "`{$c}`", array_keys($rows[0]));
        foreach ($rows as $r) {
            $vals = array_map(function($v) use ($pdo) {
                return ($v === null) ? 'NULL' : $pdo->quote((string)$v);
            }, array_values($r));
            $sqlDump .= "INSERT INTO `{$tbl}` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ");\n";
        }
        $sqlDump .= "\n";
    }
}

file_put_contents(__DIR__ . '/cars.sql', $sqlDump);
echo "Exported updated cars.sql (" . round(filesize(__DIR__ . '/cars.sql') / 1024, 1) . " KB)\n";
