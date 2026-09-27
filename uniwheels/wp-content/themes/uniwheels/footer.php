<?php
/**
 * UniWheels Theme Footer Template
 *
 * @package UniWheels
 */
?>
  <!-- Trust & Quality Strip -->
  <section id="about" style="padding: 3rem 0; border-top: 1px solid var(--border-subtle); background: #07070a;">
    <div class="container">
      <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem; text-align: center;">
        <div>
          <div style="font-family: var(--font-heading); font-size: 2.2rem; font-weight: 800; color: #fff;">6061-T6</div>
          <div style="font-size: 0.8rem; color: var(--wheels-crimson); font-weight: 700; text-transform: uppercase;">Aerospace Forged Aluminum</div>
        </div>
        <div>
          <div style="font-family: var(--font-heading); font-size: 2.2rem; font-weight: 800; color: #fff;">10,000+</div>
          <div style="font-size: 0.8rem; color: var(--wheels-crimson); font-weight: 700; text-transform: uppercase;">Tons Forging Pressure</div>
        </div>
        <div>
          <div style="font-family: var(--font-heading); font-size: 2.2rem; font-weight: 800; color: #fff;">100%</div>
          <div style="font-size: 0.8rem; color: var(--wheels-crimson); font-weight: 700; text-transform: uppercase;">100-Point Inspection & Certified</div>
        </div>
        <div>
          <div style="font-family: var(--font-heading); font-size: 2.2rem; font-weight: 800; color: #fff;">LIFETIME</div>
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

  <!-- Customer Login & Sign Up Modal (users table) -->
  <div id="customerAuthModal" class="customer-modal-backdrop" role="dialog" aria-modal="true">
    <div class="customer-modal-dialog">
      <div class="customer-modal-header">
        <div style="display:flex; align-items:center; gap:10px;">
          <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>" alt="UniWheels" style="height:28px; width:auto; filter:drop-shadow(0 0 8px rgba(196,21,46,0.6));">
          <div class="customer-modal-title">
            <span style="color:#fff;">CUSTOMER</span> <span style="color:var(--wheels-crimson);">ACCESS</span>
          </div>
        </div>
        <button type="button" class="customer-modal-close" id="customerModalClose" aria-label="Close modal">&times;</button>
      </div>

      <!-- Auth State 1: Guest (Sign In / Register) -->
      <div id="authGuestView">
        <div class="auth-tabs">
          <button type="button" class="auth-tab-btn active" id="tabBtnLogin" onclick="switchCustomerAuthTab('login')">Sign In</button>
          <button type="button" class="auth-tab-btn" id="tabBtnRegister" onclick="switchCustomerAuthTab('register')">Create Account</button>
        </div>

        <div class="customer-modal-body">
          <div id="authFeedback" class="auth-feedback-box"></div>

          <!-- Sign In Form -->
          <form id="customerLoginForm" onsubmit="handleCustomerLogin(event)">
            <div class="auth-form-group">
              <label class="auth-label" for="loginIdentifier">Username or Email</label>
              <input type="text" id="loginIdentifier" class="auth-input" placeholder="e.g. jferrari or julian@example.com" required>
            </div>
            <div class="auth-form-group">
              <label class="auth-label" for="loginPassword">Password</label>
              <input type="password" id="loginPassword" class="auth-input" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-auth-submit" id="loginSubmitBtn">Sign In to UniWheels &rarr;</button>
            <div style="text-align:center; margin-top:14px; font-size:0.78rem; color:var(--text-sub);">
              Don't have an account? <a href="javascript:void(0)" onclick="switchCustomerAuthTab('register')" style="color:var(--wheels-neon); font-weight:600; text-decoration:underline;">Create one now</a>
            </div>
          </form>

          <!-- Register Form -->
          <form id="customerRegisterForm" style="display:none;" onsubmit="handleCustomerRegister(event)">
            <div class="auth-form-group">
              <label class="auth-label" for="regFullName">Full Name</label>
              <input type="text" id="regFullName" class="auth-input" placeholder="e.g. Julian Ferrari" required>
            </div>
            <div class="auth-form-group">
              <label class="auth-label" for="regUsername">Username</label>
              <input type="text" id="regUsername" class="auth-input" placeholder="e.g. jferrari" required>
            </div>
            <div class="auth-form-group">
              <label class="auth-label" for="regEmail">Email Address</label>
              <input type="email" id="regEmail" class="auth-input" placeholder="e.g. julian@example.com" required>
            </div>
            <div class="auth-form-group">
              <label class="auth-label" for="regPassword">Password (6+ characters)</label>
              <input type="password" id="regPassword" class="auth-input" placeholder="••••••••" minlength="6" required>
            </div>
            <button type="submit" class="btn-auth-submit" id="regSubmitBtn">Create Customer Account &rarr;</button>
            <div style="text-align:center; margin-top:14px; font-size:0.78rem; color:var(--text-sub);">
              Already have an account? <a href="javascript:void(0)" onclick="switchCustomerAuthTab('login')" style="color:var(--wheels-neon); font-weight:600; text-decoration:underline;">Sign in here</a>
            </div>
          </form>
        </div>
      </div>

      <!-- Auth State 2: Logged-in Customer View -->
      <div id="authCustomerView" style="display:none;" class="customer-modal-body">
        <div class="customer-profile-view">
          <div class="customer-profile-avatar-big">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
          </div>
          <div class="customer-profile-name" id="custProfileName">Customer</div>
          <div class="customer-profile-email" id="custProfileEmail">customer@example.com</div>
          <div class="customer-profile-badge">● Verified Client</div>

          <div style="display:flex; flex-direction:column; gap:8px; margin-top:1rem;">
            <a href="#showcase" class="btn-nav-inquire" style="text-align:center; justify-content:center;" onclick="closeCustomerModal()">Explore Featured Builds</a>
            <button type="button" class="btn-side-specs" style="width:100%;" onclick="handleCustomerLogout()">Sign Out</button>
          </div>
        </div>
      </div>

    </div>
  </div>

  <script>
  // Customer Authentication Controller
  (function() {
    const authBtn = document.getElementById('userAuthBtn');
    const authModal = document.getElementById('customerAuthModal');
    const closeBtn = document.getElementById('customerModalClose');
    const userBtnText = document.getElementById('userBtnText');
    const authGuestView = document.getElementById('authGuestView');
    const authCustomerView = document.getElementById('authCustomerView');
    const feedbackBox = document.getElementById('authFeedback');
    const custName = document.getElementById('custProfileName');
    const custEmail = document.getElementById('custProfileEmail');

    const authEndpoint = '<?php echo esc_url( home_url( "/customer-auth.php" ) ); ?>';

    function checkSession() {
      fetch(authEndpoint + '?action=check')
        .then(res => res.json())
        .then(data => {
          if (data.logged_in && data.user) {
            setLoggedInState(data.user);
          } else {
            setLoggedOutState();
          }
        })
        .catch(() => setLoggedOutState());
    }

    function setLoggedInState(user) {
      if (authBtn) {
        authBtn.classList.add('logged-in');
        authBtn.title = "Account: " + (user.name || 'Client');
      }
      const badge = document.getElementById('userActiveBadge');
      if (badge) badge.style.display = 'block';
      if (authGuestView) authGuestView.style.display = 'none';
      if (authCustomerView) authCustomerView.style.display = 'block';
      if (custName) custName.textContent = user.name;
      if (custEmail) custEmail.textContent = user.email;
    }

    function setLoggedOutState() {
      if (authBtn) {
        authBtn.classList.remove('logged-in');
        authBtn.title = "Customer Account";
      }
      const badge = document.getElementById('userActiveBadge');
      if (badge) badge.style.display = 'none';
      if (authGuestView) authGuestView.style.display = 'block';
      if (authCustomerView) authCustomerView.style.display = 'none';
    }

    if (authBtn && authModal) {
      authBtn.addEventListener('click', (e) => {
        e.preventDefault();
        authModal.classList.add('active');
      });
    }

    if (closeBtn && authModal) {
      closeBtn.addEventListener('click', () => {
        authModal.classList.remove('active');
      });
    }

    if (authModal) {
      authModal.addEventListener('click', (e) => {
        if (e.target === authModal) authModal.classList.remove('active');
      });
    }

    window.closeCustomerModal = function() {
      if (authModal) authModal.classList.remove('active');
    };

    window.switchCustomerAuthTab = function(tab) {
      const tabLogin = document.getElementById('tabBtnLogin');
      const tabReg = document.getElementById('tabBtnRegister');
      const formLogin = document.getElementById('customerLoginForm');
      const formReg = document.getElementById('customerRegisterForm');
      if (feedbackBox) {
        feedbackBox.className = 'auth-feedback-box';
        feedbackBox.textContent = '';
        feedbackBox.style.display = 'none';
      }

      if (tab === 'login') {
        tabLogin.classList.add('active');
        tabReg.classList.remove('active');
        formLogin.style.display = 'block';
        formReg.style.display = 'none';
      } else {
        tabReg.classList.add('active');
        tabLogin.classList.remove('active');
        formReg.style.display = 'block';
        formLogin.style.display = 'none';
      }
    };

    window.handleCustomerLogin = function(e) {
      e.preventDefault();
      const identifier = document.getElementById('loginIdentifier').value.trim();
      const password = document.getElementById('loginPassword').value.trim();
      const submitBtn = document.getElementById('loginSubmitBtn');
      submitBtn.disabled = true;
      submitBtn.textContent = 'Signing in...';

      fetch(authEndpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'login', identifier, password })
      })
      .then(r => r.json())
      .then(data => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Sign In to UniWheels →';
        if (data.success) {
          showFeedback(data.message, 'success');
          setTimeout(() => {
            setLoggedInState(data.user);
            if (authModal) authModal.classList.remove('active');
          }, 800);
        } else {
          showFeedback(data.message, 'error');
        }
      })
      .catch(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Sign In to UniWheels →';
        showFeedback('Server connection error. Please try again.', 'error');
      });
    };

    window.handleCustomerRegister = function(e) {
      e.preventDefault();
      const full_name = document.getElementById('regFullName').value.trim();
      const username = document.getElementById('regUsername').value.trim();
      const email = document.getElementById('regEmail').value.trim();
      const password = document.getElementById('regPassword').value.trim();
      const submitBtn = document.getElementById('regSubmitBtn');
      submitBtn.disabled = true;
      submitBtn.textContent = 'Creating account...';

      fetch(authEndpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'register', full_name, username, email, password })
      })
      .then(r => r.json())
      .then(data => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Create Customer Account →';
        if (data.success) {
          showFeedback(data.message, 'success');
          setTimeout(() => {
            setLoggedInState(data.user);
            if (authModal) authModal.classList.remove('active');
          }, 900);
        } else {
          showFeedback(data.message, 'error');
        }
      })
      .catch(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Create Customer Account →';
        showFeedback('Server connection error. Please try again.', 'error');
      });
    };

    window.handleCustomerLogout = function() {
      fetch(authEndpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'logout' })
      })
      .then(r => r.json())
      .then(() => {
        setLoggedOutState();
        switchCustomerAuthTab('login');
        if (authModal) authModal.classList.remove('active');
      })
      .catch(() => {
        setLoggedOutState();
        if (authModal) authModal.classList.remove('active');
      });
    };

    function showFeedback(msg, type) {
      if (feedbackBox) {
        feedbackBox.className = 'auth-feedback-box ' + type;
        feedbackBox.textContent = msg;
        feedbackBox.style.display = 'block';
      }
    }

    checkSession();
  })();
  </script>

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
            Location: High-Performance Design Studio & Showroom
          </div>
        </div>

        <!-- Quick Links -->
        <div class="footer-col">
          <h5>Navigation</h5>
          <ul class="footer-links">
            <li><a href="<?php echo esc_url( home_url( '/#showcase' ) ); ?>">Featured 3D Showroom</a></li>
            <li><a href="<?php echo esc_url( home_url( '/#inventoryCatalogSection' ) ); ?>">Fleet Inventory & Filters</a></li>
            <li><a href="<?php echo esc_url( get_permalink( 15 ) ); ?>">Services & Trade-Ins</a></li>
            <li><a href="<?php echo esc_url( get_permalink( 13 ) ); ?>">VIP Inquiries</a></li>
            <li><a href="<?php echo esc_url( get_permalink( 16 ) ); ?>">Showroom Location</a></li>
            <li><a href="<?php echo esc_url( get_permalink( 17 ) ); ?>">Dealership Warranty</a></li>
          </ul>
        </div>

        <!-- Finishes -->
        <div class="footer-col">
          <h5>Signature Finishes</h5>
          <ul class="footer-links">
            <li><a href="<?php echo esc_url( home_url( '/#inquirySection' ) ); ?>">Candy Apple Crimson</a></li>
            <li><a href="<?php echo esc_url( home_url( '/#inquirySection' ) ); ?>">Anodized Dark Wine Red</a></li>
            <li><a href="<?php echo esc_url( home_url( '/#inquirySection' ) ); ?>">Satin Obsidian Black</a></li>
            <li><a href="<?php echo esc_url( home_url( '/#inquirySection' ) ); ?>">Brushed Liquid Smoke</a></li>
            <li><a href="<?php echo esc_url( home_url( '/#inquirySection' ) ); ?>">Gloss Carbon Fiber Lip</a></li>
          </ul>
        </div>

        <!-- Direct Inquiries -->
        <div class="footer-col">
          <h5>Direct Inquiries</h5>
          <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0.8rem;">
            Speak directly with an automotive fitment engineer:
          </p>
          <div style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 700; color: #fff; margin-bottom: 0.4rem;">
            contact@uniwheels.com
          </div>
          <div style="color: var(--wheels-bright-red); font-size: 0.95rem; font-weight: 600; margin-bottom: 1.25rem;">
            +1 (800) 864-9433
          </div>
          <a href="<?php echo esc_url( home_url( '/#inquirySection' ) ); ?>" class="btn-nav-inquire" style="width: 100%; text-align: center; justify-content: center;">Start Custom Order</a>
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
