@props(['message', 'showActions' => true])

@php
    $showActions = $showActions ?? true;
@endphp

<div class="card card-hoverable">
    <div class="card-header py-3">
        <div class="d-flex align-items-start gap-3 flex-wrap">
            <div class="avatar avatar-md flex-shrink-0">
                <span class="avatar-initial rounded-circle bg-label-primary">
                    <i class="icon-base bx bx-user"></i>
                </span>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h5 class="mb-1">{{ $message->name }}</h5>
                        <div class="d-flex align-items-center gap-2 text-body-secondary">
                            <a href="mailto:{{ $message->email }}" class="text-body">
                                <i class="icon-base bx bxs-envelope me-1"></i>{{ $message->email }}
                            </a>
                        </div>
                    </div>
                    <span class="text-body-secondary small text-nowrap">
                        <i class="icon-base bx bx-calendar me-1"></i>
                        {{ $message->created_at->format('d M Y, H:i') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <h6 class="text-body-secondary text-uppercase small fw-semibold mb-2">Subjek</h6>
        <h5 class="mb-4">{{ $message->subject }}</h5>

        <h6 class="text-body-secondary text-uppercase small fw-semibold mb-2">Pesan</h6>
        <div class="message-body">
            {!! nl2br(e($message->message)) !!}
        </div>
    </div>
    @if ($showActions)
        <div class="card-footer d-flex flex-wrap gap-2 align-items-center" data-message-actions>
            @include('admin.components.email-reply-template', ['message' => $message])

            <form
                action="{{ route('admin.messages.toggle-read', $message) }}"
                method="POST"
                class="d-inline"
                data-toggle-read-form
                data-is-read="{{ $message->is_read ? '1' : '0' }}">
                @csrf
                <button type="submit" class="btn {{ $message->is_read ? 'btn-outline-warning' : 'btn-outline-success' }}">
                    <i class="icon-base bx {{ $message->is_read ? 'bxs-envelope' : 'bxs-envelope-open' }} me-1"></i>
                    {{ $message->is_read ? 'Tandai belum dibaca' : 'Tandai sudah dibaca' }}
                </button>
            </form>

            <form
                action="{{ route('admin.messages.destroy', $message) }}"
                method="POST"
                class="d-inline"
                data-delete-form
                data-message-subject="{{ $message->subject }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="icon-base bx bx-trash me-1"></i>Hapus
                </button>
            </form>

            <button type="button" class="btn btn-outline-secondary ms-auto" onclick="window.print()">
                <i class="icon-base bx bx-printer me-1"></i>Print
            </button>
        </div>
    @endif
</div>
