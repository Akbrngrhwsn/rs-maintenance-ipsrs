<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-gray-800">{{ __('Daftar Request Pending') }}</h2>
            <div class="flex">
                <a href="{{ route('apps.ongoing') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 transition">
                    Lihat Proyek Berjalan →
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Tombol Buat Request (Hanya Manager/Direktur) --}}
            @if(in_array(Auth::user()->role, ['manager', 'direktur']))
            <div class="mb-6 bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="font-bold mb-4">Ajukan Request Baru</h3>
                <form action="{{ route('apps.store') }}" method="POST" class="flex gap-4">
                    @csrf
                    <input type="text" name="nama_aplikasi" placeholder="Nama Aplikasi" class="border-gray-300 rounded-md shadow-sm w-1/3" required>
                    <input type="text" name="deskripsi" placeholder="Deskripsi Singkat" class="border-gray-300 rounded-md shadow-sm w-1/2" required>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Kirim</button>
                </form>
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tiket</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aplikasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pemohon</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($projects as $app)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">{{ $app->ticket_number ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-900">{{ $app->nama_aplikasi }}</div>
                                <div class="text-xs text-gray-500">{{ Str::limit($app->deskripsi, 50) }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $app->user->name }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $app->status == 'pending_director' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $app->status == 'pending_director' ? 'Menunggu Direktur' : 'Menunggu Admin' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium">
                                <a href="{{ route('apps.show', $app->id) }}" class="text-indigo-600 hover:text-indigo-900 font-bold">Buka Detail</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Tidak ada request pending.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>