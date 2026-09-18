<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite('resources/js/app.js')
    </head>
    <body class="bg-[#faf9f9] font-sans antialiased text-[#1a1c1c]">
        <div class="min-h-screen bg-[#faf9f9]">
            @auth
                <header class="fixed inset-x-0 top-0 z-40 flex h-[60px] items-center justify-between border-b border-outline-variant bg-surface px-4 md:hidden">
                    <a href="{{ route('dashboard') }}" class="text-headline-md font-semibold tracking-tight text-on-surface">Instagram</a>
                    <div class="flex items-center gap-4 text-on-surface-variant">
                        <a href="{{ route('notifications.index') }}" aria-label="Notifications">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" /><path d="M13.73 21a2 2 0 0 1-3.46 0" /></svg>
                        </a>
                        <a href="{{ route('messages.index') }}" aria-label="Messages">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 8.8 8.8 0 0 1-3.5-.7L3 21l1.8-5.1a8 8 0 0 1-1-4.4 8.4 8.4 0 0 1 17.2 0z" /></svg>
                        </a>
                    </div>
                </header>
            @endauth
            @include('layouts.navigation')

            @isset($header)
                <header class="border-b border-outline-variant bg-surface md:ml-[244px]">
                    <div class="mx-auto max-w-[935px] px-4 py-4 sm:px-6">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="min-h-screen pb-12 pt-[60px] md:ml-[244px] md:pt-0 md:pb-0">
                {{ $slot }}
            </main>
        </div>

        @auth
            <div x-data="callManager({{ auth()->id() }})" x-cloak x-show="open" class="fixed inset-0 z-[80] flex items-center justify-center bg-black/80 p-4" role="dialog" aria-modal="true">
                <div class="relative flex h-[min(720px,90vh)] w-full max-w-3xl flex-col overflow-hidden rounded-3xl bg-neutral-950 text-white shadow-2xl"><div class="flex items-center justify-between px-5 py-4"><div><p class="font-semibold" x-text="title"></p><p class="text-sm text-white/60" x-text="statusLabel"></p></div><button type="button" @click="endCall()" class="rounded-full p-2">✕</button></div><div class="relative flex-1 bg-neutral-900"><video x-ref="remoteVideo" autoplay playsinline class="h-full w-full object-cover"></video><div x-show="incoming" class="absolute inset-0 flex flex-col items-center justify-center bg-neutral-900/95"><div class="mb-5 text-6xl" x-text="callerInitial"></div><h2 class="text-xl font-semibold" x-text="title"></h2><p class="mt-1 text-white/60">Incoming video call</p><div class="mt-8 flex gap-4"><button @click="decline()" class="rounded-full bg-red-500 px-6 py-3 font-semibold">Decline</button><button @click="accept()" class="rounded-full bg-emerald-500 px-6 py-3 font-semibold">Accept</button></div></div><video x-ref="localVideo" autoplay muted playsinline class="absolute bottom-5 right-5 h-32 w-24 rounded-xl border-2 border-white object-cover sm:h-44 sm:w-32"></video></div><div x-show="!incoming" class="flex justify-center gap-3 px-5 py-5"><button @click="toggleMute()" class="rounded-full bg-white/15 px-4 py-3" x-text="muted ? 'Unmute' : 'Mute'"></button><button @click="toggleCamera()" class="rounded-full bg-white/15 px-4 py-3" x-text="cameraOff ? 'Camera on' : 'Camera off'"></button><button @click="switchCamera()" class="rounded-full bg-white/15 px-4 py-3">Switch</button><button @click="endCall()" class="rounded-full bg-red-500 px-5 py-3 font-semibold">End call</button></div><p x-show="error" class="px-5 pb-4 text-center text-sm text-red-300" x-text="error"></p></div>
            </div>
        @endauth

        <div id="post-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-0 sm:p-8" role="dialog" aria-modal="true" aria-label="Post" aria-hidden="true">
            <div class="relative h-[90vh] max-h-full w-full max-w-6xl overflow-hidden bg-white sm:rounded-3xl">
                <button id="post-modal-close" type="button" class="absolute right-3 top-3 z-10 rounded-full bg-black/50 p-2 text-white transition hover:bg-black/70" aria-label="Close post">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path d="m6 6 12 12M18 6 6 18" />
                    </svg>
                </button>
                <div id="post-modal-content" class="h-full"></div>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
