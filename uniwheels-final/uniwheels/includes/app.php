<?php
/**
 * UniWheels Shared Application Helpers
 * Schema sync, editable CMS settings, automatic reservation hold release,
 * and shared formatting/status helpers for both public site and staff dashboard.
 */

if (!defined('UW_TIMEZONE_SET')) {
    date_default_timezone_set('Asia/Manila');
    define('UW_TIMEZONE_SET', true);
}

const UW_MESSENGER_DEFAULT = 'https://m.me/uniwheels';
const UW_SOLD_STATUSES = ['SOLD', 'OUT OF STOCK'];
const UW_PHOTO_CATEGORIES = [
    'Exterior',
    'Interior',
    'Trunk',
    'Dashboard',
    'Tires',
    'Damage Close-up',
    'Other',
];
const UW_DAMAGE_LABELS = [
    'NONE'     => 'No known damage',
    'MINOR'    => 'Minor cosmetic wear',
    'REPAIRED' => 'Previously repaired',
    'MAJOR'    => 'Disclosed damage',
];

function uw_default_settings(): array {
    return [
        'messenger_url'    => 'https://m.me/uniwheels',
        'showroom_address' => '123 Car City Commercial Highway',
        'footer_address'   => 'UniWheels Auto Mall, Main Highway Showroom Strip',
        'showroom_hours'   => 'Open Everyday: 8:30 AM – 6:30 PM',
        'showroom_phone'   => '(02) 8123-4567 / 0917-123-4567',
        'showroom_email'   => 'sales@uniwheelsmotors.com',
        'hero_title'       => 'Quality secondhand cars you can trust',
        'hero_subtitle'    => 'Inspected, complete with original LTO papers, and transparently documented before you visit.',
        'default_deposit'  => '5000',
        'max_hold_days'    => '7',
        'default_policy'   => "Valid government ID and driver's license required upon viewing/pickup. Reservations expire if the buyer does not complete the purchase within the agreed hold period.",
    ];
}

/**
 * Ensures new CMS/booking columns and tables exist without requiring manual SQL migrations.
 */
