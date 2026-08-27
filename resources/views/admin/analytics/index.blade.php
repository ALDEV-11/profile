@extends('admin.layouts.admin-layout')

@section('title', 'Analytics')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0">Analytics</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}" class="text-body">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">Analytics</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Date range filter -->
    <div class="card card-hoverable mb-4 no-print">
        <div class="card-body d-flex flex-wrap gap-2 align-items-center">
            <span class="fw-semibold text-body-secondary me-1">Rentang:</span>
            <div class="btn-group" role="group" aria-label="Range">
                <button type="button" class="btn btn-sm {{ $activeRange === '7d' ? 'btn-primary' : 'btn-outline-primary' }}" data-range="7d">7 Hari</button>
                <button type="button" class="btn btn-sm {{ $activeRange === '30d' ? 'btn-primary' : 'btn-outline-primary' }}" data-range="30d">30 Hari</button>
                <button type="button" class="btn btn-sm {{ $activeRange === 'all' ? 'btn-primary' : 'btn-outline-primary' }}" data-range="all">Semua Waktu</button>
            </div>

            <div class="d-flex gap-2 align-items-center ms-lg-4">
                <input type="date" id="range-start" class="form-control form-control-sm" value="{{ request('start') }}" />
                <span class="text-body-secondary">s/d</span>
                <input type="date" id="range-end" class="form-control form-control-sm" value="{{ request('end') }}" />
                <button type="button" class="btn btn-sm btn-outline-primary" id="range-apply">Terapkan</button>
            </div>

            <button type="button" class="btn btn-sm btn-outline-secondary ms-auto" onclick="window.print()">
                <i class="icon-base bx bx-printer me-1"></i>Print / PDF
            </button>
        </div>
    </div>

    <!-- Loading overlay -->
    <div
        id="analytics-loading"
        class="d-none"
        style="position: fixed; inset: 0; background: rgba(255,255,255,.7); z-index: 1080; align-items: center; justify-content: center;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Memuat...</span>
        </div>
    </div>

    @include('admin.components.analytics-cards', ['data' => $data])
    @include('admin.components.analytics-charts', ['data' => $data])
@endsection

