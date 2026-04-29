@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-8 px-4">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-800">Request User Baru</h1>
        
        <div class="flex flex-wrap items-center gap-2">
            <form action="{{ route('user-requests.index') }}" method="GET" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari..." 
                       class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-48 transition-all">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-bold shadow-sm transition-colors">
                    Cari
                </button>
            </form>

            <button type="button" onclick="openPdfModal()" class="bg-[#f59e0b] hover:bg-amber-600 text-white px-5 py-2 rounded-lg text-sm font-bold shadow-sm transition-colors">
                Unduh Bulanan (PDF)
            </button>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-8">
        <h3 class="font-bold text-gray-700 mb-4">Form Pengajuan</h3>
        <form action="{{ route('user-requests.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @csrf
            <div>
                <label class="text-xs font-bold text-gray-500">NIP</label>
                <input type="text" name="nip" class="w-full border rounded-lg p-2 mt-1 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none" required>
            </div>
            <div>
                <label class="text-xs font-bold text-gray-500">Nama Lengkap</label>
                <input type="text" name="nama" class="w-full border rounded-lg p-2 mt-1 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none" required>
            </div>
            <div>
                <label class="text-xs font-bold text-gray-500">Unit / Ruang</label>
                <input type="text" name="unit" class="w-full border rounded-lg p-2 mt-1 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none" required>
            </div>
            <div>
                <label class="text-xs font-bold text-gray-500">Status Karyawan</label>
                <input type="text" name="status_karyawan" placeholder="Contoh: Tetap/Kontrak" class="w-full border rounded-lg p-2 mt-1 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none" required>
            </div>
            <div class="md:col-span-4 text-right">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg font-bold text-sm hover:bg-blue-700 transition">Kirim Pengajuan</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 border-b border-gray-100 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Karyawan</th>
                        <th class="px-6 py-4">Diajukan Oleh</th>
                        <th class="px-6 py-4">Status</th>
                        @if(Auth::user()->role === 'admin')
                            <th class="px-6 py-4 text-center">Aksi Admin</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($requests as $r)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $r->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900 text-sm">{{ $r->nama }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">NIP: {{ $r->nip }} | {{ $r->unit }} - {{ $r->status_karyawan }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-xs font-semibold text-blue-600">{{ $r->user->name ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($r->status === 'diproses')
                                <span class="px-2.5 py-1 rounded-md bg-yellow-50 text-yellow-700 text-[10px] font-bold uppercase border border-yellow-200">
                                    Diproses
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-md bg-green-50 text-green-700 text-[10px] font-bold uppercase border border-green-200">
                                    Selesai
                                </span>
                            @endif
                        </td>
                        
                        @if(Auth::user()->role === 'admin')
                        <td class="px-6 py-4 whitespace-nowrap text-center space-x-1.5">
                            @if($r->status === 'diproses')
                                <form action="{{ route('user-requests.update-status', $r->id) }}" method="POST" class="inline-block">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-[#10b981] hover:bg-emerald-600 rounded-md font-bold text-[10px] text-white uppercase tracking-wide transition-colors" onclick="return confirm('Tandai sebagai selesai?')">
                                        Selesai
                                    </button>
                                </form>
                            @endif
                            
                            <a href="{{ route('user-requests.edit', $r->id) }}" class="inline-flex items-center px-3 py-1.5 bg-[#4f46e5] hover:bg-indigo-700 rounded-md font-bold text-[10px] text-white uppercase tracking-wide transition-colors">
                                Edit
                            </a>
                            
                            <form action="{{ route('user-requests.destroy', $r->id) }}" method="POST" class="inline-block">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-[#e11d48] hover:bg-rose-700 rounded-md font-bold text-[10px] text-white uppercase tracking-wide transition-colors" onclick="return confirm('Hapus data ini?')">
                                    Hapus
                                </button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic text-sm">
                            Belum ada request user tersimpan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="pdfModal" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 flex items-center justify-center backdrop-blur-sm transition-opacity">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 overflow-hidden transform transition-all">
        <div class="px-6 py-4 flex justify-between items-center bg-white">
            <h3 class="text-lg font-bold text-gray-800">Unduh Laporan Bulanan</h3>
            <button type="button" onclick="closePdfModal()" class="text-gray-400 hover:text-gray-600 focus:outline-none transition-colors">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <hr class="border-gray-100">
        
        <form action="{{ route('user-requests.export') }}" method="GET" class="p-6" onsubmit="return submitPdfForm()">
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-600 mb-2">Pilih Bulan & Tahun</label>
                <input type="month" id="monthYearPicker" value="{{ date('Y-m') }}" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 cursor-pointer transition-all" required>
                <p class="text-xs text-gray-400 mt-2">Pilih periode laporan yang ingin diunduh.</p>
            </div>
            
            <input type="hidden" name="month" id="hiddenMonth">
            <input type="hidden" name="year" id="hiddenYear">
            
            <div class="flex justify-end gap-3 mt-8">
                <button type="submit" class="px-5 py-2.5 bg-[#10b981] hover:bg-emerald-600 text-white font-bold text-sm rounded-lg shadow-sm transition-colors">
                    Unduh Bulanan (PDF)
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPdfModal() {
        document.getElementById('pdfModal').classList.remove('hidden');
    }
    
    function closePdfModal() {
        document.getElementById('pdfModal').classList.add('hidden');
    }

    // Fungsi untuk memecah format "2026-04" menjadi year="2026" dan month="04"
    function submitPdfForm() {
        var pickerValue = document.getElementById('monthYearPicker').value;
        
        if (!pickerValue) {
            alert('Silakan pilih bulan dan tahun terlebih dahulu.');
            return false;
        }

        // Pecah value berdasarkan tanda strip (-)
        var parts = pickerValue.split('-'); // Hasil: ["2026", "04"]
        
        // Masukkan ke input tersembunyi
        document.getElementById('hiddenYear').value = parts[0];
        document.getElementById('hiddenMonth').value = parts[1];
        
        // Tutup modal setelah klik download
        closePdfModal();
        return true;
    }
</script>

<script>
    function openPdfModal() {
        document.getElementById('pdfModal').classList.remove('hidden');
    }
    function closePdfModal() {
        document.getElementById('pdfModal').classList.add('hidden');
    }
</script>
@endsection