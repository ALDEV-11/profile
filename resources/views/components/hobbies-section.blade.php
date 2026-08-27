{{-- resources/views/components/hobbies-section.blade.php --}}
@props(['hobbies' => collect()])

@if($hobbies->isNotEmpty())
<section class="hobbies-section" id="hobbies">
    <div class="container">

        {{-- 1. Section header --}}
        <div class="hobbies-header">
            <h2 class="hobbies-heading">MY HOBBIES</h2>
            <span class="hobbies-divider"></span>
            <p class="hobbies-subtitle">Things I enjoy outside of coding</p>
        </div>

        {{-- 2. Hobby grid --}}
        <div class="hobbies-grid">
            @foreach($hobbies as $hobby)
            <div class="hobby-card">
                <span class="hobby-icon-box">
                    @php
                        $icon = $hobby->icon ?? '';
                        $iconClass = null;
                        if (Str::startsWith($icon, 'bx')) {
                            $iconClass = 'bx ' . preg_replace('/^bx\s+/', '', trim($icon));
                        }
                    @endphp
                    @if ($iconClass)
                        <i class="{{ $iconClass }}"></i>
                    @else
                        {{ $icon }}
                    @endif
                </span>
                <span class="hobby-name">{{ $hobby->name }}</span>
            </div>
            @endforeach
        </div>

    </div>
</section>
@endif
