<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Instagram') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite('resources/js/app.js')
    </head>
    <body class="bg-white font-sans text-neutral-950 antialiased">
        <main class="flex min-h-screen items-center justify-center px-4 py-8">
            {{ $slot }}
        </main>
    </body>
</html>
