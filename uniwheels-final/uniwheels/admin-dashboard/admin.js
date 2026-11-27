/* Reminders list in the header: close it when tapping elsewhere or pressing Esc. */
(function () {
  var alerts = document.querySelector('.alerts');
  if (!alerts) return;
  document.addEventListener('click', function (e) {
    if (alerts.open && !alerts.contains(e.target)) alerts.open = false;
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') alerts.open = false;
  });
})();

/**
 * Staff car form:
 * - Compress uploaded photos client-side for fast mobile loading
 * - Let staff tag each photo (Exterior, Interior, Trunk, Dashboard, Tires, Damage Close-up)
 * - Validate all required vehicle, condition/history, and policy fields
 */
(function () {
  var MAX_SIDE = 1600;
  var QUALITY = 0.82;

  var input = document.getElementById('photos');
  var preview = document.getElementById('new-photos');
  var form = document.getElementById('car-form');
  if (!input || !preview || !form) return;

  var categories = window.UW_PHOTO_CATEGORIES || [
    'Exterior', 'Interior', 'Trunk', 'Dashboard', 'Tires', 'Damage Close-up', 'Other'
  ];

  var chosen = []; // Array of { file: File, category: string }

  function shrink(file) {
    return new Promise(function (resolve) {
      if (!/^image\/(jpeg|png|webp)$/.test(file.type)) return resolve(file);
      var url = URL.createObjectURL(file);
      var img = new Image();
      img.onload = function () {
        var scale = Math.min(1, MAX_SIDE / Math.max(img.naturalWidth, img.naturalHeight));
        if (scale === 1 && file.size < 450 * 1024) {
          URL.revokeObjectURL(url);
          return resolve(file);
        }
        var canvas = document.createElement('canvas');
        canvas.width = Math.round(img.naturalWidth * scale);
        canvas.height = Math.round(img.naturalHeight * scale);
        var ctx = canvas.getContext('2d');
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        URL.revokeObjectURL(url);
        canvas.toBlob(function (blob) {
          if (!blob || blob.size >= file.size) return resolve(file);
          var name = file.name.replace(/\.[^.]+$/, '') + '.jpg';
          resolve(new File([blob], name, { type: 'image/jpeg' }));
        }, 'image/jpeg', QUALITY);
      };
      img.onerror = function () {
        URL.revokeObjectURL(url);
        resolve(file);
      };
      img.src = url;
    });
  }

  function hasExistingPhotos() {
    return !!form.querySelector('input[name="cover"]');
  }

  function render() {
    var dt = new DataTransfer();
    chosen.forEach(function (entry) { dt.items.add(entry.file); });
    input.files = dt.files;

    preview.innerHTML = '';
    chosen.forEach(function (entry, i) {
      var item = document.createElement('div');
      item.className = 'photo-item';

      var img = document.createElement('img');
      img.src = URL.createObjectURL(entry.file);
      img.alt = '';
      item.appendChild(img);

      var catSelect = document.createElement('select');
      catSelect.name = 'new_photo_labels[' + i + ']';
      catSelect.className = 'photo-cat-select';
      catSelect.setAttribute('aria-label', 'Photo category');
      categories.forEach(function (cat) {
        var opt = document.createElement('option');
        opt.value = cat;
        opt.textContent = cat;
        if (entry.category === cat) opt.selected = true;
        catSelect.appendChild(opt);
      });
      catSelect.addEventListener('change', function () {
        entry.category = catSelect.value;
      });
      item.appendChild(catSelect);

      if (i === 0 && !hasExistingPhotos()) {
        var tag = document.createElement('span');
        tag.className = 'cover-tag';
        tag.textContent = 'Cover photo';
        item.appendChild(tag);
      }

      var remove = document.createElement('button');
      remove.type = 'button';
      remove.className = 'btn btn-light';
      remove.textContent = 'Remove';
      remove.style.marginTop = '.4rem';
      remove.addEventListener('click', function () {
        chosen.splice(i, 1);
        render();
      });
      item.appendChild(remove);
      preview.appendChild(item);
    });
  }

  input.addEventListener('change', function () {
    var files = Array.prototype.slice.call(input.files);
    if (!files.length) return render();
    var saveBtn = document.getElementById('save-btn');
    var label = saveBtn.textContent;
    saveBtn.disabled = true;
    saveBtn.textContent = 'Compressing photos…';
    Promise.all(files.map(shrink)).then(function (small) {
      small.forEach(function (file, idx) {
        var defaultCat = (chosen.length === 0 && idx === 0 && !hasExistingPhotos()) ? 'Exterior' : 'Exterior';
        chosen.push({ file: file, category: defaultCat });
      });
      render();
      saveBtn.disabled = false;
      saveBtn.textContent = label;
    });
  });

  var statusSelect = document.getElementById('status');
  var priceInput = document.getElementById('price');
  var depositInput = document.getElementById('booking_deposit');
  var priceReqStar = document.getElementById('price-req-star');
  var depositReqStar = document.getElementById('deposit-req-star');

  function syncMaintenanceFields() {
    if (!statusSelect) return;
    var isMaint = statusSelect.value === 'MAINTENANCE';
    if (priceReqStar) priceReqStar.hidden = isMaint;
    if (depositReqStar) depositReqStar.hidden = isMaint;
    if (priceInput) priceInput.required = !isMaint;
    if (depositInput) depositInput.required = !isMaint;
  }
  if (statusSelect) {
    statusSelect.addEventListener('change', syncMaintenanceFields);
    syncMaintenanceFields();
  }

  form.addEventListener('submit', function (e) {
    form.querySelectorAll('.field-error.js').forEach(function (el) { el.remove(); });
    var problems = [];
    var isMaint = statusSelect && statusSelect.value === 'MAINTENANCE';

    function checkText(id, msg) {
      var el = document.getElementById(id);
      if (!el) return;
      var empty = !el.value.trim();
      el.setAttribute('aria-invalid', empty ? 'true' : 'false');
      if (empty) problems.push([el, msg]);
    }

    function checkPositive(id, msg, allowZero) {
      var el = document.getElementById(id);
      if (!el) return;
      var raw = el.value.replace(/[^0-9.]/g, '');
      var num = parseFloat(raw);
      var bad = allowZero ? (raw === '' || isNaN(num) || num < 0) : !(num > 0);
      el.setAttribute('aria-invalid', bad ? 'true' : 'false');
      if (bad) problems.push([el, msg]);
    }

    checkText('name', 'Please type the car name.');
    if (!isMaint) {
      checkPositive('price', 'Please enter the selling price.', false);
      checkPositive('booking_deposit', 'Please enter the reservation deposit.', true);
    } else {
      if (priceInput) priceInput.setAttribute('aria-invalid', 'false');
      if (depositInput) depositInput.setAttribute('aria-invalid', 'false');
    }

    checkText('brand', 'Please enter the brand.');
    var yearEl = document.getElementById('model_year');
    if (yearEl) {
      var yr = parseInt(yearEl.value, 10);
      var maxYr = new Date().getFullYear() + 1;
      var badYr = isNaN(yr) || yr < 1950 || yr > maxYr;
      yearEl.setAttribute('aria-invalid', badYr ? 'true' : 'false');
      if (badYr) problems.push([yearEl, 'Please enter a valid 4-digit year model.']);
    }
    checkText('mileage', 'Please enter the mileage.');
    checkText('stock_id', 'Please enter the stock number.');
    checkText('finish', 'Please enter the colour.');
    checkText('ownership_history', 'Please enter the ownership history.');
    checkText('damage_report', 'Please enter the condition / damage disclosure report.');
    checkText('booking_policy', 'Please enter the reservation & viewing policy.');

    var keptPhotos = Array.prototype.filter.call(
      form.querySelectorAll('input[name="remove_photo[]"]'),
      function (box) { return !box.checked; }
    ).length;
    if (!chosen.length && !keptPhotos) {
      problems.push([input.closest('label'), 'Please add at least one photo.']);
    }

    if (problems.length) {
      e.preventDefault();
      problems.forEach(function (p) {
        var msg = document.createElement('p');
        msg.className = 'field-error js';
        msg.textContent = p[1];
        p[0].insertAdjacentElement('afterend', msg);
      });
      problems[0][0].scrollIntoView({ behavior: 'smooth', block: 'center' });
      if (problems[0][0].focus) problems[0][0].focus();
      return;
    }

    var saveBtn = document.getElementById('save-btn');
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving…';
  });
})();

