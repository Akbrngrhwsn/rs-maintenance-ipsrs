<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        
        {{-- Header & Search --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 pb-4 border-b border-gray-200 gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">Daftar Laporan Masuk</h2>
                <p class="text-gray-500 mt-1">Pantau status perbaikan. Urutan berdasarkan <b class="text-red-600">Urgensi & Waktu Masuk</b>.</p>
            </div>
            
            <form action="{{ route('public.tracking') }}" method="GET" class="flex w-full md:w-80">
                <input type="text" name="ticket" placeholder="Cari No. Tiket..." value="{{ request('ticket') }}" 
                    class="w-full rounded-l-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-r-lg hover:bg-blue-700 transition">
                    Cari
                </button>
            </form>
        </div>

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
                                    <div class="text-right">
                                        <p class="text-xs text-gray-400">Masuk:</p>
                                        <p class="text-xs font-bold text-gray-600">{{ $report->created_at->format('d M H:i') }}</p>
                                        <p class="text-[10px] text-red-500 italic mt-1">
                                            {{ $report->created_at->diffForHumans() }}
                                        </p>
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

                                <div class="mt-auto pt-3 border-t border-gray-100 flex justify-between items-center text-xs text-gray-400">
                                    <span>Oleh: {{ $report->pelapor_nama ?? 'Anonim' }}</span>
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
                                    <th class="px-6 py-4 text-left">Tindakan Teknisi</th>
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
                                        <td class="px-6 py-4 text-sm text-gray-600 italic max-w-xs truncate">
                                            {{ $report->tindakan_teknisi ?? '-' }}
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
</x-app-layout>