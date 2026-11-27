/**
 * UniWheels Motors - Public Showroom Script
 * Handles filtering, photo magnifier + pinch-to-zoom lightbox,
 * Vehicle Condition & History details, and Messenger inquiries.
 */

document.addEventListener('DOMContentLoaded', () => {
  const cars = window.UNIWHEELS_CARS || [];
  const messengerUrl = window.UNIWHEELS_MESSENGER_URL || 'https://m.me/uniwheels';
  const settings = window.UNIWHEELS_SETTINGS || { defaultDeposit: 5000, maxHoldDays: 7, defaultPolicy: '' };
  const imageBase = 'assets/images/cars/';

  let activeDetailCarId = null;

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
    if (price > 0) return formatPeso(price);
    return String(car.status || '').toUpperCase() === 'MAINTENANCE' ? 'N/A' : 'Ask for price';
  }

  function statusMeta(status) {
    const k = String(status || 'AVAILABLE').toUpperCase();
    const map = {
      AVAILABLE:      { key: 'AVAILABLE', label: 'Available', cls: 'badge-available' },
      RESERVED:       { key: 'RESERVED', label: 'Reserved', cls: 'badge-reserved' },
      MAINTENANCE:    { key: 'MAINTENANCE', label: 'Under Maintenance', cls: 'badge-maintenance' },
      SOLD:           { key: 'SOLD', label: 'Sold', cls: 'badge-sold' },
      'OUT OF STOCK': { key: 'SOLD', label: 'Sold', cls: 'badge-sold' },
    };
    return map[k] || map.AVAILABLE;
  }

  function getCarById(id) {
    return cars.find((c) => Number(c.id) === Number(id)) || null;
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
  }

  function refreshCardsUI() {
    const grid = document.getElementById('activeInventoryGrid');
    const rank = (s) => (s === 'AVAILABLE' ? 0 : (s === 'RESERVED' ? 1 : (s === 'MAINTENANCE' ? 2 : 3)));

    document.querySelectorAll('#activeInventoryGrid .car-card[data-car-id]').forEach((card) => {
      const car = getCarById(card.dataset.carId);
      if (!car) return;
      const meta = statusMeta(car.status);

      card.dataset.status = meta.key;
      const badge = card.querySelector('.js-card-status-badge');
      if (badge) {
        badge.className = `card-status-badge ${meta.cls} js-card-status-badge`;
        badge.textContent = meta.label;
      }

      const actions = card.querySelector('.js-card-actions');
      if (actions && !car.is_sold) {
        if (meta.key === 'AVAILABLE') {
          actions.innerHTML = `
            <button type="button" class="btn-card-book js-car-inquire">Inquire on Messenger</button>
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

    if (grid) {
      const sorted = Array.from(grid.querySelectorAll('.car-card[data-car-id]')).sort((a, b) => {
        const ra = rank(a.dataset.status);
        const rb = rank(b.dataset.status);
        if (ra !== rb) return ra - rb;
        return Number(b.dataset.carId) - Number(a.dataset.carId);
      });
      sorted.forEach((card) => grid.appendChild(card));
    }
  }

  // Filters
  const filterInputs = document.querySelectorAll('[data-filter]');
  const cards = document.querySelectorAll('#activeInventoryGrid .car-card');
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
        ? Object.entries(active).every(([key, value]) =>
            key === 'search' ? card.dataset.search.includes(value) : card.dataset[key] === value)
        : true;
      card.hidden = !visible;
      if (visible) shown++;
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
  // VEHICLE DETAILS MODAL (#specsModal)
  // =========================================================================
  const modal = document.getElementById('specsModal');
  const modalBody = document.getElementById('modalContentBody');
  const modalTitle = document.getElementById('modalCarTitle');
  const modalCloseBtn = document.getElementById('modalCloseBtn');

  function openDetails(car) {
    if (!modal || !modalBody || !car) return;
    activeDetailCarId = car.id;

    const images = car.all_images && car.all_images.length ? car.all_images : ['car-1.jpg'];
    const labelsMap = car.photo_labels_map || {};
    let photoIndex = 0;
    const hasMultiple = images.length > 1;
    const meta = statusMeta(car.status);

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

    let statusBannerHtml = '';
    if (meta.key === 'RESERVED') {
      statusBannerHtml = `
        <div class="modal-hold-banner is-other">
          <div>${car.reserve_expires_label ? `Currently reserved until <strong>${escapeHtml(car.reserve_expires_label)}</strong>.` : 'This vehicle is currently reserved.'}</div>
        </div>
      `;
    }

    const isMaintenance = meta.key === 'MAINTENANCE';
    const hasCustomDeposit = car.effective_deposit !== null && car.effective_deposit !== undefined && car.effective_deposit !== '';
    const depositText = isMaintenance && !hasCustomDeposit
      ? 'N/A'
      : formatPeso(hasCustomDeposit ? car.effective_deposit : settings.defaultDeposit);
    const depositHtml = !car.is_sold
      ? `<div class="modal-rate-chip">Reservation Deposit: <strong>${depositText}</strong></div>`
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
          <h4 class="modal-section-heading">Reservation &amp; Viewing Policy</h4>
          <p class="modal-policy-text">${escapeHtml(car.effective_policy || settings.defaultPolicy)}</p>
        </section>

        ${car.is_sold ? '' : `
          <div class="modal-actions-bar">
            <button type="button" class="btn-modal-messenger" id="modalMessengerAction">
              <svg viewBox="0 0 28 28" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M14 2C7.373 2 2 6.945 2 13.045c0 3.477 1.745 6.577 4.473 8.577V26l4.24-2.327c1.045.291 2.155.455 3.287.455 6.627 0 12-4.945 12-11.045S20.627 2 14 2zm1.2 14.882l-3.073-3.282-6 3.282 6.6-7.009 3.145 3.282 5.928-3.282-6.6 7.009z"/></svg>
              <span>Inquire on Messenger</span>
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

    const messengerBtn = document.getElementById('modalMessengerAction');
    if (messengerBtn) messengerBtn.addEventListener('click', () => inquire(car));

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
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
      else closeDetails();
    }
  });

  // Initial sync & direct ?car=<id> link support
  apiCall('GET').then(() => {
    const params = new URLSearchParams(window.location.search);
    const carParam = parseInt(params.get('car') || '', 10);
    if (carParam) {
      const target = getCarById(carParam);
      if (target) openDetails(target);
    }
  }).catch(() => {});
});
