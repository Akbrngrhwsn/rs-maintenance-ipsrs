<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                {{-- PERBAIKAN: Logika tombol kembali. Completed juga kembali ke Ongoing --}}
                <a href="{{ in_array($project->status, ['in_progress', 'completed']) ? route('apps.ongoing') : route('apps.pending') }}" 
                   class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h2 class="font-bold text-xl text-gray-800 leading-tight">
                    {{ $project->nama_aplikasi }} 
                    <span class="text-sm font-mono text-gray-500">#{{ $project->ticket_number ?? 'No-Ticket' }}</span>
                </h2>
            </div>

            {{-- SEARCH BAR & DROPDOWN --}}
            <div class="w-full md:w-64">
                <select onchange="window.location.href='/apps/detail/' + this.value" class="w-full border-gray-300 rounded-lg text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500 cursor-pointer">
                    <option value="">-- Pindah ke Proyek Lain --</option>
                    @foreach($allProjects as $p)
                        <option value="{{ $p->id }}" {{ $p->id == $project->id ? 'selected' : '' }}>
                            {{ $p->ticket_number ?? 'APP' }} - {{ Str::limit($p->nama_aplikasi, 20) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-sm flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Info Header & Status --}}
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                <div class="flex flex-col md:flex-row justify-between items-start gap-6">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-800">Deskripsi</h3>
                        <p class="text-gray-600 mt-1 whitespace-pre-line">{{ $project->deskripsi }}</p>
                        
                        <div class="mt-4 flex flex-wrap gap-4 text-sm text-gray-500 bg-gray-50 p-3 rounded-lg border border-gray-100 inline-flex">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                {{ $project->user->name ?? 'User Tidak Dikenal' }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                {{ $project->created_at->translatedFormat('d F Y') }}
                            </span>
                        </div>
                        
                        {{-- Catatan Direktur/Admin --}}
                        @if($project->catatan_direktur || $project->catatan_admin)
                        <div class="mt-4 space-y-2">
                             @if($project->catatan_direktur)
                                <div class="text-xs bg-yellow-50 p-2 border border-yellow-200 rounded text-yellow-800 flex items-start gap-2">
                                    <span class="font-bold">Catatan Direktur:</span> 
                                    <span>{{ $project->catatan_direktur }}</span>
                                </div>
                             @endif
                             @if($project->catatan_admin)
                                <div class="text-xs bg-indigo-50 p-2 border border-indigo-200 rounded text-indigo-800 flex items-start gap-2">
                                    <span class="font-bold">Catatan Admin:</span>
                                    <span>{{ $project->catatan_admin }}</span>
                                </div>
                             @endif
                        </div>
                        @endif

                        {{-- PERBAIKAN: Tombol Download PDF (Hanya jika Selesai) --}}
                        @if($project->status === 'completed')
                            <div class="mt-6">
                                <a href="{{ route('apps.export.single', $project->id) }}" target="_blank" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-bold rounded-lg shadow transition gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    Unduh Laporan PDF
                                </a>
                            </div>
                        @endif
                    </div>
                    
                    {{-- Status Badge & Progress --}}
                    <div class="w-full md:w-auto text-right md:text-left min-w-[200px]">
                        <div class="flex flex-col items-end md:items-end">
                            @php
                                $colors = [
                                    'pending_director' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                    'approved' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'rejected' => 'bg-red-100 text-red-800 border-red-200',
                                    'in_progress' => 'bg-purple-100 text-purple-800 border-purple-200',
                                    'completed' => 'bg-green-100 text-green-800 border-green-200',
                                ];
                                $label = str_replace('_', ' ', $project->status);
                            @endphp
                            <span class="px-4 py-1.5 text-sm font-bold rounded-full border {{ $colors[$project->status] ?? 'bg-gray-100 border-gray-200' }}">
                                {{ strtoupper($label) }}
                            </span>
                            
                            <div class="mt-4 w-full">
                                <div class="flex justify-between text-xs font-bold text-gray-700 mb-1">
                                    <span>Progress</span>
                                    <span>{{ $project->progress }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $project->progress }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TOMBOL AKSI (Approve Direktur / Review Admin) --}}
                @if(Auth::user()->role === 'direktur' && $project->status === 'pending_director')
                    <div class="mt-6 border-t pt-6">
                        <form action="{{ route('apps.approve', $project->id) }}" method="POST" class="flex gap-3 items-center">
                            @csrf @method('PATCH')
                            <input type="text" name="catatan" placeholder="Catatan persetujuan / penolakan..." class="text-sm border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 flex-1">
                            <button type="submit" name="status" value="terima" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg text-sm font-bold shadow transition">ACC</button>
                            <button type="submit" name="status" value="tolak" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-bold shadow transition">Tolak</button>
                        </form>
                    </div>
                @endif
                
                @if(Auth::user()->role === 'admin' && $project->status === 'approved')
                     <div class="mt-6 border-t pt-6">
                        <form action="{{ route('apps.admin_review', $project->id) }}" method="POST" class="flex gap-3 items-center">
                            @csrf @method('PATCH')
                            <input type="text" name="catatan_admin" placeholder="Catatan teknis untuk memulai proyek..." class="text-sm border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 flex-1">
                            <button type="submit" name="action" value="terima" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-bold shadow transition">Terima & Kerjakan</button>
                            <button type="submit" name="action" value="tolak" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-bold shadow transition">Tolak</button>
                        </form>
                    </div>
                @endif
            </div>

            {{-- FITUR & CHECKLIST --}}
            @if(in_array($project->status, ['in_progress', 'completed']))
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                {{-- Kolom Kiri: Daftar Fitur --}}
                <div class="md:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2 border-b pb-2">
                        <span>📋 Checklist Fitur / Modul</span>
                    </h3>
                    <ul class="space-y-3">
                        @forelse($project->features as $feature)
                            <li class="flex items-start justify-between p-3 rounded-lg border {{ $feature->is_done ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200 hover:bg-gray-100' }} transition group">
                                
                                {{-- BAGIAN KIRI: Checkbox & Teks --}}
                                <div class="flex items-center gap-3">
                                    @if(Auth::user()->role === 'admin' && $project->status !== 'completed')
                                        <form action="{{ route('apps.toggle_feature', $feature->id) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <input type="checkbox" onchange="this.form.submit()" class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer" {{ $feature->is_done ? 'checked' : '' }}>
                                        </form>
                                    @else
                                        <input type="checkbox" disabled class="w-5 h-5 text-gray-400 rounded border-gray-300" {{ $feature->is_done ? 'checked' : '' }}>
                                    @endif
                                    
                                    <div class="flex flex-col">
                                        <span class="{{ $feature->is_done ? 'line-through text-gray-400' : 'text-gray-700 font-medium' }}">
                                            {{ $feature->nama_fitur }}
                                        </span>
                                        {{-- Waktu Selesai --}}
                                        @if($feature->is_done && $feature->completed_at)
                                            <span class="text-[10px] text-green-700 font-mono flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Selesai: {{ $feature->completed_at->format('d/m H:i') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- BAGIAN KANAN: Tombol Hapus (Hanya Admin & Project Belum Selesai) --}}
                                @if(Auth::user()->role === 'admin' && $project->status !== 'completed')
                                    <form action="{{ route('apps.delete_feature', $feature->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus fitur ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-red-600 transition p-1 rounded-md hover:bg-red-50" title="Hapus Fitur">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                @endif

                            </li>
                        @empty
                            <li class="text-center text-gray-400 italic py-8 border border-dashed rounded-lg bg-gray-50">Belum ada fitur yang ditambahkan oleh Admin.</li>
                        @endforelse
                    </ul>
                </div>

                {{-- Kolom Kanan: Panel Admin --}}
                @if(Auth::user()->role === 'admin' && $project->status !== 'completed')
                <div class="space-y-6">
                    <div class="bg-blue-50 p-5 rounded-xl border border-blue-100 shadow-sm">
                        <h4 class="font-bold text-sm text-blue-800 mb-3 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Tambah Fitur
                        </h4>
                        <form action="{{ route('apps.add_feature', $project->id) }}" method="POST">
                            @csrf
                            <input type="text" name="nama_fitur" required placeholder="Nama fitur / modul..." class="w-full text-sm border-blue-200 rounded-lg mb-3 focus:ring-blue-500 focus:border-blue-500">
                            <button class="w-full bg-blue-600 text-white py-2 rounded-lg text-sm font-bold hover:bg-blue-700 shadow transition">Simpan Fitur</button>
                        </form>
                    </div>

                    @if($project->progress == 100 && $project->features->count() > 0)
                    <div class="bg-green-50 p-5 rounded-xl border border-green-200 shadow-sm text-center">
                        <div class="mb-3 flex justify-center text-green-600">
                             <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <p class="text-sm text-green-800 mb-4 font-bold">Semua fitur telah selesai dikerjakan.</p>
                        <form action="{{ route('apps.complete', $project->id) }}" method="POST">
                            @csrf @method('PATCH')
                            <button class="w-full bg-green-600 text-white py-2.5 rounded-lg shadow hover:bg-green-700 font-bold text-sm transition flex items-center justify-center gap-2">
                                <span>🚀</span> Tandai Project Selesai
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
                @endif
            </div>
            @endif

        </div>
    </div>
</x-app-layout>