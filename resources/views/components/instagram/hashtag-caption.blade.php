@props(['caption'])

@php
    $parts = preg_split(
        '/(#[\w\x{80}-\x{FF}]+|@[\w-]+)/u',
        $caption,
        -1,
        PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
    );
@endphp

<span>
    @foreach ($parts as $part)
        @if (preg_match('/^#[\w\x{80}-\x{FF}]+$/u', $part))
            <a href="{{ url('tags/' . Str::lower(trim($part, '#'))) }}" class="text-sky-500 hover:text-sky-600">{{ $part }}</a>
        @elseif (preg_match('/^@[\w-]+$/u', $part))
            <a href="{{ route('users.show', Str::lower(trim($part, '@'))) }}" class="text-sky-500 hover:text-sky-600">{{ $part }}</a>
        @else
            {!! nl2br(e($part)) !!}
        @endif
    @endforeach
</span>
