/**
 * UniWheels - Interactive Frontend Controller
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Vehicle specs data for modal & inquiry
  const vehicleData = {
    car1: {
      name: 'Ferrari SF90 Stradale Crimson Edition',
      modelCode: 'SF90-UW-01',
      wheelName: 'UW-F01 Monoblock Forged',
      specs: {
        frontFitment: '21" x 9.5J ET28',
        rearFitment: '22" x 12.5J ET38',
        finish: 'Brushed Candy Apple Crimson',
        material: '6061-T6 Aerospace Forged Aluminum',
        weight: '9.8 kg (Front) / 11.2 kg (Rear)',
        boltPattern: '5x114.3',
        tireRecommendation: 'Michelin Pilot Sport Cup 2R'
      },
      image: 'assets/images/car-1.png',
      priceGuide: 'Inquire for Custom Fitment Pricing'
    },
    car2: {
      name: 'Apollo Intensa Emozione Carbon / Crimson',
      modelCode: 'IE-UW-09',
      wheelName: 'UW-R09 Carbon-Forged Track Spec',
      specs: {
        frontFitment: '20" x 10.0J ET22 Centerlock',
        rearFitment: '21" x 13.0J ET30 Centerlock',
        finish: 'Dark Crimson Tint / Gloss Carbon Lip',
        material: 'Dual-Piece Carbon Barrel + Forged Center',
        weight: '8.4 kg (Front) / 9.9 kg (Rear)',
        boltPattern: 'Centerlock Conversion',
        tireRecommendation: 'Pirelli P-Zero Trofeo RS'
      },
      image: 'assets/images/car-2.png',
      priceGuide: 'Ultra-Limited Bespoke Production'
    },
    car3: {
      name: 'Nissan GT-R Nismo Widebody Edition',
      modelCode: 'GTR-UW-DD3',
      wheelName: 'UW-DD3 Deep Lip Multi-Piece',
      specs: {
        frontFitment: '20" x 11.0J ET-5 Deep Concave',
        rearFitment: '20" x 12.5J ET-15 Extreme Lip',
        finish: 'Satin Midnight Black / Anodized Dark Red Rim',
        material: '3-Piece Modular Forged w/ Titanium Hardware',
        weight: '10.5 kg (Front) / 11.8 kg (Rear)',
        boltPattern: '5x114.3',
        tireRecommendation: 'Toyo Proxes R888R'
      },
      image: 'assets/images/car-3.png',
      priceGuide: 'Custom Tailored Per Vehicle'
    }
  };

  // 1b. Luxury Supercar Cockpit Light / Dark Theme Mode Controller
  const themeToggleBtn = document.getElementById('themeToggleBtn');

  function getSavedTheme() {
    try {
      const saved = localStorage.getItem('uniwheels_theme');
      if (saved === 'light' || saved === 'dark') return saved;
    } catch (e) {}
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) {
      return 'light';
    }
    return 'dark';
  }

  function applyThemeMode(theme, save) {
    const isDark = theme !== 'light';
    const activeTheme = isDark ? 'dark' : 'light';
    
    document.documentElement.setAttribute('data-theme', activeTheme);
    
    if (save) {
      try {
        localStorage.setItem('uniwheels_theme', activeTheme);
      } catch (e) {}
    }

    if (themeToggleBtn) {
      themeToggleBtn.setAttribute('aria-checked', isDark ? 'true' : 'false');
      themeToggleBtn.setAttribute('aria-label', isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode');
      themeToggleBtn.setAttribute('title', isDark ? 'Currently Dark Mode — Click for Light Mode' : 'Currently Light Mode — Click for Dark Mode');
    }
  }

  if (themeToggleBtn) {
    themeToggleBtn.addEventListener('click', function(e) {
      e.preventDefault();
      const current = document.documentElement.getAttribute('data-theme') || 'dark';
      const targetTheme = current === 'light' ? 'dark' : 'light';
      applyThemeMode(targetTheme, true);
    });
  }

  // Initial Sync
  applyThemeMode(getSavedTheme(), false);

  // 2. Select Elements
  const carSelectInput = document.getElementById('selectedCarInput');
  const inquirySection = document.getElementById('inquirySection');
  const modalBackdrop = document.getElementById('specsModal');
  const modalContentContainer = document.getElementById('modalContentBody');
  const modalTitle = document.getElementById('modalCarTitle');
  const modalClose = document.getElementById('modalCloseBtn');
  const toast = document.getElementById('toastNotice');
  const toastMessage = document.getElementById('toastMessage');

  // 3. Hover spotlight: blur other cars when one is hovered
  const carItems = document.querySelectorAll('.car-stage-item');
  carItems.forEach(item => {
    item.addEventListener('mouseenter', () => {
      carItems.forEach(other => {
        if (other !== item) {
          other.classList.add('is-dimmed');
        } else {
          other.classList.add('is-hovered');
        }
      });
    });

    item.addEventListener('mouseleave', () => {
      carItems.forEach(other => {
        other.classList.remove('is-dimmed');
        other.classList.remove('is-hovered');
      });
    });
  });

  // 4. Quick Spec Modal Handler
  window.openCarSpecs = function(carId) {
    const car = vehicleData[carId];
    if (!car) return;

    modalTitle.innerHTML = `<span style="color:var(--uni-white); font-weight:800;">${car.name}</span>`;
    modalContentContainer.innerHTML = `
      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem; align-items:center;">
        <div>
          <img src="${car.image}" alt="${car.name}" style="border-radius:12px; border:1px solid var(--border-red); width:100%; box-shadow:0 10px 25px rgba(0,0,0,0.8); background:#060609;" />
          <div style="margin-top:12px; font-size:0.85rem; color:var(--wheels-bright-red); font-weight:700;">
            ${car.wheelName}
          </div>
        </div>
        <div>
          <h4 style="font-family:var(--font-heading); color:#fff; font-size:1.1rem; margin-bottom:12px; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:6px;">Technical Specifications</h4>
          <table style="width:100%; font-size:0.8rem; border-collapse:collapse; color:var(--text-secondary);">
            <tr><td style="padding:4px 0; color:var(--text-muted);">Front Fitment:</td><td style="color:#fff; font-weight:600; text-align:right;">${car.specs.frontFitment}</td></tr>
            <tr><td style="padding:4px 0; color:var(--text-muted);">Rear Fitment:</td><td style="color:#fff; font-weight:600; text-align:right;">${car.specs.rearFitment}</td></tr>
            <tr><td style="padding:4px 0; color:var(--text-muted);">Finish:</td><td style="color:#fff; font-weight:600; text-align:right;">${car.specs.finish}</td></tr>
            <tr><td style="padding:4px 0; color:var(--text-muted);">Material:</td><td style="color:#fff; font-weight:600; text-align:right;">${car.specs.material}</td></tr>
            <tr><td style="padding:4px 0; color:var(--text-muted);">Weight:</td><td style="color:#fff; font-weight:600; text-align:right;">${car.specs.weight}</td></tr>
            <tr><td style="padding:4px 0; color:var(--text-muted);">Bolt Pattern:</td><td style="color:#fff; font-weight:600; text-align:right;">${car.specs.boltPattern}</td></tr>
          </table>
          <div style="margin-top:1.5rem; display:flex; gap:10px;">
            <button class="btn-card-messenger" onclick="inquireMessengerLead({name: '${car.name}', stock_id: '${car.modelCode}'}); closeSpecsModal();" style="width:100%; justify-content:center;">
              <svg viewBox="0 0 28 28" width="16" height="16" fill="currentColor">
                <path d="M14 2C7.373 2 2 6.945 2 13.045c0 3.477 1.745 6.577 4.473 8.577V26l4.24-2.327c1.045.291 2.155.455 3.287.455 6.627 0 12-4.945 12-11.045S20.627 2 14 2zm1.2 14.882l-3.073-3.282-6 3.282 6.6-7.009 3.145 3.282 5.928-3.282-6.6 7.009z"/>
              </svg>
              <span>Inquire via Messenger</span>
            </button>
          </div>
        </div>
      </div>
    `;

    modalBackdrop.style.display = 'flex';
  };

  window.closeSpecsModal = function() {
    if (modalBackdrop) modalBackdrop.style.display = 'none';
  };

  if (modalClose) {
    modalClose.addEventListener('click', closeSpecsModal);
  }

  if (modalBackdrop) {
    modalBackdrop.addEventListener('click', (e) => {
      if (e.target === modalBackdrop) closeSpecsModal();
    });
  }

  // 5. Inquire This Build - Connect Cards to Form
  window.selectCarForInquiry = function(carIdOrName) {
    let name = carIdOrName || 'Showroom Vehicle';
    if (vehicleData[carIdOrName]) {
      name = vehicleData[carIdOrName].name;
    }
    if (carSelectInput) {
      carSelectInput.value = name;
    }

    // Scroll to inquiry section
    if (inquirySection) {
      inquirySection.scrollIntoView({ behavior: 'smooth' });
      
      // Visual feedback highlight
      inquirySection.style.transition = 'box-shadow 0.4s ease';
      inquirySection.style.boxShadow = '0 0 35px rgba(229, 41, 69, 0.7)';
      setTimeout(() => {
        inquirySection.style.boxShadow = '';
      }, 1500);

      showToast(`Selected: ${name}`);
    }
  };

  // ==========================================================================
  // CASINO 3D TURNTABLE / ROULETTE REEL HERO SHOWCASE
  // ==========================================================================
  const casinoContainer = document.getElementById('casinoStageContainer');
  if (casinoContainer) {
    const casinoCylinder = casinoContainer.querySelector('#casinoCylinder');
    const casinoItems = Array.from(casinoCylinder.querySelectorAll('.casino-car-item'));
    const prevBtn = document.getElementById('casinoRollLeft');
    const nextBtn = document.getElementById('casinoRollRight');
    const indicatorsWrap = document.getElementById('casinoIndicators');
    const activeHud = document.getElementById('casinoActiveHud');

    let currentIndex = 0;
    const total = casinoItems.length;

    function buildIndicators() {
      if (!indicatorsWrap) return;
      indicatorsWrap.innerHTML = '';
      casinoItems.forEach((_, idx) => {
        const dot = document.createElement('button');
        dot.className = 'casino-dot' + (idx === currentIndex ? ' active' : '');
        dot.setAttribute('aria-label', `Roll to vehicle ${idx + 1}`);
        dot.addEventListener('click', (e) => {
          e.stopPropagation();
          goTo(idx);
        });
        indicatorsWrap.appendChild(dot);
      });
    }

    function goTo(targetIdx) {
      if (targetIdx === currentIndex) return;
      currentIndex = (targetIdx + total) % total;
      updatePositions();
    }

    function rollNext() {
      currentIndex = (currentIndex + 1) % total;
      updatePositions();
    }

    function rollPrev() {
      currentIndex = (currentIndex - 1 + total) % total;
      updatePositions();
    }

    function updatePositions() {
      casinoItems.forEach((item, i) => {
        // Calculate circular diff between -total/2 and +total/2
        let diff = i - currentIndex;
        if (diff > total / 2) diff -= total;
        if (diff < -total / 2) diff += total;

        item.classList.remove('pos-active', 'pos-left-1', 'pos-right-1', 'pos-left-2', 'pos-right-2', 'pos-hidden');

        if (diff === 0) {
          // Center Front - Closer to the screen, larger, 100% sharp
          item.classList.add('pos-active');
          item.style.transform = 'translateX(0) translateZ(130px) scale(1.15) rotateY(0deg)';
          item.style.opacity = '1';
          item.style.filter = 'none';
          item.style.zIndex = '25';
          item.style.pointerEvents = 'auto';
        } else if (diff === -1) {
          // Immediate Left
          item.classList.add('pos-left-1');
          item.style.transform = 'translateX(-56%) translateZ(-160px) scale(0.8) rotateY(26deg)';
          item.style.opacity = '0.65';
          item.style.filter = 'blur(1.5px) brightness(0.65)';
          item.style.zIndex = '15';
          item.style.pointerEvents = 'auto';
        } else if (diff === 1) {
          // Immediate Right
          item.classList.add('pos-right-1');
          item.style.transform = 'translateX(56%) translateZ(-160px) scale(0.8) rotateY(-26deg)';
          item.style.opacity = '0.65';
          item.style.filter = 'blur(1.5px) brightness(0.65)';
          item.style.zIndex = '15';
          item.style.pointerEvents = 'auto';
        } else if (diff === -2) {
          // Far Left
          item.classList.add('pos-left-2');
          item.style.transform = 'translateX(-98%) translateZ(-340px) scale(0.6) rotateY(44deg)';
          item.style.opacity = '0.35';
          item.style.filter = 'blur(3.5px) brightness(0.4)';
          item.style.zIndex = '8';
          item.style.pointerEvents = 'auto';
        } else if (diff === 2) {
          // Far Right
          item.classList.add('pos-right-2');
          item.style.transform = 'translateX(98%) translateZ(-340px) scale(0.6) rotateY(-44deg)';
          item.style.opacity = '0.35';
          item.style.filter = 'blur(3.5px) brightness(0.4)';
          item.style.zIndex = '8';
          item.style.pointerEvents = 'auto';
        } else {
          // Hidden back of cylinder
          item.classList.add('pos-hidden');
          const dir = diff > 0 ? 1 : -1;
          item.style.transform = `translateX(${dir * 130}%) translateZ(-520px) scale(0.4) rotateY(${dir * -60}deg)`;
          item.style.opacity = '0';
          item.style.filter = 'blur(8px) brightness(0.2)';
          item.style.zIndex = '1';
          item.style.pointerEvents = 'none';
        }
      });

      // Update dot indicators
      if (indicatorsWrap) {
        const dots = indicatorsWrap.querySelectorAll('.casino-dot');
        dots.forEach((dot, idx) => {
          dot.classList.toggle('active', idx === currentIndex);
        });
      }

      // Dot indicators updated
    }

    // Attach Event Listeners
    if (prevBtn) prevBtn.addEventListener('click', (e) => { e.stopPropagation(); rollPrev(); });
    if (nextBtn) nextBtn.addEventListener('click', (e) => { e.stopPropagation(); rollNext(); });

    casinoItems.forEach((item, idx) => {
      item.addEventListener('click', (e) => {
        if (idx !== currentIndex) {
          e.stopPropagation();
          goTo(idx);
        } else {
          // Center car clicked: filter catalog to this brand and scroll to inventory
          const brand = item.dataset.brand;
          if (typeof filterBrand !== 'undefined' && filterBrand && brand) {
            filterBrand.value = brand.toLowerCase();
            if (typeof applyCatalogFilters === 'function') applyCatalogFilters();
          }
          const catalogSec = document.getElementById('inventoryCatalogSection');
          if (catalogSec) catalogSec.scrollIntoView({ behavior: 'smooth' });
        }
      });
    });

    // Mouse wheel horizontal / vertical roll
    let wheelTimer = null;
    casinoContainer.addEventListener('wheel', (e) => {
      const delta = Math.abs(e.deltaX) > Math.abs(e.deltaY) ? e.deltaX : e.deltaY;
      if (Math.abs(delta) < 25) return;
      e.preventDefault();
      if (wheelTimer) return;
      wheelTimer = setTimeout(() => { wheelTimer = null; }, 280);
      if (delta > 0) {
        rollNext();
      } else {
        rollPrev();
      }
    }, { passive: false });

    // Swipe & Pointer Drag
    let isDragging = false;
    let startX = 0;
    let currentX = 0;

    const startDrag = (x) => {
      isDragging = true;
      startX = x;
      currentX = x;
    };

    const moveDrag = (x) => {
      if (!isDragging) return;
      currentX = x;
    };

    const endDrag = () => {
      if (!isDragging) return;
      isDragging = false;
      const diff = currentX - startX;
      if (Math.abs(diff) >= 45) {
        if (diff > 0) {
          rollPrev();
        } else {
          rollNext();
        }
      }
    };

    casinoContainer.addEventListener('mousedown', (e) => startDrag(e.clientX));
    window.addEventListener('mousemove', (e) => moveDrag(e.clientX));
    window.addEventListener('mouseup', () => endDrag());

    casinoContainer.addEventListener('touchstart', (e) => {
      if (e.touches.length === 1) startDrag(e.touches[0].clientX);
    }, { passive: true });
    casinoContainer.addEventListener('touchmove', (e) => {
      if (e.touches.length === 1) moveDrag(e.touches[0].clientX);
    }, { passive: true });
    casinoContainer.addEventListener('touchend', () => endDrag());

    // Arrow keys
    window.addEventListener('keydown', (e) => {
      if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) return;
      if (e.key === 'ArrowLeft') {
        rollPrev();
      } else if (e.key === 'ArrowRight') {
        rollNext();
      }
    });

    // Initialize
    buildIndicators();
    updatePositions();
  }



  // 9. Toast Helper
  function showToast(msg) {
    if (!toast) return;
    if (toastMessage) toastMessage.textContent = msg;
    toast.style.display = 'block';
    setTimeout(() => {
      toast.style.display = 'none';
    }, 4000);
  }

  // ========================================================
  // 10. AUTOMATED MESSENGER REDIRECT (FLOWCHART FEATURES #2 & #7)
  // Pre-loads specific car details and reference ID into an m.me link
  // ========================================================
  window.inquireMessengerLead = function(car) {
    if (!car) return;
    const stockId = car.stock_id || ('UW-' + (car.id || 'FLEET'));
    const carName = car.name || 'Verified Showroom Vehicle';
    const priceFormatted = car.price ? `₱${Number(car.price).toLocaleString('en-US')}` : 'Bespoke Valuation';
    const mileage = car.mileage || 'Delivery Miles';
    const trans = car.transmission || 'Dual-Clutch DCT';

    const msg = `Hello UniWheels Dealership Concierge! 🏁\n\nI am inquiring about the following vehicle:\n• Stock Ref: #${stockId}\n• Model: ${carName}\n• Acquisition Price: ${priceFormatted}\n• Mileage: ${mileage}\n• Transmission: ${trans}\n\nIs this vehicle currently available on-site for a private showroom viewing & test drive?`;

    const messengerUrl = `https://m.me/uniwheels?text=` + encodeURIComponent(msg);
    window.open(messengerUrl, '_blank');
  };

  // ========================================================
  // 11. FINANCIAL ESTIMATE CALCULATOR CONTROLLER (FEATURE #5)
  // Provides rough estimates for down payments and monthly amortizations
  // ========================================================
  let currentCalcCar = null;
  let currentCalcTerm = 60; // default 60 months

  window.openFinancialCalculator = function(car) {
    currentCalcCar = car || null;
    const modal = document.getElementById('financeCalcModal');
    if (!modal) return;

    const priceInput = document.getElementById('calcVehiclePrice');
    const subtitle = document.getElementById('calcVehicleSubtitle');

    if (car) {
      if (priceInput && car.price) priceInput.value = Math.round(Number(car.price));
      if (subtitle) {
        const stock = car.stock_id || ('UW-' + (car.id || '01'));
        subtitle.textContent = `${car.name || 'Selected Vehicle'} (Stock #${stock})`;
      }
    } else {
      if (subtitle) subtitle.textContent = 'Custom Dealership Acquisition Estimate';
    }

    modal.style.display = 'flex';
    runFinancialCalculation();
  };

  window.closeFinancialCalculator = function() {
    const modal = document.getElementById('financeCalcModal');
    if (modal) modal.style.display = 'none';
  };

  // Close calculator on backdrop click or Escape key
  const calcModalEl = document.getElementById('financeCalcModal');
  if (calcModalEl) {
    calcModalEl.addEventListener('click', (e) => {
      if (e.target === calcModalEl) closeFinancialCalculator();
    });
    window.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && calcModalEl.style.display === 'flex') {
        closeFinancialCalculator();
      }
    });
  }

  window.handleDownPaymentSlider = function(pct) {
    const pctDisplay = document.getElementById('calcDpPercentDisplay');
    if (pctDisplay) pctDisplay.textContent = pct + '%';
    runFinancialCalculation();
  };

  window.selectCalcTerm = function(months) {
    currentCalcTerm = parseInt(months, 10) || 60;
    const pills = document.querySelectorAll('#calcTermPills .term-pill');
    pills.forEach(p => {
      p.classList.toggle('active', parseInt(p.dataset.months, 10) === currentCalcTerm);
    });
    runFinancialCalculation();
  };

  window.runFinancialCalculation = function() {
    const priceInput = document.getElementById('calcVehiclePrice');
    const dpSlider = document.getElementById('calcDpSlider');
    const aprInput = document.getElementById('calcAprRate');

    const price = Math.max(0, parseFloat(priceInput ? priceInput.value : 0) || 0);
    const dpPct = Math.max(5, Math.min(90, parseFloat(dpSlider ? dpSlider.value : 20) || 20));
    const apr = Math.max(0, parseFloat(aprInput ? aprInput.value : 5.9) || 5.9);
    const months = currentCalcTerm || 60;

    // Financial calculations
    const dpAmount = Math.round(price * (dpPct / 100));
    const principal = Math.max(0, price - dpAmount);

    let monthlyPayment = 0;
    if (principal > 0 && months > 0) {
      if (apr > 0) {
        const monthlyRate = (apr / 100) / 12;
        monthlyPayment = Math.round((principal * monthlyRate * Math.pow(1 + monthlyRate, months)) / (Math.pow(1 + monthlyRate, months) - 1));
      } else {
        monthlyPayment = Math.round(principal / months);
      }
    }

    const totalLoanPayment = monthlyPayment * months;
    const totalInterest = Math.max(0, totalLoanPayment - principal);
    const totalCost = dpAmount + totalLoanPayment;

    // Update Telemetry Display
    const elMonthly = document.getElementById('gaugeMonthlyDisplay');
    const elTermsSub = document.getElementById('gaugeTermsSub');
    const elDpAmount = document.getElementById('calcDpAmountDisplay');
    const elGaugeDp = document.getElementById('gaugeDownPayment');
    const elGaugePrincipal = document.getElementById('gaugeFinancedPrincipal');
    const elGaugeInterest = document.getElementById('gaugeTotalInterest');
    const elGaugeCost = document.getElementById('gaugeTotalCost');

    if (elMonthly) elMonthly.textContent = '₱' + monthlyPayment.toLocaleString('en-US');
    if (elTermsSub) elTermsSub.textContent = `Per month for ${months} months @ ${apr}% APR`;
    if (elDpAmount) elDpAmount.textContent = '₱' + dpAmount.toLocaleString('en-US');
    if (elGaugeDp) elGaugeDp.textContent = '₱' + dpAmount.toLocaleString('en-US');
    if (elGaugePrincipal) elGaugePrincipal.textContent = '₱' + principal.toLocaleString('en-US');
    if (elGaugeInterest) elGaugeInterest.textContent = '₱' + totalInterest.toLocaleString('en-US');
    if (elGaugeCost) elGaugeCost.textContent = '₱' + totalCost.toLocaleString('en-US');
  };

  window.inquireMessengerWithFinancing = function() {
    const car = currentCalcCar || {};
    const stockId = car.stock_id || 'UW-FLEET';
    const carName = car.name || 'Showroom Vehicle';
    const priceInput = document.getElementById('calcVehiclePrice');
    const price = priceInput ? priceInput.value : (car.price || 0);

    const dpSlider = document.getElementById('calcDpSlider');
    const dpPct = dpSlider ? dpSlider.value : 20;
    const dpAmount = Math.round(price * (dpPct / 100));

    const elMonthly = document.getElementById('gaugeMonthlyDisplay');
    const monthlyStr = elMonthly ? elMonthly.textContent : '₱0';

    const msg = `Hello UniWheels Dealership! 🏎️\n\nI would like to inquire about financing for:\n• Vehicle: ${carName} (Stock #${stockId})\n• Vehicle Price: ₱${Number(price).toLocaleString('en-US')}\n• Proposed Down Payment: ₱${Number(dpAmount).toLocaleString('en-US')} (${dpPct}%)\n• Term: ${currentCalcTerm} Months\n• Estimated Payment: ~${monthlyStr}/month\n\nCan your financing desk arrange an on-site viewing and review these terms with me?`;

    const messengerUrl = `https://m.me/uniwheels?text=` + encodeURIComponent(msg);
    window.open(messengerUrl, '_blank');
  };



  // ========================================================
  // 12. DYNAMIC INVENTORY SEARCH & FILTERS (FLOWCHART FEATURE #1)
  // Dynamic search by brand, budget, transmission, and body type
  // ========================================================
  const searchInput = document.getElementById('catalogSearchInput');
  const filterBrand = document.getElementById('filterBrand');
  const filterBudget = document.getElementById('filterBudget');
  const filterTransmission = document.getElementById('filterTransmission');
  const filterBodyType = document.getElementById('filterBodyType');
  const btnReset = document.getElementById('btnResetFilters');
  const resultCount = document.getElementById('filterResultCount');
  const noResultsBox = document.getElementById('noCatalogResults');
  const cardElements = document.querySelectorAll('.inventory-car-card');

  function applyCatalogFilters() {
    if (!cardElements || cardElements.length === 0) return;

    const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
    const selBrand = (filterBrand ? filterBrand.value : 'all').toLowerCase();
    const selBudget = filterBudget ? filterBudget.value : 'all';
    const selTrans = (filterTransmission ? filterTransmission.value : 'all').toLowerCase();
    const selBody = (filterBodyType ? filterBodyType.value : 'all').toLowerCase();

    let visibleCount = 0;

    cardElements.forEach(card => {
      const d = card.dataset;
      const name = (d.name || '').toLowerCase();
      const brand = (d.brand || '').toLowerCase();
      const stock = (d.stock || '').toLowerCase();
      const trans = (d.transmission || '').toLowerCase();
      const body = (d.body || '').toLowerCase();
      const price = parseFloat(d.price) || 0;

      // 1. Text Search Query Match
      let matchesSearch = true;
      if (query.length > 0) {
        matchesSearch = name.includes(query) || brand.includes(query) || stock.includes(query);
      }

      // 2. Brand Match
      let matchesBrand = true;
      if (selBrand !== 'all') {
        matchesBrand = brand === selBrand || brand.includes(selBrand);
      }

      // 3. Budget Match
      let matchesBudget = true;
      if (selBudget === 'under-150k') {
        matchesBudget = price > 0 && price < 150000;
      } else if (selBudget === '150k-300k') {
        matchesBudget = price >= 150000 && price <= 300000;
      } else if (selBudget === '300k-600k') {
        matchesBudget = price > 300000 && price <= 600000;
      } else if (selBudget === 'over-600k') {
        matchesBudget = price > 600000;
      }

      // 4. Transmission Match
      let matchesTrans = true;
      if (selTrans !== 'all') {
        matchesTrans = trans.includes(selTrans);
      }

      // 5. Body Type Match
      let matchesBody = true;
      if (selBody !== 'all') {
        matchesBody = body.includes(selBody);
      }

      // Final Visibility
      const isVisible = matchesSearch && matchesBrand && matchesBudget && matchesTrans && matchesBody;
      if (isVisible) {
        card.style.display = 'flex';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });

    // Update Result Counters
    if (resultCount) {
      resultCount.textContent = `Showing ${visibleCount} of ${cardElements.length} Luxury Units`;
    }
    if (noResultsBox) {
      noResultsBox.style.display = (visibleCount === 0) ? 'block' : 'none';
    }
  }

  window.resetAllCatalogFilters = function() {
    if (searchInput) searchInput.value = '';
    if (filterBrand) filterBrand.value = 'all';
    if (filterBudget) filterBudget.value = 'all';
    if (filterTransmission) filterTransmission.value = 'all';
    if (filterBodyType) filterBodyType.value = 'all';
    applyCatalogFilters();
  };

  if (searchInput) searchInput.addEventListener('input', applyCatalogFilters);
  if (filterBrand) filterBrand.addEventListener('change', applyCatalogFilters);
  if (filterBudget) filterBudget.addEventListener('change', applyCatalogFilters);
  if (filterTransmission) filterTransmission.addEventListener('change', applyCatalogFilters);
  if (filterBodyType) filterBodyType.addEventListener('change', applyCatalogFilters);
  if (btnReset) btnReset.addEventListener('click', resetAllCatalogFilters);

});
