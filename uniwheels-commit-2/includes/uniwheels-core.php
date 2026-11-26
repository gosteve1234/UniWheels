<?php
/**
 * UniWheels Core Engine — Schema Migration, Auto-Release Holds, ECMS Settings & Public Serializer
 * Shared across WordPress theme, public AJAX API, and Staff ECMS Admin Portal.
 */

date_default_timezone_set('Asia/Manila');

if (!function_exists('uniwheels_get_pdo')) {
    function uniwheels_get_pdo() {
        global $pdo;
        if ($pdo instanceof PDO) {
            return $pdo;
        }
        $suppress_db_die = true;
        require __DIR__ . '/../connection.php';
        return $pdo;
    }
}

if (!function_exists('uniwheels_ensure_schema')) {
    function uniwheels_ensure_schema(PDO $pdo) {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        // 1. Ensure base cars table exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS `cars` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(150) NOT NULL,
            `wheel_name` VARCHAR(120) NOT NULL DEFAULT 'OEM Factory Alloy Wheels',
            `status` VARCHAR(50) NOT NULL DEFAULT 'AVAILABLE',
            `is_recommended` TINYINT(1) NOT NULL DEFAULT 0,
            `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
            `front_fitment` VARCHAR(120) NOT NULL DEFAULT 'Factory Spec Tires',
            `rear_fitment` VARCHAR(150) NOT NULL DEFAULT 'Complete Original OR/CR',
            `finish` VARCHAR(100) NOT NULL DEFAULT 'Original Paint',
            `alloy_type` VARCHAR(150) NOT NULL DEFAULT '100% Flood-Free & Mechanic Inspected',
            `weight` VARCHAR(100) NOT NULL DEFAULT 'Showroom Certified',
            `image_file` VARCHAR(255) NOT NULL DEFAULT 'car-1.jpg',
            `gallery_images` TEXT DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `brand` VARCHAR(60) DEFAULT 'Toyota',
            `price` DECIMAL(12,2) DEFAULT 0.00,
            `mileage` VARCHAR(50) DEFAULT 'Low Mileage',
            `transmission` VARCHAR(50) DEFAULT 'Automatic',
            `body_type` VARCHAR(50) DEFAULT 'Sedan',
            `fuel_type` VARCHAR(100) DEFAULT 'Gasoline',
            `seating` VARCHAR(30) DEFAULT '5 Seats',
            `stock_id` VARCHAR(40) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // 2. Add new Add-on A/C/D/E columns safely if missing
        $existingCols = [];
        foreach ($pdo->query("DESCRIBE `cars`")->fetchAll(PDO::FETCH_ASSOC) as $col) {
            $existingCols[strtolower($col['Field'])] = $col;
        }

        // Widen name & stock_id if narrow
        $pdo->exec("ALTER TABLE `cars` MODIFY COLUMN `name` VARCHAR(150) NOT NULL, MODIFY COLUMN `stock_id` VARCHAR(40) DEFAULT NULL");

        $alterParts = [];
        $columnsToAdd = [
            'year_model'             => "INT DEFAULT 2024",
            'plate_number'           => "VARCHAR(60) DEFAULT NULL",
            'original_price'         => "DECIMAL(12,2) DEFAULT NULL",
            'price_note'             => "VARCHAR(100) DEFAULT 'Negotiable'",
            'deposit_amount'         => "DECIMAL(12,2) DEFAULT 10000.00",
            'ownership_history'      => "VARCHAR(180) DEFAULT '1 previous owner'",
            'damage_disclosure'      => "TEXT DEFAULT NULL",
            'damage_photos'          => "TEXT DEFAULT NULL",
            'photo_metadata'         => "TEXT DEFAULT NULL",
            'vehicle_policies'       => "TEXT DEFAULT NULL",
            'agent_owner'            => "VARCHAR(100) DEFAULT 'Boss'",
            'reserved_until'         => "DATETIME DEFAULT NULL",
            'active_reservation_ref' => "VARCHAR(40) DEFAULT NULL"
        ];

        foreach ($columnsToAdd as $colName => $definition) {
            if (!isset($existingCols[$colName])) {
                $alterParts[] = "ADD COLUMN `{$colName}` {$definition}";
            }
        }
        if (!empty($alterParts)) {
            $pdo->exec("ALTER TABLE `cars` " . implode(', ', $alterParts));
        }

        // 3. Create reservations table (Add-ons B & C)
        $pdo->exec("CREATE TABLE IF NOT EXISTS `reservations` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `booking_ref` VARCHAR(32) NOT NULL UNIQUE,
            `car_id` INT NOT NULL,
            `customer_name` VARCHAR(120) NOT NULL,
            `customer_phone` VARCHAR(50) NOT NULL,
            `customer_email` VARCHAR(120) NOT NULL,
            `hold_days` INT NOT NULL DEFAULT 2,
            `car_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `deposit_amount` DECIMAL(12,2) NOT NULL DEFAULT 10000.00,
            `reserved_at` DATETIME NOT NULL,
            `expires_at` DATETIME NOT NULL,
            `status` VARCHAR(30) NOT NULL DEFAULT 'ACTIVE',
            `notes` TEXT DEFAULT NULL,
            `notification_sent` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_car_status` (`car_id`, `status`),
            INDEX `idx_booking_ref` (`booking_ref`),
            INDEX `idx_expires_at` (`expires_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // 4. Create site_settings ECMS table (Add-on E)
        $pdo->exec("CREATE TABLE IF NOT EXISTS `site_settings` (
            `setting_key` VARCHAR(80) PRIMARY KEY,
            `setting_value` TEXT NOT NULL,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Seed default ECMS settings if empty
        $countSettings = (int)$pdo->query("SELECT COUNT(*) FROM `site_settings`")->fetchColumn();
        if ($countSettings === 0) {
            $defaults = uniwheels_default_settings();
            $stmt = $pdo->prepare("INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`) VALUES (?, ?)");
            foreach ($defaults as $k => $v) {
                $stmt->execute([$k, $v]);
            }
        }
    }
}

if (!function_exists('uniwheels_default_settings')) {
    function uniwheels_default_settings() {
        return [
            'hero_title_prefix'     => 'Quality Secondhand Cars',
            'hero_title_highlight'  => 'You Can Trust',
            'hero_subtitle'         => 'Every unit features transparent vehicle age, ownership history, and photographed condition reports. Reserve a vehicle online or visit our showroom today.',
            'catalog_tag'           => 'Showroom Floor — Nov 2026 Price List',
            'catalog_title'         => 'Available Certified Pre-Owned Inventory',
            'catalog_subtitle'      => 'Complete vehicle profiles with honest condition disclosures, zoomable inspection photos, and real-time availability.',
            'reference_year'        => '2026',
            'default_deposit'       => '10000',
            'max_hold_days'         => '7',
            'policy_reservation'    => 'Customers may place a temporary reservation hold for 1, 2, 3, or up to 7 custom days. To lock the unit beyond the hold deadline, complete the refundable reservation deposit at our showroom or via verified bank transfer before the countdown expires.',
            'policy_release'        => 'Automatic Release Policy: If payment or showroom verification is not completed before the reservation deadline, the hold automatically expires and the vehicle status immediately returns to AVAILABLE for other buyers.',
            'policy_condition'      => 'Honest Disclosure Policy: All pre-owned units undergo a 50-point mechanical check. Existing cosmetic scratches, touch-ups, or repairs are photographed and documented upfront under "Vehicle Condition & History" prior to reservation.',
            'policy_papers'         => 'Clean Papers Guarantee: Every vehicle includes verified original LTO OR/CR, HPG clearance eligibility, deed of sale, and transparent ownership history. Walk-in mechanic inspections and test drives are warmly welcomed.',
            'contact_address'       => '123 Car City Commercial Highway, UniWheels Auto Mall',
            'contact_hours'         => 'Open Everyday: 8:30 AM – 6:30 PM (Sunday 9:00 AM – 5:00 PM)',
            'contact_phone'         => '(02) 8123-4567 / 0917-123-4567',
            'contact_email'         => 'sales@uniwheelsmotors.com',
            'messenger_url'         => 'https://m.me/uniwheels',
            'announcement_banner'   => 'November 2026 Showroom Update: Online Reservation Holds (1–7 Days) with Live Countdown & Transparent Condition Reports Now Active!'
        ];
    }
}

if (!function_exists('uniwheels_get_settings')) {
    function uniwheels_get_settings(PDO $pdo) {
        $defaults = uniwheels_default_settings();
        try {
            $rows = $pdo->query("SELECT `setting_key`, `setting_value` FROM `site_settings`")->fetchAll(PDO::FETCH_KEY_PAIR);
            if (is_array($rows)) {
                return array_merge($defaults, $rows);
            }
        } catch (Exception $e) {}
        return $defaults;
    }
}

/**
 * Requirement C: Automatic Release of Expired Reservation Holds
 * Runs automatically on page loads and API calls so expired holds free up immediately.
 */
if (!function_exists('uniwheels_release_expired_holds')) {
    function uniwheels_release_expired_holds(PDO $pdo) {
        $now = date('Y-m-d H:i:s');
        try {
            // 1. Mark expired active reservations as EXPIRED
            $stmtExp = $pdo->prepare("SELECT `id`, `car_id`, `booking_ref` FROM `reservations` WHERE `status` = 'ACTIVE' AND `expires_at` <= ?");
            $stmtExp->execute([$now]);
            $expiredList = $stmtExp->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($expiredList)) {
                $updRes = $pdo->prepare("UPDATE `reservations` SET `status` = 'EXPIRED' WHERE `id` = ?");
                $updCar = $pdo->prepare("UPDATE `cars` SET `status` = 'AVAILABLE', `reserved_until` = NULL, `active_reservation_ref` = NULL WHERE `id` = ? AND `status` = 'RESERVED'");
                foreach ($expiredList as $exp) {
                    $updRes->execute([$exp['id']]);
                    $updCar->execute([$exp['car_id']]);
                }
            }

            // 2. Also clean up any car marked RESERVED whose reserved_until timestamp has passed
            $stmtOrphan = $pdo->prepare("UPDATE `cars` SET `status` = 'AVAILABLE', `reserved_until` = NULL, `active_reservation_ref` = NULL WHERE `status` = 'RESERVED' AND `reserved_until` IS NOT NULL AND `reserved_until` <= ?");
            $stmtOrphan->execute([$now]);
        } catch (Exception $e) {
            // Non-fatal
        }
    }
}

