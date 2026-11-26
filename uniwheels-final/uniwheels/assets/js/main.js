/**
 * UniWheels Motors - Public Showroom Script
 * Handles filtering, photo magnifier + pinch-to-zoom lightbox,
 * Vehicle Condition & History details, reservation holds, and booking confirmations.
 */

document.addEventListener('DOMContentLoaded', () => {
  const cars = window.UNIWHEELS_CARS || [];
  const messengerUrl = window.UNIWHEELS_MESSENGER_URL || 'https://m.me/uniwheels';
  const settings = window.UNIWHEELS_SETTINGS || { defaultDeposit: 5000, maxHoldDays: 7, defaultPolicy: '' };
  const imageBase = 'assets/images/cars/';

  let userReservations = [];
  let activeDetailCarId = null;
  let countdownInterval = null;

  function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (ch) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[ch]));
  }

  function formatPeso(amount) {
    const n = parseFloat(amount) || 0;
    return '₱' + n.toLocaleString('en-PH', { maximumFractionDigits: 0 });
  }

  function priceLabel(car) {
    const price = parseFloat(car.price) || 0;
    return price > 0 ? formatPeso(price) : 'Ask for price';
  }

  function formatDateShort(iso) {
    if (!iso) return '';
    const d = new Date(iso + 'T00:00:00');
    if (isNaN(d.getTime())) return iso;
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  }

  function formatRemaining(expiresTs) {
    const diff = Math.max(0, Math.floor(expiresTs - Date.now() / 1000));
    if (diff <= 0) return 'Expired';
    const d = Math.floor(diff / 86400);
    const h = Math.floor((diff % 86400) / 3600);
    const m = Math.floor((diff % 3600) / 60);
    const s = diff % 60;
    const parts = [];
    if (d > 0) parts.push(`${d}d`);
    parts.push(`${String(h).padStart(2, '0')}h`);
    parts.push(`${String(m).padStart(2, '0')}m`);
    parts.push(`${String(s).padStart(2, '0')}s`);
    return parts.join(' ');
  }

  function statusMeta(status) {
    const k = String(status || 'AVAILABLE').toUpperCase();
    const map = {
      AVAILABLE:   { key: 'AVAILABLE', label: 'Available', cls: 'badge-available' },
      RESERVED:    { key: 'RESERVED', label: 'Reserved', cls: 'badge-reserved' },
      RENTED:      { key: 'RENTED', label: 'Rented', cls: 'badge-rented' },
      MAINTENANCE: { key: 'MAINTENANCE', label: 'Under Maintenance', cls: 'badge-maintenance' },
      SOLD:        { key: 'SOLD', label: 'Sold', cls: 'badge-sold' },
      'OUT OF STOCK': { key: 'SOLD', label: 'Sold', cls: 'badge-sold' },
    };
    return map[k] || map.AVAILABLE;
  }

  function getCarById(id) {
    return cars.find((c) => Number(c.id) === Number(id)) || null;
  }

  function getUserResForCar(carId) {
    const list = userReservations.filter((r) => Number(r.car_id) === Number(carId));
    // Prefer booking over hold if both exist
    return list.find((r) => r.kind === 'booking') || list[0] || null;
  }

  function inquire(car) {
    const message = `Hello UniWheels! I'm interested in the ${car.name} (${priceLabel(car)}). Is it still available for viewing or a test drive?`;
    window.open(`${messengerUrl}?text=${encodeURIComponent(message)}`, '_blank', 'noopener');
  }

  async function apiCall(method, body = null) {
    const opts = { method, credentials: 'same-origin', headers: {} };
    if (body) {
      opts.headers['Content-Type'] = 'application/json';
      opts.body = JSON.stringify(body);
    }
    const res = await fetch('api.php' + (method === 'GET' ? '?action=state' : ''), opts);
    const data = await res.json();
    if (!res.ok || !data.success) {
      throw new Error(data.message || 'Request failed. Please try again.');
    }
    applyStateUpdate(data);
    return data;
  }

  function applyStateUpdate(data) {
    if (Array.isArray(data.reservations)) {
      userReservations = data.reservations;
    }
    if (data.cars) {
      cars.forEach((car) => {
        const live = data.cars[car.id];
        if (live) {
          car.status = live.status;
          const m = statusMeta(live.status);
          car.status_label = m.label;
          car.status_class = m.cls;
          car.is_available = m.key === 'AVAILABLE';
          car.is_sold = m.key === 'SOLD';
          car.reserve_expires_ts = live.reserve_ts;
          car.reserve_expires_label = live.reserve_label;
        }
      });
    }
    refreshCardsUI();
    refreshNavReservations();
  }

  function refreshNavReservations() {
    const btn = document.getElementById('myReservationsNavBtn');
    const countEl = document.getElementById('myReservationsCount');
    if (!btn || !countEl) return;
    if (userReservations.length > 0) {
      countEl.textContent = String(userReservations.length);
      btn.hidden = false;
    } else {
      btn.hidden = true;
    }
  }

  function refreshCardsUI() {
    document.querySelectorAll('.car-card[data-car-id]').forEach((card) => {
      const car = getCarById(card.dataset.carId);
      if (!car) return;
      const meta = statusMeta(car.status);
      const owned = getUserResForCar(car.id);

      card.dataset.status = meta.key;
      const badge = card.querySelector('.js-card-status-badge');
      if (badge) {
        badge.className = `card-status-badge ${meta.cls} js-card-status-badge`;
        badge.textContent = owned && meta.key === 'RESERVED' ? 'Reserved for You' : meta.label;
      }

      const holdNote = card.querySelector('.js-card-hold-note');
      if (holdNote) {
        if (owned && owned.state !== 'confirmed' && owned.expires_ts) {
          holdNote.hidden = false;
          holdNote.innerHTML = `Held for you · <span class="js-countdown" data-ts="${owned.expires_ts}">${formatRemaining(owned.expires_ts)}</span>`;
        } else if (owned && owned.state === 'confirmed') {
          holdNote.hidden = false;
          holdNote.textContent = `Confirmed (${owned.reference})`;
        } else {
          holdNote.hidden = true;
          holdNote.textContent = '';
        }
      }

      const actions = card.querySelector('.js-card-actions');
      if (actions && !car.is_sold) {
        if (meta.key === 'AVAILABLE') {
          actions.innerHTML = `
            <button type="button" class="btn-card-book js-car-book">Book / Reserve</button>
            <button type="button" class="btn-card-link js-car-details">Photos &amp; details</button>
          `;
        } else if (owned) {
          actions.innerHTML = `
            <button type="button" class="btn-card-book js-car-details">Manage Reservation</button>
            <button type="button" class="btn-card-link js-car-details">Photos &amp; details</button>
          `;
        } else {
          actions.innerHTML = `
            <button type="button" class="btn-card-book btn-card-disabled" disabled>${escapeHtml(meta.label)}</button>
            <button type="button" class="btn-card-link js-car-details">Photos &amp; details</button>
          `;
        }
      }
    });
  }

  function startCountdownTicker() {
    if (countdownInterval) clearInterval(countdownInterval);
    let releaseTriggered = false;
    countdownInterval = setInterval(() => {
      let expiredNow = false;
      document.querySelectorAll('.js-countdown[data-ts]').forEach((el) => {
        const ts = Number(el.dataset.ts);
        if (!ts) return;
        const txt = formatRemaining(ts);
        el.textContent = txt;
        if (txt === 'Expired') expiredNow = true;
      });
      if (expiredNow && !releaseTriggered) {
        releaseTriggered = true;
        apiCall('GET').then(() => {
          releaseTriggered = false;
          if (activeDetailCarId) {
            const updatedCar = getCarById(activeDetailCarId);
            if (updatedCar && modal.style.display === 'flex') {
              openDetails(updatedCar);
            }
          }
        }).catch(() => { releaseTriggered = false; });
      }
    }, 1000);
  }

  // Filters
  const filterInputs = document.querySelectorAll('[data-filter]');
  const cards = document.querySelectorAll('.car-card');
  const countEl = document.getElementById('filterCount');
  const clearBtn = document.getElementById('filterClear');
  const noResults = document.getElementById('filterNoResults');

  function applyFilters() {
    const active = {};
    filterInputs.forEach((input) => {
      const value = input.value.trim().toLowerCase();
      if (value) active[input.dataset.filter] = value;
    });
    const filtering = Object.keys(active).length > 0;

    let shown = 0;
    cards.forEach((card) => {
      const visible = filtering
        ? card.dataset.sold !== '1' && Object.entries(active).every(([key, value]) =>
            key === 'search' ? card.dataset.search.includes(value) : card.dataset[key] === value)
        : true;
      card.hidden = !visible;
      if (visible && card.dataset.sold !== '1') shown++;
    });

    if (countEl) countEl.textContent = `${shown} unit${shown === 1 ? '' : 's'} shown`;
    if (clearBtn) clearBtn.hidden = !filtering;
    if (noResults) noResults.hidden = !(filtering && shown === 0);
  }

  filterInputs.forEach((input) => input.addEventListener(input.tagName === 'SELECT' ? 'change' : 'input', applyFilters));
  if (clearBtn) {
    clearBtn.addEventListener('click', () => {
      filterInputs.forEach((input) => { input.value = ''; });
      applyFilters();
    });
  }
  if (filterInputs.length) applyFilters();

  // Delegated card click actions
  document.addEventListener('click', (e) => {
    const card = e.target.closest('[data-car-index]');
    if (!card) return;
    const car = cars[Number(card.dataset.carIndex)];
    if (!car) return;

    if (e.target.closest('.js-car-inquire')) inquire(car);
    else if (e.target.closest('.js-car-book')) openDetails(car, 'book');
    else if (e.target.closest('.js-car-details')) openDetails(car);
  });

  // =========================================================================
  // FULLSCREEN PHOTO INSPECTOR (Pinch-to-Zoom on Mobile + Wheel/Click Zoom)
  // =========================================================================
  const lightbox = document.getElementById('photoLightbox');
  const lightboxImg = document.getElementById('lightboxImg');
  const lightboxStage = document.getElementById('lightboxStage');
  const lightboxCaption = document.getElementById('lightboxCaption');
  const lightboxPrev = document.getElementById('lightboxPrev');
  const lightboxNext = document.getElementById('lightboxNext');
  const lightboxClose = document.getElementById('lightboxClose');
  const lightboxZoomIn = document.getElementById('lightboxZoomIn');
  const lightboxZoomOut = document.getElementById('lightboxZoomOut');
  const lightboxZoomReset = document.getElementById('lightboxZoomReset');

  let lbImages = [];
  let lbLabels = {};
  let lbIndex = 0;
  let lbScale = 1;
  let lbTx = 0;
  let lbTy = 0;
  let lbDragging = false;
  let lbStartX = 0;
  let lbStartY = 0;
  let pinchStartDist = 0;
  let pinchStartScale = 1;
  let lastTapTime = 0;

  function applyLbTransform() {
    if (!lightboxImg) return;
    if (lbScale <= 1) {
      lbScale = 1;
      lbTx = 0;
      lbTy = 0;
    }
    lightboxImg.style.transform = `translate(${lbTx}px, ${lbTy}px) scale(${lbScale})`;
    if (lightboxZoomReset) lightboxZoomReset.textContent = Math.round(lbScale * 100) + '%';
  }

  function renderLightboxPhoto() {
    const file = lbImages[lbIndex] || 'car-1.jpg';
    const cat = lbLabels[file] ? ` (${lbLabels[file]})` : '';
    lightboxImg.src = imageBase + file;
    lightboxImg.onerror = () => { lightboxImg.onerror = null; lightboxImg.src = imageBase + 'car-1.jpg'; };
    lightboxCaption.textContent = `${lbIndex + 1} / ${lbImages.length}${cat}`;
    lbScale = 1;
    lbTx = 0;
    lbTy = 0;
    applyLbTransform();
    const multi = lbImages.length > 1;
    if (lightboxPrev) lightboxPrev.style.display = multi ? 'flex' : 'none';
    if (lightboxNext) lightboxNext.style.display = multi ? 'flex' : 'none';
  }

  function openLightbox(images, labels, startIndex) {
    if (!lightbox) return;
    lbImages = images;
    lbLabels = labels || {};
    lbIndex = startIndex || 0;
    renderLightboxPhoto();
    lightbox.style.display = 'flex';
  }

  function closeLightbox() {
    if (!lightbox) return;
    lightbox.style.display = 'none';
  }

  if (lightboxClose) lightboxClose.addEventListener('click', closeLightbox);
  if (lightboxPrev) lightboxPrev.addEventListener('click', () => { lbIndex = (lbIndex - 1 + lbImages.length) % lbImages.length; renderLightboxPhoto(); });
  if (lightboxNext) lightboxNext.addEventListener('click', () => { lbIndex = (lbIndex + 1) % lbImages.length; renderLightboxPhoto(); });
  if (lightboxZoomIn) lightboxZoomIn.addEventListener('click', () => { lbScale = Math.min(4, lbScale + 0.5); applyLbTransform(); });
  if (lightboxZoomOut) lightboxZoomOut.addEventListener('click', () => { lbScale = Math.max(1, lbScale - 0.5); applyLbTransform(); });
  if (lightboxZoomReset) lightboxZoomReset.addEventListener('click', () => { lbScale = 1; applyLbTransform(); });

  if (lightboxStage) {
    lightboxStage.addEventListener('wheel', (e) => {
      e.preventDefault();
      const delta = e.deltaY < 0 ? 0.25 : -0.25;
      lbScale = Math.min(4, Math.max(1, lbScale + delta));
      applyLbTransform();
    }, { passive: false });

    lightboxStage.addEventListener('mousedown', (e) => {
      if (e.target.closest('button') || lbScale <= 1) return;
      lbDragging = true;
      lbStartX = e.clientX - lbTx;
      lbStartY = e.clientY - lbTy;
    });
    window.addEventListener('mousemove', (e) => {
      if (!lbDragging) return;
      lbTx = e.clientX - lbStartX;
      lbTy = e.clientY - lbStartY;
      applyLbTransform();
    });
    window.addEventListener('mouseup', () => { lbDragging = false; });

    lightboxStage.addEventListener('touchstart', (e) => {
      if (e.target.closest('button')) return;
      if (e.touches.length === 2) {
        const dx = e.touches[0].clientX - e.touches[1].clientX;
        const dy = e.touches[0].clientY - e.touches[1].clientY;
        pinchStartDist = Math.hypot(dx, dy);
        pinchStartScale = lbScale;
      } else if (e.touches.length === 1) {
        const now = Date.now();
        if (now - lastTapTime < 280) {
          lbScale = lbScale > 1.2 ? 1 : 2.4;
          lbTx = 0;
          lbTy = 0;
          applyLbTransform();
        }
        lastTapTime = now;
        if (lbScale > 1) {
          lbDragging = true;
          lbStartX = e.touches[0].clientX - lbTx;
          lbStartY = e.touches[0].clientY - lbTy;
        }
      }
    }, { passive: true });

    lightboxStage.addEventListener('touchmove', (e) => {
      if (e.touches.length === 2) {
        e.preventDefault();
        const dx = e.touches[0].clientX - e.touches[1].clientX;
        const dy = e.touches[0].clientY - e.touches[1].clientY;
        const dist = Math.hypot(dx, dy);
        if (pinchStartDist > 0) {
          lbScale = Math.min(4, Math.max(1, pinchStartScale * (dist / pinchStartDist)));
          applyLbTransform();
        }
      } else if (e.touches.length === 1 && lbDragging && lbScale > 1) {
        e.preventDefault();
        lbTx = e.touches[0].clientX - lbStartX;
        lbTy = e.touches[0].clientY - lbStartY;
        applyLbTransform();
      }
    }, { passive: false });

    lightboxStage.addEventListener('touchend', () => { lbDragging = false; });
  }

  // =========================================================================
  // CONFIRMATION / HOLD / MY RESERVATIONS MODAL (#confirmModal)
  // =========================================================================
  const confirmModal = document.getElementById('confirmModal');
  const confirmTitle = document.getElementById('confirmModalTitle');
  const confirmBody = document.getElementById('confirmModalBody');
  const confirmClose = document.getElementById('confirmModalClose');

  function openConfirmModal(title, html) {
    if (!confirmModal || !confirmBody) return;
    confirmTitle.textContent = title;
    confirmBody.innerHTML = html;
    confirmModal.style.display = 'flex';
  }

  function closeConfirmModal() {
    if (!confirmModal) return;
    confirmModal.style.display = 'none';
  }

  if (confirmClose) confirmClose.addEventListener('click', closeConfirmModal);
  if (confirmModal) {
    confirmModal.addEventListener('click', (e) => {
      if (e.target === confirmModal) closeConfirmModal();
    });
  }

  function openHoldPopup(car) {
    const maxDays = settings.maxHoldDays || 7;
    const deposit = car.effective_deposit || settings.defaultDeposit || 5000;
    openConfirmModal('Reserve This Car', `
      <form id="holdForm" class="popup-form">
        <p class="popup-question">How many days would you like to reserve this car?</p>
        <div class="hold-duration-pills" id="holdDurationPills">
          <button type="button" class="hold-pill active" data-days="1">1 day</button>
          <button type="button" class="hold-pill" data-days="2">2 days</button>
          <button type="button" class="hold-pill" data-days="3">3 days</button>
          <button type="button" class="hold-pill" data-days="custom">Custom</button>
        </div>
        <div class="popup-field" id="customHoldDaysWrap" hidden>
          <label for="customHoldDays">Number of days (1–${maxDays})</label>
          <input type="number" id="customHoldDays" min="1" max="${maxDays}" value="4" class="popup-input">
        </div>
        <div class="popup-field">
          <label for="holdName">Your Name</label>
          <input type="text" id="holdName" required placeholder="e.g. Juan Dela Cruz" class="popup-input">
        </div>
        <div class="popup-field">
          <label for="holdContact">Mobile Number or Email</label>
          <input type="text" id="holdContact" required placeholder="e.g. 0917-123-4567 or email" class="popup-input">
        </div>
        <p class="popup-meta-note">Hold Deposit Required at Payment: <strong>${formatPeso(deposit)}</strong>. Unpaid holds automatically release after the deadline.</p>
        <div id="holdError" class="popup-error" hidden></div>
        <div class="popup-btn-row">
          <button type="button" class="btn-popup-cancel" id="holdCancelBtn">Cancel</button>
          <button type="submit" class="btn-popup-primary" id="holdSubmitBtn">Reserve Car</button>
        </div>
      </form>
    `);

    let selectedDays = 1;
    const customWrap = document.getElementById('customHoldDaysWrap');
    const customInput = document.getElementById('customHoldDays');
    document.querySelectorAll('#holdDurationPills .hold-pill').forEach((pill) => {
      pill.addEventListener('click', () => {
        document.querySelectorAll('#holdDurationPills .hold-pill').forEach((p) => p.classList.remove('active'));
        pill.classList.add('active');
        if (pill.dataset.days === 'custom') {
          customWrap.hidden = false;
          selectedDays = Number(customInput.value) || 4;
        } else {
          customWrap.hidden = true;
          selectedDays = Number(pill.dataset.days);
        }
      });
    });

    document.getElementById('holdCancelBtn').addEventListener('click', closeConfirmModal);
    document.getElementById('holdForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const errBox = document.getElementById('holdError');
      const submitBtn = document.getElementById('holdSubmitBtn');
      const isCustom = !customWrap.hidden;
      const days = isCustom ? parseInt(customInput.value, 10) : selectedDays;
      const name = document.getElementById('holdName').value.trim();
      const contact = document.getElementById('holdContact').value.trim();

      errBox.hidden = true;
      submitBtn.disabled = true;
      submitBtn.textContent = 'Reserving…';

      try {
        const res = await apiCall('POST', {
          action: 'hold',
          car_id: car.id,
          days,
          customer_name: name,
          customer_contact: contact,
        });
        openConfirmModal('Car Reserved', `
          <div class="popup-success-box">
            <p class="popup-success-title">This car is reserved for you for ${res.days} ${res.days === 1 ? 'day' : 'days'}.</p>
            <p>Complete payment before <strong>${escapeHtml(res.expires_label)}</strong>.</p>
            <p class="countdown-line">Time left: <strong class="js-countdown" data-ts="${res.expires_ts}">${formatRemaining(res.expires_ts)}</strong></p>
            <p class="ref-line">Hold Reference: <strong>${escapeHtml(res.reference)}</strong></p>
          </div>
          <div class="popup-btn-row">
            <button type="button" class="btn-popup-primary" id="holdDoneBtn">Done</button>
          </div>
        `);
        document.getElementById('holdDoneBtn').addEventListener('click', () => {
          closeConfirmModal();
          openDetails(getCarById(car.id) || car);
        });
      } catch (err) {
        errBox.textContent = err.message;
        errBox.hidden = false;
        submitBtn.disabled = false;
        submitBtn.textContent = 'Reserve Car';
      }
    });
  }

  function openBookFormPopup(car) {
    const lt = String(car.listing_type || 'SALE').toUpperCase();
    const allowBuy = lt === 'SALE' || lt === 'BOTH';
    const allowRent = (lt === 'RENT' || lt === 'BOTH') && (parseFloat(car.rental_rate) > 0);
    const defaultMode = allowBuy ? 'BUY' : 'RENT';
    const today = new Date().toISOString().slice(0, 10);
    const deposit = car.effective_deposit || settings.defaultDeposit || 5000;
    const rate = parseFloat(car.rental_rate) || 0;
    const salePrice = parseFloat(car.price) || 0;

    openConfirmModal('Book This Car', `
      <form id="bookInputForm" class="popup-form">
        <p class="popup-car-label">${escapeHtml(car.name)}</p>
        ${allowBuy && allowRent ? `
          <div class="popup-field">
            <label>Booking Type</label>
            <div class="hold-duration-pills" id="bookTypePills">
              <button type="button" class="hold-pill active" data-mode="BUY">Buy (${formatPeso(salePrice)})</button>
              <button type="button" class="hold-pill" data-mode="RENT">Rent (${formatPeso(rate)}/day)</button>
            </div>
          </div>
        ` : ''}
        <div id="rentDatesGroup" ${defaultMode === 'RENT' ? '' : 'hidden'}>
          <div class="popup-row-2">
            <div class="popup-field">
              <label for="rentalStart">Start Date</label>
              <input type="date" id="rentalStart" min="${today}" value="${today}" class="popup-input">
            </div>
            <div class="popup-field">
              <label for="rentalEnd">End Date</label>
              <input type="date" id="rentalEnd" min="${today}" value="${today}" class="popup-input">
            </div>
          </div>
          <p class="popup-meta-note" id="rentEstimateLine">1 day &times; ${formatPeso(rate)} = <strong>${formatPeso(rate)}</strong></p>
        </div>
        <div id="buyDateGroup" ${defaultMode === 'BUY' ? '' : 'hidden'}>
          <div class="popup-field">
            <label for="pickupDate">Preferred Viewing / Pickup Date</label>
            <input type="date" id="pickupDate" min="${today}" value="${today}" class="popup-input">
          </div>
        </div>
        <div class="popup-field">
          <label for="bookName">Your Name</label>
          <input type="text" id="bookName" required placeholder="e.g. Maria Santos" class="popup-input">
        </div>
        <div class="popup-field">
          <label for="bookContact">Mobile Number or Email</label>
          <input type="text" id="bookContact" required placeholder="e.g. 0917-123-4567 or email" class="popup-input">
        </div>
        <p class="popup-meta-note">Required Deposit: <strong>${formatPeso(deposit)}</strong></p>
        <div id="bookFormError" class="popup-error" hidden></div>
        <div class="popup-btn-row">
          <button type="button" class="btn-popup-cancel" id="bookFormCancel">Cancel</button>
          <button type="submit" class="btn-popup-primary">Continue</button>
        </div>
      </form>
    `);

    let mode = defaultMode;
    const rentGroup = document.getElementById('rentDatesGroup');
    const buyGroup = document.getElementById('buyDateGroup');
    const startInput = document.getElementById('rentalStart');
    const endInput = document.getElementById('rentalEnd');
    const estLine = document.getElementById('rentEstimateLine');

    function updateRentEst() {
      if (!startInput || !endInput || !estLine) return;
      const s = new Date(startInput.value + 'T00:00:00');
      const e = new Date(endInput.value + 'T00:00:00');
      const days = (!isNaN(s) && !isNaN(e) && e >= s) ? Math.round((e - s) / 86400000) + 1 : 1;
      estLine.innerHTML = `${days} ${days === 1 ? 'day' : 'days'} &times; ${formatPeso(rate)} = <strong>${formatPeso(days * rate)}</strong>`;
    }

    if (startInput && endInput) {
      startInput.addEventListener('change', () => {
        if (endInput.value < startInput.value) endInput.value = startInput.value;
        endInput.min = startInput.value;
        updateRentEst();
      });
      endInput.addEventListener('change', updateRentEst);
    }

    document.querySelectorAll('#bookTypePills .hold-pill').forEach((btn) => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('#bookTypePills .hold-pill').forEach((b) => b.classList.remove('active'));
        btn.classList.add('active');
        mode = btn.dataset.mode;
        rentGroup.hidden = mode !== 'RENT';
        buyGroup.hidden = mode !== 'BUY';
      });
    });

    document.getElementById('bookFormCancel').addEventListener('click', closeConfirmModal);
    document.getElementById('bookInputForm').addEventListener('submit', (e) => {
      e.preventDefault();
      const name = document.getElementById('bookName').value.trim();
      const contact = document.getElementById('bookContact').value.trim();
      const rentalStart = mode === 'RENT' ? startInput.value : document.getElementById('pickupDate').value;
      const rentalEnd = mode === 'RENT' ? endInput.value : rentalStart;

      let days = 1;
      let total = salePrice;
      if (mode === 'RENT') {
        const s = new Date(rentalStart + 'T00:00:00');
        const eD = new Date(rentalEnd + 'T00:00:00');
        days = Math.max(1, Math.round((eD - s) / 86400000) + 1);
        total = days * rate;
      }

      const dateLabel = mode === 'RENT'
        ? `${formatDateShort(rentalStart)} – ${formatDateShort(rentalEnd)} (${days} ${days === 1 ? 'day' : 'days'})`
        : `${formatDateShort(rentalStart)} (Pickup / Viewing)`;

      // Step 1 Confirmation Popup (Requirement B)
      openConfirmModal('Confirm Booking', `
        <p class="popup-question">Do you want to book this car?</p>
        <div class="popup-summary-card">
          <div class="popup-sum-row"><span>Vehicle</span><strong>${escapeHtml(car.name)}</strong></div>
          <div class="popup-sum-row"><span>${mode === 'RENT' ? 'Rental Dates' : 'Date'}</span><strong>${escapeHtml(dateLabel)}</strong></div>
          <div class="popup-sum-row"><span>Total Price</span><strong>${formatPeso(total)}</strong></div>
          <div class="popup-sum-row"><span>Deposit</span><strong>${formatPeso(deposit)}</strong></div>
        </div>
        <div id="proceedErr" class="popup-error" hidden></div>
        <div class="popup-btn-row">
          <button type="button" class="btn-popup-cancel" id="proceedCancelBtn">Cancel</button>
          <button type="button" class="btn-popup-primary" id="proceedConfirmBtn">Yes, Proceed</button>
        </div>
      `);

      document.getElementById('proceedCancelBtn').addEventListener('click', closeConfirmModal);
      document.getElementById('proceedConfirmBtn').addEventListener('click', async () => {
        const btn = document.getElementById('proceedConfirmBtn');
        const err = document.getElementById('proceedErr');
        btn.disabled = true;
        btn.textContent = 'Processing…';
        err.hidden = true;

        try {
          const res = await apiCall('POST', {
            action: 'book_proceed',
            car_id: car.id,
            booking_type: mode,
            customer_name: name,
            customer_contact: contact,
            rental_start: rentalStart,
            rental_end: rentalEnd,
          });
          showBookingReviewPopup(car, res);
        } catch (ex) {
          err.textContent = ex.message;
          err.hidden = false;
          btn.disabled = false;
          btn.textContent = 'Yes, Proceed';
        }
      });
    });
  }

  function showBookingReviewPopup(car, booking) {
    const isRent = String(booking.booking_type).toUpperCase() === 'RENT';
    const dateText = isRent
      ? `${formatDateShort(booking.rental_start)} – ${formatDateShort(booking.rental_end)}`
      : formatDateShort(booking.rental_start);

    openConfirmModal('Booking Summary', `
      <div class="popup-summary-card">
        <div class="popup-sum-row"><span>Booking Reference</span><strong class="ref-highlight">${escapeHtml(booking.reference)}</strong></div>
        <div class="popup-sum-row"><span>Vehicle</span><strong>${escapeHtml(car.name || booking.car_name)}</strong></div>
        <div class="popup-sum-row"><span>${isRent ? 'Rental Dates' : 'Pickup Date'}</span><strong>${escapeHtml(dateText)}</strong></div>
        <div class="popup-sum-row"><span>Total Price</span><strong>${formatPeso(booking.total)}</strong></div>
        <div class="popup-sum-row"><span>Deposit</span><strong>${formatPeso(booking.deposit)}</strong></div>
      </div>
      <div class="popup-btn-row">
        <button type="button" class="btn-popup-danger" id="reviewRemoveBtn">Remove Reservation</button>
        <button type="button" class="btn-popup-primary" id="reviewFinalizeBtn">Finalize Booking</button>
      </div>
    `);

    document.getElementById('reviewFinalizeBtn').addEventListener('click', () => {
      openFinalizePopup(car, booking);
    });
    document.getElementById('reviewRemoveBtn').addEventListener('click', () => {
      openRemovePopup(car.id || booking.car_id, booking.reference);
    });
  }

  function openFinalizePopup(car, booking) {
    openConfirmModal('Finalize Booking', `
      <p class="popup-question">Do you want to finalize your booking?</p>
      <div class="popup-summary-card">
        <div class="popup-sum-row"><span>Reference</span><strong>${escapeHtml(booking.reference)}</strong></div>
        <div class="popup-sum-row"><span>Total Price</span><strong>${formatPeso(booking.total)}</strong></div>
        <div class="popup-sum-row"><span>Deposit</span><strong>${formatPeso(booking.deposit)}</strong></div>
      </div>
      <div id="finalizeErr" class="popup-error" hidden></div>
      <div class="popup-btn-row">
        <button type="button" class="btn-popup-cancel" id="finalizeCancelBtn">Cancel</button>
        <button type="button" class="btn-popup-primary" id="finalizeConfirmBtn">Yes, Finalize</button>
      </div>
    `);

    document.getElementById('finalizeCancelBtn').addEventListener('click', () => showBookingReviewPopup(car, booking));
    document.getElementById('finalizeConfirmBtn').addEventListener('click', async () => {
      const btn = document.getElementById('finalizeConfirmBtn');
      const err = document.getElementById('finalizeErr');
      btn.disabled = true;
      btn.textContent = 'Confirming…';
      err.hidden = true;

      try {
        const res = await apiCall('POST', { action: 'book_finalize', reference: booking.reference });
        openConfirmModal('Reservation Confirmed', `
          <div class="popup-success-box">
            <p class="popup-success-title">${escapeHtml(res.message)}</p>
            <p class="ref-line">Booking Reference: <strong>${escapeHtml(res.reference)}</strong></p>
            <p>Total: <strong>${formatPeso(booking.total)}</strong> · Deposit: <strong>${formatPeso(booking.deposit)}</strong></p>
          </div>
          <div class="popup-btn-row">
            <button type="button" class="btn-popup-primary" id="finalDoneBtn">Done</button>
          </div>
        `);
        document.getElementById('finalDoneBtn').addEventListener('click', () => {
          closeConfirmModal();
          openDetails(getCarById(car.id || booking.car_id) || car);
        });
      } catch (ex) {
        err.textContent = ex.message;
        err.hidden = false;
        btn.disabled = false;
        btn.textContent = 'Yes, Finalize';
      }
    });
  }

  function openRemovePopup(carId, reference = '') {
    openConfirmModal('Remove Reservation', `
      <p class="popup-question">Are you sure you want to remove this car from your reservation?</p>
      <div id="removeErr" class="popup-error" hidden></div>
      <div class="popup-btn-row">
        <button type="button" class="btn-popup-cancel" id="removeCancelBtn">Cancel</button>
        <button type="button" class="btn-popup-danger" id="removeConfirmBtn">Yes, Remove</button>
      </div>
    `);

    document.getElementById('removeCancelBtn').addEventListener('click', closeConfirmModal);
    document.getElementById('removeConfirmBtn').addEventListener('click', async () => {
      const btn = document.getElementById('removeConfirmBtn');
      const err = document.getElementById('removeErr');
      btn.disabled = true;
      btn.textContent = 'Removing…';
      err.hidden = true;

      try {
        await apiCall('POST', { action: 'book_remove', car_id: carId, reference });
        closeConfirmModal();
        const car = getCarById(carId);
        if (car && modal.style.display === 'flex') {
          openDetails(car);
        }
      } catch (ex) {
        err.textContent = ex.message;
        err.hidden = false;
        btn.disabled = false;
        btn.textContent = 'Yes, Remove';
      }
    });
  }

  function openMyReservationsModal() {
    if (!userReservations.length) {
      openConfirmModal('My Reservations', '<p>You do not have any active reservations.</p>');
      return;
    }
    const itemsHtml = userReservations.map((r) => {
      const isConfirmed = r.state === 'confirmed';
      const isBooking = r.kind === 'booking';
      return `
        <div class="my-res-item">
          <div class="my-res-top">
            <strong>${escapeHtml(r.car_name)}</strong>
            <span class="card-status-badge ${isConfirmed ? 'badge-available' : 'badge-reserved'}">${isConfirmed ? 'Confirmed' : (isBooking ? 'Pending Booking' : 'Hold Active')}</span>
          </div>
          <div class="my-res-meta">
            Ref: <strong>${escapeHtml(r.reference)}</strong> · Deposit: <strong>${formatPeso(r.deposit)}</strong>
            ${!isConfirmed && r.expires_ts ? `<br>Expires ${escapeHtml(r.expires_label)} (<span class="js-countdown" data-ts="${r.expires_ts}">${formatRemaining(r.expires_ts)}</span>)` : ''}
          </div>
          <div class="my-res-actions">
            <button type="button" class="btn-popup-cancel js-res-view" data-car-id="${r.car_id}">View Car</button>
            ${isBooking && !isConfirmed ? `<button type="button" class="btn-popup-primary js-res-finalize" data-ref="${escapeHtml(r.reference)}" data-car-id="${r.car_id}">Finalize</button>` : ''}
            <button type="button" class="btn-popup-danger js-res-remove" data-ref="${escapeHtml(r.reference)}" data-car-id="${r.car_id}">Remove</button>
          </div>
        </div>
      `;
    }).join('');

    openConfirmModal('My Reservations', `<div class="my-res-list">${itemsHtml}</div>`);

    confirmBody.querySelectorAll('.js-res-view').forEach((b) => {
      b.addEventListener('click', () => {
        closeConfirmModal();
        const car = getCarById(b.dataset.carId);
        if (car) openDetails(car);
      });
    });
    confirmBody.querySelectorAll('.js-res-finalize').forEach((b) => {
      b.addEventListener('click', () => {
        const res = userReservations.find((x) => x.reference === b.dataset.ref);
        const car = getCarById(b.dataset.carId) || { id: b.dataset.carId, name: res?.car_name };
        if (res) openFinalizePopup(car, res);
      });
    });
    confirmBody.querySelectorAll('.js-res-remove').forEach((b) => {
      b.addEventListener('click', () => openRemovePopup(b.dataset.carId, b.dataset.ref));
    });
  }

  const navResBtn = document.getElementById('myReservationsNavBtn');
  if (navResBtn) navResBtn.addEventListener('click', openMyReservationsModal);

  // =========================================================================
  // VEHICLE DETAILS MODAL (#specsModal)
  // =========================================================================
  const modal = document.getElementById('specsModal');
  const modalBody = document.getElementById('modalContentBody');
  const modalTitle = document.getElementById('modalCarTitle');
  const modalCloseBtn = document.getElementById('modalCloseBtn');

  function openDetails(car, initialIntent = '') {
    if (!modal || !modalBody || !car) return;
    activeDetailCarId = car.id;

    const images = car.all_images && car.all_images.length ? car.all_images : ['car-1.jpg'];
    const labelsMap = car.photo_labels_map || {};
    let photoIndex = 0;
    const hasMultiple = images.length > 1;
    const meta = statusMeta(car.status);
    const owned = getUserResForCar(car.id);

    const damagePhotoIndex = images.findIndex((f) => (labelsMap[f] || '').toLowerCase().includes('damage'));

    const specRows = [
      ['Stock ID', car.stock_id],
      ['Brand', car.brand],
      ['Mileage', car.mileage],
      ['Transmission', car.transmission],
      ['Body Type', car.body_type],
      ['Fuel Type', car.fuel_type],
      ['Seating', car.seating],
      ['Color', car.finish],
    ].filter(([, v]) => v)
      .map(([k, v]) => `<div class="modal-row"><span class="modal-k">${k}</span><span class="modal-v">${escapeHtml(v)}</span></div>`)
      .join('');

    const thumbsHtml = images.map((img, idx) => {
      const cat = labelsMap[img] || (idx === 0 ? 'Exterior' : `Photo ${idx + 1}`);
      return `
        <button type="button" class="modal-thumb-btn ${idx === 0 ? 'active' : ''}" data-idx="${idx}">
          <img src="${imageBase}${escapeHtml(img)}" alt="${escapeHtml(cat)}" loading="lazy">
          <span>${escapeHtml(cat)}</span>
        </button>
      `;
    }).join('');

    // Reservation / Booking Banner inside Modal
    let statusBannerHtml = '';
    if (owned && owned.kind === 'hold' && owned.state === 'active') {
      statusBannerHtml = `
        <div class="modal-hold-banner">
          <div>
            <strong>This car is reserved for you for ${owned.days} ${owned.days === 1 ? 'day' : 'days'}.</strong>
            Complete payment before <strong>${escapeHtml(owned.expires_label)}</strong>.
            <span class="hold-timer-pill js-countdown" data-ts="${owned.expires_ts}">${formatRemaining(owned.expires_ts)}</span>
          </div>
          <div class="modal-hold-actions">
            <button type="button" class="btn-banner-primary" id="modalProceedFromHoldBtn">Book / Finalize</button>
            <button type="button" class="btn-banner-danger" id="modalRemoveResBtn">Remove Reservation</button>
          </div>
        </div>
      `;
    } else if (owned && owned.kind === 'booking' && owned.state === 'pending') {
      const isRent = String(owned.booking_type).toUpperCase() === 'RENT';
      statusBannerHtml = `
        <div class="modal-hold-banner">
          <div>
            <strong>Pending Booking (${escapeHtml(owned.reference)})</strong><br>
            ${isRent ? `Rental: ${formatDateShort(owned.rental_start)} – ${formatDateShort(owned.rental_end)}` : `Pickup: ${formatDateShort(owned.rental_start)}`}
            · Total: <strong>${formatPeso(owned.total)}</strong> · Deposit: <strong>${formatPeso(owned.deposit)}</strong>
          </div>
          <div class="modal-hold-actions">
            <button type="button" class="btn-banner-primary" id="modalFinalizePendingBtn">Finalize Booking</button>
            <button type="button" class="btn-banner-danger" id="modalRemoveResBtn">Remove Reservation</button>
          </div>
        </div>
      `;
    } else if (owned && owned.kind === 'booking' && owned.state === 'confirmed') {
      statusBannerHtml = `
        <div class="modal-hold-banner is-confirmed">
          <div>
            <strong>Confirmed Reservation (${escapeHtml(owned.reference)})</strong><br>
            Your reservation is confirmed. A confirmation email/SMS will be sent.
          </div>
          <div class="modal-hold-actions">
            <button type="button" class="btn-banner-danger" id="modalRemoveResBtn">Remove Reservation</button>
          </div>
        </div>
      `;
    } else if (meta.key === 'RESERVED' && car.reserve_expires_label) {
      statusBannerHtml = `
        <div class="modal-hold-banner is-other">
          <div>Currently reserved until <strong>${escapeHtml(car.reserve_expires_label)}</strong>.</div>
        </div>
      `;
    }

    const lt = String(car.listing_type || 'SALE').toUpperCase();
    const rateHtml = (!car.is_sold && (lt === 'RENT' || lt === 'BOTH') && parseFloat(car.rental_rate) > 0)
      ? `<div class="modal-rate-chip">Rental: <strong>${formatPeso(car.rental_rate)} / day</strong></div>`
      : '';
    const depositHtml = !car.is_sold
      ? `<div class="modal-rate-chip">Deposit: <strong>${formatPeso(car.effective_deposit || settings.defaultDeposit)}</strong></div>`
      : '';

    const firstCat = labelsMap[images[0]] || 'Exterior';

    if (modalTitle) modalTitle.textContent = car.name || 'Vehicle Details';

    modalBody.innerHTML = `
      <div class="modal-car-layout">
        <div class="modal-gallery-block">
          <div class="modal-car-img-wrap modal-zoom-stage" id="modalZoomStage">
            <img id="modalMainImg" src="${imageBase}${escapeHtml(images[0])}" alt="${escapeHtml(car.name)}" class="modal-photo modal-zoom-img">
            <span class="modal-photo-cat" id="modalPhotoCat">${escapeHtml(firstCat)}</span>
            ${hasMultiple ? `
              <button type="button" class="modal-arrow-btn modal-arrow-prev" data-step="-1" aria-label="Previous photo">&lsaquo;</button>
              <button type="button" class="modal-arrow-btn modal-arrow-next" data-step="1" aria-label="Next photo">&rsaquo;</button>
              <div class="modal-img-counter" id="modalImgCounter">1 / ${images.length}</div>
            ` : ''}
            <div class="modal-price-tag">${car.is_sold ? 'Sold' : escapeHtml(priceLabel(car))}</div>
          </div>
          <p class="modal-zoom-hint">Tap or hover on the photo to inspect vehicle details.</p>
          ${hasMultiple ? `<div class="modal-thumbs-strip" id="modalThumbsStrip">${thumbsHtml}</div>` : ''}
        </div>

        <div class="modal-status-bar">
          <span class="card-status-badge ${meta.cls}">${escapeHtml(meta.label)}</span>
          ${rateHtml}
          ${depositHtml}
        </div>

        ${statusBannerHtml}

        <!-- Requirement A: Vehicle Condition & History -->
        <section class="modal-section-card">
          <h4 class="modal-section-heading">Vehicle Condition &amp; History</h4>
          <div class="condition-grid">
            <div class="condition-item">
              <span class="condition-label">Vehicle Age</span>
              <strong class="condition-value">${escapeHtml(car.age_label || 'Verified')}</strong>
            </div>
            <div class="condition-item">
              <span class="condition-label">Ownership History</span>
              <strong class="condition-value">${escapeHtml(car.ownership_history || 'Verified ownership')}</strong>
            </div>
            <div class="condition-item condition-item-full">
              <div class="condition-damage-head">
                <span class="condition-label">Damage Disclosure</span>
                <span class="damage-pill damage-${escapeHtml(String(car.damage_status || 'NONE').toLowerCase())}">${escapeHtml(car.damage_status_label || 'Verified')}</span>
              </div>
              <p class="condition-report-text">${escapeHtml(car.damage_report || 'Inspected prior to listing.')}</p>
              ${damagePhotoIndex >= 0 ? `<button type="button" class="btn-jump-damage" id="jumpDamagePhotoBtn">View damage close-up photo &rarr;</button>` : ''}
            </div>
          </div>
        </section>

        <section class="modal-section-card">
          <h4 class="modal-section-heading">Specifications</h4>
          <div class="modal-specs-table">${specRows}</div>
        </section>

        <section class="modal-section-card">
          <h4 class="modal-section-heading">Booking &amp; Rental Policy</h4>
          <p class="modal-policy-text">${escapeHtml(car.effective_policy || settings.defaultPolicy)}</p>
        </section>

        ${car.is_sold ? '' : `
          <div class="modal-actions-bar">
            ${meta.key === 'AVAILABLE' ? `
              <div class="modal-primary-actions">
                <button type="button" class="btn-modal-primary" id="modalBookBtn">Book Now</button>
                <button type="button" class="btn-modal-reserve" id="modalReserveBtn">Reserve (Hold)</button>
              </div>
            ` : ''}
            <button type="button" class="btn-modal-messenger" id="modalMessengerAction">
              <svg viewBox="0 0 28 28" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M14 2C7.373 2 2 6.945 2 13.045c0 3.477 1.745 6.577 4.473 8.577V26l4.24-2.327c1.045.291 2.155.455 3.287.455 6.627 0 12-4.945 12-11.045S20.627 2 14 2zm1.2 14.882l-3.073-3.282-6 3.282 6.6-7.009 3.145 3.282 5.928-3.282-6.6 7.009z"/></svg>
              <span>Ask about this car on Messenger</span>
            </button>
          </div>
        `}
      </div>
    `;

    const mainImg = document.getElementById('modalMainImg');
    const zoomStage = document.getElementById('modalZoomStage');
    const photoCat = document.getElementById('modalPhotoCat');
    const imgCounter = document.getElementById('modalImgCounter');

    function setPhoto(idx) {
      photoIndex = (idx + images.length) % images.length;
      const file = images[photoIndex];
      mainImg.src = imageBase + file;
      if (photoCat) photoCat.textContent = labelsMap[file] || (photoIndex === 0 ? 'Exterior' : `Photo ${photoIndex + 1}`);
      if (imgCounter) imgCounter.textContent = `${photoIndex + 1} / ${images.length}`;
      modalBody.querySelectorAll('.modal-thumb-btn').forEach((t, i) => {
        t.classList.toggle('active', i === photoIndex);
      });
    }

    mainImg.onerror = () => { mainImg.onerror = null; mainImg.src = imageBase + 'car-1.jpg'; };

    // Desktop Hover Magnifier + Tap/Click Fullscreen Lightbox
    if (zoomStage && mainImg) {
      zoomStage.addEventListener('mousemove', (e) => {
        if (window.matchMedia('(hover: none)').matches || e.target.closest('button')) return;
        const rect = zoomStage.getBoundingClientRect();
        const xPct = Math.min(100, Math.max(0, ((e.clientX - rect.left) / rect.width) * 100));
        const yPct = Math.min(100, Math.max(0, ((e.clientY - rect.top) / rect.height) * 100));
        mainImg.style.transformOrigin = `${xPct}% ${yPct}%`;
        mainImg.classList.add('is-zoomed');
      });
      zoomStage.addEventListener('mouseleave', () => {
        mainImg.classList.remove('is-zoomed');
      });
      zoomStage.addEventListener('click', (e) => {
        if (e.target.closest('button')) return;
        openLightbox(images, labelsMap, photoIndex);
      });
    }

    modalBody.querySelectorAll('.modal-arrow-btn').forEach((btn) => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        setPhoto(photoIndex + Number(btn.dataset.step));
      });
    });

    modalBody.querySelectorAll('.modal-thumb-btn').forEach((btn) => {
      btn.addEventListener('click', () => setPhoto(Number(btn.dataset.idx)));
    });

    const jumpDmg = document.getElementById('jumpDamagePhotoBtn');
    if (jumpDmg) {
      jumpDmg.addEventListener('click', () => {
        setPhoto(damagePhotoIndex);
        zoomStage.scrollIntoView({ behavior: 'smooth', block: 'center' });
      });
    }

    const bookBtn = document.getElementById('modalBookBtn');
    if (bookBtn) bookBtn.addEventListener('click', () => openBookFormPopup(car));

    const reserveBtn = document.getElementById('modalReserveBtn');
    if (reserveBtn) reserveBtn.addEventListener('click', () => openHoldPopup(car));

    const proceedHoldBtn = document.getElementById('modalProceedFromHoldBtn');
    if (proceedHoldBtn) proceedHoldBtn.addEventListener('click', () => openBookFormPopup(car));

    const finalizePendingBtn = document.getElementById('modalFinalizePendingBtn');
    if (finalizePendingBtn && owned) finalizePendingBtn.addEventListener('click', () => showBookingReviewPopup(car, owned));

    const removeResBtn = document.getElementById('modalRemoveResBtn');
    if (removeResBtn) removeResBtn.addEventListener('click', () => openRemovePopup(car.id, owned ? owned.reference : ''));

    const messengerBtn = document.getElementById('modalMessengerAction');
    if (messengerBtn) messengerBtn.addEventListener('click', () => inquire(car));

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    if (initialIntent === 'book' && meta.key === 'AVAILABLE') {
      const actionsBar = modalBody.querySelector('.modal-actions-bar');
      if (actionsBar) actionsBar.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  }

  function closeDetails() {
    if (!modal) return;
    activeDetailCarId = null;
    modal.style.display = 'none';
    document.body.style.overflow = '';
  }

  if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeDetails);
  if (modal) {
    modal.addEventListener('click', (e) => {
      if (e.target === modal) closeDetails();
    });
  }
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      if (lightbox && lightbox.style.display === 'flex') closeLightbox();
      else if (confirmModal && confirmModal.style.display === 'flex') closeConfirmModal();
      else closeDetails();
    }
  });

  // Initial sync & direct ?car=<id> link support
  startCountdownTicker();
  apiCall('GET').then(() => {
    const params = new URLSearchParams(window.location.search);
    const carParam = parseInt(params.get('car') || '', 10);
    if (carParam) {
      const target = getCarById(carParam);
      if (target) openDetails(target);
    }
  }).catch(() => {});
});
