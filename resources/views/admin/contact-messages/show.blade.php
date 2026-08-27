@extends('admin.layouts.admin-layout')

@section('title', $message->subject)

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

        [data-message-actions] {
          display: none !important;
        }
      }
    </style>
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 no-print">
        <div>
            <h4 class="fw-bold mb-0">Detail Pesan</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}" class="text-body">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.messages.index') }}" class="text-body">Contact Messages</a>
                    </li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.messages.index') }}" class="btn btn-outline-secondary">
            <i class="icon-base bx bx-arrow-back me-1"></i>Kembali ke Inbox
        </a>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        @if ($prev)
            <a href="{{ route('admin.messages.show', $prev) }}" class="btn btn-sm btn-outline-primary">
                <i class="icon-base bx bx-chevron-left me-1"></i>Pesan sebelumnya
            </a>
        @else
            <span></span>
        @endif
        @if ($next)
            <a href="{{ route('admin.messages.show', $next) }}" class="btn btn-sm btn-outline-primary">
                Pesan berikutnya<i class="icon-base bx bx-chevron-right ms-1"></i>
            </a>
        @else
            <span></span>
        @endif
    </div>

    @include('admin.components.contact-message-card', ['message' => $message])
@endsection

@push('scripts')
    <script>
      (function () {
        function csrfToken() {
          return document.querySelector('input[name="_token"]')?.value || '';
        }

        document.querySelectorAll('[data-toggle-read-form]').forEach(function (form) {
          form.addEventListener('submit', function (e) {
            e.preventDefault();

            var url = form.getAttribute('action');
            var markRead = form.getAttribute('data-is-read') === '0';

            fetch(url, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
              },
              body: JSON.stringify({ is_read: markRead ? 1 : 0 })
            })
              .then(function (res) { return res.ok ? res.json() : null; })
              .then(function (data) {
                if (!data || !data.success) return;
                if (markRead && window.AdminApp) window.AdminApp.refreshUnread && window.AdminApp.refreshUnread();
                window.location.reload();
              })
              .catch(function () { form.submit(); });
          });
        });

        document.querySelectorAll('[data-delete-form]').forEach(function (form) {
          form.addEventListener('submit', function (e) {
            e.preventDefault();

            var subject = form.getAttribute('data-message-subject') || 'pesan ini';

            if (typeof Swal === 'undefined') {
              if (confirm('Hapus pesan "' + subject + '"?')) {
                form.submit();
              }
              return;
            }

            Swal.fire({
              title: 'Hapus pesan?',
              text: 'Pesan "' + subject + '" akan dipindahkan ke trash.',
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#d33',
              cancelButtonColor: '#6b7280',
              confirmButtonText: 'Ya, hapus!',
              cancelButtonText: 'Batal'
            }).then(function (result) {
              if (result.isConfirmed) {
                form.submit();
              }
            });
          });
        });
      })();
    </script>
@endpush
