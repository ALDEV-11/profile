@php
    $action = $hobby ? route('admin.hobbies.update', $hobby) : route('admin.hobbies.store');
    $method = $hobby ? 'PUT' : 'POST';
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

        <form action="{{ $action }}" method="POST">
            @csrf
            @method($method)

            <div class="mb-3">
                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control @error('name') is-invalid @enderror"
                    id="name"
                    name="name"
                    value="{{ old('name', $hobby->name ?? '') }}"
                    maxlength="100"
                    required />
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @include('admin.components.icon-picker', [
                'name' => 'icon',
                'value' => $hobby->icon ?? '',
                'required' => true,
            ])

            <div class="mb-3">
                <label for="order" class="form-label">Order</label>
                <input
                    type="number"
                    class="form-control @error('order') is-invalid @enderror"
                    id="order"
                    name="order"
                    value="{{ old('order', $hobby->order ?? 0) }}"
                    min="0" />
                @error('order')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Urutan tampilan di halaman (angka kecil tampil lebih dulu).</div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="icon-base bx bx-save me-1"></i>{{ $hobby ? 'Update' : 'Save' }}
                </button>
                <a href="{{ route('admin.hobbies.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

