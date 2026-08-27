@php
    $action = $socialMedia ? route('admin.social-medias.update', $socialMedia) : route('admin.social-medias.store');
    $method = $socialMedia ? 'PUT' : 'POST';
    $platformOptions = [
        'GitHub' => 'GitHub',
        'Instagram' => 'Instagram',
        'LinkedIn' => 'LinkedIn',
        'Twitter/X' => 'Twitter/X',
        'Portfolio' => 'Portfolio',
        'Custom' => 'Custom',
    ];
    $platformIcons = [
        'GitHub' => 'bx bxl-github',
        'Instagram' => 'bx bxl-instagram',
        'LinkedIn' => 'bx bxl-linkedin',
        'Twitter/X' => 'bx bxl-twitter',
        'Portfolio' => 'bx bx-briefcase',
        'Custom' => 'bx bx-link',
    ];
    $selectedPlatform = old('platform', $socialMedia->platform ?? '');
    $selectedIcon = old('icon', $socialMedia->icon ?? '');
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

        <form action="{{ $action }}" method="POST" data-social-form>
            @csrf
            @method($method)

            <div class="mb-3">
                <label for="platform" class="form-label">Platform <span class="text-danger">*</span></label>
                <select
                    class="form-select @error('platform') is-invalid @enderror"
                    id="platform"
                    name="platform"
                    data-platform-select
                    required>
                    <option value="" disabled {{ $selectedPlatform === '' ? 'selected' : '' }}>Pilih platform</option>
                    @foreach ($platformOptions as $value => $label)
                        <option value="{{ $value }}" data-icon="{{ $platformIcons[$value] }}" {{ $selectedPlatform === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('platform')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="url" class="form-label">URL <span class="text-danger">*</span></label>
                <input
                    type="url"
                    class="form-control @error('url') is-invalid @enderror"
                    id="url"
                    name="url"
                    value="{{ old('url', $socialMedia->url ?? '') }}"
                    maxlength="500"
                    placeholder="https://github.com/username"
                    required />
                @error('url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Harus diawali http:// atau https://.</div>
            </div>

            <div class="mb-3">
                <label for="icon" class="form-label">Icon</label>
                <div class="input-group">
                    <span class="input-group-text" data-icon-preview>
                        <i class="{{ $selectedIcon ?: 'bx bx-link' }}"></i>
                    </span>
                    <input
                        type="text"
                        class="form-control @error('icon') is-invalid @enderror"
                        id="icon"
                        name="icon"
                        value="{{ $selectedIcon }}"
                        maxlength="60"
                        placeholder="bx bxl-github"
                        data-icon-input />
                </div>
                @error('icon')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Terisi otomatis berdasarkan platform, tapi bisa disesuaikan.</div>
            </div>

            <div class="mb-3">
                <label for="order" class="form-label">Order</label>
                <input
                    type="number"
                    class="form-control @error('order') is-invalid @enderror"
                    id="order"
                    name="order"
                    value="{{ old('order', $socialMedia->order ?? 0) }}"
                    min="0" />
                @error('order')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Urutan tampilan di halaman (angka kecil tampil lebih dulu).</div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="icon-base bx bx-save me-1"></i>{{ $socialMedia ? 'Update' : 'Save' }}
                </button>
                <a href="{{ route('admin.social-medias.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
      (function () {
        var form = document.querySelector('[data-social-form]');
        if (!form) return;

        var platformSelect = form.querySelector('[data-platform-select]');
        var iconInput = form.querySelector('[data-icon-input]');
        var iconPreview = form.querySelector('[data-icon-preview]');

        if (platformSelect && iconInput) {
          function syncIcon() {
            var opt = platformSelect.options[platformSelect.selectedIndex];
            if (opt && opt.dataset.icon) {
              iconInput.value = opt.dataset.icon;
              iconPreview.innerHTML = '<i class="' + opt.dataset.icon + '"></i>';
            }
          }

          platformSelect.addEventListener('change', syncIcon);

          var isNew = !{{ $socialMedia ? 'true' : 'false' }};
          if (isNew) syncIcon();
        }

        if (iconInput && iconPreview) {
          iconInput.addEventListener('input', function () {
            iconPreview.innerHTML = '<i class="' + iconInput.value + '"></i>';
          });
        }
      })();
    </script>
@endpush
