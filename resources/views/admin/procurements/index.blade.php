<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">{{ __('Daftar Pengadaan') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-800">Semua Pengajuan Pengadaan</h3>
                </div>

                <div class="p-6 border-b border-gray-100">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 rounded-md bg-blue-600 text-white">Semua</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <form method="GET" action="{{ route('admin.procurements.index') }}" class="flex items-center gap-2">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tiket/ruangan/nama/merk" class="text-sm border-gray-300 rounded-md px-3 py-1">
                                <input type="date" name="date" value="{{ request('date') }}" class="text-sm border-gray-300 rounded-md px-2 py-1">
                                <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded-md text-sm">Cari</button>
                            </form>

                            <div class="flex items-center gap-2">
                                <button type="button" onclick="document.getElementById('export-modal').classList.remove('hidden')" class="px-3 py-1 bg-amber-600 text-white rounded-md text-sm">Unduh Bulanan (PDF)</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gray-50 text-xs font-bold text-gray-400 uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4 text-left">Tiket / Ruangan</th>
                                <th class="px-6 py-4 text-left">Detail Barang</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-right">Total Biaya</th>
                                <th class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            
                            @php
                                $hasProcurements = isset($procurements) && count($procurements) > 0;
                                $hasNewItems = isset($newItemRequests) && count($newItemRequests) > 0;
                            @endphp

                            @if(!$hasProcurements && !$hasNewItems)
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <span class="font-medium">Belum ada pengajuan pengadaan.</span>
                                        </div>
                                    </td>
                                </tr>
                            @else

                                {{-- ======================================================== --}}
                                {{-- 1. LOOPING PENGADAAN KERUSAKAN (MAINTENANCE)             --}}
                                {{-- ======================================================== --}}
                                @foreach($procurements as $proc)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 align-top">
                                            <div class="font-bold text-gray-800">{{ $proc->report->ruangan ?? '-' }}</div>
                                            <div class="text-xs font-mono text-gray-500">{{ $proc->report->ticket_number ?? '-' }}</div>
                                        </td>

                                        <td class="px-6 py-4 align-top text-sm text-gray-600">
                                            @php 
                                                $total = 0; 
                                                foreach($proc->items as $item) {
                                                    $qty = isset($item['jumlah']) ? (int)$item['jumlah'] : 1;
                                                    $price = isset($item['harga_satuan']) ? (float)$item['harga_satuan'] : (isset($item['harga']) ? (float)$item['harga'] : (isset($item['biaya']) ? (float)$item['biaya'] : 0));
                                                    $total += $price * $qty;
                                                }
                                            @endphp

                                            <div class="flex items-center gap-3">
                                                <button type="button" onclick="document.getElementById('modal-{{ $proc->id }}').classList.remove('hidden')" 
                                                    class="bg-blue-50 text-blue-600 border border-blue-200 px-3 py-1 rounded-md text-sm font-bold hover:bg-blue-100 transition">
                                                    Lihat Detail
                                                </button>
                                                <span class="text-xs text-gray-400">({{ count($proc->items) }} item)</span>
                                            </div>

                                            <div id="modal-{{ $proc->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center">
                                                <div class="absolute inset-0 bg-gray-900 opacity-60" onclick="document.getElementById('modal-{{ $proc->id }}').classList.add('hidden')"></div>
                                                <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full relative z-10 overflow-hidden transform transition-all m-4">
                                                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                                                        <div>
                                                            <h3 class="font-bold text-lg text-gray-800">Detail Pengajuan Pengadaan</h3>
                                                            <p class="text-xs text-gray-500">Tiket: {{ $proc->report->ticket_number ?? '-' }}</p>
                                                        </div>
                                                        <button type="button" onclick="document.getElementById('modal-{{ $proc->id }}').classList.add('hidden')" class="text-gray-400 hover:text-red-500 transition">
                                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        </button>
                                                    </div>

                                                    <div class="p-6 overflow-x-auto max-h-[70vh] overflow-y-auto">
                                                        <table class="min-w-full table-auto text-sm mb-4">
                                                            <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase">
                                                                <tr>
                                                                    <th class="px-4 py-3 text-left">Nama Barang</th>
                                                                    <th class="px-4 py-3 text-left">Merk / Tipe</th>
                                                                    <th class="px-4 py-3 text-right">Jumlah</th>
                                                                    <th class="px-4 py-3 text-right">Harga Satuan</th>
                                                                    <th class="px-4 py-3 text-right">Total</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="divide-y divide-gray-100">
                                                                @foreach($proc->items as $item)
                                                                    @php
                                                                        $qty = isset($item['jumlah']) ? (int)$item['jumlah'] : 1;
                                                                        $price = isset($item['harga_satuan']) ? (float)$item['harga_satuan'] : (isset($item['harga']) ? (float)$item['harga'] : (isset($item['biaya']) ? (float)$item['biaya'] : 0));
                                                                        $subtotal = $price * $qty;
                                                                        
                                                                        $nama = is_array($item['nama'] ?? null) ? implode(', ', (array)$item['nama']) : ($item['nama'] ?? '-');
                                                                        $merk = is_array($item['merk'] ?? null) ? implode(', ', (array)$item['merk']) : ($item['merk'] ?? ($item['spek'] ?? ($item['tipe'] ?? '-')));
                                                                    @endphp
                                                                    <tr>
                                                                        <td class="px-4 py-3 font-medium text-gray-800">{{ $nama }}</td>
                                                                        <td class="px-4 py-3 text-gray-500">{{ is_array($merk) ? implode(', ', $merk) : $merk }}</td>
                                                                        <td class="px-4 py-3 text-right font-mono">{{ $qty }}</td>
                                                                        <td class="px-4 py-3 text-right font-mono">Rp {{ number_format($price, 0, ',', '.') }}</td>
                                                                        <td class="px-4 py-3 text-right font-mono font-bold text-gray-800">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                            <tfoot>
                                                                <tr class="bg-blue-50">
                                                                    <td colspan="4" class="px-4 py-3 text-right font-bold text-blue-800 uppercase text-xs">Total Pengajuan</td>
                                                                    <td class="px-4 py-3 text-right font-bold text-blue-800 text-lg">Rp {{ number_format($total ?? 0, 0, ',', '.') }}</td>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                        <a href="{{ route('admin.procurements.export.single', $proc->id) }}" class="inline-flex items-center gap-2 bg-green-600 text-white px-3 py-1 rounded-md text-sm font-semibold hover:bg-green-700">Export Pengadaan (PDF)</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 align-top text-center">
                                            @php
                                                $statusClass = match($proc->status) {
                                                    'submitted_to_kepala_ruang', 'submitted_to_bendahara' => 'bg-amber-100 text-amber-700 border-amber-200',
                                                    'approved_by_director' => 'bg-green-100 text-green-700 border-green-200',
                                                    'completed' => 'bg-blue-100 text-blue-800 border-blue-300',
                                                    'rejected' => 'bg-red-100 text-red-700 border-red-200',
                                                    default => 'bg-gray-100 text-gray-700 border-gray-200',
                                                };
                                                $statusLabel = match($proc->status) {
                                                    'submitted_to_kepala_ruang' => 'Menunggu Kapro',
                                                    'submitted_to_bendahara' => 'Menunggu Bendahara',
                                                    'approved_by_director' => 'Disetujui',
                                                    'completed' => 'Selesai',
                                                    'rejected' => 'Ditolak',
                                                    default => ucfirst(str_replace('_', ' ', $proc->status)),
                                                };
                                            @endphp
                                            <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $statusClass }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>

                                        <td class="px-6 py-4 align-top text-right font-bold text-gray-800 font-mono">
                                            Rp {{ number_format($total ?? 0, 0, ',', '.') }}
                                        </td>

                                        <td class="px-6 py-4 align-top text-center">
                                            @if($proc->status === 'submitted_to_kepala_ruang')
                                                <a href="{{ route('procurement.edit', $proc->id) }}" class="inline-flex items-center px-3 py-1 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">Edit</a>
                                            @elseif($proc->status === 'approved_by_director')
                                                <form action="{{ route('admin.procurement.finish', $proc->id) }}" method="POST" onsubmit="return confirm('Tandai pengadaan ini sebagai selesai?')">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-3 rounded text-xs shadow">Tandai Selesai</button>
                                                </form>
                                            @elseif($proc->status === 'completed')
                                                <span class="text-gray-400 text-xs italic">Selesai</span>
                                            @else
                                                <span class="text-gray-400 text-xs italic">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                                {{-- ======================================================== --}}
                                {{-- 2. LOOPING PENGADAAN BARANG BARU DARI KARU               --}}
                                {{-- ======================================================== --}}
                                @if(isset($newItemRequests))
                                    @foreach($newItemRequests as $itemReq)
                                        <tr class="hover:bg-emerald-50 border-l-4 border-emerald-500 bg-emerald-50/20">
                                            <td class="px-6 py-4 align-top">
                                                <div class="font-bold text-gray-800">{{ $itemReq->room->name ?? '-' }}</div>
                                                <div class="text-[10px] font-bold text-emerald-600 tracking-wider mt-1 border border-emerald-200 bg-emerald-100 inline-block px-2 py-0.5 rounded">BARANG BARU</div>
                                            </td>

                                            <td class="px-6 py-4 align-top text-sm text-gray-600">
                                                <!-- <div class="font-semibold text-gray-800 mb-2 italic">Tujuan: "{{ $itemReq->purpose }}"</div> -->
                                                
                                                @php 
                                                    $totalNew = 0; 
                                                    foreach($itemReq->items as $item) {
                                                        $qty = isset($item['jumlah']) ? (int)$item['jumlah'] : 1;
                                                        $price = isset($item['harga_satuan']) ? (float)$item['harga_satuan'] : 0;
                                                        $totalNew += $price * $qty;
                                                    }
                                                @endphp

                                                <div class="flex items-center gap-3">
                                                    <button type="button" onclick="document.getElementById('modal-new-{{ $itemReq->id }}').classList.remove('hidden')" 
                                                        class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1 rounded-md text-sm font-bold hover:bg-emerald-100 transition">
                                                        Lihat Detail
                                                    </button>
                                                    <span class="text-xs text-gray-400">({{ count($itemReq->items) }} item)</span>
                                                </div>

                                                <div id="modal-new-{{ $itemReq->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center">
                                                    <div class="absolute inset-0 bg-gray-900 opacity-60" onclick="document.getElementById('modal-new-{{ $itemReq->id }}').classList.add('hidden')"></div>
                                                    <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full relative z-10 overflow-hidden transform transition-all m-4">
                                                        <div class="px-6 py-4 border-b border-emerald-100 flex justify-between items-center bg-emerald-50">
                                                            <div>
                                                                <h3 class="font-bold text-lg text-emerald-800">Detail Pengadaan Barang Baru</h3>
                                                                <p class="text-sm text-emerald-600 mt-1"><span class="font-bold">Tujuan:</span> {{ $itemReq->purpose }}</p> 
                                                            </div>
                                                            <button type="button" onclick="document.getElementById('modal-new-{{ $itemReq->id }}').classList.add('hidden')" class="text-gray-400 hover:text-red-500 transition">
                                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                            </button>
                                                        </div>

                                                        <div class="p-6 overflow-x-auto max-h-[70vh] overflow-y-auto">
                                                            <table class="min-w-full table-auto text-sm mb-4">
                                                                <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase">
                                                                    <tr>
                                                                        <th class="px-4 py-3 text-left">Nama Barang</th>
                                                                        <th class="px-4 py-3 text-right">Jumlah</th>
                                                                        <th class="px-4 py-3 text-right">Harga Satuan</th>
                                                                        <th class="px-4 py-3 text-right">Total</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="divide-y divide-gray-100">
                                                                    @foreach($itemReq->items as $item)
                                                                        @php
                                                                            $qty = isset($item['jumlah']) ? (int)$item['jumlah'] : 1;
                                                                            $price = isset($item['harga_satuan']) ? (float)$item['harga_satuan'] : 0;
                                                                            $subtotal = $price * $qty;
                                                                            $nama = is_array($item['nama'] ?? null) ? implode(', ', (array)$item['nama']) : ($item['nama'] ?? '-');
                                                                        @endphp
                                                                        <tr>
                                                                            <td class="px-4 py-3 font-medium text-gray-800">{{ $nama }}</td>
                                                                            <td class="px-4 py-3 text-right font-mono">{{ $qty }}</td>
                                                                            <td class="px-4 py-3 text-right font-mono">Rp {{ number_format($price, 0, ',', '.') }}</td>
                                                                            <td class="px-4 py-3 text-right font-mono font-bold text-gray-800">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                                <tfoot>
                                                                    <tr class="bg-emerald-50">
                                                                        <td colspan="3" class="px-4 py-3 text-right font-bold text-emerald-800 uppercase text-xs">Total Estimasi Baru</td>
                                                                        <td class="px-4 py-3 text-right font-bold text-emerald-800 text-lg">Rp {{ number_format($totalNew ?? 0, 0, ',', '.') }}</td>
                                                                    </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="px-6 py-4 align-top text-center">
                                                @php
                                                    $statusClassNew = match($itemReq->status) {
                                                        'pending_admin' => 'bg-amber-100 text-amber-700 border-amber-200',
                                                        'approved' => 'bg-green-100 text-green-700 border-green-200',
                                                        'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                                        'rejected' => 'bg-red-100 text-red-700 border-red-200',
                                                        default => 'bg-blue-100 text-blue-700 border-blue-200',
                                                    };
                                                @endphp
                                                <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $statusClassNew }}">
                                                    {{ $itemReq->status_label }}
                                                </span>
                                                @if($itemReq->reject_note)
                                                    <div class="text-xs text-red-500 mt-2">Ket: {{ $itemReq->reject_note }}</div>
                                                @endif
                                            </td>

                                            <td class="px-6 py-4 align-top text-right font-bold text-gray-800 font-mono">
                                                Rp {{ number_format($totalNew ?? 0, 0, ',', '.') }}
                                            </td>

                                            <td class="px-6 py-4 align-top text-center">
                                                @if($itemReq->status === 'pending_admin')
                                                    <form action="{{ route('admin.new_items.approve', $itemReq->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Validasi pengajuan barang baru ini dan teruskan ke Management?')">
                                                        @csrf @method('PATCH')
                                                        <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1 rounded text-xs font-bold shadow mb-1">Setujui</button>
                                                    </form>
                                                    <br>
                                                    <button type="button" onclick="document.getElementById('rejectModal-new-{{ $itemReq->id }}').classList.remove('hidden')" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-bold shadow">Tolak</button>
                                                    
                                                    <div id="rejectModal-new-{{ $itemReq->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center">
                                                        <div class="absolute inset-0 bg-gray-900 opacity-60" onclick="document.getElementById('rejectModal-new-{{ $itemReq->id }}').classList.add('hidden')"></div>
                                                        <div class="bg-white rounded-lg shadow-xl p-6 w-96 relative z-10 text-left">
                                                            <h3 class="text-lg font-bold mb-4">Tolak Barang Baru</h3>
                                                            <form action="{{ route('admin.new_items.reject', $itemReq->id) }}" method="POST">
                                                                @csrf @method('PATCH')
                                                                <textarea name="catatan" required class="w-full border-gray-300 rounded mb-4 text-sm" placeholder="Masukkan alasan penolakan..."></textarea>
                                                                <div class="flex justify-end gap-2">
                                                                    <button type="button" onclick="document.getElementById('rejectModal-new-{{ $itemReq->id }}').classList.add('hidden')" class="px-4 py-2 bg-gray-200 rounded text-sm font-bold">Batal</button>
                                                                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded text-sm font-bold">Tolak Pengajuan</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-gray-400 text-xs italic">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif

                            @endif
                        </tbody>
                    </table>
                </div>

                <div id="export-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
                    <div class="absolute inset-0 bg-gray-900 opacity-60" onclick="document.getElementById('export-modal').classList.add('hidden')"></div>
                    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full relative z-10 overflow-hidden transform transition-all m-4">
                        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                            <h3 class="font-bold text-lg text-gray-800">Unduh Laporan Bulanan</h3>
                            <button type="button" onclick="document.getElementById('export-modal').classList.add('hidden')" class="text-gray-400 hover:text-red-500">✕</button>
                        </div>
                        <form action="{{ route('admin.procurements.export.monthly') }}" method="GET">
                            <div class="p-6">
                                <label class="block text-sm font-bold text-gray-600 mb-2">Pilih Bulan</label>
                                <input type="month" name="month" value="{{ request('month', date('Y-m')) }}" class="w-full border-gray-300 rounded-md px-3 py-2">
                                <p class="text-xs text-gray-400 mt-2">Contoh: 2026-01 untuk Januari 2026</p>
                            </div>
                            <div class="px-6 py-4 border-t bg-gray-50 text-right">
                                <button type="button" onclick="document.getElementById('export-modal').classList.add('hidden')" class="mr-2 px-4 py-2 bg-gray-100 rounded-md">Batal</button>
                                <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-md">Unduh Bulanan (PDF)</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="p-6 space-y-4">
                    {{-- Pagination Pengadaan Lama --}}
                    @if(isset($procurements) && method_exists($procurements, 'links'))
                        {{ $procurements->links() }}
                    @endif
                    
                    {{-- Pagination Pengadaan Baru --}}
                    @if(isset($newItemRequests) && method_exists($newItemRequests, 'links'))
                        {{ $newItemRequests->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>