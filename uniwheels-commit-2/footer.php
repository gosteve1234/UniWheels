<?php
/**
 * UniWheels Footer Template - Pre-Owned Car Showroom & Trade-In Specialists (Standalone)
 * Includes Upgraded Vehicle Profile Modal (with Magnifier + Condition & History),
 * Multi-Step Confirmation Popups, Reservation Hold Wizard, Lightbox Pinch-Zoom, and My Reservations Tracker.
 */

$uw_settings = $GLOBALS['uniwheels_settings'] ?? (function_exists('uniwheels_default_settings') ? uniwheels_default_settings() : []);
$messenger_base = !empty($uw_settings['messenger_url']) ? $uw_settings['messenger_url'] : 'https://m.me/uniwheels';
$script_ver = file_exists(__DIR__ . '/assets/js/main.js') ? filemtime(__DIR__ . '/assets/js/main.js') : time();
?>
  <!-- Trust & Customer Assurance Section -->
  <section id="about" class="trust-guarantee-section">
    <div class="container">
      <div class="trust-section-header">
        <span class="trust-tag">Our Commitment</span>
        <h2 class="trust-title">Why Choose UniWheels Motors?</h2>
        <p class="trust-subtitle">We make buying, reserving, and trading in secondhand vehicles safe, transparent, and simple for everyday motorists.</p>
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
          <h3 class="trust-card-title">50-Point Inspection</h3>
          <p class="trust-card-desc">Every engine, transmission, brakes, and electrical system is road-tested and verified by experienced technicians before display.</p>
        </div>

        <div class="trust-card">
          <div class="trust-icon-box">
            <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
          </div>
          <div class="trust-stat">HONEST DISCLOSURE</div>
          <h3 class="trust-card-title">Condition &amp; History</h3>
          <p class="trust-card-desc">Every listing discloses exact vehicle age, ownership count, and close-up photos of any pre-existing minor scratches.</p>
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
          <h3 class="trust-card-title">Complete Original OR / CR</h3>
          <p class="trust-card-desc">All vehicles have verified titles, up-to-date LTO registrations, HPG clearance checks, and are ready for hassle-free transfer.</p>
        </div>

        <div class="trust-card">
          <div class="trust-icon-box">
            <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
          </div>
          <div class="trust-stat">FLEXIBLE HOLDS</div>
          <h3 class="trust-card-title">1–<?php echo intval($uw_settings['max_hold_days'] ?? 7); ?> Day Reservation Hold</h3>
          <p class="trust-card-desc">Temporarily lock your chosen unit online with a live countdown while you arrange showroom viewing or payment.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Friendly Showroom Footer -->
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
            Your neighborhood trusted pre-owned car retailer. Complete vehicle profiles, transparent damage disclosures, zoomable photos, and worry-free reservation holds.
          </p>
        </div>

        <div class="footer-col">
          <h4 class="footer-col-title">Showroom Hours</h4>
          <ul class="footer-hours-list">
            <li><?php echo esc_html($uw_settings['contact_hours'] ?? 'Open Everyday: 8:30 AM – 6:30 PM'); ?></li>
            <li class="footer-walkin-note">✓ Walk-ins &amp; personal mechanics warmly welcomed!</li>
          </ul>
        </div>

        <div class="footer-col">
          <h4 class="footer-col-title">Showroom Location &amp; Contact</h4>
          <div class="footer-contact-item">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
            <span><?php echo esc_html($uw_settings['contact_address'] ?? 'UniWheels Auto Mall, Main Highway'); ?></span>
          </div>
          <div class="footer-contact-item">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
            <span><?php echo esc_html($uw_settings['contact_phone'] ?? '(02) 8123-4567 / 0917-123-4567'); ?></span>
          </div>
          <div class="footer-contact-item">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
            <span><?php echo esc_html($uw_settings['contact_email'] ?? 'sales@uniwheelsmotors.com'); ?></span>
          </div>
          <div style="margin-top: 15px;">
            <a href="admin-dashboard/login.php" class="footer-staff-link">Staff &amp; ECMS Admin Sign In &rarr;</a>
          </div>
        </div>
      </div>

      <div class="footer-bottom-bar">
        <p>&copy; <?php echo date('Y'); ?> UniWheels Motors. All Rights Reserved. Certified Pre-Owned Automotive Retailer &amp; Trade-In Specialists.</p>
      </div>
    </div>
  </footer>

  <!-- Floating Messenger Quick-Chat Button -->
  <div class="floating-messenger-wrap">
    <a href="<?php echo esc_url($messenger_base); ?>" target="_blank" rel="noopener noreferrer" class="btn-floating-messenger" id="floatingMessengerBtn" title="Need help? Click to chat with us on Messenger!">
      <svg viewBox="0 0 28 28" width="24" height="24" fill="currentColor">
        <path d="M14 2C7.373 2 2 6.945 2 13.045c0 3.477 1.745 6.577 4.473 8.577V26l4.24-2.327c1.045.291 2.155.455 3.287.455 6.627 0 12-4.945 12-11.045S20.627 2 14 2zm1.2 14.882l-3.073-3.282-6 3.282 6.6-7.009 3.145 3.282 5.928-3.282-6.6 7.009z"/>
      </svg>
      <span class="floating-messenger-text">Inquire on Messenger</span>
    </a>
  </div>

  <!-- =========================================================================
       MODAL 1: COMPLETE VEHICLE PROFILE, MAGNIFIER & CONDITION REPORT (Add-ons A & D)
       ========================================================================= -->
  <div id="specsModal" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modalCarTitle" style="display:none;">
    <div class="modal-content modal-content-xl">
      <div class="modal-header">
        <div>
          <h3 id="modalCarTitle" class="modal-title">Vehicle Information</h3>
          <div id="modalCarSubtitle" class="modal-subtitle-meta"></div>
        </div>
        <button type="button" id="modalCloseBtn" class="modal-close" aria-label="Close modal">&times;</button>
      </div>
      <div id="modalContentBody" class="modal-body">
        <!-- Dynamically populated in main.js -->
      </div>
    </div>
  </div>

  <!-- =========================================================================
       MODAL 2: BOOKING CONFIRMATION POPUPS & HOLD DURATION WIZARD (Add-ons B & C)
       ========================================================================= -->
  <div id="bookingWizardModal" class="modal-backdrop" role="dialog" aria-modal="true" style="display:none; z-index:2200;">
    <div class="modal-content booking-popup-card">
      <div class="modal-header">
        <h3 id="bookingModalTitle" class="modal-title">Confirm Vehicle Reservation</h3>
        <button type="button" id="bookingModalCloseBtn" class="modal-close" aria-label="Close reservation popup">&times;</button>
      </div>
      <div id="bookingModalBody" class="modal-body">
        <!-- Step 1: "Do you want to book this car?" (Yes, Proceed / Cancel)
             Step 2: "How many days would you like to reserve this car?" (1, 2, 3, Custom) + Customer Info
             Step 3: "Do you want to finalize your booking?" Confirmation
             Step 4: Confirmed Receipt + Booking Reference + Live Countdown + Email/SMS Notice -->
      </div>
    </div>
  </div>

  <!-- =========================================================================
       MODAL 3: GENERIC ACTION CONFIRMATION POPUP (e.g., Remove Reservation)
       ========================================================================= -->
  <div id="confirmActionModal" class="modal-backdrop" role="dialog" aria-modal="true" style="display:none; z-index:2400;">
    <div class="modal-content confirm-popup-sm">
      <div class="confirm-popup-body">
        <div class="confirm-popup-icon" id="confirmPopupIcon">⚠️</div>
        <h3 class="confirm-popup-title" id="confirmPopupQuestion">Are you sure?</h3>
        <p class="confirm-popup-desc" id="confirmPopupDesc"></p>
        <div class="confirm-popup-buttons">
          <button type="button" class="btn-popup-cancel" id="confirmPopupCancelBtn">Cancel</button>
          <button type="button" class="btn-popup-proceed" id="confirmPopupProceedBtn">Yes, Proceed</button>
        </div>
      </div>
    </div>
  </div>

  <!-- =========================================================================
       MODAL 4: MY RESERVATIONS & BOOKING REFERENCE LOOKUP (Add-ons B & C)
       ========================================================================= -->
  <div id="myReservationsModal" class="modal-backdrop" role="dialog" aria-modal="true" style="display:none; z-index:2150;">
    <div class="modal-content" style="max-width:680px;">
      <div class="modal-header">
        <h3 class="modal-title">My Reservations &amp; Hold Countdown</h3>
        <button type="button" id="myResCloseBtn" class="modal-close" aria-label="Close">&times;</button>
      </div>
      <div class="modal-body">
        <form id="resLookupForm" class="res-lookup-bar">
          <input type="text" id="resLookupInput" class="filter-input" placeholder="Look up by Booking Reference (e.g. UW-2026-...), Email, or Mobile #">
          <button type="submit" class="btn-lookup-submit">Find Booking</button>
        </form>
        <div id="myReservationsList" class="my-reservations-list">
          <!-- Populated dynamically -->
        </div>
      </div>
    </div>
  </div>

  <!-- =========================================================================
       MODAL 5: MOBILE PINCH-TO-ZOOM & DESKTOP FULLSCREEN INSPECTOR LIGHTBOX (Add-on D)
       ========================================================================= -->
  <div id="photoZoomLightbox" class="zoom-lightbox-backdrop" role="dialog" aria-modal="true" style="display:none; z-index:2500;">
    <div class="zoom-lightbox-topbar">
      <div class="zoom-lightbox-caption" id="zoomLightboxCaption">Tap, scroll, or pinch to zoom in on vehicle details</div>
      <div class="zoom-lightbox-controls">
        <button type="button" class="btn-zoom-ctrl" id="zoomOutBtn" title="Zoom Out">&minus;</button>
        <span class="zoom-level-indicator" id="zoomLevelText">100%</span>
        <button type="button" class="btn-zoom-ctrl" id="zoomInBtn" title="Zoom In">+</button>
        <button type="button" class="btn-zoom-ctrl" id="zoomResetBtn" title="Reset">Reset</button>
        <button type="button" class="btn-zoom-close" id="zoomCloseBtn" aria-label="Close zoom inspector">&times;</button>
      </div>
    </div>
    <div class="zoom-lightbox-stage" id="zoomLightboxStage">
      <img id="zoomLightboxImg" src="" alt="High-Resolution Vehicle Inspection" class="zoom-lightbox-img">
    </div>
  </div>

  <script src="assets/js/main.js?v=<?php echo $script_ver; ?>"></script>
</body>
</html>