@push('styles')
    <style>
      @media print {
        .layout-menu,
        .layout-navbar,
        .content-footer,
        .layout-overlay,
        .content-backdrop,
        .no-print {
          display: none !important;
        }

        .layout-page {
          margin-left: 0 !important;
        }

        .content-wrapper {
          padding: 0 !important;
        }
      }

      .chart-container {
        position: relative;
        width: 100%;
      }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
      (function () {
        var initial = @json($data);
        var dataUrl = @json(route('admin.analytics.data'));
        var activeRange = @json($activeRange);

        var charts = {};

        function registerChart(id, config) {
          var canvas = document.getElementById(id);
          if (canvas) charts[id] = new Chart(canvas.getContext('2d'), config);
        }

        function initCharts(d) {
          if (typeof Chart === 'undefined') return;

          registerChart('visitorTrendChart', {
            type: 'line',
            data: {
              labels: d.trend.labels,
              datasets: [{
                label: 'Pengunjung unik',
                data: d.trend.data,
                borderColor: '#7367f0',
                backgroundColor: 'rgba(115, 103, 240, 0.15)',
                fill: true,
                tension: 0.35,
                pointRadius: 3,
                pointHoverRadius: 5,
                borderWidth: 2
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              interaction: { mode: 'index', intersect: false },
              plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: function (ctx) { return ctx.parsed.y + ' pengunjung'; } } }
              },
              scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 } }
              }
            }
          });

          registerChart('deviceChart', {
            type: 'doughnut',
            data: {
              labels: d.devices.labels,
              datasets: [{ data: d.devices.data, backgroundColor: d.devices.colors, borderWidth: 2 }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              cutout: '62%',
              plugins: {
                legend: {
                  position: 'bottom',
                  labels: {
                    generateLabels: function (chart) {
                      var data = chart.data;
                      return data.labels.map(function (label, i) {
                        var value = data.datasets[0].data[i];
                        var total = data.datasets[0].data.reduce(function (a, b) { return a + b; }, 0);
                        var pct = total > 0 ? Math.round((value / total) * 100) : 0;
                        return {
                          text: label + ' (' + value + ' / ' + pct + '%)',
                          fillStyle: data.datasets[0].backgroundColor[i],
                          strokeStyle: data.datasets[0].backgroundColor[i],
                          hidden: false,
                          index: i
                        };
                      });
                    }
                  }
                }
              }
            }
          });

          registerChart('referrerChart', {
            type: 'bar',
            data: {
              labels: d.referrers.labels,
              datasets: [{
                label: 'Pengunjung',
                data: d.referrers.data,
                backgroundColor: ['#7367f0', '#00cfe8', '#ff9f43', '#28c76f'],
                borderRadius: 4,
                barThickness: 22
              }]
            },
            options: {
              indexAxis: 'y',
              responsive: true,
              maintainAspectRatio: false,
              plugins: { legend: { display: false } },
              scales: {
                x: { beginAtZero: true, ticks: { precision: 0 } },
                y: { grid: { display: false } }
              }
            }
          });
        }

        function updateCharts(d) {
          if (!charts.visitorTrendChart) { initCharts(d); return; }
          charts.visitorTrendChart.data.labels = d.trend.labels;
          charts.visitorTrendChart.data.datasets[0].data = d.trend.data;
          charts.visitorTrendChart.update();

          charts.deviceChart.data.labels = d.devices.labels;
          charts.deviceChart.data.datasets[0].data = d.devices.data;
          charts.deviceChart.data.datasets[0].backgroundColor = d.devices.colors;
          charts.deviceChart.update();

          charts.referrerChart.data.labels = d.referrers.labels;
          charts.referrerChart.data.datasets[0].data = d.referrers.data;
          charts.referrerChart.update();
        }

        function deltaHtml(card) {
          if (card.trend === 'up') {
            return '<small class="d-inline-flex align-items-center text-success"><i class="icon-base bx bx-trending-up me-1"></i>+' + card.delta + '%</small>';
          }
          if (card.trend === 'down') {
            return '<small class="d-inline-flex align-items-center text-danger"><i class="icon-base bx bx-trending-down me-1"></i>' + card.delta + '%</small>';
          }
          return '<small class="d-inline-flex align-items-center text-body-secondary"><i class="icon-base bx bx-minus me-1"></i>0%</small>';
        }

        function renderCards(d) {
          ['visitors', 'pageViews', 'newToday'].forEach(function (key) {
            var value = document.querySelector('[data-stat="' + key + '"]');
            if (value) value.textContent = Number(d.cards[key].value).toLocaleString('id-ID');

            var delta = document.querySelector('[data-delta="' + key + '"]');
            if (delta) delta.innerHTML = deltaHtml(d.cards[key]);
          });
        }

        function renderPages(d) {
          var tbody = document.querySelector('[data-page-stats]');
          if (!tbody) return;

          if (!d.pages.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-body-secondary py-4">Belum ada data kunjungan.</td></tr>';
            return;
          }

          tbody.innerHTML = d.pages.map(function (page, i) {
            return '<tr>' +
              '<td class="text-body-secondary">' + (i + 1) + '</td>' +
              '<td><code>' + page.page.replace(/[<>&]/g, function (c) {
                return { '<': '&lt;', '>': '&gt;', '&': '&amp;' }[c];
              }) + '</code></td>' +
              '<td class="text-end fw-semibold">' + Number(page.views).toLocaleString('id-ID') + '</td>' +
              '<td class="text-end">' + Number(page.unique).toLocaleString('id-ID') + '</td>' +
              '</tr>';
          }).join('');
        }

        function setButtons(range) {
          document.querySelectorAll('[data-range]').forEach(function (btn) {
            var isActive = btn.getAttribute('data-range') === range;
            btn.classList.toggle('btn-primary', isActive);
            btn.classList.toggle('btn-outline-primary', !isActive);
          });
        }

        function loadData(params) {
          var overlay = document.getElementById('analytics-loading');
          if (overlay) overlay.classList.remove('d-none');

          var url = dataUrl + '?' + new URLSearchParams(params).toString();

          fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (res) {
              if (!res.ok) throw new Error('HTTP ' + res.status);
              return res.json();
            })
            .then(function (d) {
              renderCards(d);
              updateCharts(d);
              renderPages(d);
            })
            .catch(function () {
              if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Gagal memuat data', text: 'Terjadi kesalahan saat memuat statistik.', timer: 3000, showConfirmButton: false });
              }
            })
            .finally(function () {
              if (overlay) overlay.classList.add('d-none');
            });
        }

        function syncUrl(params) {
          if (window.history && window.history.replaceState) {
            var query = new URLSearchParams(params).toString();
            window.history.replaceState({}, '', window.location.pathname + (query ? '?' + query : ''));
          }
        }

        function rangeParams(range) {
          var params = { range: range };
          if (range === 'custom') {
            var start = document.getElementById('range-start').value;
            var end = document.getElementById('range-end').value;
            if (!start || !end) {
              Swal.fire({ icon: 'warning', title: 'Lengkapi tanggal', text: 'Pilih tanggal mulai dan selesai.', timer: 2500, showConfirmButton: false });
              return null;
            }
            params.start = start;
            params.end = end;
          }
          return params;
        }

        document.querySelectorAll('[data-range]').forEach(function (btn) {
          btn.addEventListener('click', function () {
            var range = btn.getAttribute('data-range');
            var params = rangeParams(range);
            if (!params) return;
            setButtons(range);
            activeRange = range;
            loadData(params);
            syncUrl(params);
          });
        });

        document.getElementById('range-apply').addEventListener('click', function () {
          var params = rangeParams('custom');
          if (!params) return;
          setButtons('custom');
          activeRange = 'custom';
          loadData(params);
          syncUrl(params);
        });

        document.addEventListener('DOMContentLoaded', function () {
          initCharts(initial);

          if (activeRange === 'custom') {
            setButtons('custom');
          }
        });

        // ── Export ──────────────────────────────────
        function exportCsv() {
          var rows = [['Page URL', 'Views', 'Unique Visitors']];
          document.querySelectorAll('[data-page-stats] tr').forEach(function (tr) {
            var cells = tr.querySelectorAll('td');
            if (cells.length < 4) return;
            rows.push([cells[1].textContent.trim(), cells[2].textContent.trim(), cells[3].textContent.trim()]);
          });

          var csv = rows.map(function (row) {
            return row.map(function (cell) { return '"' + cell.replace(/"/g, '""') + '"'; }).join(',');
          }).join('\n');

          var blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
          var a = document.createElement('a');
          a.href = URL.createObjectURL(blob);
          a.download = 'halaman-terpopuler.csv';
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
          URL.revokeObjectURL(a.href);
        }

        document.querySelectorAll('[data-export]').forEach(function (btn) {
          btn.addEventListener('click', function () {
            var kind = btn.getAttribute('data-export');
            if (kind === 'csv') { exportCsv(); return; }

            var chart = charts[kind];
            if (!chart) return;

            var a = document.createElement('a');
            a.href = chart.toBase64Image();
            a.download = kind + '.png';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
          });
        });
      })();
    </script>
@endpush