/**
 * Requirement A: Compute Vehicle Age display string
 * Example: "2021 Toyota Vios — 5 years old"
 */
if (!function_exists('uniwheels_compute_vehicle_age')) {
    function uniwheels_compute_vehicle_age($yearModel, $carName = '', $refYear = 2026) {
        $year = intval($yearModel);
        if ($year <= 1980 && preg_match('/\b(19\d{2}|20\d{2})\b/', (string)$carName, $m)) {
            $year = intval($m[1]);
        }
        if ($year <= 1980) {
            $year = $refYear;
        }
        $age = max(0, intval($refYear) - $year);
        if ($age === 0) {
            $ageLabel = 'Less than 1 year old (Current Year Model)';
            $shortAge = 'New / < 1 yr old';
        } elseif ($age === 1) {
            $ageLabel = '1 year old';
            $shortAge = '1 year old';
        } else {
            $ageLabel = "{$age} years old";
            $shortAge = "{$age} years old";
        }
        return [
            'year'       => $year,
            'age_years'  => $age,
            'age_label'  => $ageLabel,
            'short_age'  => $shortAge,
            'full_badge' => "{$year} Model — {$ageLabel}"
        ];
    }
}

/**
 * Normalize gallery & categorized inspection photos + strip Admin-only fields for public frontend
 */
