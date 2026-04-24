<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">Edit Pengadaan: {{ $app->nama_aplikasi }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100 p-8">
                
                <div class="mb-8 p-6 bg-blue-50 rounded-lg border border-blue-200">
                    <h3 class="text-sm font-bold text-blue-900 uppercase mb-4">Informasi Proyek Aplikasi</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs text-gray-600 font-semibold uppercase">Nama Aplikasi</p>
                            <p class="font-bold text-blue-900 mt-1">{{ $app->nama_aplikasi }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 font-semibold uppercase">Peminta</p>
                            <p class="font-bold text-blue-900 mt-1">{{ $app->user->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 font-semibold uppercase">Status Pengadaan</p>
                            <p class="font-bold text-blue-900 mt-1">{{ $app->procurement_approval_status }}</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.apps.update-procurement', $app->id) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    {{-- DAFTAR BARANG --}}
                    <div class="mb-8">
                        <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Daftar Barang yang Dibutuhkan
                        </h3>
                        
                        <div id="item-list" class="space-y-4">
                            @forelse($app->requested_items ?? [] as $index => $item)
                            <div class="item-card bg-gray-50 p-6 rounded-lg border border-gray-200 relative" id="item-{{ $index }}">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="font-bold text-gray-800">Barang #{{ $index + 1 }}</h4>
                                    <button type="button" onclick="removeRow({{ $index }})" class="text-red-500 hover:text-red-700 font-bold">Hapus</button>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                    <div class="md:col-span-4">
                                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nama/Jenis Barang</label>
                                        @php
                                            $nama_val = is_array($item['nama'] ?? null) ? implode(', ', (array)$item['nama']) : ($item['nama'] ?? ($item['name'] ?? ''));
                                        @endphp
                                        <input type="text" name="items[{{ $index }}][nama]" required class="w-full text-sm border-gray-300 rounded-md px-3 py-2 border" value="{{ $nama_val }}" placeholder="Contoh: SSD 512GB">
                                    </div>
                                    <div class="md:col-span-3">
                                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Merk/Tipe</label>
                                        @php
                                            $merk_val = is_array($item['merk'] ?? null) ? implode(', ', (array)$item['merk']) : ($item['merk'] ?? ($item['brand'] ?? ''));
                                        @endphp
                                        <input type="text" name="items[{{ $index }}][merk]" class="w-full text-sm border-gray-300 rounded-md px-3 py-2 border" value="{{ $merk_val }}" placeholder="Contoh: ASUS">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Jumlah</label>
                                        @php
                                            $qty_val = $item['jumlah'] ?? ($item['qty'] ?? 1);
                                        @endphp
                                        <input type="number" name="items[{{ $index }}][jumlah]" required min="1" class="w-full text-sm border-gray-300 rounded-md px-3 py-2 border" value="{{ $qty_val }}">
                                    </div>
                                    <div class="md:col-span-3">
                                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Harga Satuan (Rp)</label>
                                        @php
                                            $harga_val = $item['harga_satuan'] ?? ($item['unit_price'] ?? ($item['harga'] ?? 0));
                                        @endphp
                                        <input type="number" name="items[{{ $index }}][harga_satuan]" class="w-full text-sm border-gray-300 rounded-md px-3 py-2 border" value="{{ $harga_val }}" placeholder="0">
                                    </div>
                                    <div class="md:col-span-12">
                                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Keterangan/Deskripsi</label>
                                        @php
                                            $desk_val = is_array($item['deskripsi'] ?? null) ? implode(', ', (array)$item['deskripsi']) : ($item['deskripsi'] ?? ($item['description'] ?? ''));
                                        @endphp
                                        <input type="text" name="items[{{ $index }}][deskripsi]" class="w-full text-sm border-gray-300 rounded-md px-3 py-2 border" value="{{ $desk_val }}" placeholder="Keterangan tambahan (opsional)">
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-8 text-gray-400">
                                <p>Belum ada barang yang ditambahkan</p>
                            </div>
                            @endforelse
                        </div>

                        <div class="mt-4">
                            <button type="button" onclick="addRow()" class="px-4 py-2 bg-blue-100 text-blue-700 rounded-md font-bold hover:bg-blue-200 transition">
                                + Tambah Barang
                            </button>
                        </div>
                    </div>

                    {{-- TOMBOL AKSI --}}
                    <div class="flex justify-end gap-3 pt-6 border-t">
                        <a href="{{ route('admin.procurements.index') }}" class="px-6 py-2 text-gray-700 rounded-lg font-bold hover:bg-gray-100 transition border border-gray-300">Batal</a>
                        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg font-bold hover:bg-green-700 transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let counter = {{ count($app->requested_items ?? []) }};

        function addRow() {
            const container = document.getElementById('item-list');
            const displayNum = counter + 1;
            const newRow = `
                <div class="item-card bg-gray-50 p-6 rounded-lg border border-gray-200 relative" id="item-${counter}">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="font-bold text-gray-800">Barang #${displayNum}</h4>
                        <button type="button" onclick="removeRow(${counter})" class="text-red-500 hover:text-red-700 font-bold">Hapus</button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                        <div class="md:col-span-4">
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nama/Jenis Barang</label>
                            <input type="text" name="items[${counter}][nama]" required class="w-full text-sm border-gray-300 rounded-md px-3 py-2 border" placeholder="Contoh: SSD 512GB">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Merk/Tipe</label>
                            <input type="text" name="items[${counter}][merk]" class="w-full text-sm border-gray-300 rounded-md px-3 py-2 border" placeholder="Contoh: ASUS">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Jumlah</label>
                            <input type="number" name="items[${counter}][jumlah]" required min="1" class="w-full text-sm border-gray-300 rounded-md px-3 py-2 border">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Harga Satuan (Rp)</label>
                            <input type="number" name="items[${counter}][harga_satuan]" class="w-full text-sm border-gray-300 rounded-md px-3 py-2 border" placeholder="0">
                        </div>
                        <div class="md:col-span-12">
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Keterangan/Deskripsi</label>
                            <input type="text" name="items[${counter}][deskripsi]" class="w-full text-sm border-gray-300 rounded-md px-3 py-2 border" placeholder="Keterangan tambahan (opsional)">
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
