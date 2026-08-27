(function () {
  'use strict';

  var ICONS = window.BoxiconsList || [];

  function toArray(nodeList) {
    return Array.prototype.slice.call(nodeList);
  }

  function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function initPicker(input) {
    var modalId = input.getAttribute('data-icon-picker-target');
    if (!modalId) return;

    var modal = document.getElementById(modalId);
    if (!modal) return;

    var grid = modal.querySelector('.icon-picker-grid');
    if (!grid) return;

    var search = modal.querySelector('input[type="text"]');
    var countLabel = modal.querySelector('[data-icon-picker-count]');
    var previewId = input.getAttribute('data-icon-picker-preview');
    var preview = previewId ? document.getElementById(previewId) : null;
    var current = (input.value || '').trim().toLowerCase();

    function render(filter) {
      var q = (filter || '').trim().toLowerCase();
      var matches = q ? ICONS.filter(function (name) { return name.indexOf(q) !== -1; }) : ICONS;

      if (countLabel) {
        countLabel.textContent = matches.length + ' dari ' + ICONS.length + ' ikon';
      }

      var html = matches.map(function (name) {
        var active = name === current ? ' border-primary text-primary' : '';
        return '<button type="button" class="icon-picker-item border' + active + '" data-icon="' + name + '" title="' + name + '">' +
          '<i class="bx ' + escapeHtml(name) + '"></i><small>' + escapeHtml(name.replace(/^bx-/, '')) + '</small>' +
          '</button>';
      }).join('');

      grid.innerHTML = html || '<div class="col-12 text-center text-body-secondary py-4">Tidak ada ikon yang cocok</div>';
    }

    function select(name) {
      current = name.toLowerCase();
      input.value = current;
      input.dispatchEvent(new Event('input', { bubbles: true }));

      if (preview) {
        var icon = preview.querySelector('i.bx');
        if (icon) {
          icon.className = 'bx ' + current;
        }
      }

      if (bootstrap.Modal.getInstance(modal)) {
        bootstrap.Modal.getInstance(modal).hide();
      }

      if (search) {
        search.value = '';
        render('');
      }
    }

    // Event delegation on grid
    grid.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-icon]');
      if (btn) select(btn.getAttribute('data-icon'));
    });

    if (search) {
      var t;
      search.addEventListener('input', function () {
        clearTimeout(t);
        t = setTimeout(function () { render(search.value); }, 150);
      });
      search.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          var first = grid.querySelector('[data-icon]');
          if (first) select(first.getAttribute('data-icon'));
        }
      });
    }

    // Render once the modal opens (data may refresh each time)
    modal.addEventListener('show.bs.modal', function () {
      current = (input.value || '').trim().toLowerCase();
      if (search) search.value = '';
      render('');
    });
  }

  function init() {
    toArray(document.querySelectorAll('[data-icon-picker-target]')).forEach(initPicker);
  }

  document.addEventListener('DOMContentLoaded', init);
})();
