<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <h2 class="font-bold text-2xl text-blue-900 leading-tight">
                {{ __('Rapat Internal Divisi') }}
            </h2>
            <span class="text-sm text-gray-500 mt-2 md:mt-0 bg-white px-3 py-1 rounded-full shadow-sm border border-gray-100">
                📅 {{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Notifikasi --}}
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl shadow-sm flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Form Buat Rapat --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </span>
                    <h3 class="text-lg font-bold text-gray-800">Buat Rapat Baru</h3>
                </div>

                <div class="p-6">
                    <form method="POST" action="{{ route('meetings.store') }}">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Judul Rapat</label>
                                <input name="title" required placeholder="Contoh: Koordinasi Mingguan" 
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tanggal Rapat</label>
                                <input type="date" name="meeting_date" required 
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Divisi / Penanggung Jawab</label>
                                @if(Auth::user() && Auth::user()->role === 'admin')
                                    <select id="division_role_role" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                        <option value="admin">admin</option>
                                        <option value="direktur">direktur</option>
                                        <option value="kepala ruang">kepala ruang</option>
                                        <option value="bendahara">bendahara</option>
                                        <option value="staff">staff</option>
                                    </select>
                                    <select id="division_room_select" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm mt-2" style="display:none;" aria-hidden="true">
                                        @foreach($rooms as $r)
                                            <option value="{{ $r->name }}">{{ $r->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="division_role" id="division_role_input" value="admin" />
                                @else
                                    @php
                                        $divisionDisplay = Auth::user()->role;
                                        if(Auth::user()->role === 'kepala_ruang' && Auth::user()->room) {
                                            $divisionDisplay = Auth::user()->room->name;
                                        }
                                    @endphp
                                    <input type="text" readonly value="{{ $divisionDisplay }}" 
                                        class="w-full border-gray-200 rounded-lg bg-gray-50 text-gray-500 text-sm cursor-not-allowed" />
                                @endif
                            </div>
                        </div>

                        <div class="mt-6">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Notulensi / Hasil Rapat</label>
                            <textarea name="minutes" rows="4" required placeholder="Tuliskan poin-poin hasil rapat di sini..."
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button class="inline-flex items-center gap-2 bg-blue-900 text-white px-6 py-2.5 rounded-lg text-sm font-bold hover:bg-blue-800 transition shadow-sm hover:shadow">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                Simpan Rapat
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Riwayat Rapat --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100">
        <h3 class="text-lg font-bold text-gray-800">Riwayat Rapat</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full table-auto text-left">
            <thead class="bg-gray-50 border-b border-gray-100 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-4">ID</th>
                    <th class="px-6 py-4">Informasi Rapat</th>
                    <th class="px-6 py-4">Oleh</th>
                    <th class="px-6 py-4">Dibuat Pada</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($meetings as $m)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 font-mono">
                            #{{ $m->id }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900">{{ $m->title }}</div>
                            <div class="text-xs text-blue-600 mt-1 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                {{ \Carbon\Carbon::parse($m->meeting_date)->translatedFormat('d F Y') }}
                            </div>
                        </td>
                        <<td class="px-6 py-4 whitespace-nowrap">
    <span class="px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 text-xs font-bold border border-blue-100 uppercase">
        {{ $m->user->name ?? 'SISTEM' }}
    </span>
</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $m->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center space-x-1.5">
                            <a href="{{ route('meetings.show', $m->id) }}" class="inline-flex items-center px-3 py-1.5 bg-[#10b981] hover:bg-emerald-600 rounded-md font-bold text-[10px] text-white uppercase tracking-wide transition-colors">
                                Lihat
                            </a>
                            <a href="{{ route('meetings.export', $m->id) }}" class="inline-flex items-center px-3 py-1.5 bg-[#d97706] hover:bg-amber-700 rounded-md font-bold text-[10px] text-white uppercase tracking-wide transition-colors">
                                PDF
                            </a>
                            <a href="{{ route('meetings.edit', $m->id) }}" class="inline-flex items-center px-3 py-1.5 bg-[#4f46e5] hover:bg-indigo-700 rounded-md font-bold text-[10px] text-white uppercase tracking-wide transition-colors">
                                Edit
                            </a>
                            <form action="{{ route('meetings.destroy', $m->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus rapat ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="inline-flex items-center px-3 py-1.5 bg-[#e11d48] hover:bg-rose-700 rounded-md font-bold text-[10px] text-white uppercase tracking-wide transition-colors">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic text-sm">
                            Belum ada data rapat tersimpan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

                @if($meetings->hasPages())
                    <div class="p-4 border-t border-gray-100 bg-gray-50/30">
                        {{ $meetings->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function(){
        var roleSelect = document.getElementById('division_role_role');
        var roomSelect = document.getElementById('division_room_select');
        var hidden = document.getElementById('division_role_input');
        if(!roleSelect) return;

        function updateHidden(){
            var role = roleSelect.value;
            if(role === 'kepala_ruang'){
                roomSelect.style.display = 'block';
                roomSelect.removeAttribute('aria-hidden');
                hidden.value = roomSelect.value || '';
            } else {
                roomSelect.style.display = 'none';
                roomSelect.setAttribute('aria-hidden','true');
                hidden.value = role;
            }
        }

        roleSelect.addEventListener('change', updateHidden);
        if(roomSelect){
            roomSelect.addEventListener('change', function(){ hidden.value = roomSelect.value; });
        }
        updateHidden();
    });
    </script>
</x-app-layout>