@extends('admin.layouts.admin-layout')

@section('title', 'Contact Messages')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0">Contact Messages</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}" class="text-body">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">Contact Messages</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.messages.index', ['status' => 'trashed']) }}" class="btn btn-outline-secondary">
            <i class="icon-base bx bx-trash me-1"></i>Trash
        </a>
    </div>

    <div class="card card-hoverable">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <form action="{{ route('admin.messages.index') }}" method="GET" class="d-flex gap-2 flex-wrap">
                <div class="input-group input-group-merge" style="max-width: 320px">
                    <span class="input-group-text"><i class="icon-base bx bx-search"></i></span>
                    <input
                        type="text"
                        class="form-control"
                        name="q"
                        placeholder="Cari nama, email, atau subjek..."
                        value="{{ request('q') }}" />
                </div>
                <select name="status" class="form-select" style="max-width: 180px" onchange="this.form.submit()">
                    <option value="">Semua status</option>
                    <option value="unread" @selected(request('status') === 'unread')>Belum dibaca</option>
                    <option value="read" @selected(request('status') === 'read')>Sudah dibaca</option>
                    <option value="trashed" @selected(request('status') === 'trashed')>Di Trash</option>
                </select>
                <button type="submit" class="btn btn-outline-primary">Search</button>
                @if (request()->has('q') || request()->filled('status'))
                    <a href="{{ route('admin.messages.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </form>
            <span class="text-body-secondary">
                {{ $messages->total() }} pesan
                @if ($messages->hasPages())
                    <span class="ms-1">(hal. {{ $messages->currentPage() }}/{{ $messages->lastPage() }})</span>
                @endif
            </span>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Sender</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($messages as $message)
                        <tr class="{{ ! $message->is_read ? 'table-primary' : '' }}">
                            <td class="text-nowrap">
                                <span class="fw-semibold {{ ! $message->is_read ? '' : 'text-body' }}">
                                    {{ $message->created_at->format('d M Y') }}
                                </span>
                                <small class="text-body-secondary d-block">{{ $message->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <span class="fw-semibold {{ ! $message->is_read ? '' : 'text-body' }}">{{ $message->name }}</span>
                                <small class="text-body-secondary d-block">{{ $message->email }}</small>
                            </td>
                            <td class="{{ ! $message->is_read ? 'fw-semibold' : '' }}">
                                {{ $message->subject }}
                            </td>
                            <td>
                                @if ($message->is_read)
                                    <span class="badge bg-label-secondary">Sudah dibaca</span>
                                @else
                                    <span class="badge bg-label-primary">Belum dibaca</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a
                                        href="{{ route('admin.messages.show', $message) }}"
                                        class="btn btn-sm btn-icon btn-outline-info"
                                        title="Lihat">
                                        <i class="icon-base bx bx-show"></i>
                                    </a>

                                    <form
                                        action="{{ route('admin.messages.toggle-read', $message) }}"
                                        method="POST"
                                        class="d-inline"
                                        data-toggle-read-form
                                        data-is-read="{{ $message->is_read ? '1' : '0' }}"
                                        data-message-subject="{{ $message->subject }}">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-icon {{ $message->is_read ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                            title="{{ $message->is_read ? 'Tandai belum dibaca' : 'Tandai sudah dibaca' }}">
                                            <i class="icon-base {{ $message->is_read ? 'bx bxs-envelope' : 'bx bxs-envelope-open' }}"></i>
                                        </button>
                                    </form>

                                    <form
                                        action="{{ route('admin.messages.destroy', $message) }}"
                                        method="POST"
                                        class="d-inline"
                                        data-delete-form
                                        data-message-subject="{{ $message->subject }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus">
                                            <i class="icon-base bx bx-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-body-secondary py-4">
                                {{ request('status') === 'trashed' ? 'Trash kosong.' : 'Belum ada pesan masuk.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($messages->hasPages())
            <div class="card-footer">
                {{ $messages->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
      (function () {
        function csrfToken() {
          return document.querySelector('meta[name="csrf-token"]')?.content
            || document.querySelector('input[name="_token"]')?.value
            || '';
        }

        // ── Mark read/unread via AJAX ────────────────
        document.querySelectorAll('[data-toggle-read-form]').forEach(function (form) {
          form.addEventListener('submit', function (e) {
            e.preventDefault();

            var url = form.getAttribute('action');
            var subject = form.getAttribute('data-message-subject') || 'pesan ini';
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

                if (markRead) {
                  window.AdminApp && window.AdminApp.refreshUnread && window.AdminApp.refreshUnread();
                }
                window.location.reload();
              })
              .catch(function () { form.submit(); });
          });
        });

        // ── Delete confirmation (SweetAlert2) ────────
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
