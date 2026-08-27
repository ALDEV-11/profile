@extends('admin.layouts.admin-layout')

@section('title', 'Edit Profile')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0">Edit Profile</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}" class="text-body">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">Profile</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
            <i class="icon-base bx bx-arrow-back me-1"></i>Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        action="{{ route('admin.profile.update') }}"
        method="POST"
        enctype="multipart/form-data"
        data-profile-form
        novalidate>
        @csrf
        @method('PUT')

        <div class="row">
            {{-- Profile image column --}}
            <div class="col-xl-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <label for="profile_image" class="form-label d-block">Foto Profil</label>

                        <div class="profile-image-preview mx-auto mb-3" data-image-preview>
                            @if ($profile->profile_image)
                                <img src="{{ $profile->imageUrl() }}" alt="Foto profil" class="rounded-circle object-fit-cover border" />
                            @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center bg-label-primary text-primary">
                                    <i class="icon-base bx bx-user fs-3"></i>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            <label for="profile_image" class="btn btn-sm btn-outline-primary mb-0">
                                <i class="icon-base bx bx-upload me-1"></i>Pilih Gambar
                            </label>
                            <button type="button" class="btn btn-sm btn-outline-danger mb-0" data-remove-image>
                                <i class="icon-base bx bx-x me-1"></i>Hapus
                            </button>
                        </div>

                        <input
                            type="file"
                            class="d-none @error('profile_image') is-invalid @enderror"
                            id="profile_image"
                            name="profile_image"
                            accept="image/jpeg,image/png,image/webp"
                            data-image-input />
                        <div class="invalid-feedback d-block">
                            @error('profile_image'){{ $message }}@enderror
                        </div>

                        <input type="hidden" name="remove_image" value="0" data-remove-image-input />

                        <div class="form-text mt-2">JPG, PNG, atau WebP. Maks 4 MB.</div>
                        <div class="form-text">Saran: gambar persegi (1:1).</div>
                    </div>
                </div>
            </div>

            {{-- Fields column --}}
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="full_name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-control @error('full_name') is-invalid @enderror"
                                    id="full_name"
                                    name="full_name"
                                    value="{{ old('full_name', $profile->full_name) }}"
                                    maxlength="100"
                                    required />
                                @error('full_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label for="tagline" class="form-label">Tagline <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-control @error('tagline') is-invalid @enderror"
                                    id="tagline"
                                    name="tagline"
                                    value="{{ old('tagline', $profile->tagline) }}"
                                    maxlength="200"
                                    required />
                                @error('tagline')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label for="about_me" class="form-label">Tentang Saya <span class="text-danger">*</span></label>
                                <textarea
                                    class="form-control @error('about_me') is-invalid @enderror"
                                    id="about_me"
                                    name="about_me"
                                    rows="6"
                                    maxlength="2000"
                                    required>{{ old('about_me', $profile->about_me) }}</textarea>
                                <div class="form-text text-end" data-character-count for="about_me">0 / 2000</div>
                                @error('about_me')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input
                                    type="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    id="email"
                                    name="email"
                                    value="{{ old('email', $profile->email) }}"
                                    maxlength="255"
                                    required />
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label">Telepon <span class="text-danger">*</span></label>
                                <input
                                    type="tel"
                                    class="form-control @error('phone') is-invalid @enderror"
                                    id="phone"
                                    name="phone"
                                    value="{{ old('phone', $profile->phone) }}"
                                    maxlength="30"
                                    placeholder="+62 812 3456 7890"
                                    required />
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label for="address" class="form-label">Alamat <span class="text-danger">*</span></label>
                                <textarea
                                    class="form-control @error('address') is-invalid @enderror"
                                    id="address"
                                    name="address"
                                    rows="2"
                                    maxlength="500"
                                    required>{{ old('address', $profile->address) }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary" data-submit-button>
                                <i class="icon-base bx bx-save me-1"></i>Simpan Perubahan
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
      (function () {
        'use strict';

        var form = document.querySelector('[data-profile-form]');
        if (!form) return;

        var imageInput = form.querySelector('[data-image-input]');
        var preview = form.querySelector('[data-image-preview]');
        var removeBtn = form.querySelector('[data-remove-image]');
        var removeInput = form.querySelector('[data-remove-image-input]');
        var submitButton = form.querySelector('[data-submit-button]');
        var about = document.getElementById('about_me');
        var counter = form.querySelector('[data-character-count]');

        // Character counter for about_me
        function updateCounter() {
          if (about && counter) {
            counter.textContent = about.value.length + ' / 2000';
          }
        }
        if (about) {
          about.addEventListener('input', updateCounter);
          updateCounter();
        }

        // Image preview before save
        if (imageInput && preview) {
          imageInput.addEventListener('change', function () {
            var file = imageInput.files && imageInput.files[0];
            if (!file) return;

            var reader = new FileReader();
            reader.onload = function (e) {
              preview.innerHTML =
                '<img src="' + e.target.result + '" alt="Pratinjau" class="rounded-circle object-fit-cover border" />';
              removeInput.value = '0';
            };
            reader.readAsDataURL(file);
          });
        }

        // Remove image
        if (removeBtn && removeInput) {
          removeBtn.addEventListener('click', function () {
            removeInput.value = '1';
            if (imageInput) imageInput.value = '';
            if (preview) {
              preview.innerHTML =
                '<div class="rounded-circle d-flex align-items-center justify-content-center bg-label-primary text-primary">' +
                '<i class="icon-base bx bx-user fs-3"></i></div>';
            }
          });
        }

        // Loading state on submit
        if (form && submitButton) {
          form.addEventListener('submit', function () {
            if (form.checkValidity() || form.classList.contains('was-validated')) {
              submitButton.disabled = true;
              submitButton.innerHTML =
                '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Menyimpan...';
            }
          });
        }
      })();
    </script>
@endpush
