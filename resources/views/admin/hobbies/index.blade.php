@extends('admin.layouts.admin-layout')

@section('title', 'Hobbies')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0">Hobbies</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}" class="text-body">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">Hobbies</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.hobbies.create') }}" class="btn btn-primary">
            <i class="icon-base bx bx-plus me-1"></i>Add Hobby
        </a>
    </div>

    <div class="card card-hoverable">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Icon</th>
                        <th>Name</th>
                        <th>Order</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($hobbies as $hobby)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <span class="avatar avatar-sm">
                                    @php
                                        $icon = $hobby->icon ?? '';
                                        $iconClass = Str::startsWith($icon, 'bx')
                                            ? 'bx ' . preg_replace('/^bx\s+/', '', trim($icon))
                                            : null;
                                    @endphp
                                    @if ($iconClass)
                                        <span class="avatar-initial rounded bg-label-primary">
                                            <i class="{{ $iconClass }}"></i>
                                        </span>
                                    @else
                                        <span class="avatar-initial rounded bg-label-primary">{{ $icon }}</span>
                                    @endif
                                </span>
                            </td>
                            <td class="fw-semibold">{{ $hobby->name }}</td>
                            <td>{{ $hobby->order }}</td>
                            <td class="text-end">
                                <a
                                    href="{{ route('admin.hobbies.edit', $hobby) }}"
                                    class="btn btn-sm btn-icon btn-outline-info me-1"
                                    title="Edit">
                                    <i class="icon-base bx bx-edit-alt"></i>
                                </a>
                                <form
                                    action="{{ route('admin.hobbies.destroy', $hobby) }}"
                                    method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('Hapus hobby &quot;{{ $hobby->name }}&quot;?');">
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
                                Belum ada hobby. Klik "Add Hobby" untuk menambahkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