/* Confirmation prompt before removing a car unit. */
(function () {
  var removeForm = document.getElementById('remove-car-form');
  var modal = document.getElementById('remove-confirm-modal');
  if (!removeForm || !modal) return;

  var cancelBtn = document.getElementById('remove-cancel-btn');
  var confirmBtn = document.getElementById('remove-confirm-btn');
  var triggerBtn = document.getElementById('remove-car-trigger');
  var confirmed = false;

  function openModal() {
    modal.hidden = false;
    if (cancelBtn) cancelBtn.focus();
  }

  function closeModal() {
    modal.hidden = true;
    if (triggerBtn) triggerBtn.focus();
  }

  removeForm.addEventListener('submit', function (e) {
    if (confirmed) return;
    e.preventDefault();
    openModal();
  });

  modal.querySelectorAll('[data-close-modal]').forEach(function (el) {
    el.addEventListener('click', closeModal);
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !modal.hidden) {
      closeModal();
    }
  });

  if (confirmBtn) {
    confirmBtn.addEventListener('click', function () {
      confirmed = true;
      confirmBtn.disabled = true;
      confirmBtn.textContent = 'Removing…';
      removeForm.submit();
    });
  }
})();

/* Staff Reserve Modal on inventory dashboard (duration, buyer name, mobile/email). */
(function () {
  var modal = document.getElementById('staff-reserve-modal');
  var form = document.getElementById('staff-reserve-form');
  if (!modal || !form) return;

  var carIdInput = document.getElementById('staff-reserve-car-id');
  var carNameEl = document.getElementById('staff-reserve-car-name');
  var depositEl = document.getElementById('staff-reserve-deposit');
  var daysInput = document.getElementById('staff-reserve-days-value');
  var customWrap = document.getElementById('staff-custom-days-wrap');
  var customInput = document.getElementById('staff-custom-days');
  var nameInput = document.getElementById('staff-reserve-name');
  var contactInput = document.getElementById('staff-reserve-contact');
  var errorEl = document.getElementById('staff-reserve-error');
  var submitBtn = document.getElementById('staff-reserve-submit');
  var pills = modal.querySelectorAll('#staff-reserve-pills .hold-pill');
  var lastTrigger = null;
  var selectedMode = '1';

  function setActivePill(val) {
    selectedMode = String(val);
    pills.forEach(function (pill) {
      pill.classList.toggle('active', pill.dataset.days === selectedMode);
    });
    if (selectedMode === 'custom') {
      customWrap.hidden = false;
      daysInput.value = customInput.value || '4';
    } else {
      customWrap.hidden = true;
      daysInput.value = selectedMode;
    }
  }

  function openReserveModal(trigger) {
    lastTrigger = trigger;
    carIdInput.value = trigger.dataset.carId || '';
    carNameEl.textContent = trigger.dataset.carName || '';
    depositEl.textContent = '₱' + (trigger.dataset.deposit || '5,000');
    nameInput.value = '';
    contactInput.value = '';
    errorEl.hidden = true;
    errorEl.textContent = '';
    submitBtn.disabled = false;
    submitBtn.textContent = 'Reserve Car';
    setActivePill('1');
    modal.hidden = false;
    nameInput.focus();
  }

  function closeReserveModal() {
    modal.hidden = true;
    if (lastTrigger && lastTrigger.focus) lastTrigger.focus();
  }

  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.js-staff-reserve-btn');
    if (btn) openReserveModal(btn);
  });

  pills.forEach(function (pill) {
    pill.addEventListener('click', function () {
      setActivePill(pill.dataset.days);
      if (pill.dataset.days === 'custom' && customInput) customInput.focus();
    });
  });

  if (customInput) {
    customInput.addEventListener('input', function () {
      if (selectedMode === 'custom') daysInput.value = customInput.value;
    });
  }

  modal.querySelectorAll('[data-close-reserve]').forEach(function (el) {
    el.addEventListener('click', closeReserveModal);
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !modal.hidden) {
      closeReserveModal();
    }
  });

  form.addEventListener('submit', function (e) {
    errorEl.hidden = true;
    var days = selectedMode === 'custom' ? parseInt(customInput.value, 10) : parseInt(selectedMode, 10);
    var maxDays = customInput ? parseInt(customInput.max, 10) || 7 : 7;
    if (isNaN(days) || days < 1 || days > maxDays) {
      e.preventDefault();
      errorEl.textContent = 'Please choose between 1 and ' + maxDays + ' days.';
      errorEl.hidden = false;
      return;
    }
    daysInput.value = String(days);
    if (!nameInput.value.trim() || !contactInput.value.trim()) {
      e.preventDefault();
      errorEl.textContent = "Please enter the buyer's name and mobile number or email.";
      errorEl.hidden = false;
      return;
    }
    submitBtn.disabled = true;
    submitBtn.textContent = 'Reserving…';
  });
})();

/* Staff Sold / Trade 3-step confirmation wizard. */
(function () {
  var modal = document.getElementById('staff-sold-modal');
  var form = document.getElementById('staff-sold-form');
  if (!modal || !form) return;

  var titleEl = document.getElementById('staff-sold-title');
  var carNameEl = document.getElementById('staff-sold-car-name');
  var step1CarEl = document.getElementById('sold-step1-car');
  var carIdInput = document.getElementById('staff-sold-car-id');
  var fromTabInput = document.getElementById('staff-sold-from-tab');
  var setPriceLabel = document.getElementById('sold-set-price-label');
  var setChoiceSub = document.getElementById('sold-choice-set-sub');

  var step1 = document.getElementById('sold-step-1');
  var step2 = document.getElementById('sold-step-2');
  var step3 = document.getElementById('sold-step-3');

  var customPriceWrap = document.getElementById('sold-custom-price-wrap');
  var customPriceInput = document.getElementById('staff-sold-custom-price');
  var tradeWrap = document.getElementById('sold-trade-wrap');
  var tradeNameInput = document.getElementById('staff-trade-car-name');
  var tradeStockInput = document.getElementById('staff-trade-stock-id');
  var errEl = document.getElementById('staff-sold-error');
  var summaryBox = document.getElementById('sold-summary-box');
  var finalSubmitBtn = document.getElementById('sold-final-submit');

  var lastTrigger = null;
  var currentCarName = '';
  var currentSetPrice = 0;

  function formatPeso(n) {
    var num = parseFloat(n) || 0;
    return num > 0 ? '₱' + Math.round(num).toLocaleString('en-PH') : 'No price set';
  }

  function escapeHtml(val) {
    return String(val || '').replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function getSelectedMode() {
    var checked = form.querySelector('input[name="sale_mode"]:checked');
    return checked ? checked.value : 'set_price';
  }

  function syncModeUI() {
    var mode = getSelectedMode();
    modal.querySelectorAll('.sold-choice-card').forEach(function (card) {
      var radio = card.querySelector('input[type="radio"]');
      card.classList.toggle('is-selected', !!(radio && radio.checked));
    });
    customPriceWrap.hidden = mode !== 'custom_price';
    tradeWrap.hidden = mode !== 'trade';
    errEl.hidden = true;
  }

  function showStep(num) {
    step1.hidden = num !== 1;
    step2.hidden = num !== 2;
    step3.hidden = num !== 3;
    if (num === 1) {
      titleEl.textContent = 'Was this unit sold?';
    } else if (num === 2) {
      titleEl.textContent = 'Selling Price or Trade';
    } else {
      titleEl.textContent = 'Final Confirmation';
    }
  }

  function openSoldModal(trigger) {
    lastTrigger = trigger;
    currentCarName = trigger.dataset.carName || 'this unit';
    currentSetPrice = parseFloat(trigger.dataset.carPrice || '0') || 0;

    carIdInput.value = trigger.dataset.carId || '';
    if (trigger.dataset.fromTab) fromTabInput.value = trigger.dataset.fromTab;

    carNameEl.textContent = currentCarName + (currentSetPrice > 0 ? ' · ' + formatPeso(currentSetPrice) : '');
    step1CarEl.textContent = currentCarName;
    setPriceLabel.textContent = formatPeso(currentSetPrice);
    setChoiceSub.textContent = currentSetPrice > 0 ? 'Bought at ' + formatPeso(currentSetPrice) : 'Keep current selling price';

    var firstRadio = form.querySelector('input[name="sale_mode"][value="set_price"]');
    if (firstRadio) firstRadio.checked = true;
    customPriceInput.value = '';
    tradeNameInput.value = '';
    tradeStockInput.value = '';
    errEl.hidden = true;
    finalSubmitBtn.disabled = false;
    finalSubmitBtn.textContent = 'Confirm';

    syncModeUI();
    showStep(1);
    modal.hidden = false;
    var yesBtn = document.getElementById('sold-step1-yes');
    if (yesBtn) yesBtn.focus();
  }

  function closeSoldModal() {
    modal.hidden = true;
    if (lastTrigger && lastTrigger.focus) lastTrigger.focus();
  }

  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.js-staff-sold-btn');
    if (btn) openSoldModal(btn);
  });

  form.querySelectorAll('input[name="sale_mode"]').forEach(function (radio) {
    radio.addEventListener('change', syncModeUI);
  });

  document.getElementById('sold-step1-yes').addEventListener('click', function () {
    showStep(2);
  });
  document.getElementById('sold-step2-back').addEventListener('click', function () {
    showStep(1);
  });
  document.getElementById('sold-step3-back').addEventListener('click', function () {
    showStep(2);
  });

  document.getElementById('sold-step2-next').addEventListener('click', function () {
    errEl.hidden = true;
    var mode = getSelectedMode();
    var rows = [['Unit', escapeHtml(currentCarName)]];

    if (mode === 'set_price') {
      rows.push(['Status', 'Sold (Set selling price)']);
      rows.push(['Selling Price', escapeHtml(formatPeso(currentSetPrice))]);
      finalSubmitBtn.textContent = 'Confirm Sold';
    } else if (mode === 'custom_price') {
      var raw = customPriceInput.value.replace(/[^0-9.]/g, '');
      var amt = parseFloat(raw);
      if (!raw || isNaN(amt) || amt <= 0) {
        errEl.textContent = 'Please enter the actual selling price.';
        errEl.hidden = false;
        customPriceInput.focus();
        return;
      }
      rows.push(['Status', 'Sold (Different price)']);
      rows.push(['Final Selling Price', escapeHtml(formatPeso(amt))]);
      finalSubmitBtn.textContent = 'Confirm Sold';
    } else if (mode === 'trade') {
      var tName = tradeNameInput.value.trim();
      var tStock = tradeStockInput.value.trim();
      if (!tName || !tStock) {
        errEl.textContent = 'Please enter both the traded car name and stock number.';
        errEl.hidden = false;
        if (!tName) tradeNameInput.focus();
        else tradeStockInput.focus();
        return;
      }
      rows.push(['Destination', 'Trade section']);
      rows.push(['Traded With', escapeHtml(tName)]);
      rows.push(['Stock Number', escapeHtml(tStock)]);
      finalSubmitBtn.textContent = 'Confirm Trade';
    }

    summaryBox.innerHTML = rows.map(function (r) {
      return '<div class="sold-summary-row"><span>' + r[0] + '</span><strong>' + r[1] + '</strong></div>';
    }).join('');
    showStep(3);
    finalSubmitBtn.focus();
  });

  modal.querySelectorAll('[data-close-sold]').forEach(function (el) {
    el.addEventListener('click', closeSoldModal);
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !modal.hidden) {
      closeSoldModal();
    }
  });

  form.addEventListener('submit', function () {
    finalSubmitBtn.disabled = true;
    finalSubmitBtn.textContent = 'Saving…';
  });
})();
