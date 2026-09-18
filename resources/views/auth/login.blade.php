<x-guest-layout>
    <div class="grid w-full max-w-4xl grid-cols-1 items-center gap-8 md:grid-cols-[420px_350px]">
        <section class="relative hidden h-[620px] md:block">
            <div class="absolute left-12 top-6 h-[560px] w-[280px] rounded-[38px] border-[10px] border-neutral-900 bg-neutral-950 shadow-2xl">
                <div class="mx-auto mt-2 h-5 w-24 rounded-full bg-neutral-900"></div>
                <div class="mx-3 mt-4 overflow-hidden rounded-[24px] bg-white">
                    <div class="flex items-center gap-2 border-b border-neutral-200 p-3">
                        <div class="h-8 w-8 rounded-full bg-gradient-to-tr from-yellow-400 via-pink-500 to-purple-600"></div>
                        <div>
                            <div class="h-2.5 w-20 rounded bg-neutral-900"></div>
                            <div class="mt-1.5 h-2 w-12 rounded bg-neutral-300"></div>
                        </div>
                    </div>
                    <div class="aspect-square bg-gradient-to-br from-neutral-200 via-sky-100 to-pink-100"></div>
                    <div class="space-y-3 p-3">
                        <div class="flex gap-3">
                            <div class="h-5 w-5 rounded-full border-2 border-neutral-900"></div>
                            <div class="h-5 w-5 rounded-full border-2 border-neutral-900"></div>
                            <div class="h-5 w-5 rotate-45 border-l-2 border-t-2 border-neutral-900"></div>
                        </div>
                        <div class="h-2.5 w-24 rounded bg-neutral-900"></div>
                        <div class="h-2 w-44 rounded bg-neutral-300"></div>
                        <div class="h-2 w-32 rounded bg-neutral-200"></div>
                    </div>
                </div>
            </div>

            <div class="absolute right-6 top-36 h-[430px] w-[220px] rounded-[34px] border-[8px] border-neutral-900 bg-neutral-950 shadow-xl">
                <div class="mx-2 mt-3 overflow-hidden rounded-[22px] bg-white">
                    <div class="aspect-square bg-gradient-to-br from-pink-100 via-yellow-100 to-neutral-200"></div>
                    <div class="space-y-2 p-3">
                        <div class="h-2.5 w-20 rounded bg-neutral-900"></div>
                        <div class="h-2 w-32 rounded bg-neutral-300"></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto w-full max-w-[350px]">
            <div class="border border-neutral-300 bg-white px-10 pb-6 pt-10">
                <h1 class="mb-10 text-center text-4xl font-semibold tracking-normal">Instagram</h1>

                <x-auth-session-status class="mb-4 text-center text-sm" :status="session('status')" />

                <form method="POST" action="{{ request()->getBaseUrl() }}/login" class="space-y-2">
                    @csrf

                    <label for="email" class="sr-only">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                        placeholder="Phone number, username, or email"
                        class="h-10 w-full rounded-sm border border-neutral-300 bg-neutral-50 px-2 text-xs focus:border-neutral-400 focus:ring-0">
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />

                    <label for="password" class="sr-only">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                        placeholder="Password"
                        class="h-10 w-full rounded-sm border border-neutral-300 bg-neutral-50 px-2 text-xs focus:border-neutral-400 focus:ring-0">
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />

                    <button type="submit" class="mt-3 flex h-8 w-full items-center justify-center rounded-lg bg-sky-500 text-sm font-semibold text-white hover:bg-sky-600">
                        Log in
                    </button>

                    <label for="remember_me" class="flex items-center gap-2 pt-2 text-xs text-neutral-700">
                        <input id="remember_me" type="checkbox" name="remember" class="rounded border-neutral-300 text-sky-500 focus:ring-sky-500">
                        Remember me
                    </label>
                </form>

                <div class="my-5 flex items-center gap-4">
                    <div class="h-px flex-1 bg-neutral-300"></div>
                    <span class="text-xs font-semibold uppercase text-neutral-500">or</span>
                    <div class="h-px flex-1 bg-neutral-300"></div>
                </div>

                <button type="button" class="mx-auto flex items-center gap-2 text-sm font-semibold text-blue-900">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M22 12a10 10 0 1 0-11.6 9.9v-7h-2.5V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.4h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0 0 22 12z" />
                    </svg>
                    Log in with Facebook
                </button>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="mt-5 block text-center text-xs text-blue-900">
                        Forgot password?
                    </a>
                @endif
            </div>

            <div class="mt-3 border border-neutral-300 bg-white px-6 py-5 text-center text-sm">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-semibold text-sky-500">Sign up</a>
            </div>

            <p class="mt-5 text-center text-sm">Get the app.</p>
            <div class="mt-4 flex justify-center gap-2">
                <div class="flex h-10 w-32 items-center justify-center rounded bg-neutral-950 text-xs font-semibold text-white">App Store</div>
                <div class="flex h-10 w-32 items-center justify-center rounded bg-neutral-950 text-xs font-semibold text-white">Google Play</div>
            </div>
        </section>
    </div>
</x-guest-layout>
