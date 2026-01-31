<nav class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            
            <div class="flex items-center">
                <a href="{{ route('public.home') }}" class="flex-shrink-0 flex items-center gap-3 group">
                    <img class="h-10 w-auto md:h-12 object-contain transition group-hover:scale-105" src="{{ asset('images/logo.png') }}" alt="Logo RSU PKU">
                    
                    <div class="hidden md:block">
                        <h1 class="font-bold text-lg text-blue-900 leading-none">RSU PKU Muhammadiyah</h1>
                        <span class="text-xs text-green-600 font-bold tracking-wide uppercase">Sistem Pelaporan IT</span>
                    </div>
                </a>
            </div>

            <div class="flex items-center space-x-2 md:space-x-4">
                
                <a href="{{ route('public.tracking') }}" 
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 
                   {{ request()->routeIs('public.tracking') ? 'bg-blue-50 text-blue-800 ring-1 ring-blue-200' : 'text-gray-600 hover:bg-gray-50 hover:text-blue-700' }}">
                   <div class="flex items-center gap-2">
                       <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                       </svg>
                       Tracking
                   </div>
                </a>

                <a href="{{ route('public.home') }}" 
                   class="px-5 py-2.5 rounded-lg text-sm font-bold shadow-md transition-all transform hover:-translate-y-0.5 flex items-center gap-2
                   {{ request()->routeIs('public.home') ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-blue-900 text-white hover:bg-blue-800' }}">
                   <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                   </svg>
                   <span class="hidden sm:inline">Buat Laporan</span>
                   <span class="sm:hidden">Lapor</span> </a>

            </div>
        </div>
    </div>
</nav>