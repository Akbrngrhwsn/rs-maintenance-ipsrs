@props(['project'])

@php
    // Definisikan tahapan pengadaan
    $stages = [
        ['id' => 'pending', 'label' => 'Belum Diajukan', 'icon' => 'clipboard'],
        ['id' => 'submitted_to_management', 'label' => 'Menunggu Management', 'icon' => 'users'],
        ['id' => 'submitted_to_bendahara', 'label' => 'Menunggu Bendahara', 'icon' => 'wallet'],
        ['id' => 'submitted_to_director', 'label' => 'Menunggu Direktur', 'icon' => 'briefcase'],
        ['id' => 'approved', 'label' => 'Disetujui', 'icon' => 'check-circle'],
        ['id' => 'finish', 'label' => 'Implementasi Selesai', 'icon' => 'flag'],
    ];

    // Status mapping untuk null/undefined values
    $statusMap = [
        'pending' => 'Belum Diajukan',
        'submitted_to_management' => 'Menunggu Management',
        'submitted_to_bendahara' => 'Menunggu Bendahara',
        'submitted_to_director' => 'Menunggu Direktur',
        'approved' => 'Disetujui',
        'finish' => 'Implementasi Selesai',
        'rejected' => 'Ditolak',
    ];

    // Mapping icon ke SVG
    $iconMap = [
        'clipboard' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>',
        'users' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM9 20H4v-2a6 6 0 0112 0v2H9z"></path></svg>',
        'wallet' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10a6 6 0 016-6h.01M3 10l18-5m0 0h-6a6 6 0 00-6 6v.01M3 15h18M9 20h6a2 2 0 002-2v-2a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2z"></path></svg>',
        'briefcase' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m0 0v10l8 4"></path></svg>',
        'check-circle' => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>',
        'flag' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4a6 6 0 016-6h0a6 6 0 016 6v4M9 21h6m-6 0a6 6 0 01-6-6v-4m12 10a6 6 0 006-6v-4m0 0V3a2 2 0 10-4 0v8"></path></svg>',
    ];

    // Tentukan status saat ini dan index
    $currentStatus = $project->procurement_approval_status ?? 'pending';
    $currentIndex = array_search($currentStatus, array_column($stages, 'id'));
    $isRejected = $currentStatus === 'rejected';
@endphp

@if($project->needs_procurement)
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
        <h3 class="text-lg font-bold text-gray-800 mb-6">Status Pengadaan</h3>

        @if($isRejected)
            {{-- Rejected State --}}
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg mb-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <p class="text-sm font-bold text-red-800">Pengadaan Ditolak</p>
                        <p class="text-xs text-red-700 mt-1">Pengadaan untuk aplikasi ini telah ditolak pada tahap persetujuan.</p>
                    </div>
                </div>
            </div>
        @else
            {{-- Timeline Progress --}}
            <div class="relative">
                {{-- Desktop Timeline --}}
                <div class="hidden md:block">
                    <div class="flex items-center gap-2">
                        @foreach($stages as $idx => $stage)
                            @php
                                $isActive = $idx <= $currentIndex;
                                $isCurrent = $idx === $currentIndex;
                                $isCompleted = $idx < $currentIndex;
                            @endphp

                            <div class="flex items-center flex-1">
                                {{-- Stage Circle --}}
                                <div class="flex flex-col items-center flex-1">
                                    <div class="relative z-10 flex items-center justify-center w-12 h-12 rounded-full border-2 transition-all
                                        @if($isCompleted)
                                            bg-green-500 border-green-500
                                        @elseif($isCurrent)
                                            bg-blue-500 border-blue-500 ring-4 ring-blue-200 scale-110
                                        @else
                                            bg-gray-200 border-gray-300
                                        @endif
                                    ">
                                        <div class="text-white">
                                            @if($isCompleted)
                                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                            @elseif($isCurrent)
                                                <svg class="w-6 h-6 animate-pulse" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="3"></circle></svg>
                                            @else
                                                {{ $idx + 1 }}
                                            @endif
                                        </div>
                                    </div>
                                    <p class="text-xs font-semibold text-center mt-2 whitespace-nowrap
                                        @if($isActive)
                                            text-gray-800
                                        @else
                                            text-gray-400
                                        @endif
                                    ">
                                        {{ $stage['label'] }}
                                    </p>
                                </div>

                                {{-- Connector Line --}}
                                @if($idx < count($stages) - 1)
                                    <div class="h-1 flex-1 mx-1 rounded-full
                                        @if($isCompleted)
                                            bg-green-500
                                        @elseif($isCurrent)
                                            bg-blue-300
                                        @else
                                            bg-gray-300
                                        @endif
                                    "></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Mobile Timeline (Vertical) --}}
                <div class="md:hidden space-y-4">
                    @foreach($stages as $idx => $stage)
                        @php
                            $isActive = $idx <= $currentIndex;
                            $isCurrent = $idx === $currentIndex;
                            $isCompleted = $idx < $currentIndex;
                        @endphp

                        <div class="flex gap-4">
                            <div class="flex flex-col items-center">
                                <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 transition-all flex-shrink-0
                                    @if($isCompleted)
                                        bg-green-500 border-green-500
                                    @elseif($isCurrent)
                                        bg-blue-500 border-blue-500 ring-4 ring-blue-200
                                    @else
                                        bg-gray-200 border-gray-300
                                    @endif
                                ">
                                    <div class="text-white">
                                        @if($isCompleted)
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                        @elseif($isCurrent)
                                            <svg class="w-5 h-5 animate-pulse" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="3"></circle></svg>
                                        @else
                                            {{ $idx + 1 }}
                                        @endif
                                    </div>
                                </div>
                                @if($idx < count($stages) - 1)
                                    <div class="w-1 h-8 bg-gray-300 mt-1"></div>
                                @endif
                            </div>
                            <div class="flex-1 pt-1">
                                <p class="text-sm font-semibold @if($isActive) text-gray-800 @else text-gray-400 @endif">
                                    {{ $stage['label'] }}
                                </p>
                                @if($isCurrent)
                                    <p class="text-xs text-blue-600 mt-1">← Tahap saat ini</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Status Summary Card --}}
            <div class="mt-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-100">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-blue-900">
                            {{ $currentIndex !== false ? round((($currentIndex + 1) / count($stages)) * 100) : 0 }}% Selesai
                        </p>
                        <p class="text-xs text-blue-700 mt-1">
                            {{ $statusMap[$currentStatus] ?? 'Status tidak diketahui' }}
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif
