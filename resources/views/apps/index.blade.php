<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengajuan Aplikasi & Sistem') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    {{ session('error') }}
                </div>
            @endif

            @if(in_array(Auth::user()->role, ['kepala_ruang', 'direktur']))
    <div class="bg-white p-6 overflow-hidden shadow-sm sm:rounded-lg border-l-4 {{ Auth::user()->role === 'direktur' ? 'border-red-500' : 'border-indigo-500' }}">
        
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">Ajukan Aplikasi Baru</h3>
        </div>

        <form action="{{ route('apps.store') }}" method="POST">
            @csrf
            {{-- Mengubah grid-cols-2 menjadi grid-cols-1 agar Nama dan Deskripsi tersusun atas-bawah --}}
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Aplikasi</label>
                    <input type="text" name="nama_aplikasi" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Deskripsi Singkat</label>
                    {{-- PERUBAHAN: Menggunakan textarea --}}
                    <textarea name="deskripsi" required rows="4" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                        placeholder="Contoh: Untuk mempermudah antrian pasien..."></textarea>
                </div>
            </div>
            <div class="mt-4 text-right">
                <button type="submit" class="text-white px-4 py-2 rounded shadow font-bold {{ Auth::user()->role === 'direktur' ? 'bg-red-600 hover:bg-red-700' : 'bg-indigo-600 hover:bg-indigo-700' }}">
                    {{ Auth::user()->role === 'direktur' ? 'Kirim Langsung ke Admin IT' : 'Kirim ke Direktur' }}
                </button>
            </div>
        </form>
    </div>
