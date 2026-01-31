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

                <form action="{{ route('apps.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Aplikasi</label>
                            <input type="text" name="nama_aplikasi" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Contoh: E-Presensi">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Deskripsi Singkat</label>
                            <input type="text" name="deskripsi" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Contoh: Untuk mempermudah absensi karyawan...">
                        </div>
                    </div>
                    <div class="mt-4 text-right">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow transition">
                            Kirim ke Direktur
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