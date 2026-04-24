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
                            <span class="px-3 py-1 rounded-md bg-blue-600 text-white font-bold text-sm">Semua Jenis Pengadaan</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <form method="GET" action="{{ route('admin.procurements.index') }}" class="flex items-center gap-2">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari..." class="text-sm border-gray-300 rounded-md px-3 py-1.5 w-48">
                                <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-bold transition">Cari</button>
                            </form>

                            <div class="flex items-center gap-2 ml-2">
                                <button type="button" onclick="document.getElementById('export-modal').classList.remove('hidden')" class="px-4 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-md text-sm font-bold transition shadow-sm">
                                    Unduh Bulanan (PDF)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4 text-left">Referensi / Asal</th>
                                <th class="px-6 py-4 text-left">Detail Rincian</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-right">Estimasi Biaya</th>
                                <th class="px-6 py-4 text-center">Aksi Admin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            
                            @php
                                $hasProcurements = isset($procurements) && count($procurements) > 0;
                                $hasNewItems = isset($newItemRequests) && count($newItemRequests) > 0;
                                $hasApps = isset($appProcurements) && count($appProcurements) > 0;
                            @endphp

                            @if(!$hasProcurements && !$hasNewItems && !$hasApps)
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center text-gray-400">
                                        <div class="flex flex-col items-center justify-center gap-3">
                                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <span class="font-medium text-lg">Belum ada data pengajuan pengadaan apapun.</span>
                                        </div>
                                    </td>
                                </tr>
                            @else

                                {{-- ======================================================== --}}
                                {{-- 1. LOOPING PENGADAAN KERUSAKAN (MAINTENANCE)             --}}
                                {{-- ======================================================== --}}
                                @if(isset($procurements))
                                    @foreach($procurements as $proc)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4 align-top">
                                                <div class="font-bold text-gray-800">{{ $proc->report->ruangan ?? '-' }}</div>
                                                <div class="text-xs font-mono text-gray-500 mt-1">{{ $proc->report->ticket_number ?? '-' }}</div>
                                                <div class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                                    MAINTENANCE
                                                </div>
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
                                                        class="bg-blue-50 text-blue-600 border border-blue-200 px-3 py-1 rounded-md text-xs font-bold hover:bg-blue-100 transition">
                                                        Lihat Rincian
                                                    </button>
                                                    <span class="text-xs font-medium text-gray-400">({{ count($proc->items) }} item)</span>
                                                </div>

                                                {{-- MODAL DETAIL MAINTENANCE --}}
                                                <div id="modal-{{ $proc->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center">
                                                    <div class="absolute inset-0 bg-gray-900 opacity-60 backdrop-blur-sm" onclick="document.getElementById('modal-{{ $proc->id }}').classList.add('hidden')"></div>
                                                    <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full relative z-10 overflow-hidden transform transition-all m-4">
                                                        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                                                            <div>
                                                                <h3 class="font-bold text-lg text-gray-800">Detail Pengajuan Maintenance</h3>
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
                                                                    <tr class="bg-gray-50">
                                                                        <td colspan="4" class="px-4 py-3 text-right font-bold text-gray-600 uppercase text-xs">Total Pengajuan</td>
                                                                        <td class="px-4 py-3 text-right font-bold text-gray-800 text-lg">Rp {{ number_format($total ?? 0, 0, ',', '.') }}</td>
                                                                    </tr>
                                                                </tfoot>
                                                            </table>
                                                            <div class="mt-4 flex justify-end">
                                                                <a href="{{ route('admin.procurements.export.single', $proc->id) }}" class="inline-flex items-center gap-2 bg-gray-800 text-white px-4 py-2 rounded-md text-sm font-bold hover:bg-gray-900 shadow">Export PDF</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="px-6 py-4 align-top text-center">
                                                @php
                                                    $statusClass = match($proc->status) {
                                                        'submitted_to_kepala_ruang', 'submitted_to_bendahara' => 'bg-amber-100 text-amber-800 border-amber-200',
                                                        'approved_by_director' => 'bg-green-100 text-green-800 border-green-200',
                                                        'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                                        'rejected' => 'bg-red-100 text-red-800 border-red-200',
                                                        default => 'bg-gray-100 text-gray-800 border-gray-200',
                                                    };
                                                @endphp
                                                <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $statusClass }}">
                                                    {{ ucfirst(str_replace('_', ' ', $proc->status)) }}
                                                </span>
                                            </td>

                                            <td class="px-6 py-4 align-top text-right font-bold text-gray-800 font-mono">
                                                Rp {{ number_format($total ?? 0, 0, ',', '.') }}
                                            </td>

                                            <td class="px-6 py-4 align-top text-center">
                                                <div class="flex flex-col items-center justify-center gap-1.5">
                                                    @php
                                                        // Status yang memungkinkan edit
                                                        $editableStatuses = ['submitted_to_kepala_ruang', 'submitted_to_management', 'submitted_to_bendahara', 'submitted_to_director', 'rejected'];
                                                    @endphp
                                                    @if(in_array($proc->status, $editableStatuses))
                                                        <a href="{{ route('procurement.edit', $proc->id) }}" class="w-full text-center px-3 py-1 bg-amber-500 text-white rounded text-xs font-bold hover:bg-amber-600 transition shadow-sm">Edit</a>
                                                    @elseif($proc->status === 'approved_by_director')
                                                        <form action="{{ route('admin.procurement.finish', $proc->id) }}" method="POST" class="w-full" onsubmit="return confirm('Tandai pengadaan ini sebagai selesai?')">
                                                            @csrf @method('PATCH')
                                                            <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-1 px-2 rounded text-xs shadow-sm">Tandai Selesai</button>
                                                        </form>
                                                    @endif
                                                    
                                                    <form action="{{ route('admin.procurements.destroy', $proc->id) }}" method="POST" class="w-full" onsubmit="return confirm('Hapus permanen pengadaan maintenance ini?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="w-full px-3 py-1 bg-red-100 text-red-600 hover:bg-red-200 hover:text-red-700 rounded text-xs font-bold transition border border-transparent hover:border-red-300">Hapus</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif

                                {{-- ======================================================== --}}
                                {{-- 2. LOOPING PENGADAAN BARANG BARU (DARI KARU)             --}}
                                {{-- ======================================================== --}}
                                @if(isset($newItemRequests))
                                    @foreach($newItemRequests as $itemReq)
                                        <tr class="bg-emerald-50/20 hover:bg-emerald-50/50 transition border-l-4 border-l-emerald-400">
                                            <td class="px-6 py-4 align-top">
                                                <div class="font-bold text-gray-800">{{ $itemReq->room->name ?? '-' }}</div>
                                                <div class="text-xs font-medium text-gray-500 mt-1">{{ $itemReq->user->name ?? '-' }}</div>
                                                <div class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                                    BARANG BARU
                                                </div>
                                            </td>

                                            <td class="px-6 py-4 align-top text-sm text-gray-600">
                                                <div class="font-semibold text-gray-800 mb-2 italic line-clamp-2">"{{ $itemReq->purpose }}"</div>
                                                
                                                @php 
                                                    $totalNew = 0; 
                                                    foreach($itemReq->items as $item) {
                                                        $qty = isset($item['jumlah']) ? (int)$item['jumlah'] : 1;
                                                        $price = isset($item['harga_satuan']) ? (float)$item['harga_satuan'] : 0;
                                                        $totalNew += $price * $qty;
                                                    }
                                                @endphp

                                                <div class="flex items-center gap-3 mt-2">
                                                    <button type="button" onclick="document.getElementById('modal-new-{{ $itemReq->id }}').classList.remove('hidden')" 
                                                        class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1 rounded-md text-xs font-bold hover:bg-emerald-100 transition">
                                                        Lihat Rincian
                                                    </button>
                                                    <span class="text-xs font-medium text-gray-400">({{ count($itemReq->items) }} item)</span>
                                                </div>

                                                {{-- MODAL DETAIL BARANG BARU --}}
                                                <div id="modal-new-{{ $itemReq->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center">
                                                    <div class="absolute inset-0 bg-gray-900 opacity-60 backdrop-blur-sm" onclick="document.getElementById('modal-new-{{ $itemReq->id }}').classList.add('hidden')"></div>
                                                    <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full relative z-10 overflow-hidden transform transition-all m-4">
                                                        <div class="px-6 py-4 border-b border-emerald-100 flex justify-between items-center bg-emerald-50">
                                                            <div>
                                                                <h3 class="font-bold text-lg text-emerald-900">Detail Pengadaan Barang Baru</h3>
                                                                <p class="text-sm text-emerald-700 mt-1 font-medium">Tujuan: {{ $itemReq->purpose }}</p>
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
                                                            <div class="mt-4 flex justify-end">
                                                                <a href="{{ route('new_items.export.single', $itemReq->id) }}" target="_blank" class="inline-flex items-center gap-2 bg-emerald-600 text-white px-4 py-2 rounded-md text-sm font-bold hover:bg-emerald-700 shadow">Export PDF</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="px-6 py-4 align-top text-center">
                                                @php
                                                    $statusClassNew = match($itemReq->status) {
                                                        'pending_admin' => 'bg-amber-100 text-amber-800 border-amber-200',
                                                        'approved' => 'bg-green-100 text-green-800 border-green-200',
                                                        'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                                        'rejected' => 'bg-red-100 text-red-800 border-red-200',
                                                        default => 'bg-blue-100 text-blue-800 border-blue-200',
                                                    };
                                                @endphp
                                                <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $statusClassNew }}">
                                                    {{ $itemReq->status_label }}
                                                </span>
                                            </td>

                                            <td class="px-6 py-4 align-top text-right font-bold text-gray-800 font-mono">
                                                Rp {{ number_format($totalNew ?? 0, 0, ',', '.') }}
                                            </td>

                                            <td class="px-6 py-4 align-top text-center">
                                                <div class="flex flex-col items-center justify-center gap-1.5">
                                                    @if($itemReq->status === 'pending_admin')
                                                        <div class="flex gap-1 w-full">
                                                            <form action="{{ route('admin.new_items.approve', $itemReq->id) }}" method="POST" class="w-1/2">
                                                                @csrf @method('PATCH')
                                                                <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white px-2 py-1 rounded text-xs font-bold shadow-sm">ACC</button>
                                                            </form>
                                                            <button type="button" onclick="document.getElementById('rejectModal-new-{{ $itemReq->id }}').classList.remove('hidden')" class="w-1/2 bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs font-bold shadow-sm">Tolak</button>
                                                        </div>
                                                        
                                                        {{-- MODAL TOLAK BARANG BARU --}}
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
                                                    @endif

                                                    <a href="{{ route('admin.new_items.edit', $itemReq->id) }}" class="w-full text-center px-3 py-1 bg-amber-500 text-white rounded text-xs font-bold hover:bg-amber-600 transition shadow-sm">Edit</a>
                                                    
                                                    <form action="{{ route('admin.new_items.destroy', $itemReq->id) }}" method="POST" class="w-full" onsubmit="return confirm('Hapus permanen pengadaan barang baru ini?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="w-full px-3 py-1 bg-red-100 text-red-600 hover:bg-red-200 hover:text-red-700 rounded text-xs font-bold transition border border-transparent hover:border-red-300">Hapus</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif

                                {{-- ======================================================== --}}
                                {{-- 3. LOOPING PENGADAAN APLIKASI (DARI APP REQUEST)         --}}
                                {{-- ======================================================== --}}
                                @if(isset($appProcurements))
                                    @foreach($appProcurements as $app)
                                        @if($app->needs_procurement)
                                        <tr class="bg-indigo-50/20 hover:bg-indigo-50/50 transition border-l-4 border-l-indigo-400">
                                            <td class="px-6 py-4 align-top">
                                                <div class="font-bold text-gray-800">{{ $app->nama_aplikasi ?? $app->title ?? '-' }}</div>
                                                <div class="text-xs font-medium text-gray-500 mt-1">{{ $app->user->name ?? '-' }}</div>
                                                <div class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 border border-indigo-200">
                                                    APLIKASI
                                                </div>
                                            </td>

                                            <td class="px-6 py-4 align-top text-sm text-gray-600">
                                                <div class="italic line-clamp-2">"{{ Str::limit($app->deskripsi ?? $app->description, 80) }}"</div>
                                                
                                                <div class="mt-3">
                                                    <a href="{{ route('apps.show', $app->id) }}" class="bg-indigo-50 text-indigo-700 border border-indigo-200 px-3 py-1 rounded-md text-xs font-bold hover:bg-indigo-100 transition">
                                                        Buka Modul Proyek
                                                    </a>
                                                </div>
                                            </td>

                                            <td class="px-6 py-4 align-top text-center">
                                                @php
                                                    $statusClassApp = match($app->procurement_approval_status) {
                                                        'submitted_to_bendahara' => 'bg-amber-100 text-amber-800 border-amber-200',
                                                        'approved' => 'bg-green-100 text-green-800 border-green-200',
                                                        'rejected' => 'bg-red-100 text-red-800 border-red-200',
                                                        default => 'bg-blue-100 text-blue-800 border-blue-200',
                                                    };
                                                    $statusLabelApp = match($app->procurement_approval_status) {
                                                        'submitted_to_bendahara' => 'Menunggu Anggaran',
                                                        'approved' => 'Anggaran Disetujui',
                                                        'rejected' => 'Anggaran Ditolak',
                                                        default => ucfirst(str_replace('_', ' ', $app->procurement_approval_status)),
                                                    };
                                                @endphp
                                                <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $statusClassApp }}">
                                                    {{ $statusLabelApp }}
                                                </span>
                                            </td>

                                            <td class="px-6 py-4 align-top text-right font-bold text-gray-800 font-mono">
                                                Rp {{ number_format($app->procurement_estimate ?? 0, 0, ',', '.') }}
                                            </td>

                                            <td class="px-6 py-4 align-top text-center">
                                                <div class="flex flex-col items-center justify-center gap-1.5">
                                                    <a href="{{ route('admin.apps.edit-procurement', $app->id) }}" class="w-full text-center px-3 py-1 bg-amber-500 text-white rounded text-xs font-bold hover:bg-amber-600 transition shadow-sm">Edit Pengadaan</a>
                                                    
                                                    <form action="{{ route('admin.apps.destroy', $app->id) }}" method="POST" class="w-full" onsubmit="return confirm('Hapus permanen request aplikasi ini?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="w-full px-3 py-1 bg-red-100 text-red-600 hover:bg-red-200 hover:text-red-700 rounded text-xs font-bold transition border border-transparent hover:border-red-300">Hapus</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @endif
                                    @endforeach
                                @endif

                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- MODAL EXPORT BULANAN UTAMA --}}
                <div id="export-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
                    <div class="absolute inset-0 bg-gray-900 opacity-60 backdrop-blur-sm" onclick="document.getElementById('export-modal').classList.add('hidden')"></div>
                    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full relative z-10 overflow-hidden transform transition-all m-4">
                        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                            <h3 class="font-bold text-lg text-gray-800">Unduh Laporan Bulanan</h3>
                            <button type="button" onclick="document.getElementById('export-modal').classList.add('hidden')" class="text-gray-400 hover:text-red-500">✕</button>
                        </div>
                        <form action="{{ route('admin.procurements.export.monthly') }}" method="GET">
                            <div class="p-6">
                                <label class="block text-sm font-bold text-gray-600 mb-2">Pilih Bulan</label>
                                <input type="month" name="month" value="{{ request('month', date('Y-m')) }}" class="w-full border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-xs text-gray-400 mt-2">Data akan merangkum biaya dari seluruh jenis pengadaan.</p>
                            </div>
                            <div class="px-6 py-4 border-t bg-gray-50 text-right">
                                <button type="button" onclick="document.getElementById('export-modal').classList.add('hidden')" class="mr-2 px-4 py-2 bg-gray-100 rounded-md font-medium text-sm hover:bg-gray-200 transition">Batal</button>
                                <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-md font-bold text-sm hover:bg-amber-700 shadow-sm transition">Unduh Bulanan (PDF)</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="p-6 space-y-4">
                    {{-- Jika Anda butuh menampilkan links pagination, taruh disini --}}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>