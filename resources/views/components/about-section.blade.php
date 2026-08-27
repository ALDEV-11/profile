@props([
    'profile' => null,
])

@php
    $aboutMe = $profile?->about_me ?? 'Passionate developer crafting modern, high-performance web applications with Laravel, Vue, and modern CSS architectures.';
    $email = $profile?->email ?? 'alex.devlin@example.com';
    $phone = $profile?->phone ?? '+1 (555) 234-5678';
    $address = $profile?->address ?? 'San Francisco, CA';
    $phoneHref = 'tel:' . preg_replace('/[^+\d]/', '', $phone);
@endphp

<section class="about-section" id="about">
    <div class="container">

        <!-- 1. Section header -->
        <div class="about-header">
            <h2 class="about-heading">ABOUT ME</h2>
            <span class="about-divider"></span>
            <p class="about-subtitle">Get to know more about me</p>
        </div>

        <!-- 2. Two-column layout: description (left) + contact cards (right) -->
        <div class="about-grid-layout">

            <!-- Left: Quote card with description -->
            <div class="about-quote-card">
                <div class="about-quote-accent"></div>
                <div class="about-quote-icon">
                    <i class="bx bxs-quote-alt-left"></i>
                </div>
                <div class="about-quote-body">
                    {!! nl2br(e($aboutMe)) !!}
                </div>
                <div class="about-quote-decoration">
                    <i class="bx bxs-quote-alt-right"></i>
                </div>
            </div>

            <!-- Right: Contact cards stacked vertically -->
            <div class="about-contact-stack">
                <a href="mailto:{{ $email }}" class="about-card" aria-label="Email {{ $email }}">
                    <span class="about-card-icon"><i class="bx bx-envelope"></i></span>
                    <span class="about-card-text">
                        <span class="about-card-label">Email</span>
                        <span class="about-card-value">{{ $email }}</span>
                    </span>
                </a>

                <a href="{{ $phoneHref }}" class="about-card" aria-label="Call {{ $phone }}">
                    <span class="about-card-icon"><i class="bx bx-phone"></i></span>
                    <span class="about-card-text">
                        <span class="about-card-label">Phone</span>
                        <span class="about-card-value">{{ $phone }}</span>
                    </span>
                </a>

                <div class="about-card">
                    <span class="about-card-icon"><i class="bx bx-map"></i></span>
                    <span class="about-card-text">
                        <span class="about-card-label">Location</span>
                        <span class="about-card-value">{{ $address }}</span>
                    </span>
                </div>
            </div>

        </div>

    </div>
</section>
