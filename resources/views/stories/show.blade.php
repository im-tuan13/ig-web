<x-app-layout>

<div
    x-data="storyViewer({
        nextUrl: {{ Js::from($nextStory ? route('stories.show', $nextStory) : route('dashboard')) }},
        previousUrl: {{ Js::from($previousStory ? route('stories.show', $previousStory) : null) }},
        closeUrl: {{ Js::from(route('dashboard')) }},
        currentOwnerStoryIndex: {{ $currentOwnerStoryIndex }},
    })"
    @keydown.window.escape="close()"
    @keydown.window.arrow-right.prevent="next()"
    @keydown.window.arrow-left.prevent="previous()"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black">

    <a
        href="{{ route('dashboard') }}"
        class="absolute right-8 top-6 z-50 text-4xl text-white hover:opacity-70">
        &times;
    </a>

    <div
        class="relative w-[430px] max-w-full"
        @mouseenter="hoverPaused=true"
        @mouseleave="hoverPaused=false;holding=false">

        {{-- Progress --}}
        <div class="absolute left-4 right-4 top-4 z-20 flex gap-1">

            @foreach($ownerStories as $index=>$ownerStory)

                <div class="h-1 flex-1 overflow-hidden rounded-full bg-white/30">

                    <div
                        class="h-full bg-white transition-[width] duration-100"
                        :style="'width:'+segmentProgress({{ $index }})+'%'">
                    </div>

                </div>

            @endforeach

        </div>

        {{-- Story Image --}}
        <div
            class="relative"
            @pointerdown="hold($event)"
            @pointerup="releaseHold($event)"
            @pointercancel="releaseHold($event)"
            @lostpointercapture="releaseHold($event)">

            <img
                src="{{ $story->image_url }}"
                class="max-h-[90vh] w-full rounded-2xl object-contain select-none">

            {{-- Previous Area --}}
            @if($previousStory)
                <button
                    type="button"
                    @click="previous()"
                    class="absolute inset-y-0 left-0 w-1/2 z-10">
                </button>
            @endif

            {{-- Next Area --}}
            @if($nextStory)
                <button
                    type="button"
                    @click="next()"
                    class="absolute inset-y-0 right-0 w-1/2 z-10">
                </button>
            @endif

        </div>

        {{-- User --}}
        <div class="absolute bottom-5 left-5 z-30 flex items-center gap-3 text-white">

            @if($story->user->avatar)

                <img
                    src="{{ asset($story->user->avatar) }}"
                    class="h-10 w-10 rounded-full object-cover">

            @else

                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-black font-semibold">
                    {{ strtoupper(substr($story->user->name,0,1)) }}
                </div>

            @endif

            <div>

                <div class="font-semibold">
                    {{ $story->user->username }}
                </div>

                <div class="text-sm text-neutral-300">
                    {{ $story->created_at->diffForHumans() }}
                </div>

            </div>

        </div>

        @unless($isOwner)
            <form
                action="{{ route('stories.reply', $story) }}"
                method="POST"
                class="absolute bottom-4 left-5 right-5 z-40 flex items-center gap-2"
                @click.stop>
                @csrf
                <input
                    type="text"
                    name="message"
                    maxlength="5000"
                    required
                    placeholder="Reply to story..."
                    class="min-w-0 flex-1 rounded-full border border-white/40 bg-black/50 px-4 py-2.5 text-sm text-white placeholder:text-white/70 outline-none backdrop-blur focus:border-white"
                    @keydown.window="if ($event.key === 'Enter' && document.activeElement === $el) $event.stopPropagation()">
                <button
                    type="submit"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white text-neutral-900 transition hover:bg-neutral-200"
                    aria-label="Send story reply">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="m22 2-7 20-4-9-9-4z" />
                    </svg>
                </button>
            </form>
        @endunless

        @if($isOwner)

            <div class="absolute bottom-5 right-5 z-40 flex gap-2">

                <button
                    type="button"
                    @click.stop="viewersOpen=!viewersOpen"
                    class="rounded-full bg-black/60 px-3 py-2 text-sm text-white">

                    {{ $viewers->count() }} views

                </button>

                <form
                    action="{{ route('stories.destroy',$story) }}"
                    method="POST"
                    @click.stop>

                    @csrf
                    @method('DELETE')

                    <button
                        class="rounded-full bg-black/60 px-3 py-2 text-sm text-white"
                        onclick="return confirm('Delete this story?')">

                        Delete

                    </button>

                </form>

            </div>

            <div
                x-show="viewersOpen"
                x-cloak
                class="absolute bottom-16 right-5 z-50 w-72 max-h-64 overflow-y-auto rounded-xl bg-white p-3 shadow-xl">

                <p class="mb-2 text-sm font-semibold">

                    Viewed by

                </p>

                @forelse($viewers as $viewer)

                    <div class="flex items-center gap-3 py-2">

                        @if($viewer->avatar)

                            <img
                                src="{{ asset($viewer->avatar) }}"
                                class="h-9 w-9 rounded-full object-cover">

                        @else

                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-neutral-200">

                                {{ strtoupper(substr($viewer->name,0,1)) }}

                            </div>

                        @endif

                        <div>

                            <div class="font-semibold">

                                {{ $viewer->username }}

                            </div>

                            <div class="text-xs text-neutral-500">

                                {{ $viewer->story_viewed_at_human }}

                            </div>

                        </div>

                    </div>

                @empty

                    <p class="text-sm text-neutral-500">

                        No views yet.

                    </p>

                @endforelse

            </div>

        @endif

    </div>

</div>

</x-app-layout>
