<?php
/**
 * UniWheels Theme Footer Template
 *
 * @package UniWheels
 */
?>
  <!-- Trust & Quality Strip -->
  <section id="about" style="padding: 3rem 0; border-top: 1px solid var(--border-subtle); background: var(--bg-deep);">
    <div class="container">
      <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem; text-align: center;">
        <div>
          <div style="font-family: var(--font-heading); font-size: 2.2rem; font-weight: 800; color: var(--uni-white);">6061-T6</div>
          <div style="font-size: 0.8rem; color: var(--wheels-crimson); font-weight: 700; text-transform: uppercase;">Aerospace Forged Aluminum</div>
        </div>
        <div>
          <div style="font-family: var(--font-heading); font-size: 2.2rem; font-weight: 800; color: var(--uni-white);">10,000+</div>
          <div style="font-size: 0.8rem; color: var(--wheels-crimson); font-weight: 700; text-transform: uppercase;">Tons Forging Pressure</div>
        </div>
        <div>
          <div style="font-family: var(--font-heading); font-size: 2.2rem; font-weight: 800; color: var(--uni-white);">100%</div>
          <div style="font-size: 0.8rem; color: var(--wheels-crimson); font-weight: 700; text-transform: uppercase;">100-Point Inspection & Certified</div>
        </div>
        <div>
          <div style="font-family: var(--font-heading); font-size: 2.2rem; font-weight: 800; color: var(--uni-white);">LIFETIME</div>
          <div style="font-size: 0.8rem; color: var(--wheels-crimson); font-weight: 700; text-transform: uppercase;">Structural Warranty</div>
        </div>
      </div>
    </div>
  </section>

  <!-- Technical Specs Modal -->
  <div id="specsModal" class="modal-backdrop" role="dialog" aria-modal="true">
    <div class="modal-content">
      <div class="modal-header">
        <h3 id="modalCarTitle" class="modal-title">Vehicle Technical Specs</h3>
        <button type="button" id="modalCloseBtn" class="modal-close" aria-label="Close modal">&times;</button>
      </div>
      <div id="modalContentBody" class="modal-body">
        <!-- Injected via JavaScript -->
      </div>
    </div>
  </div>



  <!-- Notification Toast -->
  <div id="toastNotice" class="toast-notice" role="alert">
    <div style="display: flex; align-items: center; gap: 10px;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#e52945" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
      <span id="toastMessage" style="font-weight: 600; font-size: 0.9rem;">Build Selected</span>
    </div>
  </div>

  <!-- Site Footer -->
  <footer class="site-footer">
    <div class="container">
      <div class="footer-grid">
        
        <!-- Brand Col -->
        <div class="footer-col">
          <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 1.25rem;">
            <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>" alt="UniWheels Logo" style="height: 48px; width: auto;">
            <span class="brand-logo" style="font-size: 1.4rem;">
              <span class="brand-uni">UNI</span><span class="brand-wheels">WHEELS</span>
            </span>
          </div>
          <p style="color: var(--text-secondary); font-size: 0.85rem; max-width: 320px; margin-bottom: 1.5rem;">
            Premier bespoke wheel engineering and high-performance exotic automotive acquisitions. Designed with passion, precision, and power.
          </p>
          <div style="font-size: 0.8rem; color: var(--text-muted);">
            Location: Luna Street Brgy. Laguda Lapaz, Iloilo City, Philippines, 5000
          </div>
        </div>

        <!-- Quick Links -->
        <div class="footer-col">
          <h5>Navigation</h5>
          <ul class="footer-links">
            <li><a href="<?php echo esc_url( home_url( '/#showcase' ) ); ?>">3D Showroom</a></li>
            <li><a href="<?php echo esc_url( home_url( '/#inventoryCatalogSection' ) ); ?>">Vehicle Fleet & Search</a></li>
            <li><a href="<?php echo esc_url( home_url( '/#about' ) ); ?>">Quality Standards</a></li>
          </ul>
        </div>

        <!-- Direct Inquiries -->
        <div class="footer-col">
          <h5>Direct Inquiries</h5>
          <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0.8rem;">
            All inquiries and vehicle acquisitions are handled directly via Facebook Messenger:
          </p>
          <div style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 700; color: var(--uni-white); margin-bottom: 0.4rem;">
            contact@uniwheels.com
          </div>
          <div style="color: var(--wheels-bright-red); font-size: 0.95rem; font-weight: 600; margin-bottom: 1.25rem;">
            +1 (800) 864-9433
          </div>
          <a href="https://m.me/uniwheels" target="_blank" rel="noopener" class="btn-nav-inquire" style="width: 100%; text-align: center; justify-content: center;">
            <svg viewBox="0 0 28 28" width="16" height="16" fill="currentColor" style="margin-right:6px; display:inline-block; vertical-align:middle;">
              <path d="M14 2C7.373 2 2 6.945 2 13.045c0 3.477 1.745 6.577 4.473 8.577V26l4.24-2.327c1.045.291 2.155.455 3.287.455 6.627 0 12-4.945 12-11.045S20.627 2 14 2zm1.2 14.882l-3.073-3.282-6 3.282 6.6-7.009 3.145 3.282 5.928-3.282-6.6 7.009z"/>
            </svg>Inquire via Messenger
          </a>
        </div>

      </div>

      <div class="footer-bottom">
        <div>&copy; <?php echo date( 'Y' ); ?> UniWheels Performance Inc. All Rights Reserved.</div>
        <div style="display: flex; gap: 1.5rem;">
          <a href="<?php echo esc_url( get_permalink( 3 ) ); ?>" style="color: var(--text-muted);">Privacy Policy</a>
          <a href="<?php echo esc_url( get_permalink( 17 ) ); ?>" style="color: var(--text-muted);">Terms & Warranty</a>
          <a href="<?php echo esc_url( get_permalink( 16 ) ); ?>" style="color: var(--text-muted);">Contact</a>
        </div>
      </div>
    </div>
  </footer>

  <?php wp_footer(); ?>
</body>
</html>
