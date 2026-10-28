<?php
/**
 * Shows a WordPress page (About, Contact, Privacy, Terms) inside the site's own header and footer.
 * Link: page.php?p=about, where "about" is the page's slug in WordPress.
 * The text is edited in WordPress under Pages; changes show here right away.
 */

$suppress_db_die = true;
require_once __DIR__ . '/connection.php';

$slug = preg_replace('/[^a-z0-9-]/', '', strtolower((string) ($_GET['p'] ?? '')));
$wp_page = null;

try {
    // Loading WordPress before it is installed would redirect to its setup page, so check first.
    $wp_ready = $pdo && $pdo->query("SHOW TABLES LIKE 'wp_options'")->fetch();
} catch (Throwable $e) {
    $wp_ready = false;
}

if ($slug !== '' && $wp_ready) {
    define('WP_USE_THEMES', false);
    require_once __DIR__ . '/wp-load.php';
    date_default_timezone_set('Asia/Manila'); // WordPress switches PHP to UTC
    $wp_page = get_page_by_path($slug);
    if ($wp_page && $wp_page->post_status !== 'publish') {
        $wp_page = null;
    }
}

if (!$wp_page) {
    http_response_code(404);
}

$home_link = 'index.php';  // header and footer links point back to the homepage sections
$hide_guarantee = true;    // the "Why choose us" block belongs to the homepage only
include __DIR__ . '/header.php';
?>

<main class="info-page">
  <div class="container">
    <?php if ($wp_page): ?>
      <h1 class="info-page-title"><?= get_the_title($wp_page) ?></h1>
      <div class="info-page-body">
        <?= apply_filters('the_content', $wp_page->post_content) ?>
      </div>
      <?php if (current_user_can('edit_post', $wp_page->ID)): ?>
        <p class="info-page-edit"><a href="<?= esc_url(get_edit_post_link($wp_page->ID)) ?>">Edit this page in WordPress</a></p>
      <?php endif; ?>
    <?php else: ?>
      <h1 class="info-page-title">Page not found</h1>
      <div class="info-page-body">
        <p>Sorry, we couldn't find that page. <a href="index.php">Go back to our cars</a>.</p>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php include __DIR__ . '/footer.php'; ?>
