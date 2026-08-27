@php
    $fullName = $profile?->full_name ?? 'Alex Devlin';
    $tagline = $profile?->tagline ?? 'Full-Stack Engineer & Creative Architect';
    $aboutMe = $profile?->about_me ?? 'Passionate developer crafting modern, high-performance web applications with Laravel, Vue, and modern CSS architectures.';
    $email = $profile?->email ?? 'alex.devlin@example.com';
    $phone = $profile?->phone ?? '+1 (555) 234-5678';
    $address = $profile?->address ?? 'San Francisco, CA';
    $profileImage = $profile?->imageUrl() ?? asset('images/profile-avatar.png');
    $isDarkMode = false;
@endphp
<!DOCTYPE html>
<html lang="en" data-theme="{{ $isDarkMode ? 'dark' : 'light' }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Aldo Satrio Wicaksono — Personal Profile</title>

    <meta name="description" content="{{ Str::limit($aboutMe, 150) }}" />
    <meta property="og:title" content="{{ $fullName }} — Personal Profile" />
    <meta property="og:description" content="{{ Str::limit($aboutMe, 150) }}" />
    <meta property="og:image" content="{{ $profileImage }}" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/aldev-favicon.png') }}" />

    <!-- Google Fonts - Space Mono & JetBrains Mono for Brutalism Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700;800&family=Space+Mono:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet" />

    <!-- Boxicons Icons -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
</head>
<body>

    <!-- STICKY NAVBAR -->
    <x-navbar :fullName="$fullName" :socialMedias="$socialMedias" :isDarkMode="$isDarkMode" />

    <!-- Mobile Drawer & Overlay -->
    <div class="mobile-nav-overlay" id="mobileNavOverlay"></div>
    <div class="mobile-nav" id="mobileNav">
        <ul class="mobile-nav-list">
            <li><a href="#hero" class="mobile-nav-link"><i class="bx bx-home-alt"></i> Home</a></li>
            <li><a href="#about" class="mobile-nav-link"><i class="bx bx-user"></i> About</a></li>
            <li><a href="#hobbies" class="mobile-nav-link"><i class="bx bx-heart"></i> Hobbies</a></li>
            <li><a href="#education" class="mobile-nav-link"><i class="bx bx-book-open"></i> Education</a></li>
            <li><a href="#gallery" class="mobile-nav-link"><i class="bx bx-images"></i> Gallery</a></li>
            <li><a href="#contact" class="mobile-nav-link"><i class="bx bx-envelope"></i> Contact</a></li>
        </ul>

        <div style="margin-top: 2rem; display: flex; gap: 0.75rem;">
            @foreach($socialMedias as $social)
                <a href="{{ $social->url }}" target="_blank" class="social-icon-btn"><i class="{{ $social->icon ?? 'bx bx-link' }}"></i></a>
            @endforeach
        </div>
    </div>

    <!-- HERO SECTION -->
    <x-hero-section :fullName="$fullName" :tagline="$tagline" :aboutMe="$aboutMe" :profileImage="$profileImage" />

    <!-- ABOUT SECTION -->
    <x-about-section :profile="$profile" />

    <!-- HOBBIES SECTION -->
    <x-hobbies-section :hobbies="$hobbies" />

    <!-- EDUCATION SECTION -->
    <x-education-timeline :educations="$educations" />

    <!-- GALLERY SECTION -->
    <x-gallery-section :galleries="$galleries" />

    <!-- CONTACT SECTION -->
    <x-contact-section :fullName="$fullName" :tagline="$tagline" :email="$email" :phone="$phone" :address="$address" />

    <!-- FOOTER -->
    <x-footer :fullName="$fullName" :tagline="$tagline" :email="$email" :phone="$phone" :address="$address" :socialMedias="$socialMedias" />

    <!-- Main JS -->
    <script src="{{ asset('js/main.js') }}"></script>
</body>
</html>