if (!function_exists('uniwheels_prepare_public_car')) {
    function uniwheels_prepare_public_car(array $car, array $settings = [], $activeRes = null) {
        $refYear = intval($settings['reference_year'] ?? 2026);
        $defaultDeposit = floatval($settings['default_deposit'] ?? 10000);

        $ageInfo = uniwheels_compute_vehicle_age($car['year_model'] ?? 0, $car['name'] ?? '', $refYear);
        $car['year_model'] = $ageInfo['year'];
        $car['age_years'] = $ageInfo['age_years'];
        $car['age_label'] = $ageInfo['age_label'];
        $car['short_age'] = $ageInfo['short_age'];
        $car['age_display'] = $car['name'] . ' — ' . $ageInfo['age_label'];

        // Ownership history fallback
        if (empty($car['ownership_history'])) {
            $car['ownership_history'] = !empty($car['rear_fitment']) ? $car['rear_fitment'] : '1 previous owner';
        }

        // Damage disclosure fallback
        if (empty($car['damage_disclosure'])) {
            $car['damage_disclosure'] = 'No major dents or structural repairs. Minor normal-wear surface marks inspected, photographed, and documented prior to showroom display.';
        }

        // Deposit
        $dep = floatval($car['deposit_amount'] ?? 0);
        $car['deposit_amount'] = $dep > 0 ? $dep : $defaultDeposit;

        // Build categorized photo list (Exterior, Interior, Trunk, Dashboard, Tires, Damage Close-up)
        $imgs = [];
        if (!empty($car['image_file'])) {
            $imgs[] = $car['image_file'];
        }
        if (!empty($car['gallery_images'])) {
            $extra = is_string($car['gallery_images']) ? json_decode($car['gallery_images'], true) : $car['gallery_images'];
            if (is_array($extra)) {
                foreach ($extra as $gi) {
                    if (!empty($gi) && !in_array($gi, $imgs)) {
                        $imgs[] = $gi;
                    }
                }
            }
        }
        if (empty($imgs)) {
            $imgs[] = 'car-1.jpg';
        }
        $car['all_images'] = $imgs;

        // Parse structured photo metadata if present, otherwise auto-label intelligently
        $meta = [];
        if (!empty($car['photo_metadata'])) {
            $decoded = is_string($car['photo_metadata']) ? json_decode($car['photo_metadata'], true) : $car['photo_metadata'];
            if (is_array($decoded)) {
                $meta = $decoded;
            }
        }
        if (empty($meta)) {
            foreach ($imgs as $idx => $file) {
                $fl = strtolower($file);
                if (strpos($fl, 'damage') !== false || strpos($fl, 'bumper') !== false || strpos($fl, 'scratch') !== false) {
                    $cat = 'Damage Close-up';
                    $lbl = 'Documented Condition / Close-up';
                } elseif (strpos($fl, 'dash') !== false) {
                    $cat = 'Dashboard';
                    $lbl = 'Instrument Cluster & Infotainment';
                } elseif (strpos($fl, 'interior') !== false || strpos($fl, 'cabin') !== false) {
                    $cat = 'Interior';
                    $lbl = 'Cabin Seating & Interior Trim';
                } elseif (strpos($fl, 'tire') !== false || strpos($fl, 'wheel') !== false) {
                    $cat = 'Tires';
                    $lbl = 'Alloy Wheel & Tire Tread';
                } elseif (strpos($fl, 'trunk') !== false || strpos($fl, 'rear') !== false) {
                    $cat = 'Rear / Trunk';
                    $lbl = 'Rear Exterior & Trunk Cargo Area';
                } else {
                    $cat = 'Exterior';
                    $lbl = ($idx === 0) ? 'Main Showroom Exterior Profile' : 'Exterior Angle #' . ($idx + 1);
                }
                $meta[] = [
                    'file'     => $file,
                    'category' => $cat,
                    'label'    => $lbl
                ];
            }
        }
        $car['categorized_photos'] = $meta;

        // Parse damage close-up photos
        $dmgPhotos = [];
        if (!empty($car['damage_photos'])) {
            $decodedDmg = is_string($car['damage_photos']) ? json_decode($car['damage_photos'], true) : $car['damage_photos'];
            if (is_array($decodedDmg)) {
                $dmgPhotos = $decodedDmg;
            }
        }
        $car['damage_photos_list'] = $dmgPhotos;

        // Reservation countdown info
        if (strtoupper($car['status'] ?? '') === 'RESERVED' && !empty($car['reserved_until'])) {
            $car['reserved_until_iso'] = date('c', strtotime($car['reserved_until']));
            $car['reserved_until_formatted'] = date('F j, Y, g:i A', strtotime($car['reserved_until']));
            $car['reserved_hold_days'] = $activeRes ? intval($activeRes['hold_days']) : null;
        } else {
            $car['reserved_until_iso'] = null;
            $car['reserved_until_formatted'] = null;
            $car['reserved_hold_days'] = null;
        }

        // Strip internal Admin-only agent/consignor name from public payload!
        unset($car['agent_owner']);

        return $car;
    }
}
