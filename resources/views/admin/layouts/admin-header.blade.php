<nav
  class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
  id="layout-navbar">
  <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
      <i class="icon-base bx bx-menu icon-md"></i>
    </a>
  </div>

  <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
    <ul class="navbar-nav flex-row align-items-center ms-md-auto">

      <!-- Welcome message -->
      <li class="nav-item me-4 d-none d-md-flex">
        <span class="navbar-text fw-medium text-body d-flex align-items-center gap-2">
          <span class="avatar avatar-sm me-1">
            <span class="avatar-initial rounded-circle bg-label-primary">
              <i class="icon-base bx bx-wink-smile"></i>
            </span>
          </span>
          Welcome back, <span class="badge bg-primary text-black ms-1">{{ $adminName ?? auth()->user()?->name }}</span>
        </span>
      </li>

      <!-- Shortcuts -->
      <li class="nav-item navbar-dropdown dropdown-shortcuts dropdown me-4">
        <a
          class="nav-link dropdown-toggle hide-arrow p-0"
          href="javascript:void(0);"
          data-bs-toggle="dropdown"
          data-bs-auto-close="outside"
          aria-expanded="false">
          <i class="icon-base bx bx-grid-alt icon-lg"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-end p-0">
          <div class="dropdown-menu-header py-3">
            <div class="dropdown-header d-flex align-items-center py-0">
              <h6 class="text-body mb-0">Shortcuts</h6>
              <span class="badge bg-label-primary ms-auto">8</span>
            </div>
          </div>
          <div class="dropdown-shortcuts-list py-2">
            <div class="row row-bordered g-0">
              <div class="col-4 dropdown-shortcuts-item">
                <span class="dropdown-shortcuts-icon rounded-circle">
                  <i class="icon-base bx bx-user-pin bx-lg"></i>
                </span>
                <a href="javascript:void(0);" class="stretched-link">Profile</a>
              </div>
              <div class="col-4 dropdown-shortcuts-item">
                <span class="dropdown-shortcuts-icon rounded-circle">
                  <i class="icon-base bx bxs-graduation bx-lg"></i>
                </span>
                <a href="javascript:void(0);" class="stretched-link">Education</a>
              </div>
              <div class="col-4 dropdown-shortcuts-item">
                <span class="dropdown-shortcuts-icon rounded-circle">
                  <i class="icon-base bx bx-images bx-lg"></i>
                </span>
                <a href="javascript:void(0);" class="stretched-link">Gallery</a>
              </div>
              <div class="col-4 dropdown-shortcuts-item">
                <span class="dropdown-shortcuts-icon rounded-circle">
                  <i class="icon-base bx bx-share-alt bx-lg"></i>
                </span>
                <a href="javascript:void(0);" class="stretched-link">Social</a>
              </div>
              <div class="col-4 dropdown-shortcuts-item">
                <span class="dropdown-shortcuts-icon rounded-circle">
                  <i class="icon-base bx bx-heart bx-lg"></i>
                </span>
                <a href="{{ route('admin.hobbies.index') }}" class="stretched-link">Hobbies</a>
              </div>
              <div class="col-4 dropdown-shortcuts-item">
                <span class="dropdown-shortcuts-icon rounded-circle">
                  <i class="icon-base bx bx-envelope bx-lg"></i>
                </span>
                <a href="{{ route('admin.messages.index') }}" class="stretched-link">Messages</a>
              </div>
              <div class="col-4 dropdown-shortcuts-item">
                <span class="dropdown-shortcuts-icon rounded-circle">
                  <i class="icon-base bx bx-line-chart bx-lg"></i>
                </span>
                <a href="javascript:void(0);" class="stretched-link">Analytics</a>
              </div>
              <div class="col-4 dropdown-shortcuts-item">
                <span class="dropdown-shortcuts-icon rounded-circle">
                  <i class="icon-base bx bx-palette bx-lg"></i>
                </span>
                <a href="javascript:void(0);" class="stretched-link">Theme</a>
              </div>
            </div>
          </div>
        </div>
      </li>
      <!-- / Shortcuts -->

      <!-- Notifications -->
      <li class="nav-item navbar-dropdown dropdown-notifications dropdown me-4">
        <a
          class="nav-link dropdown-toggle hide-arrow p-0"
          href="javascript:void(0);"
          data-bs-toggle="dropdown"
          data-bs-auto-close="outside"
          aria-expanded="false">
          <i class="icon-base bx bx-bell icon-lg"></i>
          <span id="notification-badge" class="badge bg-danger rounded-pill badge-notification {{ $unreadMessages > 0 ? '' : 'd-none' }}">
            {{ min($unreadMessages, 99) }}
          </span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-lg p-0">
          <li class="dropdown-menu-header">
            <div class="dropdown-header d-flex align-items-center py-3">
              <h6 class="text-body mb-0 me-auto">Notifikasi</h6>
              <span class="badge bg-label-primary">{{ $unreadMessages }} baru</span>
            </div>
          </li>
          <li class="scrollable-container">
            <ul class="list-unstyled mb-0">
              @forelse ($recentMessages as $message)
                <li class="dropdown-item dropdown-notifications-item border-bottom py-2 px-0">
                  <a href="{{ route('admin.messages.show', $message) }}" class="d-flex w-100 align-items-center p-2 text-body">
                    <div class="flex-shrink-0 me-3">
                      <div class="avatar avatar-sm">
                        <span class="avatar-initial rounded-circle bg-label-{{ $message->is_read ? 'secondary' : 'primary' }}">
                          <i class="icon-base bx bx-user"></i>
                        </span>
                      </div>
                    </div>
                    <div class="flex-grow-1">
                      <h6 class="small mb-0">{{ $message->name }}</h6>
                      <small class="text-body-secondary text-truncate d-block">{{ $message->subject }}</small>
                      <small class="text-body-secondary">{{ $message->created_at->diffForHumans() }}</small>
                    </div>
                    @if (! $message->is_read)
                      <span class="badge rounded-pill bg-primary ms-2"></span>
                    @endif
                  </a>
                </li>
              @empty
                <li class="dropdown-item text-center text-body-secondary py-4">Tidak ada pesan baru</li>
              @endforelse
            </ul>
          </li>
          <li class="dropdown-menu-footer border-top">
            <a href="{{ route('admin.messages.index') }}" class="dropdown-item d-flex justify-content-center p-3 text-primary">
              Lihat semua pesan
            </a>
          </li>
        </ul>
      </li>
      <!-- / Notifications -->

      <!-- User -->
      <li class="nav-item navbar-dropdown dropdown-user dropdown">
        <a
          class="nav-link dropdown-toggle hide-arrow p-0"
          href="javascript:void(0);"
          data-bs-toggle="dropdown">
          <div class="avatar avatar-online">
            <img src="{{ auth()->user()?->avatarUrl() }}" alt class="w-px-40 h-auto rounded-circle" />
          </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <a class="dropdown-item" href="javascript:void(0);">
              <div class="d-flex">
                <div class="flex-shrink-0 me-3">
                  <div class="avatar avatar-online">
                    <img src="{{ auth()->user()?->avatarUrl() }}" alt class="w-px-40 h-auto rounded-circle" />
                  </div>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-0">{{ $adminName ?? auth()->user()?->name }}</h6>
                  <small class="text-body-secondary">Admin</small>
                </div>
              </div>
            </a>
          </li>
          <li>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="dropdown-item">
                <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Log Out</span>
              </button>
            </form>
          </li>
        </ul>
      </li>
      <!--/ User -->
    </ul>
  </div>
</nav>
