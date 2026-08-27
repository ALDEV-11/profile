<!doctype html>

<html
  lang="en"
  class="layout-menu-fixed layout-compact"
  data-template="vertical-menu-template-free">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>@yield('title', 'Dashboard') | Personal Profile</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/aldev-favicon.png') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('dist-dashboard/assets/vendor/fonts/iconify-icons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('dist-dashboard/assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('dist-dashboard/assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('dist-dashboard/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" />

    <!-- Admin customizations -->
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}" />

    @stack('styles')

    <!-- Helpers -->
    <script src="{{ asset('dist-dashboard/assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('dist-dashboard/assets/js/config.js') }}"></script>
  </head>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->
        @include('admin.layouts.admin-sidebar')
        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->
          @include('admin.layouts.admin-header')
          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->
            <div class="container-xxl flex-grow-1 container-p-y">
              @yield('content')
            </div>
            <!-- / Content -->

            <!-- Footer -->
            <footer class="content-footer footer bg-footer-theme">
              <div class="container-xxl">
                <div
                  class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
                  <div class="mb-2 mb-md-0">
                    ©
                    <script>
                      document.write(new Date().getFullYear());
                    </script>
                    Personal Profile Admin
                  </div>
                </div>
              </div>
            </footer>
            <!-- / Footer -->

            <div class="content-backdrop fade"></div>
          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <script src="{{ asset('dist-dashboard/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('dist-dashboard/assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('dist-dashboard/assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('dist-dashboard/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('dist-dashboard/assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('dist-dashboard/assets/js/main.js') }}"></script>
    <!-- / Core JS -->

    <script src="{{ asset('js/admin.js') }}"></script>
    <script src="{{ asset('js/icon-picker-data.js') }}"></script>
    <script src="{{ asset('js/icon-picker.js') }}"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @php
        $pusherOptions = config('broadcasting.connections.pusher.options', []);
        $pusherEnabled = filled(config('broadcasting.connections.pusher.key'));
        $broadcastConfig = [
            'enabled' => $pusherEnabled,
            'key' => config('broadcasting.connections.pusher.key'),
            'cluster' => $pusherOptions['cluster'] ?? null,
            'host' => $pusherOptions['host'] ?? null,
            'port' => $pusherOptions['port'] ?? null,
            'scheme' => $pusherOptions['scheme'] ?? 'https',
        ];
    @endphp

    @if ($pusherEnabled)
        <!-- Pusher + Laravel Echo (real-time notifications) -->
        <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/laravel-echo@8.0.4/dist/echo.iife.min.js"></script>
    @endif

    <script>
      window.AdminApp = window.AdminApp || {};
      window.AdminApp.unreadUrl = @json(route('admin.dashboard.unread'));
      window.AdminApp.flash = @json(session('success') ? ['type' => 'success', 'message' => session('success')] : (session('error') ? ['type' => 'error', 'message' => session('error')] : null));
      window.AdminApp.broadcast = @json($broadcastConfig);
    </script>

    @stack('scripts')
  </body>
</html>
