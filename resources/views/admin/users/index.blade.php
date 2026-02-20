<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Pengguna (Role)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900">
                {{-- Form tambah user baru --}}
                <div class="mb-6 border-b pb-4">
                    <h3 class="font-bold mb-2">Tambah Pengguna Baru</h3>
                    <form action="{{ route('admin.users.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                        @csrf
                        <div>
                            <label class="block text-sm font-bold mb-1">Nama</label>
                            <input name="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2" />
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-1">Email</label>
                            <input name="email" type="email" value="{{ old('email') }}" class="w-full border rounded px-3 py-2" />
                            <x-input-error :messages="$errors->get('email')" class="mt-1" />
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-1">Password</label>
                            <input name="password" type="password" class="w-full border rounded px-3 py-2" />
                            <x-input-error :messages="$errors->get('password')" class="mt-1" />
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-1">Role</label>
                            <select name="role" id="new_user_role" class="w-full border rounded px-3 py-2">
                                <option value="staff">Staff</option>
                                <option value="kepala_ruang">Kepala Ruang</option>
                                <option value="direktur">Direktur</option>
                                <option value="admin">Admin IT</option>
                                <option value="bendahara">Bendahara</option>
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-1" />
                        </div>

                        <div class="md:col-span-4" id="kepala_ruang_room_select" style="display:none;">
                            <label class="block text-sm font-bold mb-1">Pilih Ruangan untuk Kepala Ruang</label>
                            <select name="room_id" class="w-1/2 border rounded px-3 py-2">
                                <option value="">-- Tidak Ada --</option>
                                @foreach($rooms as $r)
                                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('room_id')" class="mt-1" />
                        </div>

                        <div class="md:col-span-4">
                            <button class="mt-2 bg-blue-800 text-white px-4 py-2 rounded">Buat Pengguna</button>
                        </div>
                    </form>
                </div>

                <script>
                    (function(){
                        const roleSel = document.getElementById('new_user_role');
                        const roomBlock = document.getElementById('kepala_ruang_room_select');
                        function update(){
                            if(roleSel.value === 'kepala_ruang') roomBlock.style.display = 'block'; else roomBlock.style.display = 'none';
                        }
                        roleSel.addEventListener('change', update);
                        update();
                    })();
                </script>

                <div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-bold">
                                <tr>
                                    <th class="px-4 py-3 text-left">Nama</th>
                                    <th class="px-4 py-3 text-left">Email</th>
                                    <th class="px-4 py-3 text-center">Role Saat Ini</th>
                                    <th class="px-4 py-3 text-center">Ubah Role</th>
                                    <th class="px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($users as $user)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 font-bold">{{ $user->name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $user->email }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $colors = [
                                                'admin' => 'bg-purple-100 text-purple-800',
                                                'direktur' => 'bg-blue-100 text-blue-800',
                                                'kepala_ruang' => 'bg-yellow-100 text-yellow-800',
                                                'staff' => 'bg-gray-100 text-gray-800',
                                                'bendahara' => 'bg-green-100 text-green-800',
                                            ];
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-xs font-bold {{ $colors[$user->role] ?? 'bg-gray-100' }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="flex items-center justify-center gap-2">
                                            @csrf @method('PATCH')
                                            <select name="role" class="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 py-1">
                                                <option value="staff" {{ $user->role == 'staff' ? 'selected' : '' }}>Staff</option>
                                                <option value="kepala_ruang" {{ $user->role == 'kepala_ruang' ? 'selected' : '' }}>Kepala Ruang</option>
                                                <option value="direktur" {{ $user->role == 'direktur' ? 'selected' : '' }}>Direktur</option>
                                                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin IT</option>
                                                <option value="bendahara" {{ $user->role == 'bendahara' ? 'selected' : '' }}>Bendahara</option>
                                            </select>
                                            <button type="submit" class="bg-blue-600 text-white p-1.5 rounded hover:bg-blue-700 transition" title="Simpan Perubahan">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($user->role === 'kepala_ruang')
                                            <form action="{{ route('admin.users.assignRoom', $user->id) }}" method="POST" class="flex items-center justify-center gap-2">
                                                @csrf @method('PATCH')
                                                <select name="room_id" class="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 py-1">
                                                    <option value="">-- Tidak Ada --</option>
                                                    @foreach($rooms as $r)
                                                        <option value="{{ $r->id }}" {{ $r->kepala_ruang_id == $user->id ? 'selected' : '' }}>{{ $r->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="bg-green-600 text-white p-1.5 rounded hover:bg-green-700 transition" title="Tetapkan Kepala ke Ruangan">
                                                    Simpan
                                                </button>
                                            </form>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-bold">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>