<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Manager') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Pesan Sukses / Error --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    {{ session('error') }}
                </div>
            @endif

           {{-- FORM REQUEST APLIKASI (Hanya Manager) --}}
            @if(Auth::user()->role === 'manager')
            <div class="bg-white p-6 overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-indigo-500">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Ajukan Aplikasi Baru</h3>
                </div>

                <form action="{{ route('apps.store') }}" method="POST" class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
    @csrf
    <div class="space-y-4">
        {{-- Nama Aplikasi --}}
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">Nama Aplikasi</label>
            <input type="text" name="nama_aplikasi" required 
                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" 
                   placeholder="Contoh: E-Presensi Karyawan">
        </div>

        {{-- Deskripsi (Full Width) --}}
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">Deskripsi Singkat</label>
            <textarea name="deskripsi" required rows="3" 
                      class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" 
                      placeholder="Jelaskan fungsi utama aplikasi ini..."></textarea>
        </div>
    </div>

    {{-- Tombol Aksi --}}
    <div class="mt-6 flex justify-end">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow transition duration-150 ease-in-out flex items-center gap-2">
            <span>Kirim ke Direktur</span>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
            </svg>
        </button>
    </div>
</form>
            </div>
            @endif

            @php
                $colors = [
                    'pending_director' => 'bg-yellow-100 text-yellow-800',
                    'approved' => 'bg-blue-100 text-blue-800',
                    'rejected' => 'bg-red-100 text-red-800',
                    'in_progress' => 'bg-purple-100 text-purple-800',
                    'completed' => 'bg-green-100 text-green-800',
                ];
                $labels = [
                    'pending_director' => 'Menunggu Direktur',
                    'approved' => 'Menunggu Admin IT',
                    'rejected' => 'Ditolak',
                    'in_progress' => 'Sedang Dikerjakan',
                    'completed' => 'Selesai',
                ];
            @endphp

            

        </div>
    </div>
</x-app-layout>