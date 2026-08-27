<header class="navbar">
        <div class="container navbar-container">
            <!-- Logo / Brand -->
            <a href="#hero" class="nav-brand" id="navBrand">
                <span class="nav-brand-text">{{ $fullName }}</span>
            </a>

            <!-- Desktop Menu -->
            <ul class="nav-menu">
                <li><a href="#hero" class="nav-link active">Home</a></li>
                <li><a href="#about" class="nav-link">About</a></li>
                <li><a href="#hobbies" class="nav-link">Hobbies</a></li>
                <li><a href="#education" class="nav-link">Education</a></li>
                <li><a href="#gallery" class="nav-link">Gallery</a></li>
                <li><a href="#contact" class="nav-link">Contact</a></li>
            </ul>

            <!-- Actions: Social Icons & Theme Toggle -->
            <div class="nav-actions">
                <div class="nav-socials">
                    @forelse($socialMedias as $social)
                        <a href="{{ $social->url }}" target="_blank" rel="noopener noreferrer" class="social-icon-btn" title="{{ $social->platform }}">
                            <i class="{{ $social->icon ?? 'bx bx-link' }}"></i>
                        </a>
                    @empty
                        <a href="https://github.com" target="_blank" class="social-icon-btn" title="GitHub"><i class="bx bxl-github"></i></a>
                        <a href="https://linkedin.com" target="_blank" class="social-icon-btn" title="LinkedIn"><i class="bx bxl-linkedin"></i></a>
                    @endforelse
                </div>

                <!-- Theme Toggle Button -->
                <button type="button" class="theme-toggle-btn" data-theme-toggle aria-label="Toggle Theme">
                    <i class="{{ $isDarkMode ? 'bx bx-sun' : 'bx bx-moon' }}" data-theme-toggle-icon></i>
                </button>

                <!-- Mobile Hamburger Button -->
                <button type="button" class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle Navigation">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>