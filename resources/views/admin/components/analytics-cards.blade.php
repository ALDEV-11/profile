@php
    $deltaBadge = function (array $card): string {
        $trend = $card['trend'];
        $delta = $card['delta'];

        if ($trend === 'up') {
            return '<small class="d-inline-flex align-items-center text-success"><i class="icon-base bx bx-trending-up me-1"></i>+'.$delta.'%</small>';
        }
        if ($trend === 'down') {
            return '<small class="d-inline-flex align-items-center text-danger"><i class="icon-base bx bx-trending-down me-1"></i>'.$delta.'%</small>';
        }

        return '<small class="d-inline-flex align-items-center text-body-secondary"><i class="icon-base bx bx-minus me-1"></i>0%</small>';
    };
@endphp

<div class="row">
    <div class="col-sm-6 col-xl-4 mb-6">
        <div class="card h-100 stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="avatar avatar-xl flex-shrink-0 me-3">
                    <span class="avatar-initial rounded-circle bg-gradient-primary text-white">
                        <i class="icon-base bx bx-user-pin bx-lg"></i>
                    </span>
                </div>
                <div class="flex-grow-1">
                    <span class="fw-semibold d-block mb-1 text-body-secondary">Unique Visitors</span>
                    <h4 class="card-title mb-1" data-stat="visitors">{{ number_format($data['cards']['visitors']['value']) }}</h4>
                    <span class="d-inline-flex align-items-center gap-1">
                        <span data-delta="visitors">{!! $deltaBadge($data['cards']['visitors']) !!}</span>
                        <small class="text-body-secondary">vs periode sebelumnya</small>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-4 mb-6">
        <div class="card h-100 stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="avatar avatar-xl flex-shrink-0 me-3">
                    <span class="avatar-initial rounded-circle bg-gradient-info text-white">
                        <i class="icon-base bx bx-show bx-lg"></i>
                    </span>
                </div>
                <div class="flex-grow-1">
                    <span class="fw-semibold d-block mb-1 text-body-secondary">Page Views</span>
                    <h4 class="card-title mb-1" data-stat="pageViews">{{ number_format($data['cards']['pageViews']['value']) }}</h4>
                    <span class="d-inline-flex align-items-center gap-1">
                        <span data-delta="pageViews">{!! $deltaBadge($data['cards']['pageViews']) !!}</span>
                        <small class="text-body-secondary">vs periode sebelumnya</small>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-4 mb-6">
        <div class="card h-100 stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="avatar avatar-xl flex-shrink-0 me-3">
                    <span class="avatar-initial rounded-circle bg-gradient-success text-white">
                        <i class="icon-base bx bx-calendar-check bx-lg"></i>
                    </span>
                </div>
                <div class="flex-grow-1">
                    <span class="fw-semibold d-block mb-1 text-body-secondary">Pengunjung Hari Ini</span>
                    <h4 class="card-title mb-1" data-stat="newToday">{{ number_format($data['cards']['newToday']['value']) }}</h4>
                    <small class="d-inline-flex align-items-center text-body-secondary">
                        <i class="icon-base bx bx-time me-1"></i>Update real-time
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
