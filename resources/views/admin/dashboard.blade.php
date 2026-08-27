@extends('admin.layouts.admin-layout')

@section('title', 'Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('dist-dashboard/assets/vendor/libs/apex-charts/apex-charts.css') }}" />
@endpush

@section('content')
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold mb-0">Dashboard</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}" class="text-body">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item active">Overview</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics cards -->
    <div class="row">
        <div class="col-sm-6 col-xl-3 mb-6">
            <div class="card h-100 stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar avatar-xl flex-shrink-0 me-3">
                        <span class="avatar-initial rounded-circle bg-gradient-primary text-white">
                            <i class="icon-base bx bx-user-pin bx-lg"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <span class="fw-semibold d-block mb-1 text-body-secondary">Total Visitors</span>
                        <h4 class="card-title mb-1">{{ number_format($totalVisitors) }}</h4>
                        <small class="d-inline-flex align-items-center text-success">
                            <i class="icon-base bx bx-trending-up me-1"></i>Semua waktu
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3 mb-6">
            <div class="card h-100 stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar avatar-xl flex-shrink-0 me-3">
                        <span class="avatar-initial rounded-circle bg-info text-white">
                            <i class="icon-base bx bx-envelope bx-lg"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <span class="fw-semibold d-block mb-1 text-body-secondary">Messages</span>
                        <h4 class="card-title mb-1">{{ number_format($totalMessages) }}</h4>
                        <small class="d-inline-flex align-items-center text-body-secondary">
                            <i class="icon-base bx bx-envelope-open me-1"></i>Diterima
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3 mb-6">
            <div class="card h-100 stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar avatar-xl flex-shrink-0 me-3">
                        <span class="avatar-initial rounded-circle bg-gradient-danger text-white">
                            <i class="icon-base bx bx-message-square-dots bx-lg"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <span class="fw-semibold d-block mb-1 text-body-secondary">Unread</span>
                        <h4 class="card-title mb-1">{{ number_format($unreadMessages) }}</h4>
                        <small class="d-inline-flex align-items-center text-danger">
                            <i class="icon-base bx bx-bell me-1"></i>{{ $unreadMessages > 0 ? $unreadMessages.' belum dibaca' : 'Semua sudah dibaca' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3 mb-6">
            <div class="card h-100 stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar avatar-xl flex-shrink-0 me-3">
                        <span class="avatar-initial rounded-circle bg-gradient-success text-white">
                            <i class="icon-base bx bx-images bx-lg"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <span class="fw-semibold d-block mb-1 text-body-secondary">Gallery Photos</span>
                        <h4 class="card-title mb-1">{{ number_format($galleryCount) }}</h4>
                        <small class="d-inline-flex align-items-center text-body-secondary">
                            <i class="icon-base bx bx-photo-album me-1"></i>Foto
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts + Recent activities -->
    <div class="row">
        <div class="col-xxl-8 mb-6">
            <div class="card h-100 card-hoverable">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title mb-0">
                        <h5 class="m-0 me-2">Visitor Trends</h5>
                        <small class="text-body-secondary">30 hari terakhir</small>
                    </div>
                </div>
                <div class="card-body">
                    <div id="visitorTrendChart"></div>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 mb-6">
            <div class="card h-100 card-hoverable">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Recent Messages</h5>
                    <span class="badge bg-label-primary">{{ $unreadMessages }} unread</span>
                </div>
                <div class="card-body">
                    <ul class="p-0 m-0 list-activity">
                        @forelse ($recentMessages as $message)
                            <li class="d-flex align-items-start mb-4">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded-circle {{ $message->is_read ? 'bg-label-secondary' : 'bg-gradient-primary text-white' }}">
                                        <i class="icon-base bx bx-user"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">{{ $message->name }}</h6>
                                        <small class="text-body-secondary text-nowrap ms-2">{{ $message->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="text-truncate mb-0 text-body-secondary">{{ $message->subject }}</p>
                                </div>
                                @if (! $message->is_read)
                                    <span class="badge rounded-pill bg-primary ms-2 mt-1"></span>
                                @endif
                            </li>
                        @empty
                            <li class="text-center text-body-secondary py-4">Belum ada pesan.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick actions -->
    <div class="row">
        <div class="col-12 mb-6">
            <div class="card card-hoverable">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6 col-lg-3">
                            <a href="{{ route('admin.hobbies.create') }}" class="quick-action d-flex align-items-center gap-3">
                                <span class="avatar avatar-lg">
                                    <span class="avatar-initial rounded-circle  text-white">
                                        <i class="icon-base bx bx-heart bx-lg"></i>
                                    </span>
                                </span>
                                <div>
                                    <h6 class="mb-0">Add Hobby</h6>
                                    <small class="text-body-secondary">Tambah hobi baru</small>
                                </div>
                                <i class="icon-base bx bx-chevron-right ms-auto text-body-secondary"></i>
                            </a>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <a href="{{ route('admin.messages.index') }}" class="quick-action d-flex align-items-center gap-3">
                                <span class="avatar avatar-lg">
                                    <span class="avatar-initial rounded-circle bg-gradient-info text-white">
                                        <i class="icon-base bx bx-envelope bx-lg"></i>
                                    </span>
                                </span>
                                <div>
                                    <h6 class="mb-0">Messages</h6>
                                    <small class="text-body-secondary">Lihat pesan masuk</small>
                                </div>
                                <i class="icon-base bx bx-chevron-right ms-auto text-body-secondary"></i>
                            </a>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <a href="javascript:void(0);" class="quick-action d-flex align-items-center gap-3">
                                <span class="avatar avatar-lg">
                                    <span class="avatar-initial rounded-circle bg-gradient-success text-white">
                                        <i class="icon-base bx bx-images bx-lg"></i>
                                    </span>
                                </span>
                                <div>
                                    <h6 class="mb-0">Gallery</h6>
                                    <small class="text-body-secondary">Kelola foto galeri</small>
                                </div>
                                <i class="icon-base bx bx-chevron-right ms-auto text-body-secondary"></i>
                            </a>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <a href="{{route('admin.educations.index')}}" class="quick-action d-flex align-items-center gap-3">
                                <span class="avatar avatar-lg">
                                    <span class="avatar-initial rounded-circle bg-gradient-warning text-white">
                                        <i class="icon-base bx bxs-graduation bx-lg"></i>
                                    </span>
                                </span>
                                <div>
                                    <h6 class="mb-0">Educations</h6>
                                    <small class="text-body-secondary">Kelola pendidikan</small>
                                </div>
                                <i class="icon-base bx bx-chevron-right ms-auto text-body-secondary"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('dist-dashboard/assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const trend = @json($visitorTrend);

        if (document.getElementById('visitorTrendChart')) {
          new ApexCharts(document.querySelector('#visitorTrendChart'), {
            chart: { type: 'area', height: 300, toolbar: { show: false } },
            series: [{
              name: 'Visitors',
              data: trend.map((row) => row.count),
            }],
            xaxis: {
              categories: trend.map((row) => row.date),
            },
            colors: [window.config.colors.primary],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth' },
            fill: {
              type: 'gradient',
              gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.5,
                opacityTo: 0.1,
              },
            },
          }).render();
        }
      });
    </script>
@endpush
