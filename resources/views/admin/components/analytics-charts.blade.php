<div class="row">
    <div class="col-xxl-8 mb-6">
        <div class="card h-100 card-hoverable">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="card-title mb-0">
                    <h5 class="m-0 me-2">Visitor Trend</h5>
                    <small class="text-body-secondary">Pengunjung unik per hari</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-export="trend">
                    <i class="icon-base bx bx-download me-1"></i>PNG
                </button>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 300px">
                    <canvas id="visitorTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-4 mb-6">
        <div class="card h-100 card-hoverable">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="card-title mb-0">
                    <h5 class="m-0 me-2">Device Type</h5>
                    <small class="text-body-secondary">Distribusi perangkat</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-export="devices">
                    <i class="icon-base bx bx-download me-1"></i>PNG
                </button>
            </div>
            <div class="card-body">
                <div class="chart-container d-flex justify-content-center" style="height: 240px">
                    <canvas id="deviceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xxl-5 mb-6">
        <div class="card h-100 card-hoverable">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="card-title mb-0">
                    <h5 class="m-0 me-2">Traffic Sources</h5>
                    <small class="text-body-secondary">Asal referrer</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-export="referrers">
                    <i class="icon-base bx bx-download me-1"></i>PNG
                </button>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 300px">
                    <canvas id="referrerChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-7 mb-6">
        <div class="card h-100 card-hoverable">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="card-title mb-0">
                    <h5 class="m-0 me-2">Halaman Terpopuler</h5>
                    <small class="text-body-secondary">Top {{ count($data['pages']) }} halaman</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-export="csv">
                    <i class="icon-base bx bx-download me-1"></i>CSV
                </button>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Page URL</th>
                            <th class="text-end">Views</th>
                            <th class="text-end">Unique Visitors</th>
                        </tr>
                    </thead>
                    <tbody data-page-stats>
                        @forelse ($data['pages'] as $index => $page)
                            <tr>
                                <td class="text-body-secondary">{{ $index + 1 }}</td>
                                <td><code>{{ $page['page'] }}</code></td>
                                <td class="text-end fw-semibold">{{ number_format($page['views']) }}</td>
                                <td class="text-end">{{ number_format($page['unique']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-body-secondary py-4">Belum ada data kunjungan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
