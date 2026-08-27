<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand demo">
    <a href="{{ route('dashboard') }}" class="app-brand-link">
      
      <span class="app-brand-text demo menu-text fw-bold ms-2">Admin Panel</span>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
      <i class="bx bx-chevron-left d-block d-xl-none align-middle"></i>
    </a>
  </div>

  <div class="menu-divider mt-0"></div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    @php
      $current = Route::currentRouteName();
    @endphp

    <!-- Overview -->
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Umum</span>
    </li>
    <li class="menu-item {{ $current === 'dashboard' ? 'active' : '' }}">
      <a href="{{ route('dashboard') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-home"></i>
        <div class="text-truncate" data-i18n="Dashboard">Dashboard</div>
      </a>
    </li>

    <!-- Content -->
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Konten</span>
    </li>
    <li class="menu-item {{ str_starts_with($current, 'admin.profile') ? 'active' : '' }}">
      <a href="{{ route('admin.profile.edit') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-edit-alt"></i>
        <div class="text-truncate" data-i18n="Profile">Profile</div>
      </a>
    </li>
    <li class="menu-item {{ str_starts_with($current, 'admin.educations') ? 'active' : '' }}">
      <a href="{{ route('admin.educations.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bxs-graduation"></i>
        <div class="text-truncate" data-i18n="Education">Education</div>
      </a>
    </li>
    <li class="menu-item {{ str_starts_with($current, 'admin.galleries') ? 'active' : '' }}">
      <a href="{{ route('admin.galleries.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-images"></i>
        <div class="text-truncate" data-i18n="Gallery">Gallery</div>
      </a>
    </li>
    <li class="menu-item {{ str_starts_with($current, 'admin.social-medias') ? 'active' : '' }}">
      <a href="{{ route('admin.social-medias.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-share-alt"></i>
        <div class="text-truncate" data-i18n="Social Media">Social Media</div>
      </a>
    </li>
    <li class="menu-item {{ str_starts_with($current, 'admin.hobbies') ? 'active' : '' }}">
      <a href="{{ route('admin.hobbies.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-heart"></i>
        <div class="text-truncate" data-i18n="Hobbies">Hobbies</div>
      </a>
    </li>

    <!-- Pengelolaan -->
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Pengelolaan</span>
    </li>
    <li class="menu-item {{ str_starts_with($current, 'admin.messages') ? 'active' : '' }}">
      <a href="{{ route('admin.messages.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-envelope"></i>
        <div class="text-truncate" data-i18n="Contact Messages">Contact Messages</div>
        @if (($unreadMessages ?? 0) > 0)
          <span class="badge badge-dot bg-danger rounded-pill ms-auto"></span>
        @endif
      </a>
    </li>
    <li class="menu-item {{ str_starts_with($current, 'admin.analytics') ? 'active' : '' }}">
      <a href="{{ route('admin.analytics.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-chart"></i>
        <div class="text-truncate" data-i18n="Analytics">Analytics</div>
      </a>
    </li>
  </ul>

  <div class="menu-divider"></div>
</aside>
