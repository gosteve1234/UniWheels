/**
 * UniWheels Motors - homepage script
 * Handles the two card actions (Messenger inquiry, details popup) and the popup's photo arrows.
 */

document.addEventListener('DOMContentLoaded', () => {
  const cars = window.UNIWHEELS_CARS || [];
  const messengerUrl = window.UNIWHEELS_MESSENGER_URL || 'https://m.me/uniwheels';
  const imageBase = 'assets/images/cars/';

  function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (ch) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[ch]));
  }

  function priceLabel(car) {
    const price = parseFloat(car.price) || 0;
    return price > 0 ? '₱' + price.toLocaleString('en-PH') : 'Ask for price';
  }

  function inquire(car) {
    const message = `Hello UniWheels! I'm interested in the ${car.name} (${priceLabel(car)}). Is it still available for viewing or a test drive?`;
    window.open(`${messengerUrl}?text=${encodeURIComponent(message)}`, '_blank', 'noopener');
  }

  // Card buttons: one delegated listener instead of inline onclick handlers.
  document.addEventListener('click', (e) => {
    const card = e.target.closest('[data-car-index]');
    if (!card) return;
    const car = cars[Number(card.dataset.carIndex)];
    if (!car) return;

    if (e.target.closest('.js-car-inquire')) inquire(car);
    else if (e.target.closest('.js-car-details')) openDetails(car);
  });

  // Filters: options are built server-side from the cars in stock.
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
      // Sold cars are only there to show the lot is moving; hide them while searching.
      const visible = filtering
        ? card.dataset.sold !== '1' && Object.entries(active).every(([key, value]) =>
            key === 'search' ? card.dataset.search.includes(value) : card.dataset[key] === value)
        : true;
      card.hidden = !visible;
      if (visible && card.dataset.sold !== '1') shown++;
    });

    if (countEl) countEl.textContent = `${shown} car${shown === 1 ? '' : 's'} available`;
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

  // Details popup
  const modal = document.getElementById('specsModal');
  const modalBody = document.getElementById('modalContentBody');
  const modalTitle = document.getElementById('modalCarTitle');
  const modalCloseBtn = document.getElementById('modalCloseBtn');

  function openDetails(car) {
    if (!modal || !modalBody) return;

    const images = car.all_images && car.all_images.length ? car.all_images : ['car-1.jpg'];
    let photoIndex = 0;
    const hasMultiple = images.length > 1;

    const rows = [
      ['Transmission', car.transmission],
      ['Mileage', car.mileage],
      ['Fuel type', car.fuel_type],
      ['Seating', car.seating],
      ['Color', car.finish],
    ].filter(([, v]) => v)
      .map(([k, v]) => `<div class="modal-row"><span class="modal-k">${k}:</span><span class="modal-v">${escapeHtml(v)}</span></div>`)
      .join('');

    if (modalTitle) modalTitle.textContent = car.name || 'Car details';

    modalBody.innerHTML = `
      <div class="modal-car-layout">
        <div class="modal-car-img-wrap">
          <img id="modalMainImg" src="${imageBase}${escapeHtml(images[0])}" alt="${escapeHtml(car.name)}" class="modal-photo">
          ${hasMultiple ? `
            <button type="button" class="modal-arrow-btn modal-arrow-prev" data-step="-1" aria-label="Previous photo">&lsaquo;</button>
            <button type="button" class="modal-arrow-btn modal-arrow-next" data-step="1" aria-label="Next photo">&rsaquo;</button>
            <div class="modal-img-counter" id="modalImgCounter">1 / ${images.length}</div>
          ` : ''}
          <div class="modal-price-tag">${car.is_sold ? 'Sold' : escapeHtml(priceLabel(car))}</div>
        </div>
        <div class="modal-specs-table">${rows}</div>
        ${car.is_sold ? '' : `
          <div class="modal-actions-bar">
            <button type="button" class="btn-modal-messenger" id="modalMessengerAction">
              <svg viewBox="0 0 28 28" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M14 2C7.373 2 2 6.945 2 13.045c0 3.477 1.745 6.577 4.473 8.577V26l4.24-2.327c1.045.291 2.155.455 3.287.455 6.627 0 12-4.945 12-11.045S20.627 2 14 2zm1.2 14.882l-3.073-3.282-6 3.282 6.6-7.009 3.145 3.282 5.928-3.282-6.6 7.009z"/></svg>
              <span>Ask about this car</span>
            </button>
          </div>
        `}
      </div>
    `;

    const mainImg = document.getElementById('modalMainImg');
    mainImg.onerror = () => { mainImg.onerror = null; mainImg.src = imageBase + 'car-1.jpg'; };

    modalBody.querySelectorAll('.modal-arrow-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        photoIndex = (photoIndex + Number(btn.dataset.step) + images.length) % images.length;
        mainImg.src = imageBase + images[photoIndex];
        document.getElementById('modalImgCounter').textContent = `${photoIndex + 1} / ${images.length}`;
      });
    });

    const messengerBtn = document.getElementById('modalMessengerAction');
    if (messengerBtn) messengerBtn.addEventListener('click', () => inquire(car));

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }

  function closeDetails() {
    if (!modal) return;
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
    if (e.key === 'Escape') closeDetails();
  });
});
