@props(['message'])

@php
    $mailto = sprintf(
        'mailto:%s?subject=%s&body=%s',
        rawurlencode($message->email),
        rawurlencode('Re: '.$message->subject),
        rawurlencode(
            "Halo {$message->name},\n\n"
            ."---------- Pesan asli ----------\n"
            ."Dari: {$message->name} <{$message->email}>\n"
            ."Tanggal: {$message->created_at->format('d M Y H:i')}\n"
            ."Subjek: {$message->subject}\n\n"
            .$message->message."\n"
            ."--------------------------------\n"
        ),
    );
@endphp

<a
    href="{{ $mailto }}"
    target="_blank"
    rel="noopener noreferrer"
    {{ $attributes->class('btn btn-primary') }}>
    <i class="icon-base bx bx-reply me-1"></i>Balas via Email
</a>
