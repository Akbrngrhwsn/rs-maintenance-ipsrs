<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-blue-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">Edit Laporan: {{ $report->ticket_number ?? 'Tanpa Tiket' }}</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <form action="{{ route('admin.report.update', $report->id) }}" method="POST" class="p-8">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Ruangan</label>
                            <select name="room_id" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}" {{ $report->room_id == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tingkat Urgensi</label>
                            <select name="urgency" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="rendah" {{ $report->urgency == 'rendah' ? 'selected' : '' }}>Rendah</option>
                                <option value="sedang" {{ $report->urgency == 'sedang' ? 'selected' : '' }}>Sedang</option>
                                <option value="tinggi" {{ $report->urgency == 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Deskripsi Keluhan</label>
                        <textarea name="keluhan" rows="3" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ $report->keluhan }}</textarea>
                    </div>

                    <hr class="my-6 border-gray-200">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Status Saat Ini</label>
                            <select name="status" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="Belum Diproses" {{ $report->status == 'Belum Diproses' ? 'selected' : '' }}>Belum Diproses</option>
                                <option value="Diproses" {{ $report->status == 'Diproses' ? 'selected' : '' }}>Sedang Diproses</option>
                                <option value="Selesai" {{ $report->status == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                <option value="Tidak Selesai" {{ $report->status == 'Tidak Selesai' ? 'selected' : '' }}>Tidak Selesai (Pengadaan)</option>
                                <option value="Ditolak" {{ $report->status == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-8">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Tindakan / Solusi Teknisi</label>
                        <textarea name="tindakan_teknisi" rows="4" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Tuliskan tindakan yang telah dilakukan...">{{ $report->tindakan_teknisi }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Isi jika laporan sudah ditangani.</p>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('dashboard') }}" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg font-bold hover:bg-gray-200 transition">Batal</a>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>