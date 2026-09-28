<?php
/**
 * UniWheels Theme Header Template
 *
 * @package UniWheels
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Sleek Luxury Navbar (No ugly pills/icons, VIP concierge removed) -->
<header class="site-header">
  <div class="container">
    <div class="nav-wrapper">
      
      <!-- Official Brand Logo -->
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="brand-link" title="<?php bloginfo( 'name' ); ?>">
        <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>" alt="UniWheels Logo" class="brand-emblem">
        <div class="brand-title-group">
          <div class="brand-logo-text">
            <span class="brand-uni">UNI</span><span class="brand-wheels">WHEELS</span>
          </div>
          <span class="brand-subtitle">FORGED & PERFORMANCE</span>
        </div>
      </a>

      <!-- Luxury Dealership Navigation Links -->
      <nav class="site-nav" aria-label="<?php esc_attr_e( 'Main Navigation', 'uniwheels' ); ?>">
        <a href="<?php echo esc_url( home_url( '/#showcase' ) ); ?>" class="nav-link">3D Showroom</a>
        <a href="<?php echo esc_url( home_url( '/#inventoryCatalogSection' ) ); ?>" class="nav-link">Inventory</a>
        <a href="<?php echo esc_url( home_url( '/#inquirySection' ) ); ?>" class="nav-link">Inquiries</a>
        <a href="<?php echo esc_url( get_permalink( 14 ) ); ?>" class="nav-link">About</a>
      </nav>

      <!-- Sleek Dealership Action CTAs (Inquire + Circular User Account) -->
      <div class="nav-actions">
        <a href="<?php echo esc_url( home_url( '/#inquirySection' ) ); ?>" class="btn-nav-inquire">Inquire Vehicle</a>
        <button type="button" class="btn-nav-user-circle" id="userAuthBtn" title="Customer Account" aria-label="Customer Account">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
          <span class="user-active-badge" id="userActiveBadge" style="display:none;"></span>
        </button>
      </div>

    </div>
  </div>
</header>
