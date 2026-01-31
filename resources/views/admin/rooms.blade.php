<x-app-layout>
    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-5xl mx-auto"> {{-- Diperlebar sedikit --}}
            <h2 class="text-2xl font-bold mb-4">Manajemen Ruangan</h2>

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 rounded text-green-700 font-medium">
                    {{ session('success') }}
                </div>
            @endif
            
            @if($errors->any())
                <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded text-red-700">
                    <ul class="list-disc ml-4">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow rounded p-4">
                {{-- Form Tambah --}}
                <div class="mb-6 border-b pb-4">
                    <h3 class="text-lg font-semibold mb-2 text-gray-700">Tambah Ruangan Baru</h3>
                    <form method="POST" action="{{ route('admin.rooms.store') }}" class="flex gap-2 items-center">
                        @csrf
                        <input name="name" placeholder="Nama ruangan..." class="border-gray-300 rounded-lg px-3 py-2 w-72 focus:ring-blue-500 focus:border-blue-500 shadow-sm" required />
                        <button class="bg-blue-800 hover:bg-blue-900 text-white px-4 py-2 rounded-lg font-bold transition">
                            + Tambah
                        </button>
                    </form>
                </div>

                <table class="w-full table-auto">
                    <thead>
                        <tr class="text-left bg-gray-50 text-gray-600 uppercase text-xs tracking-wider">
                            <th class="py-3 px-2">Nama Ruangan</th>
                            <th class="py-3 px-2">Manager</th>
                            <th class="py-3 px-2 text-center">Status</th>
                            <th class="py-3 px-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($rooms as $room)
                        {{-- Gunakan x-data untuk fitur Edit Nama --}}
                        <tr x-data="{ editingName: false, tempName: '{{ $room->name }}' }" class="hover:bg-gray-50 transition">
                            
                            {{-- KOLOM 1: Nama Ruangan (Bisa Edit) --}}
                            <td class="py-3 px-2 align-middle">
                                {{-- Tampilan Normal --}}
                                <div x-show="!editingName" class="flex items-center gap-2">
                                    <span class="font-medium text-gray-800">{{ $room->name }}</span>
                                    {{-- Tombol Edit Kecil (Icon Pencil) --}}
                                    <button @click="editingName = true" class="text-gray-400 hover:text-blue-600 transition" title="Edit Nama">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                </div>

                                {{-- Form Edit (Muncul saat diklik) --}}
                                <div x-show="editingName" class="flex items-center gap-1" style="display: none;">
                                    <form method="POST" action="{{ route('admin.rooms.update', $room->id) }}" class="flex gap-1">
                                        @csrf @method('PATCH')
                                        <input type="text" name="name" x-model="tempName" class="text-sm border-gray-300 rounded px-2 py-1 w-40 shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                                        <button type="submit" class="bg-green-600 text-white p-1 rounded hover:bg-green-700" title="Simpan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        </button>
                                    </form>
                                    <button @click="editingName = false; tempName='{{ $room->name }}'" class="bg-gray-300 text-gray-700 p-1 rounded hover:bg-gray-400" title="Batal">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                            </td>

                            {{-- KOLOM 2: Manager (Tidak Berubah) --}}
                            <td class="py-3 px-2 align-middle">{{ $room->manager?->name ?? '-' }}</td>

                            {{-- KOLOM 3: Jumlah Manager (Tidak Berubah) --}}
                            <td class="py-3 px-2 text-center align-middle">
                                <span class="px-2 py-1 rounded-full text-xs font-bold {{ $room->manager ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $room->managers_count > 0 ? 'Terisi' : 'Kosong' }}
                                </span>
                            </td>

                            {{-- KOLOM 4: Aksi (Update Manager & Hapus) --}}
                            <td class="py-3 px-2 align-middle">
                                <div class="flex items-center gap-2">
                                    {{-- Form Update Manager --}}
                                    <form method="POST" action="{{ route('admin.rooms.update', $room->id) }}" class="flex items-center gap-2">
                                        @csrf @method('PATCH')
                                        <select name="manager_id" class="text-sm rounded border-gray-300 py-1 px-2 w-40 focus:ring-blue-500 focus:border-blue-500 cursor-pointer">
                                            <option value="">-- Set Manager --</option>
                                            @foreach($managers as $m)
                                                <option value="{{ $m->id }}" @if($room->manager_id == $m->id) selected @endif>
                                                    {{ Str::limit($m->name, 15) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="bg-blue-800 hover:bg-blue-900 text-white text-xs px-3 py-1.5 rounded font-bold transition">
                                            Simpan
                                        </button>
                                    </form>

                                    {{-- Tombol Hapus --}}
                                    <form method="POST" action="{{ route('admin.rooms.destroy', $room->id) }}" onsubmit="return confirm('Yakin ingin menghapus ruangan {{ $room->name }}? Data laporan terkait mungkin akan terpengaruh.');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 p-1 transition" title="Hapus Ruangan">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
                @if($rooms->isEmpty())
                    <div class="text-center py-8 text-gray-500 italic">Belum ada data ruangan.</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>