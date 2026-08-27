{{-- resources/views/components/education-timeline.blade.php --}}
@props(['educations' => collect()])

@if($educations->isNotEmpty())
<section class="education-section" id="education">
    <div class="container">

        {{-- 1. Section header --}}
        <div class="section-header">
            <h2 class="section-title">Education Timeline</h2>
            <span class="section-divider"></span>
            <p class="section-subtitle">Academic journey and ongoing learning achievements.</p>
        </div>

        {{-- 2. Vertical alternating timeline --}}
        <div class="edu-timeline">
            @foreach($educations as $edu)
            <article class="edu-item reveal" data-degree="{{ $edu->degreeLevel() }}">
                {{-- Milestone node on the central axis --}}
                <span class="edu-node">
                    <i class="bx {{ $edu->degreeIcon() }}"></i>
                </span>

                {{-- Education card --}}
                <div class="edu-card">
                    <div class="edu-card-head">
                        <span class="edu-badge edu-badge--{{ $edu->degreeLevel() }}">{{ $edu->degreeShort() }}</span>

                        @if($edu->is_currently_studying)
                            <span class="edu-current"><i class="bx bx-badge-check"></i> Currently Studying</span>
                        @endif
                    </div>

                    <h3 class="edu-title">{{ $edu->institution }}</h3>

                    @if($edu->field_of_study)
                        <div class="edu-field">{{ $edu->field_of_study }}</div>
                    @endif

                    <div class="edu-date">
                        <i class="bx bx-calendar"></i>
                        {{ $edu->dateRange() }}
                    </div>
                </div>
            </article>
            @endforeach
        </div>

    </div>
</section>
@endif
