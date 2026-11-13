{{-- resources/views/components/gallery-section.blade.php --}}
@props(['galleries' => collect(), 'carouselGalleries' => null])

@php
    $carouselGalleries = $carouselGalleries ?? $galleries;
@endphp

@if($carouselGalleries->isNotEmpty())
<section class="gallery-section" id="gallery">
    <div class="gallery-container">

        {{-- 1. Section Header --}}
        <div class="section-header">
            <h2 class="section-title">Gallery & Projects</h2>
            <span class="section-divider"></span>
            <p class="section-subtitle">Highlights from recent engineering projects, keynotes, and events.</p>
        </div>

        {{-- 2. Category Filters --}}
        <div class="gallery-filters" role="group" aria-label="Filter gallery">
            <button type="button" class="gallery-filter-btn is-active" data-filter="all">All</button>
            <button type="button" class="gallery-filter-btn" data-filter="project">Project</button>
            <button type="button" class="gallery-filter-btn" data-filter="event">Event</button>
            <button type="button" class="gallery-filter-btn" data-filter="random_photo">Random Photo</button>
        </div>

        {{-- 3. Grid Rapi (Maksimal 6 Item Ditampilkan) --}}
        <div class="gallery-grid" id="galleryGrid">
            @foreach($galleries->take(6) as $gallery)
            @php
                $catVal = $gallery->category instanceof \BackedEnum ? $gallery->category->value : (string)$gallery->category;
                $catLabel = $gallery->category instanceof \App\Enums\GalleryCategory ? $gallery->category->label() : Str::headline($catVal);
            @endphp
            <figure class="gallery-item" data-category="{{ $catVal }}" data-gallery-id="{{ $gallery->id }}">
                <div class="gallery-card" 
                    tabindex="0" 
                    role="button"
                    data-large="{{ $gallery->imageUrl('large') }}"
                    data-caption="{{ $gallery->caption }}"
                    data-category-label="{{ $catLabel }}"
                    data-date="{{ $gallery->created_at->format('d M Y') }}"
                    aria-label="View {{ $gallery->caption }}">
                    
                    <div class="gallery-image-container">
                        <img
                            class="gallery-image"
                            src="{{ $gallery->imageUrl('thumb') }}"
                            data-src="{{ $gallery->imageUrl('medium') }}"
                            data-srcset="{{ $gallery->srcset() }}"
                            alt="{{ $gallery->caption }}"
                            loading="lazy"
                            decoding="async"
                            sizes="(min-width: 992px) 33vw, (min-width: 576px) 50vw, 100vw"
                        />

                        <div class="gallery-overlay">
                            <span class="gallery-badge">{{ $catLabel }}</span>
                            <div class="gallery-overlay-body">
                                <h3 class="gallery-overlay-title">{{ $gallery->caption }}</h3>
                                <span class="gallery-overlay-meta">{{ $gallery->created_at->format('d M Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </figure>
            @endforeach
        </div>

        <div class="gallery-carousel-data" hidden aria-hidden="true">
            @foreach($carouselGalleries as $gallery)
                @php
                    $catVal = $gallery->category instanceof \BackedEnum ? $gallery->category->value : (string)$gallery->category;
                    $catLabel = $gallery->category instanceof \App\Enums\GalleryCategory ? $gallery->category->label() : Str::headline($catVal);
                @endphp
                <div class="gallery-carousel-item" data-gallery-id="{{ $gallery->id }}">
                    <div
                        class="gallery-card"
                        data-large="{{ $gallery->imageUrl('large') }}"
                        data-caption="{{ $gallery->caption }}"
                        data-category-label="{{ $catLabel }}"
                        data-date="{{ $gallery->created_at->format('d M Y') }}"></div>
                </div>
            @endforeach
        </div>

    </div>

    {{-- 4. Lightbox Modal --}}
    <div class="gallery-lightbox" id="galleryLightbox" hidden>
        <span class="lightbox-counter"></span>
        <button type="button" class="lightbox-btn lightbox-close" data-lightbox-close aria-label="Close">&times;</button>
        <button type="button" class="lightbox-btn lightbox-nav lightbox-prev" data-lightbox-prev aria-label="Previous">&#8249;</button>
        <div class="lightbox-stage">
            <img class="lightbox-image" src="" alt="" />
        </div>
        <button type="button" class="lightbox-btn lightbox-nav lightbox-next" data-lightbox-next aria-label="Next">&#8250;</button>
        <div class="lightbox-info">
            <h3 class="lightbox-caption"></h3>
            <p class="lightbox-meta"></p>
        </div>
    </div>
</section>
@endif