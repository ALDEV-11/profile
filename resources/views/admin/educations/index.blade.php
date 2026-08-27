@extends('admin.layouts.admin-layout')

@section('title', 'Education')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0">Education</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}" class="text-body">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">Education</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.educations.create') }}" class="btn btn-primary">
            <i class="icon-base bx bx-plus me-1"></i>Add Education
        </a>
    </div>

    <div class="card card-hoverable">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <form action="{{ route('admin.educations.index') }}" method="GET" class="d-flex gap-2" data-search-form>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="icon-base bx bx-search"></i></span>
                    <input
                        type="text"
                        class="form-control"
                        name="q"
                        placeholder="Cari institution, bidang, degree..."
                        value="{{ request('q') }}" />
                </div>
                <button type="submit" class="btn btn-outline-primary">Search</button>
                @if (request('q'))
                    <a href="{{ route('admin.educations.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </form>
            <span class="text-body-secondary">Total: {{ $educations->total() }}</span>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Institution</th>
                        <th>Degree</th>
                        <th>Field of Study</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($educations as $education)
                        <tr>
                            <td>{{ $educations->firstItem() + $loop->index }}</td>
                            <td class="fw-semibold">{{ $education->institution }}</td>
                            <td>
                                <span class="badge bg-label-primary">{{ $education->degree }}</span>
                            </td>
                            <td>{{ $education->field_of_study }}</td>
                            <td>
                                @if ($education->is_currently_studying)
                                    <span class="badge bg-label-success">
                                        <i class="icon-base bx bx-badge-check me-1"></i>Currently Studying
                                    </span>
                                @else
                                    <span class="badge bg-label-secondary">Completed</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a
                                    href="{{ route('admin.educations.edit', $education) }}"
                                    class="btn btn-sm btn-icon btn-outline-info me-1"
                                    title="Edit">
                                    <i class="icon-base bx bx-edit-alt"></i>
                                </a>
                                <form
                                    action="{{ route('admin.educations.destroy', $education) }}"
                                    method="POST"
                                    class="d-inline"
                                    data-delete-form
                                    data-education-name="{{ $education->institution }}">
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
                            <td colspan="6" class="text-center text-body-secondary py-4">
                                Belum ada data pendidikan. Klik "Add Education" untuk menambahkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($educations->hasPages())
            <div class="card-footer d-flex justify-content-end">
                {{ $educations->links() }}
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

            var name = form.getAttribute('data-education-name') || 'record ini';

            if (typeof Swal === 'undefined') {
              if (confirm('Hapus education "' + name + '"?')) {
                form.submit();
              }
              return;
            }

            Swal.fire({
              title: 'Hapus education?',
              text: 'Education "' + name + '" akan dihapus.',
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
