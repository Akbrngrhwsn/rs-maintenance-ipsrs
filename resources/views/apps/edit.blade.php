<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">Edit Request Aplikasi</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                <form action="{{ route('admin.apps.update', $appRequest->id) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Judul Aplikasi/Fitur</label>
                        {{-- Ganti name menjadi nama_aplikasi --}}
                        <input type="text" name="nama_aplikasi" value="{{ $appRequest->nama_aplikasi }}" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Deskripsi Kebutuhan</label>
                        {{-- Ganti name menjadi deskripsi --}}
                        <textarea name="deskripsi" rows="5" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ $appRequest->deskripsi }}</textarea>
                    </div>

                    <div class="mb-8">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full md:w-1/2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            {{-- Daftar status disesuaikan dengan alur approval aplikasi Anda --}}
                            <option value="submitted_to_admin" {{ $appRequest->status == 'submitted_to_admin' ? 'selected' : '' }}>Menunggu Admin</option>
                            <option value="submitted_to_management" {{ $appRequest->status == 'submitted_to_management' ? 'selected' : '' }}>Menunggu Management</option>
                            <option value="submitted_to_bendahara" {{ $appRequest->status == 'submitted_to_bendahara' ? 'selected' : '' }}>Menunggu Bendahara</option>
                            <option value="pending_director" {{ $appRequest->status == 'pending_director' ? 'selected' : '' }}>Menunggu Direktur</option>
                            <option value="approved" {{ $appRequest->status == 'approved' ? 'selected' : '' }}>Disetujui / Dalam Antrian Pengerjaan</option>
                            <option value="in_progress" {{ $appRequest->status == 'in_progress' ? 'selected' : '' }}>Dalam Pengerjaan (Progres)</option>
                            <option value="completed" {{ $appRequest->status == 'completed' ? 'selected' : '' }}>Selesai</option>
                            <option value="rejected" {{ $appRequest->status == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ url()->previous() }}" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg font-bold hover:bg-gray-200 transition">Batal</a>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>