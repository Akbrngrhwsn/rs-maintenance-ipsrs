@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <div class="bg-white rounded shadow p-6">
        <h2 class="text-2xl font-bold mb-4">Pengajuan Pengadaan Barang Baru</h2>
        <p class="text-gray-600 mb-6">Gunakan form ini untuk mengajukan barang baru yang bukan merupakan perbaikan (contoh: PC untuk pegawai baru).</p>

        {{-- Wajib: BLOK PENAMPIL ERROR --}}
        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
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

        <form action="{{ route('kepala-ruang.new_items.store') }}" method="POST">
            @csrf

            <!-- RUANGAN OTOMATIS -->
            @php
                // Mengambil ruangan pertama yang dimiliki oleh Kepala Ruang
                $userRoom = Auth::user()->rooms->first(); 
            @endphp

            @if($userRoom)
                <div class="mb-4">
                    <!-- Nilai ID ruangan yang sesungguhnya dikirim secara sembunyi-sembunyi ke Controller -->
                    <input type="hidden" name="room_id" value="{{ $userRoom->id }}">
                </div>
            @else
                <div class="mb-4 p-3 bg-red-50 text-red-700 rounded-md text-sm border border-red-200">
                    <strong>Peringatan:</strong> Akun Anda belum disambungkan dengan ruangan manapun. Silakan hubungi Admin.
                </div>
            @endif
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Deskripsi Pengadaan</label>
                <input type="text" name="purpose" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required placeholder="Contoh: Komputer untuk admin ruangan baru" value="{{ old('purpose') }}">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Deskripsi Pengadaan</label>
                <!-- Cek kembali: Apakah controller Anda butuh 'purpose' atau 'description'? -->
                <input type="text" name="purpose" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required placeholder="Contoh: Komputer untuk admin ruangan baru" value="{{ old('purpose') }}">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Daftar Barang</label>
                <div id="items-container">
                    <div class="flex gap-4 mb-2 item-row items-center">
                        <div class="flex-1">
                            <input type="text" name="items[0][nama]" class="block w-full rounded-md border-gray-300" placeholder="Nama Barang" required>
                        </div>
                        <div class="w-48">
                            <input type="number" name="items[0][harga_satuan]" class="block w-full rounded-md border-gray-300" placeholder="Harga Satuan (Rp)" min="0" required>
                        </div>
                        <div class="w-32">
                            <input type="number" name="items[0][jumlah]" class="block w-full rounded-md border-gray-300" placeholder="Jumlah" min="1" required>
                        </div>
                        <button type="button" class="btn-remove-item bg-red-500 text-white px-3 py-2 rounded hidden" title="Hapus baris">X</button>
                    </div>
                </div>
                <button type="button" id="btn-add-item" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded text-sm hover:bg-blue-600">+ Tambah Barang</button>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Kirim Pengajuan ke Admin IT
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let itemIndex = 1;
    document.getElementById('btn-add-item').addEventListener('click', function() {
        const container = document.getElementById('items-container');
        const html = `
            <div class="flex gap-4 mb-2 item-row items-center">
                <div class="flex-1">
                    <input type="text" name="items[${itemIndex}][nama]" class="block w-full rounded-md border-gray-300" placeholder="Nama Barang" required>
                </div>
                <div class="w-48">
                    <input type="number" name="items[${itemIndex}][harga_satuan]" class="block w-full rounded-md border-gray-300" placeholder="Harga Satuan (Rp)" min="0" required>
                </div>
                <div class="w-32">
                    <input type="number" name="items[${itemIndex}][jumlah]" class="block w-full rounded-md border-gray-300" placeholder="Jumlah" min="1" required>
                </div>
                <button type="button" class="btn-remove-item bg-red-500 text-white px-3 py-2 rounded" title="Hapus baris">X</button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        itemIndex++;
    });

    document.getElementById('items-container').addEventListener('click', function(e) {
        if(e.target.classList.contains('btn-remove-item')) {
            e.target.closest('.item-row').remove();
        }
    });
</script>
@endsection