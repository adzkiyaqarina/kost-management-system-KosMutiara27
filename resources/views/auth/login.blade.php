<x-guest-layout>
    <div class="flex min-h-screen bg-white">

        <div class="hidden lg:flex lg:w-1/2 relative bg-emerald-900 items-center justify-center overflow-hidden">
            <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?ixlib=rb-1.2.1&auto=format&fit=crop&w=1600&q=80"
                alt="Login Image" class="absolute inset-0 w-full h-full object-cover opacity-60">
            <div class="relative z-10 text-white text-center px-10">
                <div
                    class="bg-emerald-600/20 backdrop-blur-md p-4 rounded-2xl border border-white/20 inline-block mb-6">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z">
                        </path>
                    </svg>
                </div>
                <h2 class="text-4xl font-bold mb-4">Selamat Datang Kembali</h2>
                <p class="text-emerald-100 text-lg">Masuk untuk mengecek tagihan, status sewa, dan layanan lainnya.</p>
            </div>
        </div>

        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 lg:p-16">
            <div class="w-full max-w-md space-y-8">
                <div class="text-center lg:text-left">
                    <h1 class="text-3xl font-bold text-gray-900">Masuk Akun</h1>
                    <p class="text-gray-500 mt-2">Belum punya akun? <a href="{{ route('register') }}"
                            class="text-emerald-600 font-bold hover:underline">Daftar di sini</a></p>
                </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input id="email"
                                class="block w-full px-4 py-3 border-gray-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
                                type="email" name="email" :value="old('email')" required autofocus
                                placeholder="nama@email.com" />
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <div class="flex justify-between items-center">
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            @if (Route::has('password.request'))
                                <a class="text-sm text-emerald-600 hover:text-emerald-500 font-bold"
                                    href="{{ route('password.request') }}">
                                    Lupa Password?
                                </a>
                            @endif
                        </div>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input id="password"
                                class="block w-full px-4 py-3 border-gray-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
                                type="password" name="password" required autocomplete="current-password"
                                placeholder="••••••••" />
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>


                    <button type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition transform hover:scale-[1.02]">
                        Masuk Sekarang
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-xs text-gray-400">© 2025 Mutiara27. Secure Login.</p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>