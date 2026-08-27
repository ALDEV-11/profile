(function () {
  'use strict';

  // ── Back-forward cache guard ─────────────────────
  // When the browser restores this page from its bfcache (e.g. the back
  // button after logout), force a fresh request so the session check runs.
  window.addEventListener('pageshow', function (e) {
    if (e.persisted) {
      window.location.reload();
    }
  });

  document.addEventListener('DOMContentLoaded', function () {
    // ── Sidebar toggle ─────────────────────────────
    document.querySelectorAll('.layout-menu-toggle, .layout-overlay').forEach(function (toggle) {
      toggle.addEventListener('click', function (e) {
        e.preventDefault();
        if (window.Helpers && typeof window.Helpers.toggleCollapsed === 'function') {
          window.Helpers.toggleCollapsed();
        }
      });
    });

    // ── Unread count badge ─────────────────────────
    var badge = document.getElementById('notification-badge');

    function fetchUnreadCount() {
      var url = window.AdminApp && window.AdminApp.unreadUrl;
      if (!url || !badge) return;

      fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (res) { return res.ok ? res.json() : null; })
        .then(function (data) {
          if (!data || typeof data.unread !== 'number') return;
          badge.textContent = Math.min(data.unread, 99);
          badge.classList.toggle('d-none', data.unread === 0);
        })
        .catch(function () { /* ignore transient errors */ });
    }

    if (window.AdminApp) {
      window.AdminApp.refreshUnread = fetchUnreadCount;
    }

    if (badge) {
      fetchUnreadCount();
      setInterval(fetchUnreadCount, 60000);
    }

    // ── Real-time notifications (Pusher/Echo) ──────
    var broadcast = window.AdminApp && window.AdminApp.broadcast;

    function showMessageToast(data) {
      if (typeof Swal === 'undefined' || !data) return;

      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'info',
        title: 'Pesan baru dari ' + (data.name || 'pengunjung'),
        text: data.subject || '',
        showConfirmButton: false,
        timer: 6000,
        timerProgressBar: true
      });

      var popup = Swal.getPopup();
      if (popup) {
        popup.style.cursor = 'pointer';
        popup.addEventListener('click', function () {
          if (data.url) window.location.href = data.url;
        });
      }
    }

    if (broadcast && broadcast.enabled && typeof Echo !== 'undefined') {
      var echoOptions = {
        broadcaster: 'pusher',
        key: broadcast.key,
        encrypted: broadcast.scheme === 'https'
      };
      if (broadcast.cluster) echoOptions.cluster = broadcast.cluster;
      if (broadcast.host && broadcast.host.indexOf('pusher.com') === -1) {
        echoOptions.wsHost = broadcast.host;
      }
      if (broadcast.port) echoOptions.wsPort = broadcast.port;
      if (broadcast.scheme === 'https') echoOptions.forceTLS = true;

      var echo = new Echo(echoOptions);

      echo.channel('contact-messages').listen('.message.received', function (data) {
        showMessageToast(data);
        fetchUnreadCount();
      });
    }

    // ── Flash messages via SweetAlert2 ──────────────
    var flash = window.AdminApp && window.AdminApp.flash;
    if (flash && typeof Swal !== 'undefined') {
      Swal.fire({
        icon: flash.type === 'error' ? 'error' : 'success',
        title: flash.type === 'error' ? 'Oops...' : 'Berhasil!',
        text: flash.message,
        timer: 2500,
        timerProgressBar: true,
        showConfirmButton: false,
        confirmButtonColor: '#3C3489'
      });
    }
  });
})();
