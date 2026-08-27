<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow" />

  <title>LOGIN // ADMIN PANEL</title>
  <meta name="description" content="Secure admin login with two-factor authentication" />

  <link rel="icon" type="image/png" href="{{ asset('images/aldev-favicon.png') }}" />

  <!-- Fonts: Space Mono & JetBrains Mono (Brutalism - match welcome) -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700;800&family=Space+Mono:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet" />

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
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      border-radius: 0 !important;
    }

    body {
      font-family: var(--font-mono);
      color: var(--text-main);
      background-color: var(--bg-main);
      background-image:
        linear-gradient(rgba(0,0,0,0.07) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,0,0,0.07) 1px, transparent 1px);
      background-size: 36px 36px;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      letter-spacing: -0.01em;
      -webkit-font-smoothing: antialiased;
    }

    /* ── Marquee strip ─────────────────────────── */
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

    /* ── Layout ────────────────────────────────── */
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
      max-width: 460px;
      background: var(--bg-surface);
      border: var(--bwl) solid var(--border);
      box-shadow: var(--shl) var(--shl) 0px var(--border);
      padding: 2rem 2rem 1.75rem;
      animation: cardReveal 0.55s cubic-bezier(0.22,1,0.36,1) both;
    }
    @keyframes cardReveal {
      from { opacity: 0; transform: translateY(24px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Top accent bar ────────────────────────── */
    .card-accent {
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 8px;
      background: var(--neo-cyan);
      border-bottom: var(--bw) solid var(--border);
    }

    /* ── Sticker badge ─────────────────────────── */
    .sticker {
      position: absolute;
      top: -1.5rem;
      right: -1rem;
      transform: rotate(5deg);
      background: var(--neo-yellow);
      border: 2.5px solid var(--border);
      box-shadow: 4px 4px 0 var(--border);
      padding: 0.45rem 0.85rem;
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      z-index: 2;
    }

    /* ── Brand header ──────────────────────────── */
    .brand {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem;
      margin-bottom: 1.5rem;
      padding-bottom: 1rem;
      border-bottom: 2px dashed var(--border);
    }
    .brand-left {
      display: flex;
      align-items: center;
      gap: 0.7rem;
    }
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
      width: 74px; height: 30px;
      background: repeating-linear-gradient(
        90deg,
        var(--border) 0 2px, transparent 2px 5px,
        var(--border) 5px 7px, transparent 7px 12px,
        var(--border) 12px 13px, transparent 13px 16px,
        var(--border) 16px 19px, transparent 19px 23px
      );
    }

    /* ── Title ─────────────────────────────────── */
    .login-title {
      font-size: clamp(2.6rem, 9vw, 3.8rem);
      font-weight: 800;
      text-transform: uppercase;
      line-height: 0.95;
      letter-spacing: -1px;
      margin-bottom: 0.4rem;
    }
    .login-title .hl {
      background: var(--neo-yellow);
      border: var(--bw) solid var(--border);
      box-shadow: 4px 4px 0 var(--border);
      padding: 0 0.3rem;
      display: inline-block;
    }
    .login-sub {
      font-size: 0.8rem;
      color: #555;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      margin-bottom: 1.5rem;
    }

    /* ── Alerts ────────────────────────────────── */
    .alert {
      border: var(--bw) solid var(--border);
      padding: 0.8rem 0.95rem;
      margin-bottom: 1.25rem;
      font-size: 0.8rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      box-shadow: 4px 4px 0 var(--border);
    }
    .alert-error {
      background: #FFD0D0;
      animation: shakeX 0.45s ease;
    }
    .alert-success { background: var(--neo-green); color: #000; }
    .alert ul { margin-left: 1.1rem; }
    @keyframes shakeX {
      0%,100% { transform: translateX(0); }
      20% { transform: translateX(-7px); }
      40% { transform: translateX(7px); }
      60% { transform: translateX(-5px); }
      80% { transform: translateX(5px); }
    }

    /* ── Form ──────────────────────────────────── */
    .field { margin-bottom: 1.25rem; }
    .label {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      margin-bottom: 0.45rem;
    }
    .label em {
      font-style: normal;
      color: #555;
      font-weight: 400;
    }
    .input-wrap { position: relative; }
    .input {
      width: 100%;
      font-family: var(--font-mono);
      font-size: 0.95rem;
      color: var(--text-main);
      background: #fff;
      border: 2.5px solid var(--border);
      padding: 0.8rem 2.6rem 0.8rem 0.9rem;
      box-shadow: 0 0 0 var(--border);
      transition: box-shadow 0.08s ease, transform 0.08s ease, background 0.08s ease;
      appearance: none;
    }
    .input::placeholder { color: #999; }
    .input:focus {
      outline: none;
      background: var(--neo-cyan);
      box-shadow: 4px 4px 0 var(--border);
      transform: translate(-1px, -1px);
    }
    .input-error {
      border-color: var(--neo-red);
      background: #fff5f5;
    }
    .error-text {
      display: block;
      margin-top: 0.4rem;
      font-size: 0.72rem;
      font-weight: 700;
      color: var(--neo-red);
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    .eye-btn {
      position: absolute;
      top: 50%;
      right: 0.35rem;
      transform: translateY(-50%);
      border: 2px solid var(--border);
      background: var(--bg-main);
      width: 2rem; height: 2rem;
      display: grid;
      place-items: center;
      cursor: pointer;
      color: var(--text-main);
      font-size: 1.1rem;
    }
    .eye-btn:hover { background: var(--neo-yellow); }

    .remember {
      display: flex;
      align-items: center;
      gap: 0.55rem;
      margin-bottom: 1.5rem;
      font-size: 0.78rem;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      cursor: pointer;
      user-select: none;
    }
    .remember input {
      width: 1.05rem; height: 1.05rem;
      appearance: none;
      border: 2.5px solid var(--border);
      background: #fff;
      display: grid;
      place-items: center;
      cursor: pointer;
    }
    .remember input:checked { background: var(--neo-teal); }
    .remember input:checked::after {
      content: '\2713';
      font-size: 0.8rem;
      font-weight: 700;
      line-height: 1;
    }

    /* ── Buttons ───────────────────────────────── */
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
    .btn i { vertical-align: middle; }
    .btn-login {
      background: var(--text-main);
      color: var(--bg-main);
      box-shadow: 5px 5px 0 var(--neo-orange);
      margin-bottom: 1rem;
    }
    .btn-login:hover:not(:disabled) {
      transform: translate(-2px, -2px);
      box-shadow: 7px 7px 0 var(--neo-orange);
    }
    .btn-login:active:not(:disabled) {
      transform: translate(2px, 2px);
      box-shadow: 1px 1px 0 var(--neo-orange);
    }
    .btn-home {
      background: var(--bg-surface);
      color: var(--text-main);
      box-shadow: 5px 5px 0 var(--border);
      font-size: 0.78rem;
    }
    .btn-home:hover {
      background: var(--bg-main);
      transform: translate(-2px, -2px);
      box-shadow: 7px 7px 0 var(--border);
    }
    .btn-home:active {
      transform: translate(2px, 2px);
      box-shadow: 1px 1px 0 var(--border);
    }
    .btn.loading {
      position: relative;
      pointer-events: none;
      opacity: 0.85;
    }
    .btn.loading::after {
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

    /* ── Security badge ────────────────────────── */
    .security {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.45rem;
      margin-top: 1.5rem;
      padding-top: 1rem;
      border-top: 2px dashed var(--border);
      font-size: 0.7rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #555;
    }

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
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      border-top: var(--bwl) solid var(--border);
    }
    footer.auth-foot b { color: var(--neo-cyan); }

    @media (max-width: 480px) {
      .auth-card { padding: 1.75rem 1.25rem 1.5rem; box-shadow: 8px 8px 0 var(--border); }
      .sticker { top: -1.1rem; right: -0.4rem; }
      .barcode { display: none; }
    }
  </style>
</head>

<body>
  {{-- Marquee strip --}}
  <div class="marquee" aria-hidden="true">
    <div class="marquee-track">
      @for ($i = 0; $i < 2; $i++)
        <span>ADMIN ACCESS</span>
        <span><b>TWO-FACTOR SECURED</b> ▲</span>
        <span>CONTENT MANAGEMENT</span>
        <span>▲ LOGIN REQUIRED</span>
      @endfor
    </div>
  </div>

  <main class="auth-wrap">
    <div class="auth-card">
      <div class="card-accent"></div>

      {{-- Sticker badge --}}
      <div class="sticker">&#9888; secure</div>

      {{-- Brand header --}}
      <div class="brand">
        <div class="brand-left">
          <div class="brand-mark">A</div>
          <div class="brand-name">
            ADMIN
            <small>Control Panel</small>
          </div>
        </div>
        <div class="barcode"></div>
      </div>

      {{-- Title --}}
      <h1 class="login-title">Log<span class="hl">in</span></h1>
      <p class="login-sub">Masuk untuk mengelola konten dashboard</p>

      {{-- Validation errors --}}
      @if ($errors->any())
        <div class="alert alert-error" id="alert-errors">
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- Success / status messages --}}
      @if (session('status'))
        <div class="alert alert-success" id="alert-status">{{ session('status') }}</div>
      @endif

      <form id="formAuthentication" method="POST" action="{{ route('login.attempt') }}">
        @csrf

        {{-- Email --}}
        <div class="field">
          <label class="label" for="email">
            Email <em>*</em>
          </label>
          <input
            class="input @error('email') input-error @enderror"
            type="email"
            id="email"
            name="email"
            value="{{ old('email') }}"
            placeholder="admin@example.com"
            autocomplete="email"
            autofocus />
          @error('email')
            <span class="error-text">{{ $message }}</span>
          @enderror
        </div>

        {{-- Password --}}
        <div class="field">
          <label class="label" for="password">
            Password <em>*</em>
          </label>
          <div class="input-wrap">
            <input
              class="input @error('password') input-error @enderror"
              type="password"
              id="password"
              name="password"
              placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
              autocomplete="current-password"
              aria-describedby="password" />
            <button type="button" class="eye-btn" id="toggle-password" aria-label="Tampilkan password">
              <i class="bx bx-hide"></i>
            </button>
          </div>
          @error('password')
            <span class="error-text">{{ $message }}</span>
          @enderror
        </div>

        {{-- Remember me --}}
        <label class="remember" for="remember-me">
          <input
            type="checkbox"
            id="remember-me"
            name="remember"
            {{ old('remember') ? 'checked' : '' }} />
          Remember Me
        </label>

        {{-- Submit --}}
        <button class="btn btn-login" type="submit" id="btn-login">
          <i class="bx bx-log-in"></i>&nbsp; Login
        </button>

        {{-- Back to home --}}
        <a href="{{ url('/') }}" class="btn btn-home">
          <i class="bx bx-arrow-back"></i>&nbsp; Kembali ke Beranda
        </a>
      </form>

      {{-- Security badge --}}
      <div class="security">
        <i class="bx bx-lock-alt"></i>
        <span>Dilindungi autentikasi dua faktor</span>
      </div>
    </div>
  </main>

  <footer class="auth-foot">
    <span>&copy; {{ date('Y') }} — Personal Profile</span>
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var toggle = document.getElementById('toggle-password');
      var password = document.getElementById('password');
      if (toggle && password) {
        toggle.addEventListener('click', function () {
          var show = password.type === 'password';
          password.type = show ? 'text' : 'password';
          toggle.querySelector('i').className = show ? 'bx bx-show' : 'bx bx-hide';
        });
      }

      // ── Loading state on form submit ───────────
      var form = document.getElementById('formAuthentication');
      var btnLogin = document.getElementById('btn-login');
      if (form && btnLogin) {
        form.addEventListener('submit', function () {
          btnLogin.classList.add('loading');
          btnLogin.disabled = true;
        });
      }

      // ── Auto-dismiss alerts ────────────────────
      document.querySelectorAll('.alert').forEach(function (el) {
        setTimeout(function () {
          el.style.transition = 'opacity 0.4s ease';
          el.style.opacity = '0';
          setTimeout(function () { el.remove(); }, 400);
        }, 8000);
      });
    });
  </script>
</body>
</html>
