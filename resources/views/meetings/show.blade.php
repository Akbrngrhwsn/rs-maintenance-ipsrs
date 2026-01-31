@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-10 px-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Detail Rapat</h1>
            <p class="mt-1 text-sm text-gray-500">Informasi lengkap mengenai agenda dan hasil pembahasan rapat.</p>
        </div>
        <div class="mt-4 md:mt-0 flex flex-wrap gap-2">
            <a href="{{ route('meetings.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
            <a href="{{ route('meetings.edit', $meeting->id) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-500 hover:bg-yellow-600 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </a>
            <a href="{{ route('meetings.export', $meeting->id) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Download PDF
            </a>
        </div>
    </div>

    <div class="bg-white shadow-sm overflow-hidden rounded-xl border border-gray-200">
        <div class="px-6 py-5 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-bold text-gray-900">
                {{ $meeting->title }}
            </h3>
        </div>
        
        <div class="px-6 py-5">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Tanggal Rapat</dt>
                    <dd class="mt-2 text-sm text-gray-900 flex items-center">
                        <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        {{ \Carbon\Carbon::parse($meeting->meeting_date)->translatedFormat('d F Y') }}
                    </dd>
                </div>

                @php
                    $divisionDisplay = $meeting->division_role;
                    if (!$divisionDisplay && optional($meeting->creator)->role === 'manager' && optional($meeting->creator->room)->name) {
                        $divisionDisplay = $meeting->creator->room->name;
                    }
                    if (!$divisionDisplay) {
                        $divisionDisplay = optional($meeting->creator)->role ?? '-';
                    }
                @endphp
                <div class="sm:col-span-1">
                    <dt class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Divisi / Unit</dt>
                    <dd class="mt-2 text-sm text-gray-900">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                            {{ $divisionDisplay }}
                        </span>
                    </dd>
                </div>

                <div class="sm:col-span-2 border-t border-gray-100 pt-4">
                    <dt class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Riwayat Input</dt>
                    <dd class="mt-2 text-xs text-gray-600 flex gap-4">
                        <span><strong>Dibuat:</strong> {{ $meeting->created_at->format('d/m/Y H:i') }}</span>
                        @if($meeting->edited_by)
                            <span class="text-orange-600 font-medium italic"><strong>Diedit:</strong> {{ $meeting->updated_at->format('d/m/Y H:i') }}</span>
                        @endif
                    </dd>
                </div>

                <div class="sm:col-span-2 border-t border-gray-100 pt-6">
                    <dt class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Notulensi / Pembahasan</dt>
                    <dd class="text-sm text-gray-800 bg-gray-50 p-6 rounded-lg border border-gray-200 leading-relaxed shadow-inner">
                        <div class="whitespace-pre-wrap">{!! nl2br(e($meeting->minutes ?? '-')) !!}</div>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection