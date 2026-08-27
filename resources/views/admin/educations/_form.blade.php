@php
    $action = $education ? route('admin.educations.update', $education) : route('admin.educations.store');
    $method = $education ? 'PUT' : 'POST';
    $current = $education ? $education->is_currently_studying : (bool) old('is_currently_studying');
@endphp

<div class="card">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ $action }}" method="POST" data-education-form>
            @csrf
            @method($method)

            <div class="mb-3">
                <label for="institution" class="form-label">Institution <span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control @error('institution') is-invalid @enderror"
                    id="institution"
                    name="institution"
                    value="{{ old('institution', $education->institution ?? '') }}"
                    maxlength="200"
                    required />
                @error('institution')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="field_of_study" class="form-label">Field of Study <span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control @error('field_of_study') is-invalid @enderror"
                    id="field_of_study"
                    name="field_of_study"
                    value="{{ old('field_of_study', $education->field_of_study ?? '') }}"
                    maxlength="100"
                    required />
                @error('field_of_study')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="degree" class="form-label">Degree <span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control @error('degree') is-invalid @enderror"
                    id="degree"
                    name="degree"
                    value="{{ old('degree', $education->degree ?? '') }}"
                    maxlength="100"
                    placeholder="SMP, SMA/SMK, Diploma, Bachelor, Master, dst."
                    required />
                @error('degree')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Bisa diisi jenjang bebas, mis. SMP, SMK, S1, S2.</div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="start_date" class="form-label">Start Year <span class="text-danger">*</span></label>
                    <input
                        type="number"
                        class="form-control @error('start_date') is-invalid @enderror"
                        id="start_date"
                        name="start_date"
                        value="{{ old('start_date', $education?->start_date?->format('Y') ?? '') }}"
                        min="1900"
                        max="{{ now()->year + 10 }}"
                        placeholder="2018"
                        required />
                    @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="end_date" class="form-label">End Year</label>
                    <input
                        type="number"
                        class="form-control @error('end_date') is-invalid @enderror"
                        id="end_date"
                        name="end_date"
                        value="{{ old('end_date', $education?->end_date?->format('Y') ?? '') }}"
                        min="1900"
                        max="{{ now()->year + 10 }}"
                        placeholder="2022" />
                    @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-check form-switch mb-4">
                <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    id="is_currently_studying"
                    name="is_currently_studying"
                    value="1"
                    data-currently-studying
                    {{ $current ? 'checked' : '' }} />
                <label class="form-check-label" for="is_currently_studying">
                    Currently Studying
                </label>
                <div class="form-text">Jika dicentang, end year tidak wajib diisi dan akan ditampilkan sebagai "Present".</div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="icon-base bx bx-save me-1"></i>{{ $education ? 'Update' : 'Save' }}
                </button>
                <a href="{{ route('admin.educations.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
      (function () {
        var form = document.querySelector('[data-education-form]');
        if (!form) return;

        var endDate = form.querySelector('#end_date');
        var studying = form.querySelector('[data-currently-studying]');

        function toggleEndDate() {
          if (studying.checked) {
            endDate.removeAttribute('required');
          } else {
            endDate.setAttribute('required', 'required');
          }
        }

        studying.addEventListener('change', toggleEndDate);
        toggleEndDate();

        form.addEventListener('submit', function (e) {
          var start = parseInt(form.querySelector('#start_date').value, 10);
          var end = parseInt(endDate.value, 10);

          if (end && start && end < start) {
            e.preventDefault();
            endDate.setCustomValidity('End year harus setelah start year.');
            endDate.reportValidity();
            return;
          }

          endDate.setCustomValidity('');
        });

        endDate.addEventListener('input', function () {
          endDate.setCustomValidity('');
        });
      })();
    </script>
@endpush
