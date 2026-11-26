<?php
/**
 * UniWheels Header Template - Pre-Owned & Trade-Ins (ECMS-Driven Standalone)
 */

if (!isset($GLOBALS['uniwheels_settings'])) {
    require_once __DIR__ . '/includes/uniwheels-core.php';
    $uw_pdo = uniwheels_get_pdo();
    if ($uw_pdo) {
        uniwheels_ensure_schema($uw_pdo);
        uniwheels_release_expired_holds($uw_pdo);
        $GLOBALS['uniwheels_settings'] = uniwheels_get_settings($uw_pdo);
    } else {
        $GLOBALS['uniwheels_settings'] = uniwheels_default_settings();
    }
}
$uw_settings = $GLOBALS['uniwheels_settings'];

if (!function_exists('esc_html')) {
    function esc_html($s) { return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('esc_attr')) {
    function esc_attr($s) { return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('esc_url')) {
    function esc_url($s) { return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$style_ver = file_exists(__DIR__ . '/assets/css/style.css') ? filemtime(__DIR__ . '/assets/css/style.css') : time();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniWheels Motors - Certified Pre-Owned &amp; Trade-Ins</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">

    <!-- Google Fonts: Inter and Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Main Dealership Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo $style_ver; ?>">
</head>
<body>

<?php if (!empty($uw_settings['announcement_banner'])): ?>
<div class="ecms-announcement-bar">
  <div class="container ecms-announcement-inner">
    <span class="ecms-live-dot" aria-hidden="true"></span>
    <span><?php echo esc_html($uw_settings['announcement_banner']); ?></span>
  </div>
</div>
<?php endif; ?>

<!-- Clean, Friendly Navigation Bar for Everyday Drivers & Families -->
<header class="site-header">
  <div class="container">
    <div class="nav-wrapper">
      
      <!-- Official Brand Logo -->
      <a href="index.php" class="brand-link" title="UniWheels Motors - RIDE YOUR DREAMS">
        <img src="assets/images/logo.png" alt="UniWheels Motors Logo" class="brand-emblem">
        <div class="brand-title-group">
          <div class="brand-logo-text">
            <span class="brand-uni">UNI</span><span class="brand-wheels">WHEELS</span> <span class="brand-motors">MOTORS</span>
          </div>
          <span class="brand-subtitle">RIDE YOUR DREAMS</span>
        </div>
      </a>

      <!-- Friendly, Highly Readable Navigation Links -->
      <nav class="site-nav" aria-label="Main Navigation">
        <a href="#showcase" class="nav-link">Featured Cars</a>
        <a href="#inventoryCatalogSection" class="nav-link">Available Inventory</a>
        <a href="#policiesSection" class="nav-link">Policies</a>
        <a href="#tradeInSection" class="nav-link">Trade-In</a>
        <a href="#inquirySection" class="nav-link">Visit &amp; Contact</a>
      </nav>

      <!-- Quick Customer Reservation Tracker Button -->
      <div class="nav-actions" style="margin-left:auto;">
        <button type="button" class="btn-my-reservations" id="openMyReservationsBtn" title="View your active reservation holds or look up a booking reference">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
          </svg>
          <span>My Reservations</span>
          <span class="my-res-count-pill" id="myResCountBadge" style="display:none;">0</span>
        </button>
      </div>

    </div>
  </div>
</header>
