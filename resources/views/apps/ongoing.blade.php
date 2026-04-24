<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-gray-800">{{ __('Proyek Sedang Berjalan') }}</h2>
            <div class="flex gap-2">
                {{-- Tombol Laporan Bulanan Baru (Hanya Admin) --}}
                @if(Auth::user()->role === 'admin')
                <button type="button" onclick="openAppMonthlyModal()"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg shadow hover:bg-indigo-700 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Laporan Bulanan
                </button>
                @endif

                <a href="{{ route('apps.pending') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 transition">
                    ← Request Aplikasi
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($projects as $app)
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200 hover:shadow-md transition flex flex-col">
                <div class="p-6 flex flex-col flex-1">
                    <div class="flex justify-between items-start mb-2">
                        <span class="bg-blue-100 text-blue-800 text-[10px] font-mono px-2 py-1 rounded">{{ $app->ticket_number }}</span>
                        <span class="text-xs font-bold text-gray-400">{{ $app->created_at->format('d M Y') }}</span>
                    </div>
                    
                    {{-- Gunakan app_name jika nama_aplikasi error --}}
                    <h3 class="text-lg font-bold text-gray-800 mb-1">{{ $app->nama_aplikasi ?? $app->app_name }}</h3>
                    
                    {{-- Gunakan description jika deskripsi error --}}
                    <p class="text-sm text-gray-600 mb-4 h-10 overflow-hidden">{{ Str::limit($app->deskripsi ?? $app->description, 60) }}</p>
                    
                    {{-- Progress Bar Mini --}}
                    <div class="mb-4 mt-auto">
                        <div class="flex justify-between text-xs mb-1">
                            <span>Progress</span>
                            <span class="font-bold">{{ $app->progress }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                            <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $app->progress }}%"></div>
                        </div>
                    </div>

                    {{-- Kumpulan Tombol Aksi --}}
                    <div class="flex items-center gap-2 mt-2">
                        <a href="{{ route('apps.show', $app->id) }}" class="flex-1 text-center bg-gray-50 border border-gray-200 text-gray-700 font-bold py-2 px-3 rounded-lg hover:bg-gray-100 hover:text-blue-600 transition text-sm">
                            Lihat Proyek
                        </a>
                        
                        {{-- Tombol Edit dan Hapus Khusus Admin --}}
                        @if(Auth::user()->role === 'admin')
                            <a href="{{ route('admin.apps.edit', $app->id) }}" class="flex-none bg-amber-50 border border-amber-200 text-amber-600 hover:bg-amber-100 hover:text-amber-700 font-bold py-2 px-3 rounded-lg transition text-sm" title="Edit">
                                Edit
                            </a>
                            <form action="{{ route('admin.apps.destroy', $app->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus permintaan aplikasi ini secara permanen?')" class="flex-none">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full bg-red-50 border border-red-200 text-red-600 hover:bg-red-100 hover:text-red-700 font-bold py-2 px-3 rounded-lg transition text-sm" title="Hapus">
                                    Hapus
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-12 text-gray-500">Belum ada proyek yang sedang berjalan.</div>
            @endforelse
        </div>
    </div>

    {{-- MODAL POP-UP --}}
    <div id="app-monthly-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full m-4 overflow-hidden relative">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-lg text-gray-800">Unduh Laporan Bulanan</h3>
                <button type="button" onclick="closeAppMonthlyModal()" class="text-gray-400 hover:text-red-500">✕</button>
            </div>
            
            <form action="{{ route('admin.apps.export.monthly') }}" method="GET">
                <div class="p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Bulan & Tahun</label>
                    <input type="month" name="month" value="{{ date('Y-m') }}" 
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required />
                </div>
                
                <div class="px-6 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
                    <button type="button" onclick="closeAppMonthlyModal()" class="bg-white border px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Batal</button>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-bold text-sm hover:bg-indigo-700 shadow-md transition">Unduh PDF</button>
                </div>
            </form>
        </div>
    </div>

    {{-- SCRIPT MODAL --}}
    <script>
        function openAppMonthlyModal() {
            document.getElementById('app-monthly-modal').classList.remove('hidden');
        }

        function closeAppMonthlyModal() {
            document.getElementById('app-monthly-modal').classList.add('hidden');
        }

        // Tutup modal jika klik di luar area modal
        window.onclick = function(event) {
            let modal = document.getElementById('app-monthly-modal');
            if (event.target == modal) {
                closeAppMonthlyModal();
            }
        }
    </script>
</x-app-layout>