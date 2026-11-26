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

  var listingSelect = document.getElementById('listing_type');
  var priceReqTag = document.getElementById('price-req-tag');
  var rateReqTag = document.getElementById('rate-req-tag');

  function syncListingReqUI() {
    if (!listingSelect) return;
    var mode = listingSelect.value;
    if (priceReqTag) priceReqTag.textContent = (mode === 'RENT') ? 'optional for rent-only' : 'required';
    if (rateReqTag) rateReqTag.textContent = (mode === 'SALE') ? 'optional' : 'required';
  }
  if (listingSelect) {
    listingSelect.addEventListener('change', syncListingReqUI);
    syncListingReqUI();
  }

  form.addEventListener('submit', function (e) {
    form.querySelectorAll('.field-error.js').forEach(function (el) { el.remove(); });
    var problems = [];
    var mode = listingSelect ? listingSelect.value : 'SALE';

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
    if (mode === 'SALE' || mode === 'BOTH') {
      checkPositive('price', 'Please enter the selling price.', false);
    }
    if (mode === 'RENT' || mode === 'BOTH') {
      checkPositive('rental_rate', 'Please enter the daily rental rate.', false);
    }
    checkPositive('booking_deposit', 'Please enter the booking deposit.', true);

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
    checkText('booking_policy', 'Please enter the booking & rental policy.');

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
