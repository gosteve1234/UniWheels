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

/* "Copy Facebook post": puts the ready-made caption on the clipboard to paste in Facebook. */
(function () {
  function copyText(text) {
    if (navigator.clipboard && window.isSecureContext) {
      return navigator.clipboard.writeText(text);
    }
    var box = document.createElement('textarea');
    box.value = text;
    box.style.position = 'fixed';
    box.style.opacity = '0';
    document.body.appendChild(box);
    box.select();
    var ok = document.execCommand('copy');
    box.remove();
    return ok ? Promise.resolve() : Promise.reject();
  }

  document.querySelectorAll('.js-copy-post').forEach(function (btn) {
    var label = btn.textContent;
    btn.addEventListener('click', function () {
      copyText(btn.getAttribute('data-post')).then(function () {
        btn.textContent = 'Copied! Paste it in Facebook';
        btn.classList.add('is-done');
      }, function () {
        btn.textContent = 'Could not copy. Try again';
      }).then(function () {
        setTimeout(function () {
          btn.textContent = label;
          btn.classList.remove('is-done');
        }, 4000);
      });
    });
  });
})();

/**
 * Staff pages: shrink big phone photos before upload, show previews of new photos,
 * and check the required boxes before sending so chosen photos are not lost.
 */
(function () {
  var MAX_SIDE = 1600;
  var QUALITY = 0.82;

  var input = document.getElementById('photos');
  var preview = document.getElementById('new-photos');
  var form = document.getElementById('car-form');
  if (!input || !preview || !form) return;

  var chosen = []; // File objects, already shrunk, in the order they were added

  function shrink(file) {
    return new Promise(function (resolve) {
      if (!/^image\/(jpeg|png|webp)$/.test(file.type)) return resolve(file);
      var url = URL.createObjectURL(file);
      var img = new Image();
      img.onload = function () {
        var scale = Math.min(1, MAX_SIDE / Math.max(img.naturalWidth, img.naturalHeight));
        if (scale === 1 && file.size < 600 * 1024) {
          URL.revokeObjectURL(url);
          return resolve(file);
        }
        var canvas = document.createElement('canvas');
        canvas.width = Math.round(img.naturalWidth * scale);
        canvas.height = Math.round(img.naturalHeight * scale);
        var ctx = canvas.getContext('2d');
        ctx.fillStyle = '#fff'; // PNGs with see-through parts get a white background in JPG
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
    chosen.forEach(function (f) { dt.items.add(f); });
    input.files = dt.files;

    preview.innerHTML = '';
    chosen.forEach(function (file, i) {
      var item = document.createElement('div');
      item.className = 'photo-item';
      var img = document.createElement('img');
      img.src = URL.createObjectURL(file);
      img.alt = '';
      item.appendChild(img);
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
    saveBtn.textContent = 'Getting photos ready…';
    Promise.all(files.map(shrink)).then(function (small) {
      chosen = chosen.concat(small);
      render();
      saveBtn.disabled = false;
      saveBtn.textContent = label;
    });
  });

  form.addEventListener('submit', function (e) {
    form.querySelectorAll('.field-error.js').forEach(function (el) { el.remove(); });
    var problems = [];

    [['name', 'Please type the car name.'], ['price', 'Please type the price.']].forEach(function (pair) {
      var field = document.getElementById(pair[0]);
      var empty = pair[0] === 'price' ? !(parseFloat(field.value.replace(/[^0-9.]/g, '')) > 0) : !field.value.trim();
      field.setAttribute('aria-invalid', empty ? 'true' : 'false');
      if (empty) problems.push([field, pair[1]]);
    });

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
