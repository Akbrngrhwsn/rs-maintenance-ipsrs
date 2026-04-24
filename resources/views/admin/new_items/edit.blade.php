<x-app-layout>
    <div class="max-w-5xl mx-auto py-10 px-4">
        <div class="bg-white p-8 rounded-xl shadow-sm border">
            <h2 class="text-2xl font-bold mb-8">Edit Pengadaan Barang Baru</h2>
            
            <form action="{{ route('admin.new_items.update', $procurement->id) }}" method="POST">
                @csrf @method('PATCH')

                <div class="mb-6 p-6 bg-blue-50 rounded-lg border border-blue-200">
                    <h3 class="font-bold text-blue-900 mb-4">Informasi Pengadaan</h3>
                    
                    <div class="mb-4">
                        <label class="block font-bold text-gray-700 mb-2">Tujuan Pengadaan</label>
                        <input type="text" name="purpose" value="{{ $procurement->purpose }}" required class="w-full rounded-lg border-gray-300 px-4 py-2 border">
                    </div>

                    <div class="mb-4">
                        <label class="block font-bold text-gray-700 mb-2">Status Approval</label>
                        <select name="status" class="w-full rounded-lg border-gray-300 px-4 py-2 border">
                            <option value="pending_admin" {{ $procurement->status == 'pending_admin' ? 'selected' : '' }}>Menunggu Admin</option>
                            <option value="pending_management" {{ $procurement->status == 'pending_management' ? 'selected' : '' }}>Menunggu Management</option>
                            <option value="approved" {{ $procurement->status == 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="rejected" {{ $procurement->status == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                </div>

                {{-- EDIT ITEMS BARANG --}}
                <div class="mb-6">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Daftar Barang
                    </h3>
                    
                    <div id="item-list" class="space-y-4">
                        @foreach($procurement->items as $index => $item)
                        <div class="item-card bg-gray-50 p-6 rounded-lg border border-gray-200 relative" id="item-{{ $index }}">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="font-bold text-gray-800">Barang #{{ $index + 1 }}</h4>
                                <button type="button" onclick="removeRow({{ $index }})" class="text-red-500 hover:text-red-700 font-bold">Hapus</button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                <div class="md:col-span-4">
                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nama/Jenis Barang</label>
                                    @php
                                        $nama_val = is_array($item['nama'] ?? null) ? implode(', ', (array)$item['nama']) : ($item['nama'] ?? '');
                                    @endphp
                                    <input type="text" name="items[{{ $index }}][nama]" required class="w-full text-sm border-gray-300 rounded-md px-3 py-2 border" value="{{ $nama_val }}" placeholder="Contoh: SSD 512GB">
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Merk/Tipe</label>
                                    @php
                                        $merk_val = is_array($item['merk'] ?? null) ? implode(', ', (array)$item['merk']) : ($item['merk'] ?? '');
                                    @endphp
                                    <input type="text" name="items[{{ $index }}][merk]" class="w-full text-sm border-gray-300 rounded-md px-3 py-2 border" value="{{ $merk_val }}" placeholder="Contoh: ASUS">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Jumlah</label>
                                    <input type="number" name="items[{{ $index }}][jumlah]" required min="1" class="w-full text-sm border-gray-300 rounded-md px-3 py-2 border" value="{{ $item['jumlah'] ?? 1 }}">
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Harga Satuan (Rp)</label>
                                    @php
                                        $harga_val = $item['harga_satuan'] ?? ($item['harga'] ?? 0);
                                    @endphp
                                    <input type="number" name="items[{{ $index }}][harga_satuan]" class="w-full text-sm border-gray-300 rounded-md px-3 py-2 border" value="{{ $harga_val }}" placeholder="0">
                                </div>
                                <div class="md:col-span-12">
                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Keterangan/Deskripsi</label>
                                    @php
                                        $desk_val = is_array($item['deskripsi'] ?? null) ? implode(', ', (array)$item['deskripsi']) : ($item['deskripsi'] ?? '');
                                    @endphp
                                    <input type="text" name="items[{{ $index }}][deskripsi]" class="w-full text-sm border-gray-300 rounded-md px-3 py-2 border" value="{{ $desk_val }}" placeholder="Keterangan tambahan (opsional)">
                                </div>
                            </div>
                        </div>
                        @endforeach
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

    <script>
        let counter = {{ count($procurement->items ?? []) }};

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