function uw_ensure_schema(?PDO $pdo): void {
    static $done = false;
    if ($done || !$pdo) {
        return;
    }
    $done = true;

    try {
        $pdo->exec("SET time_zone = '+08:00'");
    } catch (Throwable $e) {
        // Ignore if MySQL timezone setting is restricted.
    }

    try {
        $cols = $pdo->query("SHOW COLUMNS FROM cars")->fetchAll(PDO::FETCH_COLUMN);
        $colSet = array_flip($cols);

        $alterParts = [];
        if (!isset($colSet['model_year'])) {
            $alterParts[] = "ADD COLUMN model_year SMALLINT UNSIGNED NULL";
        }
        if (!isset($colSet['ownership_history'])) {
            $alterParts[] = "ADD COLUMN ownership_history VARCHAR(255) NULL";
        }
        if (!isset($colSet['damage_status'])) {
            $alterParts[] = "ADD COLUMN damage_status VARCHAR(16) NULL";
        }
        if (!isset($colSet['damage_report'])) {
            $alterParts[] = "ADD COLUMN damage_report TEXT NULL";
        }
        if (!isset($colSet['photo_labels'])) {
            $alterParts[] = "ADD COLUMN photo_labels TEXT NULL";
        }
        if (!isset($colSet['listing_type'])) {
            $alterParts[] = "ADD COLUMN listing_type VARCHAR(10) NOT NULL DEFAULT 'SALE'";
        }
        if (!isset($colSet['rental_rate'])) {
            $alterParts[] = "ADD COLUMN rental_rate DECIMAL(12,2) NULL";
        }
        if (!isset($colSet['booking_deposit'])) {
            $alterParts[] = "ADD COLUMN booking_deposit DECIMAL(12,2) NULL";
        }
        if (!isset($colSet['booking_policy'])) {
            $alterParts[] = "ADD COLUMN booking_policy TEXT NULL";
        }
        if (!isset($colSet['reserve_expires_at'])) {
            $alterParts[] = "ADD COLUMN reserve_expires_at DATETIME NULL AFTER reserved_at";
        }
        if (!isset($colSet['listed_price'])) {
            $alterParts[] = "ADD COLUMN listed_price DECIMAL(12,2) NULL AFTER price";
        }
        if (!isset($colSet['traded_at'])) {
            $alterParts[] = "ADD COLUMN traded_at DATETIME NULL AFTER sold_at";
        }
        if (!isset($colSet['trade_car_name'])) {
            $alterParts[] = "ADD COLUMN trade_car_name VARCHAR(255) NULL AFTER traded_at";
        }
        if (!isset($colSet['trade_stock_id'])) {
            $alterParts[] = "ADD COLUMN trade_stock_id VARCHAR(64) NULL AFTER trade_car_name";
        }
        if ($alterParts) {
            $pdo->exec("ALTER TABLE cars " . implode(', ', $alterParts));
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
            setting_key VARCHAR(64) NOT NULL PRIMARY KEY,
            setting_value TEXT NOT NULL,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS uw_reservation_holds (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            car_id INT NOT NULL,
            reference VARCHAR(24) NOT NULL UNIQUE,
            owner_hash CHAR(64) NOT NULL,
            customer_name VARCHAR(100) NOT NULL,
            customer_contact VARCHAR(150) NOT NULL,
            rental_start DATE NULL,
            rental_end DATE NULL,
            total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            deposit DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            expires_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            state VARCHAR(20) NOT NULL DEFAULT 'active',
            KEY idx_car_state (car_id, state),
            KEY idx_owner (owner_hash)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS vehicle_bookings (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            car_id INT NOT NULL,
            reference VARCHAR(24) NOT NULL UNIQUE,
            owner_token CHAR(64) NOT NULL,
            booking_type VARCHAR(10) NOT NULL,
            state VARCHAR(16) NOT NULL DEFAULT 'pending',
            rental_start DATE NULL,
            rental_end DATE NULL,
            total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            deposit DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            customer_name VARCHAR(100) NULL,
            customer_contact VARCHAR(150) NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            KEY idx_vb_car (car_id, state),
            KEY idx_vb_owner (owner_token)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Seed default CMS settings once if missing.
        $existing = $pdo->query("SELECT setting_key FROM site_settings")->fetchAll(PDO::FETCH_COLUMN);
        $existingSet = array_flip($existing);
        $stmt = $pdo->prepare("INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
        foreach (uw_default_settings() as $k => $v) {
            if (!isset($existingSet[$k])) {
                $stmt->execute([$k, $v]);
            }
        }
    } catch (Throwable $e) {
        // Keep site usable even if DDL privileges are restricted.
    }
}

function uw_get_settings(?PDO $pdo): array {
    $settings = uw_default_settings();
    if (!$pdo) {
        return $settings;
    }
    uw_ensure_schema($pdo);
    try {
        $rows = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll();
        foreach ($rows as $row) {
            if ($row['setting_key'] !== '') {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        }
    } catch (Throwable $e) {
        // Fall back to defaults.
    }
    return $settings;
}

/**
 * Marks expired holds in uw_reservation_holds without auto-releasing RESERVED vehicles,
 * so staff are notified and can manually toggle "Release reserve" when a buyer does not visit.
 */
function uw_release_expired_holds(?PDO $pdo): void {
    if (!$pdo) {
        return;
    }
    uw_ensure_schema($pdo);
    try {
        $pdo->exec("UPDATE uw_reservation_holds SET state = 'expired' WHERE state = 'active' AND expires_at <= NOW()");
    } catch (Throwable $e) {
        // Do not fail page rendering if check fails.
    }
}

function uw_extract_year(array $car): int {
    $year = (int) ($car['model_year'] ?? 0);
    if ($year >= 1950 && $year <= (int) date('Y') + 1) {
        return $year;
    }
    if (preg_match('/\b(19\d{2}|20\d{2})\b/', (string) ($car['name'] ?? ''), $m)) {
        return (int) $m[1];
    }
    return 0;
}

function uw_car_age_label(array $car): string {
    $year = uw_extract_year($car);
    if ($year <= 0) {
        return 'Year not specified';
    }
    $age = max(0, (int) date('Y') - $year);
    if ($age === 0) {
        return "{$year} Model — Current year";
    }
    return "{$year} Model — {$age} " . ($age === 1 ? 'year old' : 'years old');
}

function uw_status_meta(string $status): array {
    $key = strtoupper(trim($status)) ?: 'AVAILABLE';
    $map = [
        'AVAILABLE'    => ['key' => 'AVAILABLE', 'label' => 'Available', 'class' => 'badge-available'],
        'RESERVED'     => ['key' => 'RESERVED', 'label' => 'Reserved', 'class' => 'badge-reserved'],
        'MAINTENANCE'  => ['key' => 'MAINTENANCE', 'label' => 'Under Maintenance', 'class' => 'badge-maintenance'],
        'TRADED'       => ['key' => 'TRADED', 'label' => 'Traded', 'class' => 'badge-sold'],
        'SOLD'         => ['key' => 'SOLD', 'label' => 'Sold', 'class' => 'badge-sold'],
        'OUT OF STOCK' => ['key' => 'SOLD', 'label' => 'Sold', 'class' => 'badge-sold'],
    ];
    return $map[$key] ?? $map['AVAILABLE'];
}

function uw_photo_labels(array $car): array {
    $raw = json_decode((string) ($car['photo_labels'] ?? ''), true);
    return is_array($raw) ? $raw : [];
}

function uw_damage_label(?string $code): string {
    $k = strtoupper(trim((string) $code));
    return UW_DAMAGE_LABELS[$k] ?? 'Condition verified';
}
