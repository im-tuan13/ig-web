@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $notifications */
    /** @var \App\Models\User $user */
    $user = auth()->user();
@endphp

<x-app-layout>
    <div class="mx-auto max-w-[720px] px-4 py-6 sm:px-6">

        @if (session('success'))
            <div role="status"
                class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="min-h-[60vh] rounded-xl border border-neutral-200 bg-white px-6 py-6 shadow-sm">
            <x-instagram.notification-panel :notifications="$notifications" />
        </div>

    </div>
</x-app-layout>

