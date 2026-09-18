@props(['story'])

@php
    $user = $story['user'];
    $firstStory = $story['stories']->first();
@endphp

<a
    href="{{ route('stories.show', $firstStory) }}"
    class="group shrink-0 text-center">

    <div class="rounded-full {{ $story['has_unseen'] ? 'bg-gradient-to-tr from-yellow-400 via-pink-500 to-purple-600' : 'bg-neutral-300' }} p-[3px]">

        <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-full bg-white">

            @if($user->avatar)
                <img
                    src="{{ asset($user->avatar) }}"
                    alt="{{ $user->username }}"
                    class="h-full w-full object-cover">
            @else
                <div class="flex h-full w-full items-center justify-center rounded-full bg-neutral-200 font-semibold uppercase">
                    {{ substr($user->name,0,1) }}
                </div>
            @endif

        </div>

    </div>

    <p class="mt-1.5 w-[74px] truncate text-xs font-medium text-neutral-600">
        {{ $user->username }}
    </p>

</a>
