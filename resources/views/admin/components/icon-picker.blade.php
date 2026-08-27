@props([
    'name' => 'icon',
    'value' => '',
    'label' => 'Icon',
    'required' => false,
    'modalId' => 'iconPickerModal',
    'gridId' => 'iconPickerGrid',
    'searchId' => 'iconPickerSearch',
    'previewId' => 'iconPickerPreview',
    'inputId' => null,
    'hint' => 'Klik tombol untuk memilih ikon dari pustaka Boxicons.',
])

@php
    $inputId = $inputId ?? $name;
    $iconClass = null;
    if ($value) {
        if (Str::startsWith($value, 'bx-')) {
            $iconClass = $value;
        } elseif (Str::length($value) <= 4) {
            $iconClass = null; // emoji / legacy value
        } else {
            $iconClass = 'bx-'.$value;
        }
    }
@endphp

<div class="mb-3">
    <label for="{{ $inputId }}" class="form-label">{{ $label }} @if ($required)<span class="text-danger">*</span>@endif</label>

    <div class="input-group">
        <span class="input-group-text" id="{{ $previewId }}">
            @if ($value && !$iconClass)
                {{ $value }}
            @else
                <i class="bx {{ $iconClass ?: 'bx-smile' }}"></i>
            @endif
        </span>
        <input
            type="text"
            class="form-control @error($name) is-invalid @enderror"
            id="{{ $inputId }}"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            maxlength="60"
            placeholder="e.g. bx-joystick"
            aria-describedby="{{ $previewId }}"
            data-icon-picker-target="{{ $modalId }}"
            data-icon-picker-preview="{{ $previewId }}"
            @if ($required) required @endif
            @readonly(true) />
        <button
            type="button"
            class="btn btn-outline-primary"
            data-bs-toggle="modal"
            data-bs-target="#{{ $modalId }}">
            <i class="bx bx-grid-alt me-1"></i>Pilih Ikon
        </button>
    </div>

    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    <div class="form-text">{{ $hint }}</div>
</div>

{{-- Icon picker modal --}}
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-grid-alt me-1"></i>Pilih Ikon Boxicons
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 position-relative">
                    <i class="position-absolute text-body-secondary"
                        style="left: 0.9rem; top: 50%; transform: translateY(-50%);"></i>
                    <input
                        type="text"
                        class="form-control ps-5"
                        id="{{ $searchId }}"
                        placeholder="Cari ikon..."
                        autocomplete="off" />
                </div>
                <div class="icon-picker-result text-body-secondary small mb-2" data-icon-picker-count></div>
                <div
                    class="icon-picker-grid row row-cols-3 row-cols-sm-4 row-cols-md-5 row-cols-lg-6 g-2"
                    id="{{ $gridId }}">
                </div>
            </div>
        </div>
    </div>
</div>
