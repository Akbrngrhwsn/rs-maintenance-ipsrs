@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
            <div class="p-6 sm:p-8">
                
                {{-- Header Minimalis --}}
                <div class="mb-8 border-b border-gray-100 pb-6">
                    <h2 class="text-xl font-semibold text-gray-800">
                        Pengajuan Barang Baru
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Lengkapi rincian barang yang ingin diajukan untuk unit kerja Anda.
                    </p>
                </div>

                {{-- Alert Error Minimalis --}}
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg border border-red-100">
                        <ul class="text-sm list-disc list-inside font-medium">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('kepala-ruang.new_items.store') }}" method="POST" id="procurementForm">
                    @csrf

                    <div class="space-y-6">
                        {{-- Pemilihan Ruangan --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ruangan Asal</label>
                            @php $userRooms = Auth::user()->rooms; @endphp

                            @if($userRooms->count() > 1)
                                <select name="room_id" required 
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="" disabled selected>Pilih Ruangan</option>
                                    @foreach($userRooms as $room)
                                        <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                                            {{ $room->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @elseif($userRooms->count() === 1)
                                @php $singleRoom = $userRooms->first(); @endphp
                                <input type="hidden" name="room_id" value="{{ $singleRoom->id }}">
                                <div class="text-sm font-semibold text-gray-700 bg-gray-50 px-3 py-2 rounded-md border border-gray-200 inline-block">
                                    {{ $singleRoom->name }}
                                </div>
                            @else
                                <div class="text-sm text-red-600 bg-red-50 p-3 rounded-md border border-red-100">
                                    Akun Anda belum terhubung dengan ruangan manapun.
                                </div>
                            @endif
                        </div>

                        {{-- Tujuan Pengadaan --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tujuan / Alasan Pengadaan</label>
                            <input type="text" name="purpose" required 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                                   placeholder="Contoh: Penambahan inventaris komputer staf baru" value="{{ old('purpose') }}">
                        </div>

                        {{-- Section Daftar Barang --}}
                        <div class="pt-4">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Rincian Barang</h3>
                                <button type="button" id="btn-add-item" 
                                        class="inline-flex items-center px-3 py-1 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    + Tambah Baris
                                </button>
                            </div>

                            <div id="items-container" class="space-y-3">
                                {{-- Baris Pertama --}}
                                <div class="flex flex-col sm:flex-row gap-3 item-row p-4 bg-gray-50/50 rounded-lg border border-gray-100">
                                    <div class="flex-1">
                                        <input type="text" name="items[0][nama]" class="w-full rounded-md border-gray-300 shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Nama Barang" required>
                                    </div>
                                    <div class="w-full sm:w-40">
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">Rp</span>
                                            </div>
                                            <input type="number" name="items[0][harga_satuan]" class="w-full pl-9 rounded-md border-gray-300 shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Harga" min="0" required>
                                        </div>
                                    </div>
                                    <div class="w-full sm:w-24">
                                        <input type="number" name="items[0][jumlah]" class="w-full rounded-md border-gray-300 shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 text-center" value="1" min="1" required>
                                    </div>
                                    <div class="w-full sm:w-10 flex items-center justify-end">
                                        <button type="button" class="btn-remove-item text-gray-400 hover:text-red-500 transition hidden">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Footer Actions --}}
                    <div class="mt-8 pt-6 border-t border-gray-100 flex items-center justify-between">
                        <a href="{{ url()->previous() }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">
                            Batal
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                            Kirim Pengajuan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let itemIndex = 1;
    document.getElementById('btn-add-item').addEventListener('click', function() {
        const container = document.getElementById('items-container');
        const html = `
            <div class="flex flex-col sm:flex-row gap-3 item-row p-4 bg-gray-50/50 rounded-lg border border-gray-100 animate-fadeIn">
                <div class="flex-1">
                    <input type="text" name="items[${itemIndex}][nama]" class="w-full rounded-md border-gray-300 shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Nama Barang" required>
                </div>
                <div class="w-full sm:w-40 text-left">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm text-left">Rp</span>
                        </div>
                        <input type="number" name="items[${itemIndex}][harga_satuan]" class="w-full pl-9 rounded-md border-gray-300 shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Harga" min="0" required>
                    </div>
                </div>
                <div class="w-full sm:w-24 text-left">
                    <input type="number" name="items[${itemIndex}][jumlah]" class="w-full rounded-md border-gray-300 shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 text-center" value="1" min="1" required>
                </div>
                <div class="w-full sm:w-10 flex items-center justify-end text-left">
                    <button type="button" class="btn-remove-item text-gray-400 hover:text-red-500 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        itemIndex++;
    });

    document.getElementById('items-container').addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-remove-item');
        if(btn) {
            btn.closest('.item-row').remove();
        }
    });
</script>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeIn {
        animation: fadeIn 0.2s ease-out forwards;
    }
</style>
@endsection