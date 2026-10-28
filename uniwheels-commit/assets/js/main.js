/**
 * UniWheels Motors - Certified Pre-Owned & Trade-In Specialists
 * Accessible Frontend Script with Messenger Integration, Slideshows & High HCI UX
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. DATA INITIALIZATION
  const featuredCars = window.UNIWHEELS_FEATURED_CARS || [];
  const catalogCars = window.UNIWHEELS_CATALOG_CARS || [];
  const themeBaseUrl = window.THEME_BASE_URL || '.';

  let currentFeaturedIndex = 0;
  let heroSlideshowTimer = null;
  let heroPhotoIndex = 0;

  // 2. FEATURED SHOWCASE NAVIGATION & AUTOMATIC SLIDESHOW (Item 1 & Item 9)
  const showcaseImg = document.getElementById('showcaseImg');
  const showcaseCarName = document.getElementById('showcaseCarName');
  const showcasePriceValue = document.getElementById('showcasePriceValue');
  const showcaseStockId = document.getElementById('showcaseStockId');
  const showcaseStatusTag = document.getElementById('showcaseStatusTag');
  const showcasePhotoDots = document.getElementById('showcasePhotoDots');
  const currentCarIdxEl = document.getElementById('currentCarIdx');
  const specTrans = document.getElementById('specTrans');
  const specMileage = document.getElementById('specMileage');
  const specFuel = document.getElementById('specFuel');
  const specSeats = document.getElementById('specSeats');
  const showcaseMessengerBtn = document.getElementById('showcaseMessengerBtn');
  const showcaseDetailsBtn = document.getElementById('showcaseDetailsBtn');
  const showcasePrevBtn = document.getElementById('showcasePrevBtn');
  const showcaseNextBtn = document.getElementById('showcaseNextBtn');

  function updateHeroDots(total) {
    if (!showcasePhotoDots) return;
    const dots = showcasePhotoDots.querySelectorAll('.showcase-photo-dot');
    dots.forEach((d, i) => {
      d.classList.toggle('active', i === heroPhotoIndex);
    });
  }

  function updateShowcase(index) {
    if (!featuredCars.length) return;
    
    // Clear any active hero slideshow timer
    if (heroSlideshowTimer) {
      clearInterval(heroSlideshowTimer);
      heroSlideshowTimer = null;
    }

    // Bounds check
    if (index < 0) currentFeaturedIndex = featuredCars.length - 1;
    else if (index >= featuredCars.length) currentFeaturedIndex = 0;
    else currentFeaturedIndex = index;

    const car = featuredCars[currentFeaturedIndex];
    if (!car) return;

    if (currentCarIdxEl) currentCarIdxEl.textContent = (currentFeaturedIndex + 1);

    // Get all photos for this vehicle
    const images = (car.all_images && car.all_images.length) ? car.all_images : [car.image_file || 'car-1.jpg'];
    heroPhotoIndex = 0;

    if (showcaseImg) {
      showcaseImg.style.opacity = '0.4';
      setTimeout(() => {
        showcaseImg.src = `${themeBaseUrl}/assets/images/cars/${images[0]}`;
        showcaseImg.alt = car.name || 'Pre-owned car';
        showcaseImg.style.opacity = '1';
      }, 120);
    }

    // Render interactive dot indicators
    if (showcasePhotoDots) {
      showcasePhotoDots.innerHTML = '';
      if (images.length > 1) {
        images.forEach((img, i) => {
          const dot = document.createElement('span');
          dot.className = `showcase-photo-dot ${i === 0 ? 'active' : ''}`;
          dot.setAttribute('title', `View photo ${i + 1} of ${images.length}`);
          dot.onclick = () => {
            heroPhotoIndex = i;
            if (showcaseImg) showcaseImg.src = `${themeBaseUrl}/assets/images/cars/${images[heroPhotoIndex]}`;
            updateHeroDots(images.length);
          };
          showcasePhotoDots.appendChild(dot);
        });
      }
    }

    // Requirement 9: Automatic slideshow in featured cars for this specific car
    if (images.length > 1) {
      heroSlideshowTimer = setInterval(() => {
        heroPhotoIndex = (heroPhotoIndex + 1) % images.length;
        if (showcaseImg) {
          showcaseImg.style.opacity = '0.7';
          setTimeout(() => {
            showcaseImg.src = `${themeBaseUrl}/assets/images/cars/${images[heroPhotoIndex]}`;
            showcaseImg.style.opacity = '1';
          }, 100);
        }
        updateHeroDots(images.length);
      }, 3400);
    }

    if (showcaseCarName) showcaseCarName.textContent = car.name || '';
    
    const price = parseFloat(car.price) || 0;
    if (showcasePriceValue) {
      showcasePriceValue.textContent = price > 0 ? ('₱' + price.toLocaleString()) : 'Inquire for Price';
    }

    // Stock ID in the top right corner
    const stockId = car.stock_id || ('UW-0' + (car.id || 1));
    if (showcaseStockId) showcaseStockId.textContent = '#' + stockId.toUpperCase();

    // Status badge at top left synchronized with database
    if (showcaseStatusTag) {
      const status = (car.status || 'AVAILABLE').toUpperCase();
      showcaseStatusTag.textContent = status;
      showcaseStatusTag.className = 'card-status-badge badge-' + status.toLowerCase();
    }

    if (specTrans) specTrans.textContent = car.transmission || 'Automatic';
    if (specMileage) specMileage.textContent = car.mileage || 'Low Mileage';
    if (specFuel) specFuel.textContent = car.fuel_type || 'Gasoline';
    if (specSeats) specSeats.textContent = car.seating || '5 Seats';

    // Buttons
    if (showcaseMessengerBtn) {
      showcaseMessengerBtn.onclick = () => inquireMessengerLead(car);
    }
    if (showcaseDetailsBtn) {
      showcaseDetailsBtn.onclick = () => openCarDetailsModal(car);
    }
  }

  if (showcasePrevBtn) {
    showcasePrevBtn.addEventListener('click', () => {
      updateShowcase(currentFeaturedIndex - 1);
    });
  }

  if (showcaseNextBtn) {
    showcaseNextBtn.addEventListener('click', () => {
      updateShowcase(currentFeaturedIndex + 1);
    });
  }

  // Initial showcase call
  if (featuredCars.length > 0) {
    updateShowcase(0);
  }

  // 3. ONE-CLICK MESSENGER INQUIRY (Top CTA)
  window.inquireMessengerLead = function(car) {
    if (!car) return;
    const carName = car.name || 'Vehicle';
    const stockId = car.stock_id || ('UW-' + (car.id || '01'));
    const price = parseFloat(car.price) || 0;
    const priceText = price > 0 ? ('₱' + price.toLocaleString()) : 'your listed price';
    
    const message = `Hello UniWheels! I am interested in inquiring about the ${carName} (Stock #${stockId}) listed for ${priceText}. Is this vehicle still available for showroom viewing or test drive? Thank you!`;
    const messengerUrl = `https://m.me/uniwheels?text=` + encodeURIComponent(message);
    window.open(messengerUrl, '_blank');
  };

  // 4. LIVE INVENTORY FILTERING & SEARCH
  const searchInput = document.getElementById('catalogSearchInput');
  const brandSelect = document.getElementById('filterBrand');
  const budgetSelect = document.getElementById('filterBudget');
  const bodySelect = document.getElementById('filterBodyType');
  const transSelect = document.getElementById('filterTransmission');
  const carCards = document.querySelectorAll('.car-card');
  const countBadge = document.getElementById('filterResultCount');
  const noResultsBox = document.getElementById('noCatalogResults');

  function applyFilters() {
    const query = (searchInput?.value || '').toLowerCase().trim();
    const brandVal = (brandSelect?.value || 'all').toLowerCase();
    const budgetVal = budgetSelect?.value || 'all';
    const bodyVal = (bodySelect?.value || 'all').toLowerCase();
    const transVal = (transSelect?.value || 'all').toLowerCase();

    let visibleCount = 0;

    carCards.forEach(card => {
      const cardName = card.getAttribute('data-name') || '';
      const cardStock = card.getAttribute('data-stock') || '';
      const cardBrand = card.getAttribute('data-brand') || '';
      const cardBody = card.getAttribute('data-body') || '';
      const cardTrans = card.getAttribute('data-trans') || '';
      const cardPrice = parseFloat(card.getAttribute('data-price') || 0);

      // Search match
      const matchesSearch = !query || cardName.includes(query) || cardStock.includes(query) || cardBrand.includes(query);

      // Brand match
      const matchesBrand = (brandVal === 'all') || (cardBrand === brandVal);

      // Body match
      const matchesBody = (bodyVal === 'all') || (cardBody.includes(bodyVal));

      // Transmission match
      const matchesTrans = (transVal === 'all') || (cardTrans.includes(transVal));

      // Budget match
      let matchesBudget = true;
      if (budgetVal === 'under-500k') {
        matchesBudget = cardPrice > 0 && cardPrice < 500000;
      } else if (budgetVal === '500k-800k') {
        matchesBudget = cardPrice >= 500000 && cardPrice <= 800000;
      } else if (budgetVal === '800k-1m') {
        matchesBudget = cardPrice > 800000 && cardPrice <= 1000000;
      } else if (budgetVal === 'over-1m') {
        matchesBudget = cardPrice > 1000000;
      }

      if (matchesSearch && matchesBrand && matchesBody && matchesTrans && matchesBudget) {
        card.style.display = '';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });

    if (countBadge) {
      countBadge.textContent = `Showing ${visibleCount} Available Vehicle${visibleCount === 1 ? '' : 's'}`;
    }

    if (noResultsBox) {
      noResultsBox.style.display = (visibleCount === 0) ? 'block' : 'none';
    }
  }

  if (searchInput) searchInput.addEventListener('input', applyFilters);
  if (brandSelect) brandSelect.addEventListener('change', applyFilters);
  if (budgetSelect) budgetSelect.addEventListener('change', applyFilters);
  if (bodySelect) bodySelect.addEventListener('change', applyFilters);
  if (transSelect) transSelect.addEventListener('change', applyFilters);

  window.resetAllCatalogFilters = function() {
    if (searchInput) searchInput.value = '';
    if (brandSelect) brandSelect.value = 'all';
    if (budgetSelect) budgetSelect.value = 'all';
    if (bodySelect) bodySelect.value = 'all';
    if (transSelect) transSelect.value = 'all';
    applyFilters();
  };

  // 5. REQUIREMENT 10: HOVER SLIDESHOW IN AVAILABLE INVENTORY
  function initInventoryHoverSlideshow() {
    carCards.forEach(card => {
      const img = card.querySelector('.car-card-photo');
      const imagesData = card.getAttribute('data-images');
      if (!img || !imagesData) return;

      let images = [];
      try {
        images = JSON.parse(imagesData);
      } catch (e) {
        images = [];
      }

      if (!Array.isArray(images) || images.length <= 1) return;

      let hoverTimer = null;
      let hoverIndex = 0;

      card.addEventListener('mouseenter', () => {
        if (hoverTimer) clearInterval(hoverTimer);
        hoverIndex = 0;
        hoverTimer = setInterval(() => {
          hoverIndex = (hoverIndex + 1) % images.length;
          img.src = `${themeBaseUrl}/assets/images/cars/${images[hoverIndex]}`;
        }, 1100);
      });

      card.addEventListener('mouseleave', () => {
        if (hoverTimer) {
          clearInterval(hoverTimer);
          hoverTimer = null;
        }
        img.src = `${themeBaseUrl}/assets/images/cars/${images[0]}`;
      });
    });
  }
  initInventoryHoverSlideshow();

  // 6. CAR DETAILS MODAL (Requirements 3 & 11)
  // - Status removed from top-left corner
  // - Schedule showroom visit button removed
  // - Manual arrows (< and >) for browsing photos with NO automatic slideshow
  const specsModal = document.getElementById('specsModal');
  const modalCloseBtn = document.getElementById('modalCloseBtn');
  const modalBody = document.getElementById('modalContentBody');
  const modalTitle = document.getElementById('modalCarTitle');

  let modalImagesList = [];
  let modalActiveImageIndex = 0;

  window.openCarDetailsModal = function(car) {
    if (!specsModal || !modalBody) return;

    const price = parseFloat(car.price) || 0;
    const priceStr = price > 0 ? ('₱' + price.toLocaleString()) : 'Inquire for Price';
    
    modalImagesList = (car.all_images && car.all_images.length) ? car.all_images : [car.image_file || 'car-1.jpg'];
    modalActiveImageIndex = 0;

    if (modalTitle) modalTitle.textContent = car.name || 'Vehicle Specifications';

    const hasMultiple = modalImagesList.length > 1;

    modalBody.innerHTML = `
      <div class="modal-car-layout">
        <!-- Photo Container: Manual Arrows, No Top-Left Status Badge -->
        <div class="modal-car-img-wrap">
          <img id="modalMainImg" src="${themeBaseUrl}/assets/images/cars/${modalImagesList[0]}" alt="${car.name}" class="modal-photo" onerror="this.src='${themeBaseUrl}/assets/images/cars/car-1.jpg'">
          
          ${hasMultiple ? `
            <button type="button" class="modal-arrow-btn modal-arrow-prev" id="modalArrowPrev" aria-label="Previous image">&lsaquo;</button>
            <button type="button" class="modal-arrow-btn modal-arrow-next" id="modalArrowNext" aria-label="Next image">&rsaquo;</button>
            <div class="modal-img-counter" id="modalImgCounter">1 / ${modalImagesList.length}</div>
          ` : ''}

          <div class="modal-price-tag">${priceStr}</div>
        </div>

        <!-- Specifications (Clean & Verified) -->
        <div class="modal-specs-table">
          <div class="modal-row"><span class="modal-k">Stock ID:</span><span class="modal-v">${car.stock_id || 'UW-' + car.id}</span></div>
          <div class="modal-row"><span class="modal-k">Brand / Model:</span><span class="modal-v">${car.brand || ''} ${car.name || ''}</span></div>
          <div class="modal-row"><span class="modal-k">Transmission:</span><span class="modal-v">${car.transmission || 'Automatic'}</span></div>
          <div class="modal-row"><span class="modal-k">Mileage:</span><span class="modal-v">${car.mileage || 'Low Mileage'}</span></div>
          <div class="modal-row"><span class="modal-k">Fuel Type:</span><span class="modal-v">${car.fuel_type || 'Gasoline'}</span></div>
          <div class="modal-row"><span class="modal-k">Seating Capacity:</span><span class="modal-v">${car.seating || '5 Seats'}</span></div>
          <div class="modal-row"><span class="modal-k">Exterior Color:</span><span class="modal-v">${car.finish || 'Clean Finish'}</span></div>
        </div>

        <!-- Actions: Inquire on Messenger Only (Schedule showroom visit button removed) -->
        <div class="modal-actions-bar">
          <button type="button" class="btn-modal-messenger" id="modalMessengerAction">
            <svg viewBox="0 0 28 28" width="20" height="20" fill="currentColor">
              <path d="M14 2C7.373 2 2 6.945 2 13.045c0 3.477 1.745 6.577 4.473 8.577V26l4.24-2.327c1.045.291 2.155.455 3.287.455 6.627 0 12-4.945 12-11.045S20.627 2 14 2zm1.2 14.882l-3.073-3.282-6 3.282 6.6-7.009 3.145 3.282 5.928-3.282-6.6 7.009z"/>
            </svg>
            <span>Inquire on Messenger</span>
          </button>
        </div>
      </div>
    `;

    const messengerBtn = document.getElementById('modalMessengerAction');
    if (messengerBtn) {
      messengerBtn.onclick = () => inquireMessengerLead(car);
    }

    // Requirement 11: Manual Navigation Arrows without Automatic Slideshow
    if (hasMultiple) {
      const prevBtn = document.getElementById('modalArrowPrev');
      const nextBtn = document.getElementById('modalArrowNext');
      const mainImg = document.getElementById('modalMainImg');
      const counter = document.getElementById('modalImgCounter');

      function showModalPhoto(index) {
        modalActiveImageIndex = index;
        if (mainImg) {
          mainImg.src = `${themeBaseUrl}/assets/images/cars/${modalImagesList[modalActiveImageIndex]}`;
        }
        if (counter) {
          counter.textContent = `${modalActiveImageIndex + 1} / ${modalImagesList.length}`;
        }
      }

      if (prevBtn) {
        prevBtn.onclick = (e) => {
          e.stopPropagation();
          const targetIdx = (modalActiveImageIndex - 1 + modalImagesList.length) % modalImagesList.length;
          showModalPhoto(targetIdx);
        };
      }

      if (nextBtn) {
        nextBtn.onclick = (e) => {
          e.stopPropagation();
          const targetIdx = (modalActiveImageIndex + 1) % modalImagesList.length;
          showModalPhoto(targetIdx);
        };
      }
    }

    specsModal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  };

  window.closeCarDetailsModal = function() {
    if (specsModal) {
      specsModal.style.display = 'none';
      document.body.style.overflow = '';
    }
  };

  if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeCarDetailsModal);
  if (specsModal) {
    specsModal.addEventListener('click', (e) => {
      if (e.target === specsModal) closeCarDetailsModal();
    });
  }

});
