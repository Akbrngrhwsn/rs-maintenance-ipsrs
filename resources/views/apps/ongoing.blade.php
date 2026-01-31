<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-gray-800">{{ __('Proyek Sedang Berjalan') }}</h2>
            <div class="flex">
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
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200 hover:shadow-md transition">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-2">
                        <span class="bg-blue-100 text-blue-800 text-[10px] font-mono px-2 py-1 rounded">{{ $app->ticket_number }}</span>
                        <span class="text-xs font-bold text-gray-400">{{ $app->created_at->format('d M Y') }}</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-1">{{ $app->nama_aplikasi }}</h3>
                    <p class="text-sm text-gray-600 mb-4 h-10 overflow-hidden">{{ Str::limit($app->deskripsi, 60) }}</p>
                    
                    {{-- Progress Bar Mini --}}
                    <div class="mb-4">
                        <div class="flex justify-between text-xs mb-1">
                            <span>Progress</span>
                            <span class="font-bold">{{ $app->progress }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                            <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $app->progress }}%"></div>
                        </div>
                    </div>

                    <a href="{{ route('apps.show', $app->id) }}" class="block w-full text-center bg-gray-50 border border-gray-200 text-gray-700 font-bold py-2 rounded-lg hover:bg-gray-100 hover:text-blue-600 transition">
                        Lihat Proyek
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-12 text-gray-500">Belum ada proyek yang sedang berjalan.</div>
            @endforelse
        </div>
    </div>
</x-app-layout>