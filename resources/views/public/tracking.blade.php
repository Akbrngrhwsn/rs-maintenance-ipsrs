<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        
        {{-- Header & Search --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 pb-4 border-b border-gray-200 gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">Daftar Laporan Masuk</h2>
                <p class="text-gray-500 mt-1">Pantau status perbaikan. Urutan berdasarkan <b class="text-red-600">Urgensi & Waktu Masuk</b>.</p>
            </div>
            
            <div class="flex flex-col md:flex-row gap-2 w-full md:w-auto">
                <form action="{{ route('public.tracking') }}" method="GET" class="flex w-full md:w-80">
                    <input type="text" name="ticket" placeholder="Cari No. Tiket..." value="{{ request('ticket') }}" 
                        class="w-full rounded-l-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-r-lg hover:bg-blue-700 transition">
                        Cari
                    </button>
                </form>

                {{-- Tombol Export (hanya untuk authenticated user) --}}
                @auth
                    <button onclick="openExportModal()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition font-semibold text-sm">
                        📥 Unduh Riwayat
                    </button>
                @endauth
            </div>
        </div>

        {{-- BANNER TEKNISI JAGA (TAMBAHAN) --}}
        @if($onDutyStaff)
        <div class="mb-8 bg-blue-50 border border-blue-200 rounded-2xl p-4 flex items-center gap-4 shadow-sm">
            <div class="bg-blue-600 p-2.5 rounded-xl text-white shadow-md">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <div>
                <p class="text-[10px] text-blue-600 font-bold uppercase tracking-widest">Teknisi IT Standby Saat Ini:</p>
                <p class="text-lg font-black text-blue-900 leading-tight">{{ $onDutyStaff->nama }}</p>
            </div>
        </div>
        @endif

        {{-- BAGIAN 1: PENDING / BELUM SELESAI (Tetap Menggunakan Card Grid) --}}
        <div class="mb-12">
            <div class="flex items-center gap-3 mb-5">
                <h3 class="text-xl font-bold text-gray-800">Prioritas Pengerjaan</h3>
                <span class="bg-red-100 text-red-800 text-xs font-bold px-2 py-1 rounded-full">{{ $pendingReports->total() }} Antrian</span>
            </div>

            @if($pendingReports->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($pendingReports as $report)
                        <div class="group bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-xl hover:border-blue-300 transition-all duration-300 flex flex-col h-full relative overflow-hidden">
                            
                            {{-- Indikator Warna Samping --}}
                            <div class="absolute left-0 top-0 bottom-0 w-1.5 
                                {{ $report->urgency == 'tinggi' ? 'bg-red-600' : ($report->urgency == 'sedang' ? 'bg-yellow-400' : 'bg-green-400') }}">
                            </div>

                            <div class="p-5 pl-6 flex flex-col h-full">
                                {{-- Header Card --}}
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex flex-col gap-1">
                                        @if($report->ticket_number)
                                            <span class="bg-gray-100 text-gray-600 text-[10px] font-mono px-2 py-0.5 rounded border w-fit select-all">
                                                {{ $report->ticket_number }}
                                            </span>
                                        @endif
                                        <div class="flex gap-2 mt-1">
                                            @php
                                                $statusColor = 'bg-gray-100 text-gray-600';
                                                if($report->status == 'Diproses') $statusColor = 'bg-blue-100 text-blue-700';
                                                elseif($report->status == 'Belum Diproses') $statusColor = 'bg-amber-100 text-amber-700';
                                            @endphp
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded border uppercase tracking-wider {{ $statusColor }}">
                                                {{ $report->status }}
                                            </span>
                                            
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded border uppercase tracking-wider
                                                {{ $report->urgency == 'tinggi' ? 'bg-red-50 text-red-700 border-red-200 animate-pulse' : 
                                                  ($report->urgency == 'sedang' ? 'bg-yellow-50 text-yellow-700 border-yellow-200' : 'bg-green-50 text-green-700 border-green-200') }}">
                                                {{ ucfirst($report->urgency) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-right flex flex-col items-end">
                                        <p class="text-xs text-gray-400">Masuk:</p>
                                        <p class="text-xs font-bold text-gray-600">{{ $report->created_at->format('d M H:i') }}</p>
                                        <p class="text-[10px] text-red-500 italic mt-0.5 mb-1">
                                            {{ $report->created_at->diffForHumans() }}
                                        </p>
                                        
                                        {{-- INDIKATOR DIBACA (ALA WHATSAPP) --}}
                                        @if($report->is_read_by_admin)
                                            <div class="flex items-center gap-1 mt-1 text-blue-500 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-100" title="Sudah dilihat Admin IT">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 17l4 4L15 11" opacity="0.5"></path>
                                                </svg>
                                                <span class="text-[9px] font-bold uppercase tracking-wider">Dibaca</span>
                                            </div>
                                        @else
                                            <div class="flex items-center gap-1 mt-1 text-gray-400 bg-gray-50 px-2 py-0.5 rounded-full border border-gray-200" title="Belum dilihat Admin IT">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span class="text-[9px] font-bold uppercase tracking-wider">Terkirim</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Isi Laporan --}}
                                <h3 class="font-bold text-lg text-gray-800 line-clamp-1 mb-1">{{ $report->ruangan }}</h3>
                                <p class="text-gray-600 text-sm mb-3 flex-1 line-clamp-3">{{ $report->keluhan }}</p>

                                @if($report->urgency_reason)
                                    <p class="text-xs text-gray-500 italic bg-gray-50 p-2 rounded mb-3 border border-gray-100">
                                        "{{ Str::limit($report->urgency_reason, 60) }}"
                                    </p>
                                @endif

                                <div class="mt-auto pt-3 border-t border-gray-100 flex justify-between items-center text-[11px]">
                                    <span class="text-gray-400">Oleh: {{ $report->pelapor_nama ?? 'Anonim' }}</span>
                                    
                                    {{-- INFO TEKNISI PADA CARD --}}
                                    @if($report->itStaff)
                                        <span class="font-bold text-blue-600 italic flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h.01a1 1 0 100-2H10zm3 0a1 1 0 000 2h.01a1 1 0 100-2H13zM7 13a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h.01a1 1 0 100-2H10zm3 0a1 1 0 000 2h.01a1 1 0 100-2H13z" clip-rule="evenodd"></path></svg>
                                            {{ $report->itStaff->nama }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-6 px-4">
                    {{ $pendingReports->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-10 bg-green-50 rounded-xl border border-green-100">
                    <p class="text-green-600 font-medium">Tidak ada laporan yang tertunda. Semua aman!</p>
                </div>
            @endif
        </div>

        {{-- BAGIAN 2: SELESAI / HISTORY (DIUBAH MENJADI TABEL) --}}
        <div>
            <div class="flex items-center gap-3 mb-5 border-t pt-8">
                <h3 class="text-xl font-bold text-gray-500">Riwayat Selesai</h3>
                <span class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-1 rounded-full">{{ $completedReports->total() }} Laporan</span>
            </div>

            @if($completedReports->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead class="bg-gray-50 text-xs font-bold text-gray-400 uppercase tracking-wider">
                                <tr>
                                    <th class="px-6 py-4 text-left">Tanggal</th>
                                    <th class="px-6 py-4 text-left">Ruangan / Tiket</th>
                                    <th class="px-6 py-4 text-left">Keluhan</th>
                                    <th class="px-6 py-4 text-left">Keterangan</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($completedReports as $report)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $report->created_at->format('d M Y') }}
                                            <div class="text-xs text-gray-400">{{ $report->created_at->format('H:i') }} WIB</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-800">{{ $report->ruangan }}</div>
                                            @if($report->ticket_number)
                                                <div class="text-xs font-mono text-gray-400 select-all">{{ $report->ticket_number }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" title="{{ $report->keluhan }}">
                                            {{ Str::limit($report->keluhan, 50) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 max-w-xs">
                                            <div class="italic truncate" title="{{ $report->tindakan_teknisi }}">{{ $report->tindakan_teknisi ?? '-' }}</div>
                                            
                                            {{-- NAMA PENINDAK LANJUT DI RIWAYAT --}}
                                            @if($report->itStaff)
                                                <div class="text-[10px] font-bold text-blue-500 mt-1 uppercase tracking-tighter">
                                                    Penangan: {{ $report->itStaff->nama }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($report->status == 'Selesai')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    ✅ Selesai
                                                </span>
                                            @elseif($report->status == 'Ditolak')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    ⛔ Ditolak
                                                </span>
                                            @elseif($report->status == 'Tidak Selesai')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                    📦 Pengadaan
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $report->status }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Pagination Links --}}
                <div class="mt-6 px-4">
                    {{ $completedReports->appends(request()->query())->links() }}
                </div>
            @else
                @if(request('ticket') == '')
                    <div class="text-center py-10 border border-dashed border-gray-300 rounded-xl bg-gray-50">
                        <p class="text-gray-400 text-sm">Belum ada riwayat laporan selesai.</p>
                    </div>
                @else
                    <div class="text-center py-10 border border-dashed border-gray-300 rounded-xl bg-gray-50">
                        <p class="text-gray-400 text-sm">Tiket tidak ditemukan di riwayat.</p>
                    </div>
                @endif
            @endif
        </div>

    </div>

    {{-- MODAL EXPORT RIWAYAT KERUSAKAN --}}
    @auth
    <div id="export-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-gray-900 opacity-60" onclick="closeExportModal()"></div>
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full relative z-10 overflow-hidden m-4">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-lg text-gray-800">Unduh Riwayat Kerusakan</h3>
                <button type="button" onclick="closeExportModal()" class="text-gray-400 hover:text-red-500">✕</button>
            </div>
            <form action="{{ route('tracking.export.monthly') }}" method="GET">
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Bulan & Tahun</label>
                        <input type="month" name="month" value="{{ date('Y-m') }}" 
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 text-sm" required />
                    </div>

                    {{-- ROOM SELECTION (Hanya untuk Kepala Ruang dengan Multiple Rooms) --}}
                    @auth
                        @if(Auth::user()->role === 'kepala_ruang')
                            @php
                                $userRooms = Auth::user()->rooms()->orderBy('name')->get();
                            @endphp
                            @if($userRooms->count() > 1)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Ruangan (Optional)</label>
                                    <p class="text-xs text-gray-500 mb-2">Jika tidak dipilih, akan mencakup semua ruangan Anda</p>
                                    <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-200 rounded-md p-3 bg-gray-50">
                                        @foreach($userRooms as $room)
                                            <label class="flex items-center gap-2 cursor-pointer hover:bg-white p-2 rounded transition">
                                                <input type="checkbox" name="room_ids[]" value="{{ $room->id }}" 
                                                    class="rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer" />
                                                <span class="text-sm text-gray-700">{{ $room->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @elseif($userRooms->count() === 1)
                                <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                                    <p class="text-xs font-medium text-blue-700">
                                        📍 Ruangan: <strong>{{ $userRooms->first()->name }}</strong>
                                    </p>
                                    <input type="hidden" name="room_ids[]" value="{{ $userRooms->first()->id }}" />
                                </div>
                            @endif
                        @endif
                    @endauth

                    <p class="text-xs text-gray-500 italic">
                        📌 Laporan akan mencakup laporan kerusakan yang diselesaikan (Selesai, Ditolak, Pengadaan) pada bulan yang dipilih.
                    </p>
                </div>
                <div class="px-6 py-4 border-t bg-gray-50 text-right space-x-2">
                    <button type="button" onclick="closeExportModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-200">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-bold hover:bg-green-700">
                        Unduh PDF
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openExportModal() {
            document.getElementById('export-modal').classList.remove('hidden');
        }

        function closeExportModal() {
            document.getElementById('export-modal').classList.add('hidden');
        }
    </script>
    @endauth

</x-app-layout>