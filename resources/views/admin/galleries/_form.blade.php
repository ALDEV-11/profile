@php
    $action = $gallery ? route('admin.galleries.update', $gallery) : route('admin.galleries.store');
    $method = $gallery ? 'PUT' : 'POST';
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

        <form
            action="{{ $action }}"
            method="POST"
            enctype="multipart/form-data"
            data-gallery-form
            novalidate>
            @csrf
            @method($method)

            <div class="mb-4">
                <label class="form-label">Photo @unless ($gallery)
                        <span class="text-danger">*</span>
                    @endunless</label>

                <div
                    class="gallery-upload-zone @error('image') is-invalid @enderror"
                    data-drop-zone>
                    <input
                        type="file"
                        class="form-control d-none"
                        id="image"
                        name="image"
                        accept="image/jpeg,image/webp"
                        data-image-input
                        {{ $gallery ? '' : 'required' }} />
                    <div class="text-center py-5">
                        <i class="icon-base bx bx-cloud-upload fs-2 text-body-secondary d-block mb-2"></i>
                        <p class="mb-1">Seret & lepas foto di sini, atau klik untuk memilih</p>
                        <small class="text-body-secondary">Format: JPG, JPEG, WebP · Maks 4MB</small>
                    </div>
                </div>

                <div class="gallery-preview d-none mt-3" data-image-preview>
                    <img src="" alt="Preview" class="img-thumbnail rounded" style="max-height: 240px" data-preview-img />
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" data-preview-clear>
                        <i class="icon-base bx bx-x me-1"></i>Hapus pilihan
                    </button>
                </div>

                @if ($gallery?->image_path)
                    <div class="mt-3">
                        <small class="text-body-secondary d-block mb-1">Foto saat ini:</small>
                        <img
                            src="{{ $gallery->imageUrl('medium') }}"
                            alt="{{ $gallery->caption ?? 'Gallery photo' }}"
                            class="img-thumbnail rounded"
                            style="max-height: 160px" />
                    </div>
                @endif

                @error('image')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="caption" class="form-label">Caption</label>
                <textarea
                    class="form-control @error('caption') is-invalid @enderror"
                    id="caption"
                    name="caption"
                    rows="3"
                    maxlength="500"
                    placeholder="Deskripsi singkat foto ini (opsional)">{{ old('caption', $gallery->caption ?? '') }}</textarea>
                @error('caption')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text text-end" data-char-count>0 / 500</div>
            </div>

            <div class="mb-3">
                <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                <select
                    class="form-select @error('category') is-invalid @enderror"
                    id="category"
                    name="category"
                    required>
                    <option value="" disabled {{ old('category', $gallery?->category->value ?? '') === '' ? 'selected' : '' }}>
                        Pilih kategori
                    </option>
                    @foreach (['project' => 'Project', 'event' => 'Event', 'random_photo' => 'Random Photo'] as $value => $label)
                        <option value="{{ $value }}" {{ old('category', $gallery?->category->value ?? '') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('category')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" data-submit-button>
                    <i class="icon-base bx bx-save me-1"></i>{{ $gallery ? 'Update' : 'Upload' }}
                </button>
                <a href="{{ route('admin.galleries.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
      (function () {
        var form = document.querySelector('[data-gallery-form]');
        if (!form) return;

        var input = form.querySelector('[data-image-input]');
        var dropZone = form.querySelector('[data-drop-zone]');
        var preview = form.querySelector('[data-image-preview]');
        var previewImg = form.querySelector('[data-preview-img]');
        var caption = form.querySelector('#caption');
        var counter = form.querySelector('[data-char-count]');
        var submitBtn = form.querySelector('[data-submit-button]');

        function showPreview(file) {
          if (!file || !file.type.match(/^image\/(jpeg|webp)$/)) return;
          var reader = new FileReader();
          reader.onload = function (e) {
            previewImg.src = e.target.result;
            preview.classList.remove('d-none');
          };
          reader.readAsDataURL(file);
        }

        dropZone.addEventListener('click', function () { input.click(); });

        dropZone.addEventListener('dragover', function (e) {
          e.preventDefault();
          dropZone.classList.add('is-dragging');
        });
        dropZone.addEventListener('dragleave', function () {
          dropZone.classList.remove('is-dragging');
        });
        dropZone.addEventListener('drop', function (e) {
          e.preventDefault();
          dropZone.classList.remove('is-dragging');
          if (e.dataTransfer.files.length) {
            input.files = e.dataTransfer.files;
            showPreview(input.files[0]);
          }
        });

        input.addEventListener('change', function () { showPreview(input.files[0]); });

        var clearBtn = form.querySelector('[data-preview-clear]');
        if (clearBtn) {
          clearBtn.addEventListener('click', function () {
            input.value = '';
            preview.classList.add('d-none');
            previewImg.src = '';
          });
        }

        if (caption) {
          function updateCounter() {
            counter.textContent = caption.value.length + ' / 500';
          }
          caption.addEventListener('input', updateCounter);
          updateCounter();
        }

        form.addEventListener('submit', function () {
          if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Uploading...';
          }
        });
      })();
    </script>
@endpush
