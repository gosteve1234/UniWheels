/**
 * UniWheels Motors - Certified Pre-Owned & Trade-In Specialists
 * Complete Frontend Controller for Add-ons A, B, C, D & E:
 *  A. Vehicle Condition & History (Age, Ownership, Photographed Damage Disclosure, Policies)
 *  B. Multi-Step Booking Confirmation Popups (Book -> Hold Days -> Finalize -> Receipt + Cancel Confirm)
 *  C. Real-Time Reservation Hold Status, Live Ticking Countdown & Automatic Deadline Release
 *  D. Large High-Res Photos with Desktop Hover Magnifier & Mobile Pinch-to-Zoom Lightbox
 */

document.addEventListener('DOMContentLoaded', () => {
  const featuredCars = window.UNIWHEELS_FEATURED_CARS || [];
  const catalogCars = window.UNIWHEELS_CATALOG_CARS || [];
  const settings = window.UNIWHEELS_SETTINGS || {};
  const themeBaseUrl = window.THEME_BASE_URL || '.';
  const apiUrl = window.UNIWHEELS_API_URL || './api.php';

  // Build quick lookup map by car ID
  const carsById = new Map();
  catalogCars.forEach(c => carsById.set(Number(c.id), c));
  featuredCars.forEach(c => {
    if (!carsById.has(Number(c.id))) carsById.set(Number(c.id), c);
  });

  const STORAGE_KEY_REFS = 'uniwheels_saved_booking_refs_v1';

  function getSavedRefs() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY_REFS);
      const parsed = raw ? JSON.parse(raw) : [];
      return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
      return [];
    }
  }

  function saveBookingRef(ref) {
    if (!ref) return;
    const list = getSavedRefs();
    const upper = String(ref).toUpperCase();
    if (!list.includes(upper)) {
      list.unshift(upper);
      try {
        localStorage.setItem(STORAGE_KEY_REFS, JSON.stringify(list.slice(0, 25)));
      } catch (e) {}
    }
    refreshMyResBadge();
  }

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str == null ? '' : String(str);
    return div.innerHTML;
  }

  function formatPeso(num) {
    const n = parseFloat(num) || 0;
    return n > 0 ? ('₱' + n.toLocaleString('en-PH', { maximumFractionDigits: 0 })) : 'Inquire for Price';
  }

  function statusToSlug(st) {
    return String(st || 'AVAILABLE').toLowerCase().trim().replace(/\s+/g, '-');
  }

  function formatCountdownParts(msRemaining) {
    if (msRemaining <= 0) return null;
    const totalSec = Math.floor(msRemaining / 1000);
    const days = Math.floor(totalSec / 86400);
    const hours = Math.floor((totalSec % 86400) / 3600);
    const mins = Math.floor((totalSec % 3600) / 60);
    const secs = totalSec % 60;
    const parts = [];
    if (days > 0) parts.push(`${days}d`);
    parts.push(`${String(hours).padStart(2, '0')}h`);
    parts.push(`${String(mins).padStart(2, '0')}m`);
    parts.push(`${String(secs).padStart(2, '0')}s`);
    return parts.join(' ');
  }

  // =========================================================================
  // 1. DESKTOP HOVER MAGNIFIER & MOBILE PINCH-TO-ZOOM LIGHTBOX (Add-on D)
  // =========================================================================
  function attachHoverMagnifier(containerEl, imgEl, lensEl, zoomFactor = 2.35) {
    if (!containerEl || !imgEl || !lensEl) return;

    function updateLens(e) {
      // Only activate hover lens on fine-pointer (desktop mouse) devices
      if (window.matchMedia && !window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
        lensEl.style.display = 'none';
        return;
      }
      const rect = imgEl.getBoundingClientRect();
      if (rect.width < 40 || rect.height < 40) return;

      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;

      if (x < 0 || y < 0 || x > rect.width || y > rect.height) {
        lensEl.style.display = 'none';
        return;
      }

      lensEl.style.display = 'block';
      const lensW = lensEl.offsetWidth || 165;
      const lensH = lensEl.offsetHeight || 165;

      const wrapRect = containerEl.getBoundingClientRect();
      const leftInWrap = (e.clientX - wrapRect.left) - lensW / 2;
      const topInWrap = (e.clientY - wrapRect.top) - lensH / 2;

      lensEl.style.left = `${leftInWrap}px`;
      lensEl.style.top = `${topInWrap}px`;
      lensEl.style.backgroundImage = `url('${imgEl.src}')`;
      lensEl.style.backgroundSize = `${rect.width * zoomFactor}px ${rect.height * zoomFactor}px`;

      const bgX = -((x * zoomFactor) - lensW / 2);
      const bgY = -((y * zoomFactor) - lensH / 2);
      lensEl.style.backgroundPosition = `${bgX}px ${bgY}px`;
    }

    containerEl.onmousemove = updateLens;
    containerEl.onmouseleave = () => {
      lensEl.style.display = 'none';
    };
  }

  // Fullscreen Pinch-to-Zoom & Pan Inspector Lightbox
  const zoomLightbox = document.getElementById('photoZoomLightbox');
  const zoomStage = document.getElementById('zoomLightboxStage');
  const zoomImg = document.getElementById('zoomLightboxImg');
  const zoomCaption = document.getElementById('zoomLightboxCaption');
  const zoomLevelText = document.getElementById('zoomLevelText');
  const zoomInBtn = document.getElementById('zoomInBtn');
  const zoomOutBtn = document.getElementById('zoomOutBtn');
  const zoomResetBtn = document.getElementById('zoomResetBtn');
  const zoomCloseBtn = document.getElementById('zoomCloseBtn');

  let zScale = 1;
  let zPosX = 0;
  let zPosY = 0;
  let isDraggingZoom = false;
  let dragStartX = 0;
  let dragStartY = 0;
  let initialPinchDist = null;
  let initialPinchScale = 1;

  function renderZoomTransform() {
    if (!zoomImg) return;
    if (zScale <= 1) {
      zScale = 1;
      zPosX = 0;
      zPosY = 0;
    }
    zoomImg.style.transform = `translate(${zPosX}px, ${zPosY}px) scale(${zScale})`;
    if (zoomLevelText) zoomLevelText.textContent = `${Math.round(zScale * 100)}%`;
    if (zoomStage) zoomStage.style.cursor = zScale > 1 ? 'grab' : 'zoom-in';
  }

  window.openPhotoZoomInspector = function(src, captionText) {
    if (!zoomLightbox || !zoomImg) return;
    zoomImg.src = src;
    if (zoomCaption) {
      zoomCaption.textContent = captionText || 'Tap, scroll, or pinch to inspect vehicle details';
    }
    zScale = 1;
    zPosX = 0;
    zPosY = 0;
    renderZoomTransform();
    zoomLightbox.style.display = 'flex';
  };

  function closePhotoZoomInspector() {
    if (zoomLightbox) zoomLightbox.style.display = 'none';
  }

  if (zoomInBtn) zoomInBtn.onclick = () => { zScale = Math.min(4, zScale + 0.5); renderZoomTransform(); };
  if (zoomOutBtn) zoomOutBtn.onclick = () => { zScale = Math.max(1, zScale - 0.5); renderZoomTransform(); };
  if (zoomResetBtn) zoomResetBtn.onclick = () => { zScale = 1; zPosX = 0; zPosY = 0; renderZoomTransform(); };
  if (zoomCloseBtn) zoomCloseBtn.onclick = closePhotoZoomInspector;

  if (zoomStage && zoomImg) {
    // Wheel zoom on desktop
    zoomStage.addEventListener('wheel', (e) => {
      e.preventDefault();
      const delta = e.deltaY < 0 ? 0.3 : -0.3;
      zScale = Math.min(4, Math.max(1, zScale + delta));
      renderZoomTransform();
    }, { passive: false });

    // Double-click / tap toggle zoom
    zoomStage.addEventListener('dblclick', (e) => {
      e.preventDefault();
      zScale = zScale > 1 ? 1 : 2.5;
      if (zScale === 1) { zPosX = 0; zPosY = 0; }
      renderZoomTransform();
    });

    // Mouse drag pan
    zoomStage.addEventListener('mousedown', (e) => {
      if (zScale <= 1) return;
      isDraggingZoom = true;
      dragStartX = e.clientX - zPosX;
      dragStartY = e.clientY - zPosY;
      zoomStage.style.cursor = 'grabbing';
    });
    window.addEventListener('mousemove', (e) => {
      if (!isDraggingZoom) return;
      zPosX = e.clientX - dragStartX;
      zPosY = e.clientY - dragStartY;
      renderZoomTransform();
    });
    window.addEventListener('mouseup', () => {
      isDraggingZoom = false;
      if (zoomStage && zScale > 1) zoomStage.style.cursor = 'grab';
    });

    // Mobile Touch Pinch-to-Zoom & 1-Finger Pan
    zoomStage.addEventListener('touchstart', (e) => {
      if (e.touches.length === 2) {
        const dx = e.touches[0].clientX - e.touches[1].clientX;
        const dy = e.touches[0].clientY - e.touches[1].clientY;
        initialPinchDist = Math.hypot(dx, dy);
        initialPinchScale = zScale;
      } else if (e.touches.length === 1 && zScale > 1) {
        isDraggingZoom = true;
        dragStartX = e.touches[0].clientX - zPosX;
        dragStartY = e.touches[0].clientY - zPosY;
      }
    }, { passive: true });

    zoomStage.addEventListener('touchmove', (e) => {
      if (e.touches.length === 2 && initialPinchDist) {
        e.preventDefault();
        const dx = e.touches[0].clientX - e.touches[1].clientX;
        const dy = e.touches[0].clientY - e.touches[1].clientY;
        const dist = Math.hypot(dx, dy);
        zScale = Math.min(4, Math.max(1, initialPinchScale * (dist / initialPinchDist)));
        renderZoomTransform();
      } else if (e.touches.length === 1 && isDraggingZoom && zScale > 1) {
        e.preventDefault();
        zPosX = e.touches[0].clientX - dragStartX;
        zPosY = e.touches[0].clientY - dragStartY;
        renderZoomTransform();
      }
    }, { passive: false });

    zoomStage.addEventListener('touchend', () => {
      initialPinchDist = null;
      isDraggingZoom = false;
    });
  }

  // =========================================================================
  // 2. HERO FEATURED SHOWCASE & MAGNIFIER INTEGRATION
  // =========================================================================
  let currentFeaturedIndex = 0;
  let heroSlideshowTimer = null;
  let heroPhotoIndex = 0;

  const showcaseImg = document.getElementById('showcaseImg');
  const heroMagWrap = document.getElementById('heroMagnifierWrap');
  const heroMagLens = document.getElementById('heroMagnifierLens');
  const showcaseCarName = document.getElementById('showcaseCarName');
  const showcaseAgeBadge = document.getElementById('showcaseAgeBadge');
  const showcasePriceValue = document.getElementById('showcasePriceValue');
  const showcasePriceNote = document.getElementById('showcasePriceNote');
  const showcaseDepositVal = document.getElementById('showcaseDepositVal');
  const showcaseStockId = document.getElementById('showcaseStockId');
  const showcaseStatusTag = document.getElementById('showcaseStatusTag');
  const showcasePhotoDots = document.getElementById('showcasePhotoDots');
  const showcaseCountdownBanner = document.getElementById('showcaseCountdownBanner');
  const currentCarIdxEl = document.getElementById('currentCarIdx');
  const specTrans = document.getElementById('specTrans');
  const specMileage = document.getElementById('specMileage');
  const specSeats = document.getElementById('specSeats');
  const specOwnership = document.getElementById('specOwnership');
  const showcaseReserveBtn = document.getElementById('showcaseReserveBtn');
  const showcaseReserveBtnText = document.getElementById('showcaseReserveBtnText');
  const showcaseMessengerBtn = document.getElementById('showcaseMessengerBtn');
  const showcaseDetailsBtn = document.getElementById('showcaseDetailsBtn');
  const showcasePrevBtn = document.getElementById('showcasePrevBtn');
  const showcaseNextBtn = document.getElementById('showcaseNextBtn');

  function updateHeroDots() {
    if (!showcasePhotoDots) return;
    showcasePhotoDots.querySelectorAll('.showcase-photo-dot').forEach((d, i) => {
      d.classList.toggle('active', i === heroPhotoIndex);
    });
  }

  function updateShowcase(index) {
    if (!featuredCars.length) return;
    if (heroSlideshowTimer) {
      clearInterval(heroSlideshowTimer);
      heroSlideshowTimer = null;
    }

    if (index < 0) currentFeaturedIndex = featuredCars.length - 1;
    else if (index >= featuredCars.length) currentFeaturedIndex = 0;
    else currentFeaturedIndex = index;

    const rawCar = featuredCars[currentFeaturedIndex];
    const car = carsById.get(Number(rawCar.id)) || rawCar;
    if (!car) return;

    if (currentCarIdxEl) currentCarIdxEl.textContent = (currentFeaturedIndex + 1);

    const images = (car.all_images && car.all_images.length) ? car.all_images : [car.image_file || 'car-1.jpg'];
    heroPhotoIndex = 0;

    if (showcaseImg) {
      showcaseImg.src = `${themeBaseUrl}/assets/images/cars/${images[0]}`;
      showcaseImg.alt = car.name || 'Pre-owned car';
    }

    if (heroMagWrap && showcaseImg) {
      attachHoverMagnifier(heroMagWrap, showcaseImg, heroMagLens, 2.25);
      heroMagWrap.onclick = (e) => {
        if (e.target.closest('.showcase-photo-dot')) return;
        openPhotoZoomInspector(showcaseImg.src, `${car.name} — High-Resolution Inspection`);
      };
    }

    if (showcasePhotoDots) {
      showcasePhotoDots.innerHTML = '';
      if (images.length > 1) {
        images.forEach((img, i) => {
          const dot = document.createElement('span');
          dot.className = `showcase-photo-dot ${i === 0 ? 'active' : ''}`;
          dot.title = `Photo ${i + 1} of ${images.length}`;
          dot.onclick = (e) => {
            e.stopPropagation();
            heroPhotoIndex = i;
            if (showcaseImg) showcaseImg.src = `${themeBaseUrl}/assets/images/cars/${images[heroPhotoIndex]}`;
            updateHeroDots();
          };
          showcasePhotoDots.appendChild(dot);
        });
      }
    }

    if (showcaseCarName) {
      showcaseCarName.innerHTML = `${escapeHtml(car.name)} — <span class="car-age-inline">${escapeHtml(car.age_label || '')}</span>`;
    }
    if (showcaseAgeBadge) showcaseAgeBadge.textContent = car.short_age || '';
    if (showcasePriceValue) showcasePriceValue.textContent = formatPeso(car.price);
    if (showcasePriceNote) showcasePriceNote.textContent = car.price_note || 'Negotiable';
    if (showcaseDepositVal) showcaseDepositVal.textContent = formatPeso(car.deposit_amount || 10000);

    const stockId = car.stock_id || ('UW-' + (car.id || 1));
    if (showcaseStockId) showcaseStockId.textContent = '#' + String(stockId).toUpperCase();

    const status = String(car.status || 'AVAILABLE').toUpperCase();
    if (showcaseStatusTag) {
      showcaseStatusTag.textContent = status;
      showcaseStatusTag.className = 'card-status-badge badge-' + statusToSlug(status);
    }

    if (showcaseCountdownBanner) {
      if (status === 'RESERVED' && car.reserved_until_iso) {
        showcaseCountdownBanner.style.display = 'block';
        showcaseCountdownBanner.setAttribute('data-expires', car.reserved_until_iso);
        showcaseCountdownBanner.setAttribute('data-formatted', car.reserved_until_formatted || '');
        showcaseCountdownBanner.setAttribute('data-days', car.reserved_hold_days || 2);
      } else {
        showcaseCountdownBanner.style.display = 'none';
        showcaseCountdownBanner.removeAttribute('data-expires');
      }
    }

    if (specTrans) specTrans.textContent = `${car.transmission || 'Automatic'} • ${car.fuel_type || 'Gasoline'}`;
    if (specMileage) specMileage.textContent = car.mileage || 'Low Mileage';
    if (specSeats) specSeats.textContent = `${car.finish || 'Clean'} • ${car.seating || '5 Seats'}`;
    if (specOwnership) specOwnership.textContent = car.ownership_history || '1 previous owner';

    if (showcaseReserveBtn) {
      const isAvail = status === 'AVAILABLE';
      showcaseReserveBtn.disabled = !isAvail;
      showcaseReserveBtn.classList.toggle('is-disabled', !isAvail);
      if (showcaseReserveBtnText) {
        showcaseReserveBtnText.textContent = isAvail ? 'Book / Reserve This Car' : `Currently ${status}`;
      }
      showcaseReserveBtn.onclick = () => {
        if (isAvail) initiateCarBooking(car);
      };
    }

    if (showcaseMessengerBtn) showcaseMessengerBtn.onclick = () => inquireMessengerLead(car);
    if (showcaseDetailsBtn) showcaseDetailsBtn.onclick = () => openCarDetailsModal(car);
    tickAllCountdowns();
  }

  if (showcasePrevBtn) showcasePrevBtn.addEventListener('click', () => updateShowcase(currentFeaturedIndex - 1));
  if (showcaseNextBtn) showcaseNextBtn.addEventListener('click', () => updateShowcase(currentFeaturedIndex + 1));
  if (featuredCars.length > 0) updateShowcase(0);

  // =========================================================================
  // 3. GLOBAL HELPERS & MESSENGER INQUIRY
  // =========================================================================
  window.openCarDetailsById = function(id) {
    const car = carsById.get(Number(id));
    if (car) openCarDetailsModal(car);
  };

  window.initiateCarBookingById = function(id) {
    const car = carsById.get(Number(id));
    if (car) initiateCarBooking(car);
  };

  window.inquireMessengerById = function(id) {
    const car = carsById.get(Number(id));
    if (car) inquireMessengerLead(car);
  };

  window.inquireMessengerLead = function(car) {
    if (!car) return;
    const carName = car.name || 'Vehicle';
    const stockId = car.stock_id || ('UW-' + (car.id || '01'));
    const priceText = formatPeso(car.price);
    const baseMessenger = settings.messenger_url || 'https://m.me/uniwheels';
    const message = `Hello UniWheels! I am interested in the ${carName} (${car.age_label || ''}, Stock #${stockId}) listed at ${priceText}. Is this unit available for showroom inspection or reservation hold?`;
    window.open(`${baseMessenger}?text=` + encodeURIComponent(message), '_blank');
  };

  // =========================================================================
  // 4. FAST INVENTORY FILTERING + PAGINATION ("Show More" for Mobile Speed)
  // =========================================================================
  const searchInput = document.getElementById('catalogSearchInput');
  const brandSelect = document.getElementById('filterBrand');
  const budgetSelect = document.getElementById('filterBudget');
  const bodySelect = document.getElementById('filterBodyType');
  const statusSelect = document.getElementById('filterStatus');
  const transSelect = document.getElementById('filterTransmission');
  const carCards = Array.from(document.querySelectorAll('.car-card'));
  const countBadge = document.getElementById('filterResultCount');
  const noResultsBox = document.getElementById('noCatalogResults');
  const loadMoreWrap = document.getElementById('loadMoreWrap');
  const loadMoreBtn = document.getElementById('loadMoreCarsBtn');

  const PAGE_CHUNK = 18;
  let visibleLimit = PAGE_CHUNK;

  function applyFilters(resetPagination = true) {
    if (resetPagination) visibleLimit = PAGE_CHUNK;

    const query = (searchInput?.value || '').toLowerCase().trim();
    const brandVal = (brandSelect?.value || 'all').toLowerCase();
    const budgetVal = budgetSelect?.value || 'all';
    const bodyVal = (bodySelect?.value || 'all').toLowerCase();
    const statusVal = (statusSelect?.value || 'all').toLowerCase();
    const transVal = (transSelect?.value || 'all').toLowerCase();

    let totalMatched = 0;
    let shownSoFar = 0;

    carCards.forEach(card => {
      const cardName = card.getAttribute('data-name') || '';
      const cardStock = card.getAttribute('data-stock') || '';
      const cardBrand = card.getAttribute('data-brand') || '';
      const cardBody = card.getAttribute('data-body') || '';
      const cardStatus = card.getAttribute('data-status') || 'available';
      const cardTrans = card.getAttribute('data-trans') || '';
      const cardPrice = parseFloat(card.getAttribute('data-price') || 0);

      const matchesSearch = !query || cardName.includes(query) || cardStock.includes(query) || cardBrand.includes(query);
      const matchesBrand = (brandVal === 'all') || (cardBrand === brandVal);
      const matchesBody = (bodyVal === 'all') || cardBody.includes(bodyVal);
      const matchesStatus = (statusVal === 'all') || (cardStatus === statusVal);
      const matchesTrans = (transVal === 'all') || cardTrans.includes(transVal);

      let matchesBudget = true;
      if (budgetVal === 'under-500k') matchesBudget = cardPrice > 0 && cardPrice < 500000;
      else if (budgetVal === '500k-800k') matchesBudget = cardPrice >= 500000 && cardPrice <= 800000;
      else if (budgetVal === '800k-1.2m') matchesBudget = cardPrice > 800000 && cardPrice <= 1200000;
      else if (budgetVal === '1.2m-2m') matchesBudget = cardPrice > 1200000 && cardPrice <= 2000000;
      else if (budgetVal === 'over-2m') matchesBudget = cardPrice > 2000000;

      if (matchesSearch && matchesBrand && matchesBody && matchesStatus && matchesTrans && matchesBudget) {
        totalMatched++;
        if (shownSoFar < visibleLimit) {
          card.style.display = '';
          shownSoFar++;
        } else {
          card.style.display = 'none';
        }
      } else {
        card.style.display = 'none';
      }
    });

    if (countBadge) {
      countBadge.textContent = `Showing ${shownSoFar} of ${totalMatched} Matching Vehicle${totalMatched === 1 ? '' : 's'}`;
    }
    if (noResultsBox) {
      noResultsBox.style.display = (totalMatched === 0) ? 'block' : 'none';
    }
    if (loadMoreWrap && loadMoreBtn) {
      if (shownSoFar < totalMatched) {
        loadMoreWrap.style.display = 'block';
        loadMoreBtn.textContent = `Show More Vehicles (${totalMatched - shownSoFar} remaining)`;
      } else {
        loadMoreWrap.style.display = 'none';
      }
    }
  }

  if (loadMoreBtn) {
    loadMoreBtn.addEventListener('click', () => {
      visibleLimit += PAGE_CHUNK;
      applyFilters(false);
    });
  }

  [searchInput].forEach(el => el && el.addEventListener('input', () => applyFilters(true)));
  [brandSelect, budgetSelect, bodySelect, statusSelect, transSelect].forEach(el => el && el.addEventListener('change', () => applyFilters(true)));

  window.resetAllCatalogFilters = function() {
    if (searchInput) searchInput.value = '';
    if (brandSelect) brandSelect.value = 'all';
    if (budgetSelect) budgetSelect.value = 'all';
    if (bodySelect) bodySelect.value = 'all';
    if (statusSelect) statusSelect.value = 'all';
    if (transSelect) transSelect.value = 'all';
    applyFilters(true);
  };

  applyFilters(true);

  // =========================================================================
  // 5. COMPLETE VEHICLE PROFILE MODAL — ADD-ONS A, C & D
  //    ("Vehicle Condition & History", Categorized Zoom Gallery, Clear Policies)
  // =========================================================================
  const specsModal = document.getElementById('specsModal');
  const modalCloseBtn = document.getElementById('modalCloseBtn');
  const modalBody = document.getElementById('modalContentBody');
  const modalTitle = document.getElementById('modalCarTitle');
  const modalSubtitle = document.getElementById('modalCarSubtitle');

  let activeModalCarId = null;

  window.openCarDetailsModal = function(carInput) {
    if (!specsModal || !modalBody || !carInput) return;
    const car = carsById.get(Number(carInput.id)) || carInput;
    activeModalCarId = Number(car.id);

    const status = String(car.status || 'AVAILABLE').toUpperCase();
    const statusSlug = statusToSlug(status);
    const isAvail = status === 'AVAILABLE';
    const priceStr = formatPeso(car.price);
    const depositStr = formatPeso(car.deposit_amount || settings.default_deposit || 10000);
    const stockStr = car.stock_id || ('UW-' + car.id);

    // Requirement A: Display year model and "X years old"
    if (modalTitle) {
      modalTitle.textContent = `${car.name} — ${car.age_label || ''}`;
    }
    if (modalSubtitle) {
      modalSubtitle.innerHTML = `
        <span class="card-status-badge badge-${statusSlug}" style="position:static;">${escapeHtml(status)}</span>
        <span>Stock #${escapeHtml(stockStr)}</span>
        ${car.plate_number ? `<span>• Plate/Conduction: <strong>${escapeHtml(car.plate_number)}</strong></span>` : ''}
      `;
    }

    // Build categorized photos list (Exterior, Rear / Trunk, Interior, Dashboard, Tires, Damage Close-up)
    const catPhotos = (Array.isArray(car.categorized_photos) && car.categorized_photos.length)
      ? car.categorized_photos
      : [{ file: car.image_file || 'car-1.jpg', category: 'Exterior', label: 'Main Exterior Photo' }];

    const damagePhotos = Array.isArray(car.damage_photos_list) ? car.damage_photos_list : [];

    modalBody.innerHTML = `
      <div class="vehicle-profile-grid">
        <!-- LEFT COLUMN: Large Photo Viewer + Hover Magnifier + Categorized Thumbnails (Add-on D) -->
        <div class="vp-gallery-col">
          <div class="vp-main-photo-stage magnifier-container" id="modalMagnifierStage" title="Hover or tap to inspect vehicle details">
            <img id="modalMainImg" src="${themeBaseUrl}/assets/images/cars/${encodeURIComponent(catPhotos[0].file)}" alt="${escapeHtml(car.name)}" class="vp-main-photo" onerror="this.src='${themeBaseUrl}/assets/images/cars/car-1.jpg'">
            <div class="magnifier-lens" id="modalMagnifierLens" aria-hidden="true"></div>
            <span class="vp-photo-cat-tag" id="modalPhotoCatTag">${escapeHtml(catPhotos[0].category)} — ${escapeHtml(catPhotos[0].label)}</span>
            ${catPhotos.length > 1 ? `
              <button type="button" class="modal-arrow-btn modal-arrow-prev" id="modalArrowPrev" aria-label="Previous image">&lsaquo;</button>
              <button type="button" class="modal-arrow-btn modal-arrow-next" id="modalArrowNext" aria-label="Next image">&rsaquo;</button>
              <div class="modal-img-counter" id="modalImgCounter">1 / ${catPhotos.length}</div>
            ` : ''}
          </div>

          <div class="vp-magnifier-instruction">
            <span>🔍 Tap or hover on the photo to inspect vehicle details (scratches, interior, tires &amp; dashboard).</span>
            <button type="button" class="btn-open-zoom" id="modalFullscreenZoomBtn">Fullscreen / Pinch Zoom</button>
          </div>

          <!-- Categorized Thumbnail Strip -->
          <div class="vp-thumbs-strip" id="modalThumbsStrip">
            ${catPhotos.map((p, i) => `
              <button type="button" class="vp-thumb-item ${i === 0 ? 'active' : ''}" data-idx="${i}">
                <img src="${themeBaseUrl}/assets/images/cars/${encodeURIComponent(p.file)}" alt="${escapeHtml(p.label)}" loading="lazy" onerror="this.src='${themeBaseUrl}/assets/images/cars/car-1.jpg'">
                <span class="vp-thumb-cat">${escapeHtml(p.category)}</span>
              </button>
            `).join('')}
          </div>
        </div>

        <!-- RIGHT COLUMN: Pricing, Real-Time Hold Countdown, Specs & Actions -->
        <div class="vp-info-col">
          <div class="vp-price-summary-card">
            <div>
              <div class="vp-price-k">Selling Price (${escapeHtml(car.price_note || 'Negotiable')})</div>
              <div class="vp-price-v">${escapeHtml(priceStr)}</div>
            </div>
            <div class="vp-deposit-box">
              <div class="vp-price-k">Reservation Hold Deposit</div>
              <div class="vp-deposit-v">${escapeHtml(depositStr)}</div>
            </div>
          </div>

          <!-- Live Countdown Box when Reserved -->
          <div id="modalLiveCountdownBox" class="live-hold-banner"
               ${(status === 'RESERVED' && car.reserved_until_iso) ? `data-expires="${escapeHtml(car.reserved_until_iso)}" data-formatted="${escapeHtml(car.reserved_until_formatted || '')}" data-days="${escapeHtml(car.reserved_hold_days || 2)}"` : 'style="display:none;"'}>
          </div>

          <!-- Core Specifications Grid -->
          <div class="vp-specs-grid">
            <div class="vp-spec-cell"><span class="vp-sk">Year &amp; Age</span><strong class="vp-sv">${escapeHtml(car.year_model)} (${escapeHtml(car.age_label)})</strong></div>
            <div class="vp-spec-cell"><span class="vp-sk">Odometer</span><strong class="vp-sv">${escapeHtml(car.mileage || 'Low Mileage')}</strong></div>
            <div class="vp-spec-cell"><span class="vp-sk">Transmission</span><strong class="vp-sv">${escapeHtml(car.transmission || 'Automatic')}</strong></div>
            <div class="vp-spec-cell"><span class="vp-sk">Fuel Type</span><strong class="vp-sv">${escapeHtml(car.fuel_type || 'Gasoline')}</strong></div>
            <div class="vp-spec-cell"><span class="vp-sk">Category &amp; Seats</span><strong class="vp-sv">${escapeHtml(car.body_type || 'Sedan')} • ${escapeHtml(car.seating || '5 Seats')}</strong></div>
            <div class="vp-spec-cell"><span class="vp-sk">Exterior Color</span><strong class="vp-sv">${escapeHtml(car.finish || 'Original Paint')}</strong></div>
          </div>

          <!-- Primary CTA Actions -->
          <div class="vp-cta-stack">
            <button type="button" class="btn-reserve-primary ${!isAvail ? 'is-disabled' : ''}" id="modalBookCarBtn" ${!isAvail ? 'disabled' : ''}>
              <svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2.3"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
              <span>${isAvail ? 'Book / Reserve This Car' : `Unavailable (${escapeHtml(status)})`}</span>
            </button>
            <button type="button" class="btn-modal-messenger" id="modalMessengerAction">
              <svg viewBox="0 0 28 28" width="19" height="19" fill="currentColor">
                <path d="M14 2C7.373 2 2 6.945 2 13.045c0 3.477 1.745 6.577 4.473 8.577V26l4.24-2.327c1.045.291 2.155.455 3.287.455 6.627 0 12-4.945 12-11.045S20.627 2 14 2zm1.2 14.882l-3.073-3.282-6 3.282 6.6-7.009 3.145 3.282 5.928-3.282-6.6 7.009z"/>
              </svg>
              <span>Ask About This Vehicle on Messenger</span>
            </button>
          </div>
        </div>
      </div>

      <!-- ===============================================================
           ADD-ON A: REQUIRED "VEHICLE CONDITION & HISTORY" SECTION
           =============================================================== -->
      <section class="vp-condition-history-section">
        <div class="vp-section-header">
          <h4 class="vp-section-title">Vehicle Condition &amp; History</h4>
          <span class="vp-honesty-badge">✓ Verified Honest Disclosure</span>
        </div>

        <div class="vp-condition-cards-grid">
          <div class="vp-cond-card">
            <span class="vp-cond-label">1. Vehicle Age</span>
            <strong class="vp-cond-main">${escapeHtml(car.name)} — ${escapeHtml(car.age_label)}</strong>
            <p class="vp-cond-sub">Model Year ${escapeHtml(car.year_model)} • Verified odometer reading of ${escapeHtml(car.mileage || 'Low Mileage')}.</p>
          </div>

          <div class="vp-cond-card">
            <span class="vp-cond-label">2. Ownership History</span>
            <strong class="vp-cond-main">${escapeHtml(car.ownership_history || '1 previous owner')}</strong>
            <p class="vp-cond-sub">Complete original LTO OR/CR, verified deed of sale, and clean HPG record.</p>
          </div>

          <div class="vp-cond-card vp-cond-card-wide">
            <span class="vp-cond-label">3. Transparent Damage &amp; Condition Disclosure</span>
            <p class="vp-cond-disclosure-text">${escapeHtml(car.damage_disclosure)}</p>
            ${damagePhotos.length > 0 ? `
              <div class="vp-damage-photos-row">
                ${damagePhotos.map(dp => {
                  const file = typeof dp === 'string' ? dp : dp.file;
                  const cap = (typeof dp === 'object' && dp.caption) ? dp.caption : 'Documented pre-existing condition photo';
                  const fullSrc = `${themeBaseUrl}/assets/images/cars/${encodeURIComponent(file)}`;
                  return `
                    <div class="vp-damage-photo-chip" onclick="openPhotoZoomInspector('${fullSrc}', '${escapeHtml(cap)}')">
                      <img src="${fullSrc}" alt="${escapeHtml(cap)}" onerror="this.src='${themeBaseUrl}/assets/images/cars/car-1.jpg'">
                      <div>
                        <strong>📷 Inspect Condition Photo</strong>
                        <span>${escapeHtml(cap)} (Click to zoom)</span>
                      </div>
                    </div>
                  `;
                }).join('')}
              </div>
            ` : ''}
          </div>
        </div>
      </section>

      <!-- CLEAR RESERVATION & DEALERSHIP POLICIES ON EVERY VEHICLE PAGE -->
      <section class="vp-policies-box">
        <h4 class="vp-policies-heading">Reservation Hold, Deposit &amp; Inspection Policies</h4>
        <ul class="vp-policies-list">
          <li><strong>Reservation Hold:</strong> ${escapeHtml(car.vehicle_policies || settings.policy_reservation || 'Reserve for 1, 2, 3, or custom days with a refundable deposit.')}</li>
          <li><strong>Automatic Release:</strong> ${escapeHtml(settings.policy_release || 'Unpaid holds automatically expire at the deadline and return to Available.')}</li>
          <li><strong>Showroom Verification:</strong> ${escapeHtml(settings.policy_papers || 'Original OR/CR and personal mechanic inspections are welcomed prior to final payment.')}</li>
        </ul>
      </section>
    `;

    // Interactive Gallery + Hover Magnifier + Lightbox binding
    let activeIdx = 0;
    const mainImg = document.getElementById('modalMainImg');
    const stageWrap = document.getElementById('modalMagnifierStage');
    const lensEl = document.getElementById('modalMagnifierLens');
    const catTag = document.getElementById('modalPhotoCatTag');
    const counter = document.getElementById('modalImgCounter');
    const thumbBtns = Array.from(modalBody.querySelectorAll('.vp-thumb-item'));

    function setModalPhoto(idx) {
      activeIdx = (idx + catPhotos.length) % catPhotos.length;
      const item = catPhotos[activeIdx];
      if (mainImg) {
        mainImg.src = `${themeBaseUrl}/assets/images/cars/${encodeURIComponent(item.file)}`;
      }
      if (catTag) catTag.textContent = `${item.category} — ${item.label}`;
      if (counter) counter.textContent = `${activeIdx + 1} / ${catPhotos.length}`;
      thumbBtns.forEach((b, bi) => b.classList.toggle('active', bi === activeIdx));
    }

    thumbBtns.forEach(btn => {
      btn.onclick = () => setModalPhoto(Number(btn.getAttribute('data-idx')));
    });

    const prevBtn = document.getElementById('modalArrowPrev');
    const nextBtn = document.getElementById('modalArrowNext');
    if (prevBtn) prevBtn.onclick = (e) => { e.stopPropagation(); setModalPhoto(activeIdx - 1); };
    if (nextBtn) nextBtn.onclick = (e) => { e.stopPropagation(); setModalPhoto(activeIdx + 1); };

    if (stageWrap && mainImg) {
      attachHoverMagnifier(stageWrap, mainImg, lensEl, 2.5);
      stageWrap.onclick = (e) => {
        if (e.target.closest('.modal-arrow-btn')) return;
        const item = catPhotos[activeIdx];
        openPhotoZoomInspector(mainImg.src, `${car.name} — ${item.category}: ${item.label}`);
      };
    }

    const fsZoomBtn = document.getElementById('modalFullscreenZoomBtn');
    if (fsZoomBtn && mainImg) {
      fsZoomBtn.onclick = () => {
        const item = catPhotos[activeIdx];
        openPhotoZoomInspector(mainImg.src, `${car.name} — ${item.category}: ${item.label}`);
      };
    }

    const bookBtn = document.getElementById('modalBookCarBtn');
    if (bookBtn && isAvail) {
      bookBtn.onclick = () => initiateCarBooking(car);
    }

    const msgBtn = document.getElementById('modalMessengerAction');
    if (msgBtn) msgBtn.onclick = () => inquireMessengerLead(car);

    specsModal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    tickAllCountdowns();
  };

  window.closeCarDetailsModal = function() {
    if (specsModal) {
      specsModal.style.display = 'none';
      activeModalCarId = null;
      document.body.style.overflow = '';
    }
  };

  if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeCarDetailsModal);
  if (specsModal) {
    specsModal.addEventListener('click', (e) => {
      if (e.target === specsModal) closeCarDetailsModal();
    });
  }

  // =========================================================================
  // 6. GENERIC CONFIRMATION POPUP DIALOG (Add-on B)
  // =========================================================================
  const confirmModal = document.getElementById('confirmActionModal');
  const confirmIcon = document.getElementById('confirmPopupIcon');
  const confirmQ = document.getElementById('confirmPopupQuestion');
  const confirmDesc = document.getElementById('confirmPopupDesc');
  const confirmProceed = document.getElementById('confirmPopupProceedBtn');
  const confirmCancel = document.getElementById('confirmPopupCancelBtn');

  function askConfirmation({ icon = '🚗', question, description = '', proceedText = 'Yes, Proceed', cancelText = 'Cancel', danger = false }) {
    return new Promise((resolve) => {
      if (!confirmModal) {
        resolve(window.confirm(question));
        return;
      }
      if (confirmIcon) confirmIcon.textContent = icon;
      if (confirmQ) confirmQ.textContent = question;
      if (confirmDesc) confirmDesc.innerHTML = description;
      if (confirmProceed) {
        confirmProceed.textContent = proceedText;
        confirmProceed.classList.toggle('is-danger', !!danger);
      }
      if (confirmCancel) confirmCancel.textContent = cancelText;

      confirmModal.style.display = 'flex';

      function cleanup(result) {
        confirmModal.style.display = 'none';
        confirmProceed.onclick = null;
        confirmCancel.onclick = null;
        resolve(result);
      }

      confirmProceed.onclick = () => cleanup(true);
      confirmCancel.onclick = () => cleanup(false);
    });
  }

  // =========================================================================
  // 7. BOOKING CONFIRMATION POPUPS & RESERVATION HOLD WIZARD (Add-ons B & C)
  //    Step 1 Popup: "Do you want to book this car?" [Yes, Proceed] [Cancel]
  //    Step 2 Form:  "How many days would you like to reserve this car?" (1, 2, 3, Custom)
  //    Step 3 Popup: "Do you want to finalize your booking?"
  //    Step 4 View:  Confirmed Summary + Dates + Price + Deposit + Ref + Countdown + Email/SMS Notice
  // =========================================================================
  const bookingModal = document.getElementById('bookingWizardModal');
  const bookingModalTitle = document.getElementById('bookingModalTitle');
  const bookingModalBody = document.getElementById('bookingModalBody');
  const bookingModalCloseBtn = document.getElementById('bookingModalCloseBtn');

  function closeBookingModal() {
    if (bookingModal) bookingModal.style.display = 'none';
  }
  if (bookingModalCloseBtn) bookingModalCloseBtn.onclick = closeBookingModal;
  if (bookingModal) {
    bookingModal.addEventListener('click', (e) => {
      if (e.target === bookingModal) closeBookingModal();
    });
  }

  window.initiateCarBooking = async function(carInput) {
    const car = carsById.get(Number(carInput.id)) || carInput;
    if (!car) return;

    const status = String(car.status || 'AVAILABLE').toUpperCase();
    if (status !== 'AVAILABLE') {
      alert(`Sorry, ${car.name} is currently ${status} and cannot be reserved right now.`);
      return;
    }

    // POPUP 1 (Requirement B): "Do you want to book this car?" — Yes, Proceed / Cancel
    const step1Confirmed = await askConfirmation({
      icon: '🚘',
      question: 'Do you want to book this car?',
      description: `
        <div class="popup-car-mini-summary">
          <strong>${escapeHtml(car.name)}</strong> (${escapeHtml(car.age_label || '')})<br>
          <span>Selling Price: <strong>${escapeHtml(formatPeso(car.price))}</strong> • Hold Deposit: <strong>${escapeHtml(formatPeso(car.deposit_amount || 10000))}</strong></span>
        </div>
      `,
      proceedText: 'Yes, Proceed',
      cancelText: 'Cancel'
    });

    if (!step1Confirmed) return;

    // Open Step 2: Hold Duration Selection (1 day, 2 days, 3 days, or Custom input) + Customer Details
    openHoldDurationStep(car);
  };

  function openHoldDurationStep(car) {
    if (!bookingModal || !bookingModalBody) return;
    const maxHoldDays = Math.max(1, parseInt(settings.max_hold_days || '7', 10));
    const depositAmt = parseFloat(car.deposit_amount || settings.default_deposit || 10000);

    if (bookingModalTitle) bookingModalTitle.textContent = 'Reserve & Hold Vehicle';

    bookingModalBody.innerHTML = `
      <form id="reservationHoldForm" class="booking-hold-form">
        <div class="booking-car-banner">
          <img src="${themeBaseUrl}/assets/images/cars/${encodeURIComponent(car.image_file || 'car-1.jpg')}" alt="${escapeHtml(car.name)}" onerror="this.src='${themeBaseUrl}/assets/images/cars/car-1.jpg'">
          <div>
            <div class="bcb-title">${escapeHtml(car.name)} <span class="bcb-age">(${escapeHtml(car.short_age || '')})</span></div>
            <div class="bcb-meta">Price: <strong>${escapeHtml(formatPeso(car.price))}</strong> &bull; Required Deposit: <strong>${escapeHtml(formatPeso(depositAmt))}</strong></div>
          </div>
        </div>

        <!-- Requirement C: Ask for hold duration -->
        <div class="hold-duration-section">
          <label class="hold-question-label">How many days would you like to reserve this car?</label>
          <div class="hold-options-grid" id="holdDurationOptions">
            <button type="button" class="hold-opt-btn" data-days="1">1 Day</button>
            <button type="button" class="hold-opt-btn active" data-days="2">2 Days</button>
            <button type="button" class="hold-opt-btn" data-days="3">3 Days</button>
            <button type="button" class="hold-opt-btn" data-days="custom">Custom Input</button>
          </div>

          <div id="customDaysWrap" class="custom-days-wrap" style="display:none;">
            <label for="customHoldDaysInput">Enter custom hold duration (1 to ${maxHoldDays} days):</label>
            <input type="number" id="customHoldDaysInput" min="1" max="${maxHoldDays}" value="4" class="filter-input">
          </div>

          <div class="hold-deadline-preview" id="holdDeadlinePreview"></div>
        </div>

        <!-- Customer Contact Fields for Confirmation Email/SMS -->
        <div class="booking-fields-grid">
          <div class="booking-field">
            <label for="resCustName">Your Full Name *</label>
            <input type="text" id="resCustName" class="filter-input" placeholder="e.g. Juan Dela Cruz" required>
          </div>
          <div class="booking-field">
            <label for="resCustPhone">Mobile Number (for SMS) *</label>
            <input type="tel" id="resCustPhone" class="filter-input" placeholder="e.g. 0917-123-4567" required>
          </div>
          <div class="booking-field booking-field-full">
            <label for="resCustEmail">Email Address (for Confirmation Receipt) *</label>
            <input type="email" id="resCustEmail" class="filter-input" placeholder="e.g. juan@email.com" required>
          </div>
          <div class="booking-field booking-field-full">
            <label for="resCustNotes">Preferred Showroom Visit / Payment Note (Optional)</label>
            <input type="text" id="resCustNotes" class="filter-input" placeholder="e.g. Will visit Saturday morning with mechanic">
          </div>
        </div>

        <div id="bookingFormError" class="booking-error-alert" style="display:none;"></div>

        <div class="confirm-popup-buttons" style="margin-top:1.25rem;">
          <button type="button" class="btn-popup-cancel" id="holdStepCancelBtn">Cancel</button>
          <button type="submit" class="btn-popup-proceed" id="holdStepSubmitBtn">Continue to Finalize &rarr;</button>
        </div>
      </form>
    `;

    let selectedMode = '2';
    const optBtns = Array.from(bookingModalBody.querySelectorAll('.hold-opt-btn'));
    const customWrap = document.getElementById('customDaysWrap');
    const customInput = document.getElementById('customHoldDaysInput');
    const deadlinePreview = document.getElementById('holdDeadlinePreview');

    function getSelectedDays() {
      if (selectedMode === 'custom') {
        const v = parseInt(customInput?.value || '4', 10);
        return Math.min(maxHoldDays, Math.max(1, isNaN(v) ? 1 : v));
      }
      return parseInt(selectedMode, 10) || 2;
    }

    function updateDeadlinePreview() {
      const d = getSelectedDays();
      const target = new Date(Date.now() + d * 86400 * 1000);
      const formatted = target.toLocaleString('en-US', {
        month: 'long', day: 'numeric', year: 'numeric',
        hour: 'numeric', minute: '2-digit'
      });
      if (deadlinePreview) {
        deadlinePreview.innerHTML = `⏳ Hold preview: Reserved for <strong>${d} day${d === 1 ? '' : 's'}</strong>. Payment/deposit due before <strong>${escapeHtml(formatted)}</strong> (auto-releases if unpaid).`;
      }
    }

    optBtns.forEach(btn => {
      btn.onclick = () => {
        selectedMode = btn.getAttribute('data-days');
        optBtns.forEach(b => b.classList.toggle('active', b === btn));
        if (customWrap) customWrap.style.display = (selectedMode === 'custom') ? 'block' : 'none';
        updateDeadlinePreview();
      };
    });

    if (customInput) customInput.addEventListener('input', updateDeadlinePreview);
    updateDeadlinePreview();

    const cancelBtn = document.getElementById('holdStepCancelBtn');
    if (cancelBtn) cancelBtn.onclick = closeBookingModal;

    const form = document.getElementById('reservationHoldForm');
    const errBox = document.getElementById('bookingFormError');
    const submitBtn = document.getElementById('holdStepSubmitBtn');

    form.onsubmit = async (e) => {
      e.preventDefault();
      if (errBox) errBox.style.display = 'none';

      const holdDays = getSelectedDays();
      const customerName = document.getElementById('resCustName').value.trim();
      const customerPhone = document.getElementById('resCustPhone').value.trim();
      const customerEmail = document.getElementById('resCustEmail').value.trim();
      const notes = document.getElementById('resCustNotes').value.trim();

      // POPUP 2 (Requirement B): "Do you want to finalize your booking?"
      const finalizeConfirmed = await askConfirmation({
        icon: '✅',
        question: 'Do you want to finalize your booking?',
        description: `
          You are placing a <strong>${holdDays}-day reservation hold</strong> on <strong>${escapeHtml(car.name)}</strong>.<br>
          Total Price: <strong>${escapeHtml(formatPeso(car.price))}</strong> &bull; Deposit: <strong>${escapeHtml(formatPeso(depositAmt))}</strong>
        `,
        proceedText: 'Yes, Proceed',
        cancelText: 'Cancel'
      });

      if (!finalizeConfirmed) return;

      submitBtn.disabled = true;
      submitBtn.textContent = 'Confirming Reservation...';

      try {
        const resp = await fetch(apiUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: 'create_reservation',
            car_id: car.id,
            customer_name: customerName,
            customer_phone: customerPhone,
            customer_email: customerEmail,
            hold_days: holdDays,
            notes
          })
        });
        const data = await resp.json();

        if (!data.success) {
          if (errBox) {
            errBox.textContent = data.message || 'Unable to complete reservation.';
            errBox.style.display = 'block';
          }
          submitBtn.disabled = false;
          submitBtn.textContent = 'Continue to Finalize →';
          return;
        }

        const r = data.reservation;
        saveBookingRef(r.booking_ref);

        // Update local car state & live DOM immediately
        applyCarStatusUpdate(car.id, {
          status: 'RESERVED',
          reserved_until_iso: r.expires_at_iso,
          reserved_until_formatted: r.expires_at_formatted,
          hold_days: r.hold_days
        });

        renderBookingConfirmationReceipt(r);
      } catch (err) {
        if (errBox) {
          errBox.textContent = 'Network error while saving reservation. Please try again.';
          errBox.style.display = 'block';
        }
        submitBtn.disabled = false;
        submitBtn.textContent = 'Continue to Finalize →';
      }
    };

    bookingModal.style.display = 'flex';
  }

  function renderBookingConfirmationReceipt(r) {
    if (!bookingModalBody) return;
    if (bookingModalTitle) bookingModalTitle.textContent = 'Reservation Confirmed!';

    bookingModalBody.innerHTML = `
      <div class="booking-confirmed-receipt">
        <div class="bcr-success-banner">
          <div class="bcr-check-circle">✓</div>
          <div>
            <strong>Your reservation is confirmed. A confirmation email/SMS will be sent.</strong>
            <div style="font-size:0.84rem; margin-top:2px;">Sent to ${escapeHtml(r.customer_email)} and ${escapeHtml(r.customer_phone)}</div>
          </div>
        </div>

        <!-- Requirement C: Explicit Hold Countdown Message -->
        <div class="live-hold-banner bcr-countdown-highlight"
             data-expires="${escapeHtml(r.expires_at_iso)}"
             data-formatted="${escapeHtml(r.expires_at_formatted)}"
             data-days="${escapeHtml(r.hold_days)}">
          This car is reserved for you for ${escapeHtml(r.hold_days)} day${r.hold_days == 1 ? '' : 's'}. Complete payment before ${escapeHtml(r.expires_at_formatted)}.
        </div>

        <!-- Requirement B: Show dates, total price, deposit, and booking reference -->
        <div class="bcr-details-table">
          <div class="bcr-row">
            <span>Booking Reference</span>
            <strong class="bcr-ref-code">${escapeHtml(r.booking_ref)}</strong>
          </div>
          <div class="bcr-row">
            <span>Reserved Vehicle</span>
            <strong>${escapeHtml(r.car_name)} (#${escapeHtml(r.stock_id)})</strong>
          </div>
          <div class="bcr-row">
            <span>Reservation Start</span>
            <strong>${escapeHtml(r.reserved_at_formatted)}</strong>
          </div>
          <div class="bcr-row">
            <span>Hold Deadline (${escapeHtml(r.hold_days)} Day${r.hold_days == 1 ? '' : 's'})</span>
            <strong>${escapeHtml(r.expires_at_formatted)}</strong>
          </div>
          <div class="bcr-row">
            <span>Total Vehicle Price</span>
            <strong>${escapeHtml(formatPeso(r.car_price))}</strong>
          </div>
          <div class="bcr-row">
            <span>Required Hold Deposit</span>
            <strong style="color:#15803d;">${escapeHtml(formatPeso(r.deposit_amount))}</strong>
          </div>
        </div>

        <div class="confirm-popup-buttons" style="margin-top:1.25rem;">
          <button type="button" class="btn-popup-cancel" id="receiptViewMyResBtn">View My Reservations</button>
          <button type="button" class="btn-popup-proceed" id="receiptDoneBtn">Done</button>
        </div>
      </div>
    `;

    const doneBtn = document.getElementById('receiptDoneBtn');
    if (doneBtn) doneBtn.onclick = closeBookingModal;

    const myResBtn = document.getElementById('receiptViewMyResBtn');
    if (myResBtn) {
      myResBtn.onclick = () => {
        closeBookingModal();
        openMyReservationsModal();
      };
    }

    tickAllCountdowns();
  }

  // =========================================================================
  // 8. MY RESERVATIONS TRACKER & "REMOVE CAR FROM RESERVATION" POPUP
  // =========================================================================
  const myResModal = document.getElementById('myReservationsModal');
  const openMyResBtn = document.getElementById('openMyReservationsBtn');
  const myResCloseBtn = document.getElementById('myResCloseBtn');
  const myResList = document.getElementById('myReservationsList');
  const myResCountBadge = document.getElementById('myResCountBadge');
  const resLookupForm = document.getElementById('resLookupForm');
  const resLookupInput = document.getElementById('resLookupInput');

  async function refreshMyResBadge() {
    const refs = getSavedRefs();
    if (!myResCountBadge) return;
    if (!refs.length) {
      myResCountBadge.style.display = 'none';
      return;
    }
    try {
      const resp = await fetch(apiUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'lookup_reservations', refs })
      });
      const data = await resp.json();
      if (data.success && Array.isArray(data.reservations)) {
        const activeCount = data.reservations.filter(x => x.status === 'ACTIVE').length;
        if (activeCount > 0) {
          myResCountBadge.textContent = activeCount;
          myResCountBadge.style.display = 'inline-flex';
        } else {
          myResCountBadge.style.display = 'none';
        }
      }
    } catch (e) {}
  }

  async function loadReservationsIntoModal(queryStr = '') {
    if (!myResList) return;
    myResList.innerHTML = '<div style="padding:1.5rem; text-align:center; color:#64748b;">Loading reservations...</div>';

    const refs = getSavedRefs();
    if (!queryStr && !refs.length) {
      myResList.innerHTML = `
        <div class="empty-res-box">
          <p><strong>No reservations saved on this browser yet.</strong></p>
          <p style="font-size:0.88rem; color:#64748b; margin-top:4px;">Click <em>Book / Reserve</em> on any available car, or enter your Booking Reference / Email / Mobile # above to look up an existing hold.</p>
        </div>
      `;
      return;
    }

    try {
      const resp = await fetch(apiUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'lookup_reservations', query: queryStr, refs })
      });
      const data = await resp.json();
      const list = (data.success && Array.isArray(data.reservations)) ? data.reservations : [];

      if (!list.length) {
        myResList.innerHTML = `<div class="empty-res-box"><p>No matching reservation records found.</p></div>`;
        return;
      }

      myResList.innerHTML = list.map(r => {
        const isAct = r.status === 'ACTIVE';
        return `
          <div class="my-res-item-card">
            <div class="my-res-top">
              <div>
                <span class="my-res-ref">${escapeHtml(r.booking_ref)}</span>
                <span class="card-status-badge badge-${isAct ? 'reserved' : 'sold'}" style="position:static; margin-left:6px;">${escapeHtml(r.status)}</span>
              </div>
              <strong style="color:#1e3a8a;">${escapeHtml(formatPeso(r.car_price))}</strong>
            </div>
            <div class="my-res-title">${escapeHtml(r.car_name)} <small style="color:#64748b;">(#${escapeHtml(r.stock_id)})</small></div>
            <div class="my-res-meta">
              Customer: <strong>${escapeHtml(r.customer_name)}</strong> &bull; Deposit: <strong>${escapeHtml(formatPeso(r.deposit_amount))}</strong>
            </div>
            ${isAct ? `
              <div class="live-hold-banner" style="margin-top:8px;"
                   data-expires="${escapeHtml(r.expires_at_iso)}"
                   data-formatted="${escapeHtml(r.expires_at_formatted)}"
                   data-days="${escapeHtml(r.hold_days)}">
                This car is reserved for you for ${escapeHtml(r.hold_days)} days. Complete payment before ${escapeHtml(r.expires_at_formatted)}.
              </div>
              <div style="margin-top:10px; display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" class="btn-remove-res" data-cancel-ref="${escapeHtml(r.booking_ref)}" data-car-name="${escapeHtml(r.car_name)}">
                  Remove Car from Reservation
                </button>
              </div>
            ` : `
              <div style="font-size:0.82rem; color:#64748b; margin-top:6px;">Hold ended / ${escapeHtml(r.status.toLowerCase())} (Deadline was ${escapeHtml(r.expires_at_formatted)})</div>
            `}
          </div>
        `;
      }).join('');

      // Bind "Are you sure you want to remove this car from your reservation?" popup!
      myResList.querySelectorAll('[data-cancel-ref]').forEach(btn => {
        btn.onclick = async () => {
          const ref = btn.getAttribute('data-cancel-ref');
          const cName = btn.getAttribute('data-car-name');

          const confirmed = await askConfirmation({
            icon: '🗑️',
            question: 'Are you sure you want to remove this car from your reservation?',
            description: `Removing <strong>${escapeHtml(cName)}</strong> (Ref <strong>${escapeHtml(ref)}</strong>) will cancel your hold and immediately make the vehicle available to other buyers.`,
            proceedText: 'Yes, Proceed',
            cancelText: 'Cancel',
            danger: true
          });

          if (!confirmed) return;

          btn.disabled = true;
          btn.textContent = 'Removing...';
          try {
            const cResp = await fetch(apiUrl, {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ action: 'cancel_reservation', booking_ref: ref })
            });
            const cData = await cResp.json();
            if (cData.success) {
              applyCarStatusUpdate(cData.car_id, {
                status: 'AVAILABLE',
                reserved_until_iso: null,
                reserved_until_formatted: null,
                hold_days: null
              });
              loadReservationsIntoModal(resLookupInput?.value.trim() || '');
              refreshMyResBadge();
            } else {
              alert(cData.message || 'Could not cancel reservation.');
              btn.disabled = false;
            }
          } catch (e) {
            btn.disabled = false;
          }
        };
      });

      tickAllCountdowns();
    } catch (err) {
      myResList.innerHTML = `<div class="empty-res-box"><p>Unable to load reservations right now.</p></div>`;
    }
  }

  window.openMyReservationsModal = function() {
    if (!myResModal) return;
    myResModal.style.display = 'flex';
    loadReservationsIntoModal(resLookupInput?.value.trim() || '');
  };

  if (openMyResBtn) openMyResBtn.onclick = openMyReservationsModal;
  if (myResCloseBtn) myResCloseBtn.onclick = () => { myResModal.style.display = 'none'; };
  if (myResModal) {
    myResModal.addEventListener('click', (e) => {
      if (e.target === myResModal) myResModal.style.display = 'none';
    });
  }
  if (resLookupForm) {
    resLookupForm.onsubmit = (e) => {
      e.preventDefault();
      loadReservationsIntoModal(resLookupInput?.value.trim() || '');
    };
  }

  // =========================================================================
  // 9. REAL-TIME STATUS SYNC, LIVE COUNTDOWN TICKER & AUTO-RELEASE (Add-on C)
  // =========================================================================
  function applyCarStatusUpdate(carId, stObj) {
    const id = Number(carId);
    const car = carsById.get(id);
    const newStatus = String(stObj.status || 'AVAILABLE').toUpperCase();
    const slug = statusToSlug(newStatus);

    if (car) {
      car.status = newStatus;
      car.reserved_until_iso = stObj.reserved_until_iso || null;
      car.reserved_until_formatted = stObj.reserved_until_formatted || null;
      car.reserved_hold_days = stObj.hold_days || null;
    }

    // Update Catalog Card DOM
    const card = document.getElementById(`carCard-${id}`);
    const badge = document.getElementById(`cardBadge-${id}`);
    const holdBox = document.getElementById(`cardHoldBox-${id}`);
    const resBtn = document.getElementById(`cardReserveBtn-${id}`);

    if (card) {
      card.setAttribute('data-status', newStatus.toLowerCase());
      card.classList.toggle('is-sold', newStatus === 'SOLD');
      card.classList.toggle('is-maintenance', newStatus === 'UNDER MAINTENANCE');
    }
    if (badge) {
      badge.textContent = newStatus;
      badge.className = `card-status-badge badge-${slug}`;
    }
    if (holdBox) {
      if (newStatus === 'RESERVED' && stObj.reserved_until_iso) {
        holdBox.style.display = 'block';
        holdBox.setAttribute('data-expires', stObj.reserved_until_iso);
        holdBox.setAttribute('data-days', stObj.hold_days || 2);
      } else {
        holdBox.style.display = 'none';
        holdBox.removeAttribute('data-expires');
      }
    }
    if (resBtn) {
      const isAvail = newStatus === 'AVAILABLE';
      resBtn.disabled = !isAvail;
      resBtn.classList.toggle('is-disabled', !isAvail);
      resBtn.innerHTML = `<span>${isAvail ? 'Book / Reserve' : escapeHtml(newStatus)}</span>`;
    }

    // Refresh Hero Showcase if currently showing this car
    if (featuredCars[currentFeaturedIndex] && Number(featuredCars[currentFeaturedIndex].id) === id) {
      updateShowcase(currentFeaturedIndex);
    }

    // Refresh Modal if currently open on this car
    if (activeModalCarId === id && car) {
      openCarDetailsModal(car);
    }

    // Recompute top status pills
    let availCnt = 0;
    let resCnt = 0;
    carsById.forEach(c => {
      const s = String(c.status || 'AVAILABLE').toUpperCase();
      if (s === 'AVAILABLE') availCnt++;
      else if (s === 'RESERVED') resCnt++;
    });
    const elA = document.getElementById('countAvailStat');
    const elR = document.getElementById('countResStat');
    if (elA) elA.textContent = availCnt;
    if (elR) elR.textContent = resCnt;
  }

  let syncInFlight = false;
  async function syncServerStatuses() {
    if (syncInFlight) return;
    syncInFlight = true;
    try {
      const resp = await fetch(`${apiUrl}?action=sync_status`, { cache: 'no-store' });
      const data = await resp.json();
      if (data.success && data.statuses) {
        Object.values(data.statuses).forEach(stObj => {
          const existing = carsById.get(Number(stObj.id));
          if (existing && (String(existing.status).toUpperCase() !== stObj.status || existing.reserved_until_iso !== stObj.reserved_until_iso)) {
            applyCarStatusUpdate(stObj.id, stObj);
          }
        });
      }
    } catch (e) {
    } finally {
      syncInFlight = false;
    }
  }

  function tickAllCountdowns() {
    let expiredDetected = false;
    const now = Date.now();

    // 1. Update prominent banners (.live-hold-banner)
    document.querySelectorAll('.live-hold-banner[data-expires]').forEach(el => {
      const iso = el.getAttribute('data-expires');
      if (!iso) return;
      const expMs = new Date(iso).getTime();
      const diff = expMs - now;
      if (diff <= 0) {
        expiredDetected = true;
        el.innerHTML = '⌛ Reservation hold deadline reached — releasing vehicle back to Available...';
      } else {
        const formattedDeadline = el.getAttribute('data-formatted') || new Date(expMs).toLocaleString();
        const days = el.getAttribute('data-days') || '2';
        const timerStr = formatCountdownParts(diff);
        el.innerHTML = `
          <div><strong>⏳ Reserved Hold (${escapeHtml(days)} day${days == 1 ? '' : 's'}):</strong> Complete payment before <strong>${escapeHtml(formattedDeadline)}</strong>.</div>
          <div class="countdown-clock-pill">Time Remaining: <strong>${timerStr}</strong></div>
        `;
      }
    });

    // 2. Update compact card countdown strips (.card-hold-countdown)
    document.querySelectorAll('.card-hold-countdown[data-expires]').forEach(el => {
      const iso = el.getAttribute('data-expires');
      if (!iso) return;
      const expMs = new Date(iso).getTime();
      const diff = expMs - now;
      if (diff <= 0) {
        expiredDetected = true;
        el.textContent = '⌛ Hold expired — releasing...';
      } else {
        el.innerHTML = `⏳ Hold expires in <strong>${formatCountdownParts(diff)}</strong>`;
      }
    });

    if (expiredDetected) {
      syncServerStatuses();
    }
  }

  // Start 1-second live countdown ticker & 25-second background real-time sync
  tickAllCountdowns();
  refreshMyResBadge();
  setInterval(tickAllCountdowns, 1000);
  setInterval(syncServerStatuses, 25000);

  // Close topmost modal on Escape key
  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    if (zoomLightbox && zoomLightbox.style.display !== 'none') closePhotoZoomInspector();
    else if (confirmModal && confirmModal.style.display !== 'none') confirmCancel?.click();
    else if (bookingModal && bookingModal.style.display !== 'none') closeBookingModal();
    else if (myResModal && myResModal.style.display !== 'none') myResModal.style.display = 'none';
    else if (specsModal && specsModal.style.display !== 'none') closeCarDetailsModal();
  });
});
