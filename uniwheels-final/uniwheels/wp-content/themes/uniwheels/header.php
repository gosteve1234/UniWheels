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
    <script>
      (function() {
        try {
          var saved = localStorage.getItem('uniwheels_theme');
          if (saved === 'light' || saved === 'dark') {
            document.documentElement.setAttribute('data-theme', saved);
          } else {
            document.documentElement.setAttribute('data-theme', 'dark');
          }
        } catch (e) {}
      })();
    </script>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Sleek Luxury Navbar -->
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
          <span class="brand-subtitle">DRIVE YOUR DREAMS</span>
        </div>
      </a>

      <!-- Luxury Dealership Navigation Links -->
      <nav class="site-nav" aria-label="<?php esc_attr_e( 'Main Navigation', 'uniwheels' ); ?>">
        <a href="<?php echo esc_url( home_url( '/#showcase' ) ); ?>" class="nav-link">3D Showroom</a>
        <a href="<?php echo esc_url( home_url( '/#inventoryCatalogSection' ) ); ?>" class="nav-link">Vehicle Fleet</a>
        <a href="<?php echo esc_url( home_url( '/#about' ) ); ?>" class="nav-link">About</a>
      </nav>

      <!-- Sleek Dealership Action CTAs (Direct Messenger Inquire + Theme Toggle) -->
      <div class="nav-actions">
        <a href="https://m.me/uniwheels" target="_blank" rel="noopener" class="btn-nav-inquire">
          <svg viewBox="0 0 28 28" width="16" height="16" fill="currentColor" style="margin-right:6px; display:inline-block; vertical-align:middle;">
            <path d="M14 2C7.373 2 2 6.945 2 13.045c0 3.477 1.745 6.577 4.473 8.577V26l4.24-2.327c1.045.291 2.155.455 3.287.455 6.627 0 12-4.945 12-11.045S20.627 2 14 2zm1.2 14.882l-3.073-3.282-6 3.282 6.6-7.009 3.145 3.282 5.928-3.282-6.6 7.009z"/>
          </svg>Inquire via Messenger
        </a>
        <!-- Luxury Supercar Cockpit Theme Toggle (Dark / Light Mode) -->
        <button type="button" class="btn-theme-toggle" id="themeToggleBtn" aria-label="Toggle Light and Dark Mode" title="Toggle Light/Dark Theme" role="switch" aria-checked="false">
          <span class="theme-toggle-track">
            <span class="theme-toggle-icon theme-icon-sun" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="5"></circle>
                <line x1="12" y1="1" x2="12" y2="3"></line>
                <line x1="12" y1="21" x2="12" y2="23"></line>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                <line x1="1" y1="12" x2="3" y2="12"></line>
                <line x1="21" y1="12" x2="23" y2="12"></line>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
              </svg>
            </span>
            <span class="theme-toggle-icon theme-icon-moon" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
              </svg>
            </span>
            <span class="theme-toggle-thumb" aria-hidden="true"></span>
          </span>
        </button>
      </div>

    </div>
  </div>
</header>
