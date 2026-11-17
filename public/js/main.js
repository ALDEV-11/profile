/**
 * Personal Profile Homepage Interactivity
 * Features:
 *  - Dynamic Light / Dark Theme Switching & Persistence
 *  - Mobile Hamburger Drawer Toggle
 *  - Smooth Scroll Navigation with Sticky Header Offset
 *  - ScrollSpy Section Highlighting
 */

document.addEventListener('DOMContentLoaded', () => {
  // Flag JS availability so CSS can opt into hidden/reveal states
  document.documentElement.classList.add('js');

  // Elements
  const htmlElement = document.documentElement;
  const themeToggleBtns = document.querySelectorAll('[data-theme-toggle]');
  const themeToggleIcons = document.querySelectorAll('[data-theme-toggle-icon]');
  const hamburgerBtn = document.getElementById('hamburgerBtn');
  const mobileNav = document.getElementById('mobileNav');
  const mobileNavOverlay = document.getElementById('mobileNavOverlay');
  const navLinks = document.querySelectorAll('.nav-link, .mobile-nav-link, .scroll-link');
  const sections = document.querySelectorAll('section[id]');
  const navbarEl = document.querySelector('.navbar');

  /* ==========================================================================
     1. THEME SWITCHING (LIGHT / DARK)
     ========================================================================== */
  function initTheme() {
    const savedTheme = localStorage.getItem('profile_theme');
    
    if (savedTheme) {
      setTheme(savedTheme);
    } else {
      // Respect default setting rendered from database or system preference
      const currentTheme = htmlElement.getAttribute('data-theme') || 'light';
      setTheme(currentTheme);
    }
  }

  function setTheme(theme) {
    if (theme === 'dark') {
      htmlElement.setAttribute('data-theme', 'dark');
      themeToggleIcons.forEach(icon => {
        icon.className = 'bx bx-sun';
      });
    } else {
      htmlElement.setAttribute('data-theme', 'light');
      themeToggleIcons.forEach(icon => {
        icon.className = 'bx bx-moon';
      });
    }
    localStorage.setItem('profile_theme', theme);
  }

  themeToggleBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const activeTheme = htmlElement.getAttribute('data-theme');
      const newTheme = activeTheme === 'dark' ? 'light' : 'dark';
      setTheme(newTheme);
    });
  });

  initTheme();

  /* ==========================================================================
     2. HAMBURGER MENU TOGGLE
     ========================================================================== */
  function toggleMobileNav(isOpen) {
    if (isOpen) {
      hamburgerBtn?.classList.add('is-active');
      mobileNav?.classList.add('is-open');
      mobileNavOverlay?.classList.add('is-open');
      document.body.style.overflow = 'hidden';
    } else {
      hamburgerBtn?.classList.remove('is-active');
      mobileNav?.classList.remove('is-open');
      mobileNavOverlay?.classList.remove('is-open');
      document.body.style.overflow = '';
    }
  }

  if (hamburgerBtn) {
    hamburgerBtn.addEventListener('click', () => {
      const isOpen = mobileNav?.classList.contains('is-open');
      toggleMobileNav(!isOpen);
    });
  }

  if (mobileNavOverlay) {
    mobileNavOverlay.addEventListener('click', () => {
      toggleMobileNav(false);
    });
  }

  /* ==========================================================================
     3. SMOOTH SCROLLING WITH OFFSET
     ========================================================================== */
  navLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      const href = link.getAttribute('href');

      if (href && href.startsWith('#')) {
        e.preventDefault();
        const targetSection = document.querySelector(href);

        if (targetSection) {
          // Close mobile menu if open
          toggleMobileNav(false);

          const headerOffset = 80;
          const elementPosition = targetSection.getBoundingClientRect().top;
          const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

          window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
          });
        }
      }
    });
  });

  /* ==========================================================================
     4. SCROLLSPY (ACTIVE NAV LINK HIGHLIGHT)
     ========================================================================== */
  function highlightActiveNav() {
    const scrollY = window.pageYOffset;

    sections.forEach(current => {
      const sectionHeight = current.offsetHeight;
      const sectionTop = current.offsetTop - 120;
      const sectionId = current.getAttribute('id');
      const correspondingLink = document.querySelector(`.nav-link[href*="${sectionId}"]`);

      if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
        correspondingLink?.classList.add('active');
      } else {
        correspondingLink?.classList.remove('active');
      }
    });
  }

  window.addEventListener('scroll', highlightActiveNav);

  /* ==========================================================================
     4b. NAVBAR TRANSPARENCY (transparent on hero, solid after)
     ========================================================================== */
  function toggleNavbarBackground() {
    if (!navbarEl) return;

    const scrollThreshold = 8;
    const shouldSolidify = window.pageYOffset > scrollThreshold;

    navbarEl.classList.toggle('is-scrolled', shouldSolidify);
  }

  toggleNavbarBackground();
  window.addEventListener('scroll', toggleNavbarBackground);

  /* ==========================================================================
     4c. HERO CURSOR-TRACKED SPOTLIGHT
     ========================================================================== */
  const heroSection = document.getElementById('hero');

  if (heroSection) {
    let hovering = false;

    heroSection.addEventListener('mousemove', (e) => {
      const rect = heroSection.getBoundingClientRect();
      const mx = ((e.clientX - rect.left) / rect.width) * 100;
      const my = ((e.clientY - rect.top) / rect.height) * 100;

      heroSection.style.setProperty('--mx', mx + '%');
      heroSection.style.setProperty('--my', my + '%');

      if (!hovering) {
        hovering = true;
        heroSection.classList.add('is-hovering');
      }
    });

    heroSection.addEventListener('mouseleave', () => {
      hovering = false;
      heroSection.classList.remove('is-hovering');
    });
  }

  /* ==========================================================================
     5. TYPEWRITER ANIMATION FOR TAGLINE
     ========================================================================== */
  const typedTaglineEl = document.getElementById('typedTagline');

  if (typedTaglineEl) {
    const primaryTagline = typedTaglineEl.getAttribute('data-primary') || 'Full-Stack Engineer & Creative Architect';
    
    // Phrases list featuring primary tagline from database & complementary skills
    const phrases = [
      primaryTagline
      // 'Laravel & Modern Web Specialist',
      // 'Passionate UI/UX Enthusiast',
      // 'Scalable System Architect'
    ];

    let phraseIndex = 0;
    let charIndex = primaryTagline.length;
    let isDeleting = true;
    const typingSpeed = 80;
    const deletingSpeed = 40;
    const pauseDuration = 3000;

    function typeEffect() {
      const currentPhrase = phrases[phraseIndex];

      if (isDeleting) {
        typedTaglineEl.textContent = currentPhrase.substring(0, charIndex - 1);
        charIndex--;
      } else {
        typedTaglineEl.textContent = currentPhrase.substring(0, charIndex + 1);
        charIndex++;
      }

      let nextSpeed = isDeleting ? deletingSpeed : typingSpeed;

      if (!isDeleting && charIndex === currentPhrase.length) {
        nextSpeed = pauseDuration;
        isDeleting = true;
      } else if (isDeleting && charIndex === 0) {
        isDeleting = false;
        phraseIndex = (phraseIndex + 1) % phrases.length;
        nextSpeed = 400;
      }

      setTimeout(typeEffect, nextSpeed);
    }

    setTimeout(typeEffect, pauseDuration);
  }

  /* ==========================================================================
     6. SCROLL REVEAL ANIMATIONS
     ========================================================================== */
  const revealElements = document.querySelectorAll('.reveal');

  if (revealElements.length) {
    if ('IntersectionObserver' in window) {
      const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
          }
        });
      }, { threshold: 0.12 });

      revealElements.forEach(el => revealObserver.observe(el));
    } else {
      revealElements.forEach(el => el.classList.add('is-visible'));
    }
  }

  /* ==========================================================================
     7. GALLERY FILTER
     ========================================================================== */
  const filterButtons = document.querySelectorAll('.gallery-filter-btn');
  const galleryItems = document.querySelectorAll('.gallery-item');

  function applyGalleryFilter(filter) {
    galleryItems.forEach(item => {
      if (item._hideTimer) {
        clearTimeout(item._hideTimer);
        item._hideTimer = null;
      }

      const matches = filter === 'all' || item.dataset.category === filter;

      if (matches) {
        item.classList.remove('is-hidden');
        item.classList.add('is-fading');
        requestAnimationFrame(() => {
          requestAnimationFrame(() => item.classList.remove('is-fading'));
        });
      } else {
        item.classList.add('is-fading');
        item._hideTimer = window.setTimeout(() => item.classList.add('is-hidden'), 260);
      }
    });
  }

  filterButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      filterButtons.forEach(b => b.classList.remove('is-active'));
      btn.classList.add('is-active');
      applyGalleryFilter(btn.dataset.filter);
    });
  });

  /* ==========================================================================
     8. GALLERY LAZY LOADING (swap srcset on scroll)
     ========================================================================== */
  const lazyImages = document.querySelectorAll('.gallery-image[data-src]');

  function loadLazyImage(img) {
    const srcset = img.dataset.srcset;
    if (srcset) {
      img.setAttribute('srcset', srcset);
    }
    img.src = img.dataset.src;
    img.addEventListener('load', () => img.classList.add('is-loaded'), { once: true });
  }

  if (lazyImages.length) {
    if ('IntersectionObserver' in window) {
      const imgObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            loadLazyImage(entry.target);
            observer.unobserve(entry.target);
          }
        });
      }, { rootMargin: '250px 0px' });

      lazyImages.forEach(img => imgObserver.observe(img));
    } else {
      lazyImages.forEach(img => {
        loadLazyImage(img);
        img.classList.add('is-loaded');
      });
    }
  }

  /* ==========================================================================
     9. CONTACT FORM (AJAX submit, validation, toast)
     ========================================================================== */
  const contactForm = document.getElementById('contactForm');

  if (contactForm) {
    const submitBtn = contactForm.querySelector('.contact-submit-btn');
    const submitLabel = contactForm.querySelector('.btn-label');

    function setLoading(isLoading) {
      if (!submitBtn) return;
      submitBtn.disabled = isLoading;
      submitBtn.classList.toggle('is-loading', isLoading);
    }

    function setFieldError(fieldName, message) {
      const input = contactForm.querySelector(`[name="${fieldName}"]`);
      const errorEl = contactForm.querySelector(`[data-error-for="${fieldName}"]`);

      input?.classList.toggle('is-invalid', Boolean(message));
      if (errorEl) {
        errorEl.textContent = message || '';
        errorEl.classList.toggle('is-visible', Boolean(message));
      }
    }

    function clearErrors() {
      ['name', 'email', 'subject', 'message'].forEach(field => setFieldError(field, ''));
    }

    function validateField(field, value) {
      const trimmed = value.trim();

      if (field === 'name') {
        if (!trimmed) return 'Please enter your name.';
        if (trimmed.length < 2) return 'Name must be at least 2 characters.';
        if (trimmed.length > 100) return 'Name may not exceed 100 characters.';
      }

      if (field === 'email') {
        if (!trimmed) return 'Please enter your email address.';
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmed)) return 'Please enter a valid email address.';
      }

      if (field === 'subject') {
        if (!trimmed) return 'Please enter a subject.';
        if (trimmed.length < 5) return 'Subject must be at least 5 characters.';
        if (trimmed.length > 100) return 'Subject may not exceed 100 characters.';
      }

      if (field === 'message') {
        if (!trimmed) return 'Please enter your message.';
        if (trimmed.length < 10) return 'Message must be at least 10 characters.';
        if (trimmed.length > 1000) return 'Message may not exceed 1000 characters.';
      }

      if (/<\/?[a-z][\s\S]*>/i.test(trimmed)) {
        return 'HTML tags are not allowed.';
      }

      return '';
    }

    contactForm.querySelectorAll('.form-control').forEach(input => {
      input.addEventListener('blur', () => {
        setFieldError(input.name, validateField(input.name, input.value));
      });

      input.addEventListener('input', () => {
        const errorEl = contactForm.querySelector(`[data-error-for="${input.name}"]`);
        if (errorEl?.classList.contains('is-visible')) {
          setFieldError(input.name, validateField(input.name, input.value));
        }
      });
    });

    contactForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      clearErrors();

      let hasErrors = false;
      ['name', 'email', 'subject', 'message'].forEach(field => {
        const input = contactForm.querySelector(`[name="${field}"]`);
        const message = validateField(field, input.value);
        if (message) {
          hasErrors = true;
          setFieldError(field, message);
        }
      });

      if (hasErrors) return;

      setLoading(true);

      try {
        const response = await fetch(contactForm.action, {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': contactForm.querySelector('input[name="_token"]').value,
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: new FormData(contactForm)
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
          if (response.status === 422 && data.errors) {
            Object.entries(data.errors).forEach(([field, messages]) => {
              setFieldError(field, Array.isArray(messages) ? messages[0] : messages);
            });
          } else if (response.status === 429) {
            const message = data.message || 'Too many messages sent. Please try again later.';
            if (typeof Swal !== 'undefined') {
              Swal.fire({
                icon: 'warning',
                title: 'Slow down!',
                text: message,
                confirmButtonColor: '#3C3489'
              });
            } else {
              alert(message);
            }
          } else {
            const message = data.message || 'Something went wrong. Please try again.';
            if (typeof Swal !== 'undefined') {
              Swal.fire({ icon: 'error', title: 'Oops...', text: message, confirmButtonColor: '#3C3489' });
            } else {
              alert(message);
            }
          }
          return;
        }

        contactForm.reset();
        clearErrors();

        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'success',
            title: 'Message Sent!',
            text: data.message || 'Thank you! Your message received.',
            confirmButtonColor: '#3C3489',
            timer: 3500,
            timerProgressBar: true,
            showConfirmButton: true
          });
        } else {
          alert(data.message || 'Thank you! Your message received.');
        }
      } catch (err) {
        if (typeof Swal !== 'undefined') {
          Swal.fire({ icon: 'error', title: 'Oops...', text: 'Network error. Please try again.', confirmButtonColor: '#3C3489' });
        } else {
          alert('Network error. Please try again.');
        }
      } finally {
        setLoading(false);
      }
    });
  }

  /* ==========================================================================
     10. FOOTER (copyright year, social links)
     ========================================================================== */
  const copyrightYearEl = document.querySelector('[data-copyright-year]');
  if (copyrightYearEl) {
    copyrightYearEl.textContent = new Date().getFullYear();
  }

  // Social media links: open in new tab with safe attributes (progressive enhancement)
  document.querySelectorAll('.footer-social-btn, .social-icon-btn').forEach(link => {
    const url = link.getAttribute('href');
    if (url && !/^https?:\/\//i.test(url)) {
      link.setAttribute('href', 'https://' + url);
    }
  });

  /* ==========================================================================
     11. GALLERY LIGHTBOX
     ========================================================================== */
  const lightbox = document.getElementById('galleryLightbox');
  const lightboxImage = lightbox?.querySelector('.lightbox-image');
  const lightboxCaption = lightbox?.querySelector('.lightbox-caption');
  const lightboxMeta = lightbox?.querySelector('.lightbox-meta');
  const lightboxCounter = lightbox?.querySelector('.lightbox-counter');
  const carouselItems = document.querySelectorAll('.gallery-carousel-item');
  let lightboxItems = [];
  let lightboxIndex = 0;

  function visibleGalleryItems() {
    return Array.from(galleryItems).filter(item => !item.classList.contains('is-hidden'));
  }

  function renderLightbox() {
    const card = lightboxItems[lightboxIndex]?.querySelector('.gallery-card');
    if (!card) return;

    lightboxImage.src = card.dataset.large;
    lightboxImage.alt = card.dataset.caption || '';
    lightboxCaption.textContent = card.dataset.caption || '';
    lightboxMeta.textContent = `${card.dataset.categoryLabel} · ${card.dataset.date}`;
    lightboxCounter.textContent = `${lightboxIndex + 1} / ${lightboxItems.length}`;
  }

  function openLightbox(item) {
    lightboxItems = Array.from(carouselItems);
    const itemId = item.dataset.galleryId;
    lightboxIndex = lightboxItems.findIndex(
      carouselItem => carouselItem.dataset.galleryId === itemId
    );
    if (lightboxIndex === -1) return;

    renderLightbox();
    lightbox.hidden = false;
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    if (!lightbox || lightbox.hidden) return;
    lightbox.hidden = true;
    document.body.style.overflow = '';
  }

  function stepLightbox(dir) {
    if (!lightboxItems.length) return;
    lightboxIndex = (lightboxIndex + dir + lightboxItems.length) % lightboxItems.length;
    renderLightbox();
  }

  if (lightbox) {
    galleryItems.forEach(item => {
      const card = item.querySelector('.gallery-card');
      card?.addEventListener('click', () => openLightbox(item));
      card?.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          openLightbox(item);
        }
      });
    });

    lightbox.querySelector('[data-lightbox-close]')?.addEventListener('click', closeLightbox);
    lightbox.querySelector('[data-lightbox-prev]')?.addEventListener('click', () => stepLightbox(-1));
    lightbox.querySelector('[data-lightbox-next]')?.addEventListener('click', () => stepLightbox(1));
    lightbox.addEventListener('click', e => {
      if (e.target === lightbox) closeLightbox();
    });

    document.addEventListener('keydown', e => {
      if (lightbox.hidden) return;
      if (e.key === 'Escape') closeLightbox();
      if (e.key === 'ArrowLeft') stepLightbox(-1);
      if (e.key === 'ArrowRight') stepLightbox(1);
    });
  }
});
