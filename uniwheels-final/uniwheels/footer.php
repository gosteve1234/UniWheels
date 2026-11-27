<?php
$uw_settings = $settings ?? (function_exists('uw_default_settings') ? uw_default_settings() : [
    'messenger_url'  => 'https://m.me/uniwheels',
    'footer_address' => 'UniWheels Auto Mall, Main Highway Showroom Strip',
    'showroom_phone' => '(02) 8123-4567 / 0917-123-4567',
    'showroom_email' => 'sales@uniwheelsmotors.com',
]);
$uw_esc = fn($s) => htmlspecialchars((string) ($s ?? ''), ENT_QUOTES, 'UTF-8');
?>
  <?php if (empty($hide_guarantee)): ?>
  <section id="about" class="trust-guarantee-section">
    <div class="container">
      <div class="trust-section-header">
        <span class="trust-tag">Our Commitment</span>
        <h2 class="trust-title">Why Choose UniWheels Motors?</h2>
      </div>

      <div class="trust-grid">
        <div class="trust-card">
          <div class="trust-icon-box">
            <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
              <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
          </div>
          <div class="trust-stat">MECHANIC CHECKED</div>
          <h3 class="trust-card-title">Full Inspection</h3>
          <p class="trust-card-desc">Engine, transmission, brakes, and electrical systems road-tested before listing.</p>
        </div>

        <div class="trust-card">
          <div class="trust-icon-box">
            <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
          </div>
          <div class="trust-stat">HONEST DISCLOSURE</div>
          <h3 class="trust-card-title">Verified History</h3>
          <p class="trust-card-desc">Authentic mileage, ownership history, and documented condition photos on every unit.</p>
        </div>

        <div class="trust-card">
          <div class="trust-icon-box">
            <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
              <line x1="16" y1="13" x2="8" y2="13"></line>
              <line x1="16" y1="17" x2="8" y2="17"></line>
            </svg>
          </div>
          <div class="trust-stat">CLEAN PAPERS</div>
          <h3 class="trust-card-title">Original OR / CR</h3>
          <p class="trust-card-desc">Verified titles, LTO registrations, and HPG clearance ready for transfer.</p>
        </div>

        <div class="trust-card">
          <div class="trust-icon-box">
            <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="23 4 23 10 17 10"></polyline>
              <polyline points="1 20 1 14 7 14"></polyline>
              <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
            </svg>
          </div>
          <div class="trust-stat">FAIR APPRAISAL</div>
          <h3 class="trust-card-title">15-Min Trade-In</h3>
          <p class="trust-card-desc">Apply your current car's appraised value toward any unit in our showroom.</p>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <footer class="site-footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand-col">
          <div class="footer-logo">
            <img src="assets/images/logo.png" alt="UniWheels Motors" class="footer-logo-img">
            <div>
              <div class="footer-brand-title">UNI<span>WHEELS</span> MOTORS</div>
              <div class="footer-brand-sub">RIDE YOUR DREAMS</div>
            </div>
          </div>
          <p class="footer-about-text">
            Certified pre-owned vehicles with transparent condition reports, verified papers, and hassle-free showroom visits.
          </p>
        </div>

        <div class="footer-col">
          <h4 class="footer-col-title">Showroom Hours</h4>
          <ul class="footer-hours-list">
            <li><span>Monday – Saturday:</span> <strong>8:30 AM – 6:30 PM</strong></li>
            <li><span>Sunday:</span> <strong>9:00 AM – 5:00 PM</strong></li>
            <li class="footer-walkin-note">Walk-ins welcome</li>
          </ul>
        </div>

        <div class="footer-col">
          <h4 class="footer-col-title">Location &amp; Contact</h4>
          <div class="footer-contact-item">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
            <span><?= $uw_esc($uw_settings['footer_address']) ?></span>
          </div>
          <div class="footer-contact-item">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
            <span><?= $uw_esc($uw_settings['showroom_phone']) ?></span>
          </div>
          <div class="footer-contact-item">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
            <span><?= $uw_esc($uw_settings['showroom_email']) ?></span>
          </div>
          <div style="margin-top: 12px;">
            <a href="admin-dashboard/login.php" class="footer-staff-link">Staff &amp; Admin Sign In &rarr;</a>
          </div>
        </div>
      </div>

      <div class="footer-bottom-bar">
        <nav class="footer-links" aria-label="More information">
          <a href="page.php?p=about">About Us</a>
          <a href="page.php?p=contact">Contact Us</a>
          <a href="page.php?p=terms-warranty">Terms &amp; Warranty</a>
          <a href="page.php?p=privacy-policy">Privacy Policy</a>
        </nav>
        <p>&copy; <?php echo date('Y'); ?> UniWheels Motors. All Rights Reserved.</p>
      </div>
    </div>
  </footer>

  <div class="floating-messenger-wrap">
    <a href="<?= $uw_esc($uw_settings['messenger_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn-floating-messenger" id="floatingMessengerBtn" title="Chat with us on Messenger">
      <svg viewBox="0 0 28 28" width="24" height="24" fill="currentColor">
        <path d="M14 2C7.373 2 2 6.945 2 13.045c0 3.477 1.745 6.577 4.473 8.577V26l4.24-2.327c1.045.291 2.155.455 3.287.455 6.627 0 12-4.945 12-11.045S20.627 2 14 2zm1.2 14.882l-3.073-3.282-6 3.282 6.6-7.009 3.145 3.282 5.928-3.282-6.6 7.009z"/>
      </svg>
      <span class="floating-messenger-text">Inquire on Messenger</span>
    </a>
  </div>

  <!-- Vehicle Details Modal -->
  <div id="specsModal" class="modal-backdrop" role="dialog" aria-modal="true" style="display:none;">
    <div class="modal-content modal-content-wide">
      <div class="modal-header">
        <h3 id="modalCarTitle" class="modal-title">Vehicle Information</h3>
        <button type="button" id="modalCloseBtn" class="modal-close" aria-label="Close modal">&times;</button>
      </div>
      <div id="modalContentBody" class="modal-body"></div>
    </div>
  </div>

  <!-- Fullscreen Photo Zoom / Pinch-to-Zoom Inspector -->
  <div id="photoLightbox" class="lightbox-backdrop" role="dialog" aria-modal="true" style="display:none;">
    <div class="lightbox-topbar">
      <span id="lightboxCaption" class="lightbox-caption"></span>
      <div class="lightbox-tools">
        <button type="button" id="lightboxZoomOut" class="lightbox-tool-btn" aria-label="Zoom out">&minus;</button>
        <button type="button" id="lightboxZoomReset" class="lightbox-tool-btn" aria-label="Reset zoom">100%</button>
        <button type="button" id="lightboxZoomIn" class="lightbox-tool-btn" aria-label="Zoom in">+</button>
        <button type="button" id="lightboxClose" class="lightbox-tool-btn lightbox-close-btn" aria-label="Close inspector">&times;</button>
      </div>
    </div>
    <div id="lightboxStage" class="lightbox-stage">
      <button type="button" id="lightboxPrev" class="modal-arrow-btn modal-arrow-prev" aria-label="Previous photo">&lsaquo;</button>
      <img id="lightboxImg" src="" alt="" class="lightbox-img" draggable="false">
      <button type="button" id="lightboxNext" class="modal-arrow-btn modal-arrow-next" aria-label="Next photo">&rsaquo;</button>
    </div>
    <div class="lightbox-hint">Pinch, scroll, or double-tap to zoom · Drag to pan</div>
  </div>

  <script src="assets/js/main.js?v=<?= @filemtime(__DIR__ . '/assets/js/main.js') ?: '1' ?>"></script>
</body>
</html>
