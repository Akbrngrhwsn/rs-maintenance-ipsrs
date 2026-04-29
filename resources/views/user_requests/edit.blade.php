@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit Request User</h1>
    
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <form action="{{ route('user-requests.update', $requestData->id) }}" method="POST" class="grid grid-cols-1 gap-5">
            @csrf
            @method('PUT')
            
            <div>
                <label class="text-sm font-bold text-gray-700">NIP</label>
                <input type="text" name="nip" value="{{ old('nip', $requestData->nip) }}" class="w-full border border-gray-300 rounded-lg p-2 mt-1.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
            </div>
            
            <div>
                <label class="text-sm font-bold text-gray-700">Nama Lengkap</label>
                <input type="text" name="nama" value="{{ old('nama', $requestData->nama) }}" class="w-full border border-gray-300 rounded-lg p-2 mt-1.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
            </div>
            
            <div>
                <label class="text-sm font-bold text-gray-700">Unit / Ruang</label>
                <input type="text" name="unit" value="{{ old('unit', $requestData->unit) }}" class="w-full border border-gray-300 rounded-lg p-2 mt-1.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
            </div>
            
            <div>
                <label class="text-sm font-bold text-gray-700">Status Karyawan</label>
                <input type="text" name="status_karyawan" value="{{ old('status_karyawan', $requestData->status_karyawan) }}" class="w-full border border-gray-300 rounded-lg p-2 mt-1.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
            </div>
            
            <div class="mt-4 flex gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg font-bold text-sm hover:bg-blue-700 transition">
                    Simpan Perubahan
                </button>
                <a href="{{ route('user-requests.index') }}" class="bg-gray-100 text-gray-700 px-6 py-2.5 rounded-lg font-bold text-sm hover:bg-gray-200 transition border border-gray-200">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection