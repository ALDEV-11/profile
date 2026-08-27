@extends('admin.layouts.admin-layout')

@section('title', 'Gallery')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0">Gallery</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}" class="text-body">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">Gallery</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.galleries.create') }}" class="btn btn-primary">
            <i class="icon-base bx bx-cloud-upload me-1"></i>Upload Photo
        </a>
    </div>

    <div class="card card-hoverable">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <form action="{{ route('admin.galleries.index') }}" method="GET" class="d-flex gap-2">
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="icon-base bx bx-search"></i></span>
                    <input
                        type="text"
                        class="form-control"
                        name="q"
                        placeholder="Cari caption atau kategori..."
                        value="{{ request('q') }}" />
                </div>
                <button type="submit" class="btn btn-outline-primary">Search</button>
                @if (request('q'))
                    <a href="{{ route('admin.galleries.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </form>
            <span class="text-body-secondary">Total: {{ $galleries->total() }}</span>
        </div>

        @if ($galleries->isEmpty())
            <div class="text-center text-body-secondary py-5">
                <i class="icon-base bx bx-images fs-2 d-block mb-2"></i>
                Belum ada foto. Klik "Upload Photo" untuk menambahkan.
            </div>
        @else
            <div class="row g-3 p-3">
                @foreach ($galleries as $gallery)
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card border gallery-card h-100">
                            <a href="{{ $gallery->imageUrl('large') }}" target="_blank" class="gallery-thumb-link">
                                <img
                                    src="{{ $gallery->imageUrl('thumb') }}"
                                    alt="{{ $gallery->caption ?? 'Gallery photo' }}"
                                    class="card-img-top gallery-thumb"
                                    loading="lazy" />
                            </a>
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div class="text-truncate">
                                        <span class="badge bg-label-primary">{{ $gallery->category->label() }}</span>
                                        <p class="mb-0 small text-body-secondary mt-1">
                                            {{ $gallery->created_at->format('d M Y') }}
                                        </p>
                                    </div>
                                    <div class="d-flex flex-nowrap">
                                        <a
                                            href="{{ route('admin.galleries.edit', $gallery) }}"
                                            class="btn btn-sm btn-icon btn-outline-info me-1"
                                            title="Edit">
                                            <i class="icon-base bx bx-edit-alt"></i>
                                        </a>
                                        <form
                                            action="{{ route('admin.galleries.destroy', $gallery) }}"
                                            method="POST"
                                            class="d-inline"
                                            data-delete-form
                                            data-gallery-caption="{{ $gallery->caption ?? 'foto ini' }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Delete">
                                                <i class="icon-base bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                @if ($gallery->caption)
                                    <p class="card-text small mb-0 mt-1">{{ $gallery->caption }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($galleries->hasPages())
            <div class="card-footer d-flex justify-content-end">
                {{ $galleries->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
      (function () {
        var deleteForms = document.querySelectorAll('[data-delete-form]');

        deleteForms.forEach(function (form) {
          form.addEventListener('submit', function (e) {
            e.preventDefault();

            var caption = form.getAttribute('data-gallery-caption') || 'foto ini';

            if (typeof Swal === 'undefined') {
              if (confirm('Hapus ' + caption + '?')) {
                form.submit();
              }
              return;
            }

            Swal.fire({
              title: 'Hapus foto?',
              text: 'Foto "' + caption + '" akan dihapus beserta file gambarnya.',
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
