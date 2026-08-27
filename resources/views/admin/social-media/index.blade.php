@extends('admin.layouts.admin-layout')

@section('title', 'Social Media')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0">Social Media</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}" class="text-body">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">Social Media</li>
                </ol>
            </nav>
        </div>
        @if ($canAdd)
            <a href="{{ route('admin.social-medias.create') }}" class="btn btn-primary">
                <i class="icon-base bx bx-plus me-1"></i>Add Social Media
            </a>
        @else
            <span class="text-body-secondary">Maksimal {{ \App\Services\SocialMediaService::MAX_LINKS }} link tercapai</span>
        @endif
    </div>

    <div class="card card-hoverable">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <form action="{{ route('admin.social-medias.index') }}" method="GET" class="d-flex gap-2">
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="icon-base bx bx-search"></i></span>
                    <input
                        type="text"
                        class="form-control"
                        name="q"
                        placeholder="Cari platform atau URL..."
                        value="{{ request('q') }}" />
                </div>
                <button type="submit" class="btn btn-outline-primary">Search</button>
                @if (request('q'))
                    <a href="{{ route('admin.social-medias.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </form>
            <span class="text-body-secondary">Total: {{ $socialMedias->count() }}</span>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Platform</th>
                        <th>URL</th>
                        <th>Icon</th>
                        <th>Order</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($socialMedias as $socialMedia)
                        <tr>
                            <td class="fw-semibold">{{ $socialMedia->platform }}</td>
                            <td>
                                <a href="{{ $socialMedia->url }}" target="_blank" rel="noopener noreferrer" class="text-body">
                                    {{ $socialMedia->url }}
                                </a>
                            </td>
                            <td>
                                <span class="avatar avatar-sm">
                                    <span class="avatar-initial rounded bg-label-primary">
                                        <i class="{{ $socialMedia->icon ?: 'bx bx-link' }}"></i>
                                    </span>
                                </span>
                            </td>
                            <td>{{ $socialMedia->order }}</td>
                            <td class="text-end">
                                <a
                                    href="{{ route('admin.social-medias.edit', $socialMedia) }}"
                                    class="btn btn-sm btn-icon btn-outline-info me-1"
                                    title="Edit">
                                    <i class="icon-base bx bx-edit-alt"></i>
                                </a>
                                <form
                                    action="{{ route('admin.social-medias.destroy', $socialMedia) }}"
                                    method="POST"
                                    class="d-inline"
                                    data-delete-form
                                    data-social-platform="{{ $socialMedia->platform }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Delete">
                                        <i class="icon-base bx bx-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-body-secondary py-4">
                                Belum ada link sosial media. Klik "Add Social Media" untuk menambahkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
      (function () {
        // ── Delete confirmation ─────────────────────
        document.querySelectorAll('[data-delete-form]').forEach(function (form) {
          form.addEventListener('submit', function (e) {
            e.preventDefault();

            var platform = form.getAttribute('data-social-platform') || 'link ini';

            if (typeof Swal === 'undefined') {
              if (confirm('Hapus link ' + platform + '?')) {
                form.submit();
              }
              return;
            }

            Swal.fire({
              title: 'Hapus link?',
              text: 'Link "' + platform + '" akan dihapus.',
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
