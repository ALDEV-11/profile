<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col footer-brand-col">
                <div class="footer-brand">{{ $fullName }}</div>
                <p class="footer-description">{{ $tagline }}</p>

                <div class="footer-socials" role="list" aria-label="Social media links">
                    @foreach($socialMedias->take(5) as $social)
                        <a href="{{ $social->url }}" target="_blank" rel="noopener noreferrer" class="footer-social-btn" title="{{ $social->platform }}" role="listitem">
                            <i class="{{ $social->icon ?? 'bx bx-link' }}"></i>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="footer-col">
                <div class="footer-title">Quick Links</div>
                <ul class="footer-links">
                    <li><a href="#hero" class="footer-link">Home</a></li>
                    <li><a href="#about" class="footer-link">About</a></li>
                    <li><a href="#gallery" class="footer-link">Gallery</a></li>
                    <li><a href="#contact" class="footer-link">Contact</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <div class="footer-title">Contact</div>
                <ul class="footer-contact-list">
                    <li>
                        <a href="mailto:{{ $email }}" class="footer-contact-item">
                            <i class="bx bx-envelope"></i>
                            <span>{{ $email }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="footer-contact-item">
                            <i class="bx bx-phone"></i>
                            <span>{{ $phone }}</span>
                        </a>
                    </li>
                    <li>
                        <span class="footer-contact-item">
                            <i class="bx bx-map"></i>
                            <span>{{ $address }}</span>
                        </span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-copyright">
                &copy; <span data-copyright-year>{{ date('Y') }}</span> {{ $fullName }}. All rights reserved.
            </div>
            <div class="footer-copyright">
                Made  by <a href="https://www.instagram.com/ald_str1/#" class="footer-link" target="_blank">@ald_str1</a>
            </div>
        </div>
    </div>
</footer>
