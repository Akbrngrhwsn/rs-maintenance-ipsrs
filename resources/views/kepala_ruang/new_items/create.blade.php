@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <!-- Header Page -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-1.5">Pengajuan Pengadaan Barang Baru</h1>
        <p class="text-sm text-gray-500">Gunakan form ini untuk mengajukan barang baru yang bukan merupakan perbaikan (contoh: PC untuk pegawai baru).</p>
    </div>

    {{-- BLOK PENAMPIL ERROR --}}
    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-xl shadow-sm">
            <div class="flex items-start">
                <div class="flex-shrink-0 mt-0.5">
                    <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-bold text-red-800">Pengajuan gagal dikirim:</h3>
                    <ul class="mt-1 text-sm text-red-700 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Card Form -->
    <form action="{{ route('kepala-ruang.new_items.store') }}" method="POST" class="bg-white p-6 md:p-8 rounded-xl shadow-sm border border-gray-100">
        @csrf
        
        <!-- RUANGAN OTOMATIS -->
        @php
            $userRoom = Auth::user()->rooms->first(); 
        @endphp

        <div class="mb-6">
            <label class="block font-bold text-gray-700 mb-1.5 text-sm">Deskripsi Pengadaan</label>
            <input type="text" name="purpose" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder-gray-400" required placeholder="Contoh: Komputer untuk admin ruangan baru" value="{{ old('purpose') }}">
        </div>

        <!-- DAFTAR BARANG -->
        <div class="mb-2 pt-4 border-t border-gray-100">
            <label class="block font-bold text-gray-700 mb-3 text-sm">Daftar Barang</label>
            
            <div id="items-container" class="space-y-3">
                <!-- Baris Barang Pertama (Tanpa Tombol Hapus) -->
                <div class="flex flex-col sm:flex-row gap-3 item-row items-start sm:items-center">
                    <div class="w-full sm:flex-1">
                        <input type="text" name="items[0][nama]" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all placeholder-gray-400" placeholder="Nama Barang" required>
                    </div>
                    <div class="w-full sm:w-48">
                        <input type="number" name="items[0][harga_satuan]" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all placeholder-gray-400" placeholder="Harga Satuan (Rp)" min="0" required>
                    </div>
                    <div class="w-full sm:w-32">
                        <input type="number" name="items[0][jumlah]" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all placeholder-gray-400" placeholder="Jumlah" min="1" required>
                    </div>
                    <button type="button" class="btn-remove-item hidden p-2.5 rounded-lg border border-transparent" disabled>
                        <!-- Placeholder alignment -->
                        <div class="w-5 h-5"></div>
                    </button>
                </div>
            </div>

            <!-- Tombol Tambah Barang -->
            <button type="button" id="btn-add-item" class="mt-4 px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs uppercase tracking-wider rounded-lg transition-colors border border-blue-200 inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Tambah Barang Baru
            </button>
        </div>

        <div class="mt-8 pt-5 border-t border-gray-100 flex justify-end">
            <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-lg shadow-sm transition-colors inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Kirim Pengajuan ke Admin IT
            </button>
        </div>
    </form>
</div>

<script>
    let itemIndex = 1;
    document.getElementById('btn-add-item').addEventListener('click', function() {
        const container = document.getElementById('items-container');
        const html = `
            <div class="flex flex-col sm:flex-row gap-3 item-row items-start sm:items-center">
                <div class="w-full sm:flex-1">
                    <input type="text" name="items[${itemIndex}][nama]" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all placeholder-gray-400" placeholder="Nama Barang" required>
                </div>
                <div class="w-full sm:w-48">
                    <input type="number" name="items[${itemIndex}][harga_satuan]" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all placeholder-gray-400" placeholder="Harga Satuan (Rp)" min="0" required>
                </div>
                <div class="w-full sm:w-32">
                    <input type="number" name="items[${itemIndex}][jumlah]" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all placeholder-gray-400" placeholder="Jumlah" min="1" required>
                </div>
                <!-- Tombol Hapus Baris -->
                <button type="button" class="btn-remove-item bg-red-50 hover:bg-red-100 text-red-600 p-2.5 rounded-lg transition-colors border border-red-100 flex-shrink-0" title="Hapus baris ini">
                    <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        itemIndex++;
    });

    document.getElementById('items-container').addEventListener('click', function(e) {
        // Logika untuk menghapus baris ketika tombol tong sampah diklik
        if(e.target.classList.contains('btn-remove-item') || e.target.closest('.btn-remove-item')) {
            e.target.closest('.item-row').remove();
        }
    });
</script>
@endsection