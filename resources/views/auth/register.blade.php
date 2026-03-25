<x-guest-layout>
    <div class="flex min-h-screen bg-white">

        <div class="hidden lg:flex lg:w-1/2 relative bg-emerald-900 items-center justify-center overflow-hidden">
            <img src="https://images.unsplash.com/photo-1598928506311-c55ded91a20c?ixlib=rb-1.2.1&auto=format&fit=crop&w=1600&q=80"
                alt="Register Image" class="absolute inset-0 w-full h-full object-cover opacity-60">
            <div class="relative z-10 text-white text-center px-10">
                <div
                    class="bg-emerald-600/20 backdrop-blur-md p-4 rounded-2xl border border-white/20 inline-block mb-6">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                        </path>
                    </svg>
                </div>
                <h2 class="text-4xl font-bold mb-4">Gabung Bersama Kami</h2>
                <p class="text-emerald-100 text-lg">Buat akun untuk melakukan pemesanan kamar dan pengelolaan profil</p>
            </div>
        </div>

        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 lg:p-16">
            <div class="w-full max-w-md space-y-8">
                <div class="text-center lg:text-left">
                    <h1 class="text-3xl font-bold text-gray-900">Buat Akun Baru</h1>
                    <p class="text-gray-500 mt-2">Sudah punya akun? <a href="{{ route('login') }}"
                            class="text-emerald-600 font-bold hover:underline">Masuk di sini</a></p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input id="name"
                            class="block w-full px-4 py-3 mt-1 border-gray-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
                            type="text" name="name" :value="old('name')" required autofocus placeholder="Nama Anda" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input id="email"
                            class="block w-full px-4 py-3 mt-1 border-gray-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
                            type="email" name="email" :value="old('email')" required placeholder="nama@email.com" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input id="password"
                            class="block w-full px-4 py-3 mt-1 border-gray-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
                            type="password" name="password" required autocomplete="new-password"
                            placeholder="Minimal 8 karakter" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi
                            Password</label>
                        <input id="password_confirmation"
                            class="block w-full px-4 py-3 mt-1 border-gray-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
                            type="password" name="password_confirmation" required placeholder="Ulangi password" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <button type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition transform hover:scale-[1.02]">
                        Daftar Sekarang
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-xs text-gray-400">Dengan mendaftar, Anda menyetujui Syarat & Ketentuan kami.</p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>