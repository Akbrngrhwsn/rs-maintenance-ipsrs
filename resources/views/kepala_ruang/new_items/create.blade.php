@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <div class="bg-white rounded shadow p-6">
        <h2 class="text-2xl font-bold mb-4">Pengajuan Pengadaan Barang Baru</h2>
        <p class="text-gray-600 mb-6">Gunakan form ini untuk mengajukan barang baru yang bukan merupakan perbaikan (contoh: PC untuk pegawai baru).</p>

        <form action="{{ route('kepala-ruang.new_items.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Deskripsi Pengadaan</label>
                <input type="text" name="purpose" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required placeholder="Contoh: Komputer untuk admin ruangan baru">
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