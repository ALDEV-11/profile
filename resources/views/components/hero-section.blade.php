<section class="hero-section" id="hero">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-text-content">
                    <h1 class="hero-title">
                        Hi, I'm <span class="text-gradient">{{ $fullName }}</span>
                    </h1>
                    <p class="hero-tagline">
                        <span id="typedTagline" data-primary="{{ $tagline }}"></span><span class="typing-cursor">|</span>
                    </p>
                    <p class="hero-description">{{ Str::words($aboutMe, 25) }}</p>

                    <div class="hero-cta-group">
                        <a href="#about" class="btn btn-primary scroll-link">
                            Explore About Me <i class="bx bx-right-arrow-alt"></i>
                        </a>
                        <a href="#contact" class="btn btn-outline scroll-link">
                            Get In Touch
                        </a>
                    </div>
                </div>

                <div class="hero-image-wrapper">
                    <div class="hero-avatar-frame">
                        <img src="{{ $profileImage }}" alt="{{ $fullName }}" class="hero-avatar-img" fetchpriority="high" decoding="async" width="300" height="300" />
                    </div>
                </div>
            </div>
        </div>
    </section>