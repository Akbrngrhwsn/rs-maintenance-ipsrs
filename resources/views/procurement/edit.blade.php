<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-blue-900 leading-tight">{{ __('Edit Pengadaan Barang') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100 p-8">
                <div class="mb-8 p-4 bg-gray-50 rounded-xl border border-gray-100">
                    <h3 class="text-sm font-bold text-gray-500 uppercase mb-2">Detail Laporan:</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-400">Nomor Tiket</p>
                            <p class="font-mono font-bold">{{ $proc->report->ticket_number }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Ruangan</p>
                            <p class="font-bold">{{ $proc->report->ruangan }}</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('procurement.update', $proc->id) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div id="item-list" class="space-y-6">
                        @foreach($proc->items as $index => $item)
                        <div class="item-card bg-gray-50/50 p-6 rounded-2xl border border-gray-200 relative" id="item-{{ $index }}">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="item-number font-bold text-blue-900 uppercase text-sm tracking-wider">Pengajuan Barang #{{ $index+1 }}</h3>
                                <button type="button" onclick="removeRow({{ $index }})" class="text-red-400 hover:text-red-600 transition">Hapus</button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="md:col-span-4">
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Nama/Jenis Barang</label>
                                    <input type="text" name="items[{{ $index }}][nama]" required class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500" value="{{ $item['nama'] ?? '' }}" placeholder="Contoh: SSD 512GB">
                                </div>
                                <div class="md:col-span-4">
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Merk/Tipe</label>
                                    <input type="text" name="items[{{ $index }}][merk]" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500" value="{{ $item['merk'] ?? ($item['spek'] ?? '') }}" placeholder="Contoh: ASUS">
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Jml</label>
                                    <input type="number" name="items[{{ $index }}][jumlah]" required min="1" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500" value="{{ $item['jumlah'] ?? 1 }}">
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Estimasi Harga Satuan (Rp)</label>
                                    <input type="number" name="items[{{ $index }}][harga_satuan]" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500" value="{{ $item['harga_satuan'] ?? ($item['harga'] ?? ($item['biaya'] ?? 0)) }}" placeholder="0">
                                </div>
                                <div class="md:col-span-12 mt-3">
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Deskripsi</label>
                                    <input type="text" name="items[{{ $index }}][deskripsi]" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500" value="{{ $item['deskripsi'] ?? '' }}" placeholder="Keterangan tambahan (opsional)">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        <button type="button" onclick="addRow()" class="text-blue-600 font-bold">Tambah Barang</button>
                    </div>

                    <div class="mt-8 flex flex-col md:flex-row justify-between items-center gap-4 border-t pt-6">
                        <a href="{{ route('dashboard') }}" class="flex-1 md:flex-none text-center px-6 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Batal</a>
                        <button type="submit" class="flex-1 md:flex-none bg-red-600 text-white px-8 py-2 rounded-xl font-bold hover:bg-red-700 shadow-lg shadow-red-100 transition">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let counter = {{ count($proc->items) }};

        function addRow() {
            const container = document.getElementById('item-list');
            const displayNum = counter + 1;
            const newRow = `
                <div class="item-card bg-gray-50/50 p-6 rounded-2xl border border-gray-200 relative mt-6" id="item-${counter}">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="item-number font-bold text-blue-900 uppercase text-sm tracking-wider">Pengajuan Barang #${displayNum}</h3>
                        <button type="button" onclick="removeRow(${counter})" class="text-red-400 hover:text-red-600 transition">Hapus</button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-4">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Nama/Jenis Barang</label>
                            <input type="text" name="items[${counter}][nama]" required class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500" placeholder="Contoh: SSD 512GB">
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Merk/Tipe</label>
                            <input type="text" name="items[${counter}][merk]" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500" placeholder="Contoh: ASUS">
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Jml</label>
                            <input type="number" name="items[${counter}][jumlah]" required min="1" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Estimasi Harga Satuan (Rp)</label>
                            <input type="number" name="items[${counter}][harga_satuan]" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500" placeholder="0">
                        </div>
                        <div class="md:col-span-12 mt-3">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Deskripsi</label>
                            <input type="text" name="items[${counter}][deskripsi]" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500" placeholder="Keterangan tambahan (opsional)">
                        </div>
                    </div>
                </div>`;
            container.insertAdjacentHTML('beforeend', newRow);
            counter++;
        }

        function removeRow(id) {
            const row = document.getElementById(`item-${id}`);
            if(row) row.remove();
        }
    </script>
</x-app-layout>
