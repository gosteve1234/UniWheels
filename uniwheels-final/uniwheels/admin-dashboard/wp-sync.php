<?php
/**
 * Publishes cars to WordPress as posts, so the WordPress site stays in step with the staff pages.
 *
 * Usage, at the top level of a page (not inside a function, WordPress must load in global scope):
 *     $wp_sync_ids = [$car_id];
 *     require __DIR__ . '/wp-sync.php';
 *
 * Each car gets one post (linked by the "_uniwheels_car_id" field), filed under "Cars for sale" or "Sold cars".
 * Removed cars go to the WordPress trash. If WordPress is not installed yet, nothing happens.
 */

if (empty($wp_sync_ids) || !$pdo || !file_exists(__DIR__ . '/../wp-load.php')) {
    return;
}

// Imported WordPress tables can exist without wp-config.php. In that case
// wp-load.php redirects to setup and exits before the staff page can redirect.
// Match WordPress's supported config locations, including its parent-directory rule.
$wp_root = dirname(__DIR__);
$wp_parent = dirname($wp_root);
if (!file_exists($wp_root . '/wp-config.php') &&
    !(file_exists($wp_parent . '/wp-config.php') && !file_exists($wp_parent . '/wp-settings.php'))) {
    return;
}

try {
    // Loading WordPress before it is installed would redirect to its setup page, so check first.
    if (!$pdo->query("SHOW TABLES LIKE 'wp_options'")->fetch()) {
        return;
    }
} catch (Throwable $e) {
    return;
}

define('WP_USE_THEMES', false);
require_once __DIR__ . '/../wp-load.php';
date_default_timezone_set('Asia/Manila'); // WordPress switches PHP to UTC

/** The WordPress category id for a name, created if missing. */
function uw_wp_category($name) {
    $term = term_exists($name, 'category');
    if (!$term) {
        $term = wp_insert_term($name, 'category');
    }
    return is_wp_error($term) ? 0 : (int) $term['term_id'];
}

/** Copies a car photo into the WordPress media library once and returns its attachment id. */
function uw_wp_photo($file, $post_id) {
    $path = realpath(__DIR__ . '/../assets/images/cars/' . $file);
    if (!$path) {
        return 0;
    }
    $found = get_posts(['post_type' => 'attachment', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids',
        'meta_key' => '_uniwheels_photo', 'meta_value' => $file]);
    if ($found) {
        return (int) $found[0];
    }
    $upload = wp_upload_bits($file, null, file_get_contents($path));
    if (!empty($upload['error'])) {
        return 0;
    }
    $type = wp_check_filetype($upload['file']);
    $id = wp_insert_attachment(['post_mime_type' => $type['type'], 'post_title' => pathinfo($file, PATHINFO_FILENAME), 'post_status' => 'inherit'], $upload['file'], $post_id);
    if (!$id || is_wp_error($id)) {
        return 0;
    }
    require_once ABSPATH . 'wp-admin/includes/image.php';
    wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $upload['file']));
    update_post_meta($id, '_uniwheels_photo', $file);
    return (int) $id;
}

function uw_wp_content($car) {
    $is_sold = in_array(strtoupper($car['status']), SOLD_STATUSES, true);
    $html = '';
    foreach (car_photos($car) as $file) {
        $html .= '<img src="' . esc_url(home_url('/assets/images/cars/' . rawurlencode($file))) . '" alt="' . esc_attr($car['name']) . '" />' . "\n";
    }
    if ($is_sold) {
        $html .= '<p><strong>SOLD</strong></p>' . "\n";
    } else {
        $price = '<strong>' . esc_html(peso($car['price'])) . '</strong>';
        if ((float) $car['old_price'] > (float) $car['price']) {
            $price .= ' <del>' . esc_html(peso($car['old_price'])) . '</del> (price reduced)';
        }
        $html .= '<p>' . $price . (strtoupper($car['status']) === 'RESERVED' ? ' · Reserved' : '') . '</p>' . "\n";
    }
    $details = array_filter(array_map('trim', [$car['mileage'], $car['transmission'], $car['body_type'], $car['fuel_type'], $car['seating'], $car['finish']]));
    if ($details) {
        $html .= "<ul>\n";
        foreach ($details as $detail) {
            $html .= '<li>' . esc_html($detail) . "</li>\n";
        }
        $html .= "</ul>\n";
    }
    if (!$is_sold) {
        $html .= '<p><a href="' . esc_url(MESSENGER_URL) . '">Message us on Messenger to view or reserve this car</a></p>' . "\n";
    }
    return $html;
}

function uw_wp_publish($car) {
    $existing = get_posts(['post_type' => 'post', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids',
        'meta_key' => '_uniwheels_car_id', 'meta_value' => (int) $car['id']]);
    $post_id = $existing ? (int) $existing[0] : 0;

    if (!empty($car['deleted_at'])) {
        if ($post_id) {
            wp_trash_post($post_id);
        }
        return;
    }
    if ($post_id && get_post_status($post_id) === 'trash') {
        wp_untrash_post($post_id);
    }

    $is_sold = in_array(strtoupper($car['status']), SOLD_STATUSES, true);
    $post = [
        'post_type' => 'post',
        'post_status' => 'publish',
        'post_title' => ($is_sold ? 'SOLD: ' : '') . $car['name'],
        'post_content' => uw_wp_content($car),
        'post_category' => [uw_wp_category($is_sold ? 'Sold cars' : 'Cars for sale')],
    ];
    if ($post_id) {
        $post['ID'] = $post_id;
        wp_update_post(wp_slash($post));
    } else {
        $post_id = wp_insert_post(wp_slash($post));
        if (!$post_id || is_wp_error($post_id)) {
            return;
        }
        update_post_meta($post_id, '_uniwheels_car_id', (int) $car['id']);
    }

    $photos = car_photos($car);
    if ($photos && ($photo_id = uw_wp_photo($photos[0], $post_id))) {
        set_post_thumbnail($post_id, $photo_id);
    }
}

foreach ((array) $wp_sync_ids as $wp_car_id) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM cars WHERE id = ?');
        $stmt->execute([(int) $wp_car_id]);
        if ($wp_car = $stmt->fetch()) {
            uw_wp_publish($wp_car);
        }
    } catch (Throwable $e) {
        error_log('UniWheels WordPress sync failed for car ' . $wp_car_id . ': ' . $e->getMessage());
    }
}