@endif

            @foreach($projects as $app)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex justify-between items-start flex-wrap gap-4">
                        
                        <div class="w-full md:w-1/2">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="text-xl font-bold text-gray-800">{{ $app->nama_aplikasi }}</h3>
                                @php
                                    $colors = [
                                        'pending_director' => 'bg-yellow-100 text-yellow-800',
                                        'approved' => 'bg-blue-100 text-blue-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        'in_progress' => 'bg-purple-100 text-purple-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                    ];
                                    $labels = [
                                        'pending_director' => 'Menunggu Direktur',
                                        'approved' => 'Menunggu Admin IT',
                                        'rejected' => 'Ditolak',
                                        'in_progress' => 'Sedang Dikerjakan',
                                        'completed' => 'Selesai',
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs font-bold rounded {{ $colors[$app->status] ?? 'bg-gray-100' }}">
                                    {{ $labels[$app->status] ?? $app->status }}
                                </span>
                            </div>
                            <p class="text-gray-600 text-sm">Request by: <span class="font-semibold">{{ $app->user->name }}</span> ({{ ucfirst($app->user->role) }})</p>
                            <p class="text-gray-500 text-sm italic mt-1">"{{ $app->deskripsi }}"</p>
                            
                            <div class="mt-3 space-y-1">
                                @if($app->catatan_direktur)
                                    <div class="text-xs bg-gray-50 p-2 rounded border border-gray-200">
                                        <span class="font-bold text-gray-700">Direktur:</span> {{ $app->catatan_direktur }}
                                    </div>
                                @endif
                                @if($app->catatan_admin)
                                    <div class="text-xs bg-gray-50 p-2 rounded border border-gray-200">
                                        <span class="font-bold text-gray-700">Admin IT:</span> {{ $app->catatan_admin }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if(Auth::user()->role === 'direktur' && $app->status === 'pending_director')
                        <div class="bg-yellow-50 p-4 rounded border border-yellow-200 w-full md:w-1/3">
                            <h4 class="font-bold text-sm text-yellow-800 mb-2">Persetujuan Direktur</h4>
                            <form action="{{ route('apps.approve', $app->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <textarea name="catatan" class="w-full text-sm border-gray-300 rounded mb-2" rows="2" placeholder="Catatan (opsional)..."></textarea>
                                <div class="flex gap-2">
                                    <button type="submit" name="status" value="terima" class="flex-1 bg-green-600 text-white py-1 rounded hover:bg-green-700 text-sm font-bold">ACC</button>
                                    <button type="submit" name="status" value="tolak" class="flex-1 bg-red-600 text-white py-1 rounded hover:bg-red-700 text-sm font-bold">Tolak</button>
                                </div>
                            </form>
                        </div>
                        @endif

                        @if(Auth::user()->role === 'admin' && $app->status === 'approved')
                        <div class="bg-indigo-50 p-4 rounded border border-indigo-200 w-full md:w-1/3">
                            <h4 class="font-bold text-sm text-indigo-800 mb-2">👮 Tinjauan Admin IT</h4>
                            <p class="text-xs text-gray-600 mb-2">Direktur setuju/Bypass. Apakah tim IT sanggup?</p>
                            <form action="{{ route('apps.admin_review', $app->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <textarea name="catatan_admin" class="w-full text-sm border-gray-300 rounded mb-2" rows="2" placeholder="Alasan penolakan / Info teknis..."></textarea>
                                <div class="flex gap-2">
                                    <button type="submit" name="action" value="terima" class="flex-1 bg-indigo-600 text-white py-1 rounded hover:bg-indigo-700 text-sm font-bold">Terima & Kerjakan</button>
                                    <button type="submit" name="action" value="tolak" class="flex-1 bg-red-600 text-white py-1 rounded hover:bg-red-700 text-sm font-bold">Tolak</button>
                                </div>
                            </form>
                        </div>
                        @endif
                    </div>

                    @if(in_array($app->status, ['in_progress', 'completed']))
                        <hr class="my-4 border-gray-200">
                        
                        <div class="mb-6">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-semibold text-gray-700">Progres Pengerjaan Fitur</span>
                                <span class="font-bold text-blue-600">{{ $app->progress }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500 ease-out" style="width: {{ $app->progress }}%"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-bold text-gray-700 mb-3 flex items-center">📋 Checklist Fitur</h4>
                                <ul class="space-y-2">
                                    @forelse($app->features as $feature)
                                        <li class="flex items-center p-2 bg-gray-50 rounded border {{ $feature->is_done ? 'bg-green-50 border-green-200' : 'border-gray-200' }}">
                                            @if(Auth::user()->role === 'admin' && $app->status !== 'completed')
                                                <form action="{{ route('apps.toggle_feature', $feature->id) }}" method="POST" class="mr-3 flex items-center">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="checkbox" onchange="this.form.submit()" class="w-5 h-5 rounded text-blue-600 focus:ring-blue-500 cursor-pointer" {{ $feature->is_done ? 'checked' : '' }}>
                                                </form>
                                            @else
                                                <input type="checkbox" disabled class="w-5 h-5 rounded text-gray-400 mr-3" {{ $feature->is_done ? 'checked' : '' }}>
                                            @endif
                                            
                                            <span class="{{ $feature->is_done ? 'line-through text-gray-400' : 'text-gray-700 font-medium' }}">
                                                {{ $feature->nama_fitur }}
                                            </span>
                                        </li>
                                    @empty
                                        <li class="text-gray-400 text-sm italic p-2 border border-dashed rounded text-center">Belum ada rincian fitur yang ditambahkan.</li>
                                    @endforelse
                                </ul>
                            </div>

                            @if(Auth::user()->role === 'admin' && $app->status !== 'completed')
                            <div class="bg-blue-50 p-5 rounded-lg border border-blue-100 h-fit">
                                <h4 class="font-bold text-sm text-blue-800 mb-3 uppercase tracking-wide">🔧 Panel Pengerjaan</h4>
                                
                                <form action="{{ route('apps.add_feature', $app->id) }}" method="POST" class="mb-4">
                                    @csrf
                                    <label class="text-xs text-blue-600 font-semibold mb-1 block">Tambah Item Pekerjaan:</label>
                                    <div class="flex gap-2">
                                        <input type="text" name="nama_fitur" required placeholder="Contoh: Buat Login Page..." class="w-full text-sm border-blue-200 rounded focus:border-blue-500 focus:ring-blue-500">
                                        <button class="bg-blue-600 text-white px-4 rounded hover:bg-blue-700 font-bold">+</button>
                                    </div>
                                </form>

                                @if($app->progress == 100 && $app->features->count() > 0)
                                    <div class="mt-4 pt-4 border-t border-blue-200">
                                        <p class="text-xs text-green-700 mb-2">Semua fitur telah selesai!</p>
                                        <form action="{{ route('apps.complete', $app->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button class="w-full bg-green-600 text-white py-2 rounded shadow hover:bg-green-700 font-bold text-sm flex justify-center items-center gap-2">
                                                <span>✅</span> Tandai Project Selesai
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
            @endforeach

        </div>
    </div>
</x-app-layout>