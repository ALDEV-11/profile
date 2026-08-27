<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow" />

  <title>VERIFY OTP // ADMIN PANEL</title>
  <meta name="description" content="Two-factor authentication OTP verification" />

  <link rel="icon" type="image/png" href="{{ asset('images/aldev-favicon.png') }}" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700;800&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet" />

  <!-- Icons -->
  <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />

  <style>
    :root {
      --neo-cyan:    #7DFAFF;
      --neo-blue:    #3300FF;
      --neo-yellow:  #FFEC00;
      --neo-orange:  #FF4910;
      --neo-teal:    #1BEDCA;
      --neo-pink:    #FF01F6;
      --neo-red:     #FF2D55;
      --neo-green:   #00FF85;

      --bg-main:     #FAF8F5;
      --bg-surface:  #FFFFFF;
      --text-main:   #000000;
      --text-muted:  #1A1A1A;
      --border:      #000000;
      --bw:          3px;
      --bwl:         4px;
      --sh:          5px;
      --shl:         8px;
      --font-mono:   'Space Mono', 'JetBrains Mono', monospace;
    }

    *, *::before, *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      border-radius: 0 !important;
    }

    body {
      font-family: var(--font-mono);
      background-color: var(--bg-main);
      background-image:
        linear-gradient(rgba(0,0,0,0.07) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,0,0,0.07) 1px, transparent 1px);
      background-size: 36px 36px;
      color: var(--text-main);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* ── Marquee ──────────────────────────────── */
    .marquee {
      background: var(--text-main);
      color: var(--bg-main);
      border-bottom: var(--bwl) solid var(--border);
      overflow: hidden;
      padding: 0.55rem 0;
      flex-shrink: 0;
    }
    .marquee-track {
      display: inline-flex;
      white-space: nowrap;
      animation: marquee 22s linear infinite;
    }
    .marquee-track span {
      padding-right: 2.5rem;
      font-weight: 700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      font-size: 0.78rem;
    }
    .marquee-track b { color: var(--neo-cyan); }
    @keyframes marquee {
      from { transform: translateX(0); }
      to   { transform: translateX(-50%); }
    }

    /* ── Layout ───────────────────────────────── */
    .auth-wrap {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 3rem 1.25rem;
    }

    .auth-card {
      position: relative;
      width: 100%;
      max-width: 480px;
      background: var(--bg-surface);
      border: var(--bwl) solid var(--border);
      box-shadow: var(--shl) var(--shl) 0px var(--border);
      padding: 2.25rem 2.25rem 2rem;
      animation: cardReveal 0.55s cubic-bezier(0.22,1,0.36,1) both;
    }
    @keyframes cardReveal {
      from { opacity: 0; transform: translateY(24px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Top accent bar ───────────────────────── */
    .card-accent {
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 8px;
      background: var(--neo-cyan);
      border-bottom: var(--bw) solid var(--border);
    }

    /* ── Sticker ──────────────────────────────── */
    .sticker {
      position: absolute;
      top: -1.4rem;
      right: -1rem;
      transform: rotate(4deg);
      background: var(--neo-yellow);
      border: var(--bw) solid var(--border);
      box-shadow: 4px 4px 0 var(--border);
      padding: 0.4rem 0.85rem;
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      z-index: 2;
    }

    /* ── Brand header ─────────────────────────── */
    .brand {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 0.5rem;
      margin-bottom: 1.5rem;
      padding-bottom: 1rem;
      border-bottom: 2px dashed var(--border);
    }
    .brand-left { display: flex; align-items: center; gap: 0.75rem; }
    .brand-mark {
      width: 46px; height: 46px;
      background: var(--neo-teal);
      border: var(--bw) solid var(--border);
      box-shadow: 3px 3px 0 var(--border);
      display: grid;
      place-items: center;
      font-size: 1.4rem;
      font-weight: 800;
    }
    .brand-name {
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      font-size: 1rem;
      line-height: 1;
    }
    .brand-name small {
      display: block;
      font-size: 0.65rem;
      font-weight: 400;
      letter-spacing: 0.2em;
      color: #555;
      margin-top: 0.3rem;
    }
    .barcode {
      width: 72px; height: 28px;
      background: repeating-linear-gradient(
        90deg,
        var(--border) 0 2px, transparent 2px 5px,
        var(--border) 5px 7px, transparent 7px 12px,
        var(--border) 12px 13px, transparent 13px 16px,
        var(--border) 16px 19px, transparent 19px 23px
      );
    }

    /* ── Title ────────────────────────────────── */
    .otp-title {
      font-size: clamp(2.4rem, 9vw, 3.4rem);
      font-weight: 800;
      text-transform: uppercase;
      line-height: 0.95;
      letter-spacing: -1px;
      margin-bottom: 0.4rem;
    }
    .otp-title .hl {
      background: var(--neo-cyan);
      border: var(--bw) solid var(--border);
      box-shadow: 4px 4px 0 var(--border);
      padding: 0 0.3rem;
      display: inline-block;
    }
    .otp-sub {
      font-size: 0.8rem;
      color: #555;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      margin-bottom: 1.5rem;
    }

    /* ── Attempts badge ───────────────────────── */
    .attempts-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.3rem 0.85rem;
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      border: var(--bw) solid var(--border);
      margin-bottom: 1.25rem;
    }
    .attempts-safe   { background: var(--neo-green); color: #000; box-shadow: 3px 3px 0 var(--border); }
    .attempts-warn   { background: var(--neo-yellow); color: #000; box-shadow: 3px 3px 0 var(--border); }
    .attempts-danger { background: var(--neo-red); color: #fff; box-shadow: 3px 3px 0 var(--border); }

    /* ── Alerts ───────────────────────────────── */
    .alert {
      border: var(--bw) solid var(--border);
      padding: 0.8rem 0.95rem;
      margin-bottom: 1.25rem;
      font-size: 0.8rem;
      box-shadow: 4px 4px 0 var(--border);
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }
    .alert-error {
      background: #FFD0D0;
      animation: shakeX 0.45s ease;
    }
    .alert-success { background: var(--neo-green); color: #000; }
    @keyframes shakeX {
      0%,100% { transform: translateX(0); }
      20% { transform: translateX(-7px); }
      40% { transform: translateX(7px); }
      60% { transform: translateX(-5px); }
      80% { transform: translateX(5px); }
    }

    /* ── Timer ────────────────────────────────── */
    .otp-timer {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      background: var(--neo-yellow);
      border: var(--bw) solid var(--border);
      box-shadow: 3px 3px 0 var(--border);
      padding: 0.35rem 0.85rem;
      font-size: 0.78rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      margin-bottom: 1.5rem;
    }
    .timer-value { font-weight: 800; font-size: 0.9rem; }
    .timer-expired { color: var(--neo-red); background: #FFD0D0; }

    /* ── OTP inputs ───────────────────────────── */
    .otp-input-group {
      display: flex;
      gap: 0.75rem;
      justify-content: center;
      margin: 1.5rem 0;
    }
    .otp-digit {
      width: 3.6rem;
      height: 3.6rem;
      text-align: center;
      font-family: var(--font-mono);
      font-size: 1.6rem;
      font-weight: 800;
      color: var(--text-main);
      background: var(--bg-surface);
      border: var(--bwl) solid var(--border);
      box-shadow: 4px 4px 0 var(--border);
      outline: none;
      transition: transform 0.1s ease, box-shadow 0.1s ease, background 0.1s ease;
      caret-color: var(--neo-orange);
    }
    .otp-digit:focus {
      background: var(--neo-cyan);
      transform: translate(-2px, -2px);
      box-shadow: 6px 6px 0 var(--border);
    }
    .otp-digit.filled {
      background: var(--neo-teal);
    }
    .otp-digit.error {
      background: #FFD0D0;
      border-color: var(--neo-red);
      animation: shakeX 0.4s ease;
    }

    /* ── Buttons ──────────────────────────────── */
    .btn {
      display: block;
      width: 100%;
      text-align: center;
      font-family: var(--font-mono);
      font-size: 0.9rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      border: var(--bwl) solid var(--border);
      padding: 0.95rem 1.25rem;
      cursor: pointer;
      transition: transform 0.1s ease, box-shadow 0.1s ease;
      text-decoration: none;
    }
    .btn-verify {
      background: var(--text-main);
      color: var(--bg-main);
      box-shadow: 5px 5px 0 var(--neo-orange);
      margin-bottom: 1rem;
    }
    .btn-verify:hover:not(:disabled) {
      transform: translate(-2px, -2px);
      box-shadow: 7px 7px 0 var(--neo-orange);
    }
    .btn-verify:active:not(:disabled) {
      transform: translate(2px, 2px);
      box-shadow: 1px 1px 0 var(--neo-orange);
    }
    .btn-verify:disabled {
      opacity: 0.7;
      cursor: not-allowed;
    }
    .btn-logout {
      background: var(--bg-surface);
      color: var(--text-main);
      box-shadow: 5px 5px 0 var(--border);
      font-size: 0.78rem;
    }
    .btn-logout:hover {
      background: var(--bg-main);
      transform: translate(-2px, -2px);
      box-shadow: 7px 7px 0 var(--border);
    }

    /* Loading spinner */
    .btn-loading {
      position: relative;
      pointer-events: none;
      opacity: 0.8;
    }
    .btn-loading::after {
      content: '';
      display: inline-block;
      width: 0.8rem; height: 0.8rem;
      margin-left: 0.6rem;
      border: 2px solid var(--bg-main);
      border-top-color: var(--neo-cyan);
      border-radius: 50% !important;
      animation: spin 0.6s linear infinite;
      vertical-align: middle;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── Resend section ───────────────────────── */
    .resend-section {
      text-align: center;
      margin: 1.25rem 0;
      padding: 1rem;
      background: var(--bg-main);
      border: var(--bw) solid var(--border);
    }
    .resend-label {
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #555;
      margin-bottom: 0.6rem;
      font-weight: 700;
    }
    .resend-btn {
      background: var(--neo-pink);
      color: #000;
      border: var(--bw) solid var(--border);
      box-shadow: 3px 3px 0 var(--border);
      padding: 0.5rem 1.25rem;
      font-family: var(--font-mono);
      font-size: 0.8rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      cursor: pointer;
      transition: transform 0.1s ease, box-shadow 0.1s ease;
    }
    .resend-btn:hover:not(:disabled) {
      transform: translate(-2px,-2px);
      box-shadow: 5px 5px 0 var(--border);
    }
    .resend-btn:disabled {
      background: #ddd;
      color: #888;
      cursor: not-allowed;
      box-shadow: 2px 2px 0 var(--border);
    }
    .resend-countdown {
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      color: #555;
      margin-top: 0.5rem;
      letter-spacing: 0.06em;
    }

    /* ── Security footer ──────────────────────── */
    .security {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.45rem;
      margin-top: 1.5rem;
      padding-top: 1rem;
      border-top: 2px dashed var(--border);
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #555;
      font-weight: 700;
    }

    /* ── Footer bar ───────────────────────────── */
    footer.auth-foot {
      flex-shrink: 0;
      background: var(--text-main);
      color: var(--bg-main);
      padding: 0.9rem 1.5rem;
      display: flex;
      justify-content: space-between;
      gap: 1rem;
      flex-wrap: wrap;
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      border-top: var(--bwl) solid var(--border);
      font-weight: 700;
    }
    footer.auth-foot b { color: var(--neo-cyan); }

    @media (max-width: 480px) {
      .auth-card { padding: 1.75rem 1.25rem 1.5rem; box-shadow: 6px 6px 0 var(--border); }
      .sticker { top: -1rem; right: -0.4rem; }
      .barcode { display: none; }
      .otp-digit { width: 3rem; height: 3rem; font-size: 1.3rem; }
    }
  </style>
</head>

<body>
  {{-- Marquee strip --}}
  <div class="marquee" aria-hidden="true">
    <div class="marquee-track">
      @for ($i = 0; $i < 2; $i++)
        <span>VERIFIKASI OTP</span>
        <span><b>TWO-FACTOR AUTH</b> ▲</span>
        <span>ADMIN ACCESS</span>
        <span>▲ SECURE LOGIN</span>
      @endfor
    </div>
  </div>

  <main class="auth-wrap">
    <div class="auth-card">
      <div class="card-accent"></div>

      {{-- Sticker --}}
      <div class="sticker">&#9888; 2FA</div>

      {{-- Brand header --}}
      <div class="brand">
        <div class="brand-left">
          <div class="brand-mark"><i class="bx bx-shield-quarter"></i></div>
          <div class="brand-name">
            ADMIN
            <small>Control Panel</small>
          </div>
        </div>
        <div class="barcode"></div>
      </div>

      {{-- Title --}}
      <h1 class="otp-title">Verify<span class="hl">OTP</span></h1>
      <p class="otp-sub">Masukkan kode 4 digit yang dikirim ke email kamu</p>

      {{-- Attempts badge --}}
      @if (isset($attemptsRemaining))
        <div>
          @if ($attemptsRemaining > 2)
            <span class="attempts-badge attempts-safe">
              <i class="bx bx-check-circle"></i> {{ $attemptsRemaining }} percobaan tersisa
            </span>
          @elseif ($attemptsRemaining === 2)
            <span class="attempts-badge attempts-warn">
              <i class="bx bx-error"></i> {{ $attemptsRemaining }} percobaan tersisa
            </span>
          @else
            <span class="attempts-badge attempts-danger">
              <i class="bx bx-error-circle"></i> {{ $attemptsRemaining }} percobaan tersisa
            </span>
          @endif
        </div>
      @endif

      {{-- Errors --}}
      @if ($errors->any())
        <div class="alert alert-error" id="alert-errors">
          {{ $errors->first() }}
        </div>
      @endif

      {{-- Status --}}
      @if (session('status'))
        <div class="alert alert-success" id="alert-status">{{ session('status') }}</div>
      @endif

      {{-- Timer --}}
      @if (isset($expiresAt))
        <div class="otp-timer" id="otp-timer">
          <i class="bx bx-time-five"></i>
          Berlaku: <span class="timer-value" id="timer-value">--:--</span>
        </div>
      @endif

      {{-- OTP form --}}
      <form id="formOtp" method="POST" action="{{ route('two-factor.verify.post') }}">
        @csrf
        <input type="hidden" name="otp" id="otp-hidden" value="" />

        <div class="otp-input-group" id="otp-inputs">
          <input type="text" class="otp-digit" data-index="0" inputmode="numeric" maxlength="1" autocomplete="off" autofocus />
          <input type="text" class="otp-digit" data-index="1" inputmode="numeric" maxlength="1" autocomplete="off" />
          <input type="text" class="otp-digit" data-index="2" inputmode="numeric" maxlength="1" autocomplete="off" />
          <input type="text" class="otp-digit" data-index="3" inputmode="numeric" maxlength="1" autocomplete="off" />
        </div>

        <button class="btn btn-verify" type="submit" id="btn-verify">
          <i class="bx bx-check-double"></i>&nbsp; Verifikasi
        </button>
      </form>

      {{-- Resend OTP --}}
      <div class="resend-section">
        <p class="resend-label">Tidak menerima kode?</p>
        <form method="POST" action="{{ route('two-factor.resend') }}" id="resend-form">
          @csrf
          <button
            type="submit"
            class="resend-btn"
            id="resend-btn"
            {{ ($resendCooldown ?? 0) > 0 ? 'disabled' : '' }}>
            <i class="bx bx-send"></i>&nbsp; Kirim Ulang OTP
          </button>
        </form>
        @if (($resendCooldown ?? 0) > 0)
          <p class="resend-countdown" id="resend-countdown">
            Tunggu <span id="resend-timer">{{ $resendCooldown }}</span> detik
          </p>
        @endif
      </div>

      {{-- Logout --}}
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-logout">
          <i class="bx bx-log-out"></i>&nbsp; Keluar &amp; Login Ulang
        </button>
      </form>

      {{-- Security badge --}}
      <div class="security">
        <i class="bx bx-lock-alt"></i>
        <span>Sesi terenkripsi &amp; terlindungi</span>
      </div>
    </div>
  </main>

  <footer class="auth-foot">
    <span>&copy; {{ date('Y') }} — Personal Profile</span>
    <span>2FA <b>ACTIVE</b></span>
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const digits = document.querySelectorAll('.otp-digit');
      const hidden = document.getElementById('otp-hidden');
      const form   = document.getElementById('formOtp');
      const btnVer = document.getElementById('btn-verify');

      function updateHidden() {
        let val = '';
        digits.forEach(function (d) { val += d.value; });
        hidden.value = val;
        digits.forEach(function (d) {
          d.classList.toggle('filled', d.value.length === 1);
          d.classList.remove('error');
        });
      }

      digits.forEach(function (input, idx) {
        input.addEventListener('input', function () {
          this.value = this.value.replace(/\D/g, '').slice(0, 1);
          updateHidden();
          if (this.value && idx < digits.length - 1) digits[idx + 1].focus();
          if (hidden.value.length === 4) {
            btnVer.classList.add('btn-loading');
            btnVer.disabled = true;
            form.submit();
          }
        });

        input.addEventListener('keydown', function (e) {
          if (e.key === 'Backspace' && !this.value && idx > 0) {
            digits[idx - 1].focus();
            digits[idx - 1].value = '';
            updateHidden();
          }
          if (e.key === 'ArrowLeft' && idx > 0)  { e.preventDefault(); digits[idx - 1].focus(); }
          if (e.key === 'ArrowRight' && idx < digits.length - 1) { e.preventDefault(); digits[idx + 1].focus(); }
        });

        input.addEventListener('focus', function () { this.select(); });

        input.addEventListener('paste', function (e) {
          e.preventDefault();
          var pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 4);
          for (var i = 0; i < pasted.length && i < digits.length; i++) digits[i].value = pasted[i];
          updateHidden();
          if (pasted.length >= 4) {
            digits[3].focus();
            btnVer.classList.add('btn-loading');
            btnVer.disabled = true;
            form.submit();
          } else if (pasted.length > 0) {
            digits[Math.min(pasted.length, digits.length - 1)].focus();
          }
        });
      });

      // Show error state on inputs
      @if ($errors->any())
        digits.forEach(function (d) { d.classList.add('error'); });
      @endif

      // OTP expiry countdown
      @if (isset($expiresAt))
        var expiresAt  = new Date('{{ $expiresAt }}');
        var timerEl    = document.getElementById('timer-value');
        var timerWrap  = document.getElementById('otp-timer');

        function updateTimer() {
          var diff = Math.max(0, Math.floor((expiresAt - new Date()) / 1000));
          var mins = Math.floor(diff / 60);
          var secs = diff % 60;
          if (diff <= 0) {
            timerEl.textContent = 'KADALUARSA';
            timerWrap.classList.add('timer-expired');
            clearInterval(timerInterval);
            btnVer.disabled = true;
            btnVer.classList.add('btn-loading');
            return;
          }
          timerEl.textContent = String(mins).padStart(2,'0') + ':' + String(secs).padStart(2,'0');
        }
        updateTimer();
        var timerInterval = setInterval(updateTimer, 1000);
      @endif

      // Resend cooldown countdown
      @if (($resendCooldown ?? 0) > 0)
        var resendSecs = {{ $resendCooldown }};
        var resendTimerEl = document.getElementById('resend-timer');
        var resendBtn     = document.getElementById('resend-btn');
        var resendCdEl    = document.getElementById('resend-countdown');

        var resendInterval = setInterval(function () {
          resendSecs--;
          if (resendSecs <= 0) {
            clearInterval(resendInterval);
            resendBtn.removeAttribute('disabled');
            if (resendCdEl) resendCdEl.style.display = 'none';
          } else {
            resendTimerEl.textContent = resendSecs;
          }
        }, 1000);
      @endif

      // Loading on submit
      form.addEventListener('submit', function () {
        btnVer.classList.add('btn-loading');
        btnVer.disabled = true;
      });

      // Auto-dismiss status alert
      var statusAlert = document.getElementById('alert-status');
      if (statusAlert) {
        setTimeout(function () {
          statusAlert.style.transition = 'opacity 0.4s ease';
          statusAlert.style.opacity = '0';
          setTimeout(function () { statusAlert.remove(); }, 400);
        }, 5000);
      }
    });
  </script>
</body>
</html>
