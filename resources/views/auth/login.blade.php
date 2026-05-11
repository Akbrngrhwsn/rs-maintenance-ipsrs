<x-guest-layout>
    <div class="min-h-screen flex flex-col justify-center items-center pt-6 sm:pt-0 bg-gray-50 px-4">
        
        <div class="mb-8 text-center">
            <a href="/" class="inline-flex flex-col items-center group">   
                <h1 class="text-2xl font-bold text-green-900">Pemeliharaan Unit IT</h1>
                <p class="text-sm text-green-600 font-bold tracking-widest uppercase">RSU PKU Muhammadiyah</p>
            </a>
        </div>

        <div class="w-full sm:max-w-md bg-white shadow-xl rounded-2xl border-t-4 border-green-900 overflow-hidden">
            <div class="p-8">
                
                <h2 class="text-xl font-semibold text-gray-800 mb-6 text-center">Masuk ke Dashboard</h2>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-bold text-gray-700 mb-1">Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" 
                                class="pl-10 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 py-2.5 transition ease-in-out duration-150" 
                                placeholder="nama@rsupku.com">
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-bold text-gray-700 mb-1">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input id="password" type="password" name="password" required autocomplete="current-password"
                                class="pl-10 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 py-2.5 transition ease-in-out duration-150" 
                                placeholder="••••••••">
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between mt-4">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                            <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-green-900 shadow-sm focus:ring-green-500 cursor-pointer" name="remember">
                            <span class="ml-2 text-sm text-gray-600 group-hover:text-green-800 transition">Ingat saya</span>
                        </label>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-bold text-white bg-green-900 hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all transform hover:-translate-y-0.5">
                            MASUK
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <p class="mt-8 text-center text-xs text-gray-400">
            &copy; {{ date('Y') }} SIMRS RSU PKU Muhammadiyah Jatinom.
        </p>
    </div>
</x-guest-layout>