<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">Manajemen Teknisi IT</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-bold mb-4">Tambah Teknisi Baru</h3>
                <form action="{{ route('admin.it_staff.store') }}" method="POST" class="flex gap-3">
                    @csrf
                    <input type="text" name="nama" required placeholder="Nama Lengkap Teknisi..." class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md font-bold hover:bg-blue-700 transition">Simpan</button>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Teknisi</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status Tugas</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($staffs as $staff)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                {{ $staff->nama }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($staff->is_on_duty)
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">Sedang Bertugas (On-Duty)</span>
                                @else
                                    <form action="{{ route('admin.it_staff.onduty', $staff->id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-xs bg-gray-100 hover:bg-blue-100 hover:text-blue-700 text-gray-600 px-3 py-1 rounded-full border transition">Jadikan On-Duty</button>
                                    </form>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <form action="{{ route('admin.it_staff.destroy', $staff->id) }}" method="POST" onsubmit="return confirm('Hapus teknisi ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-bold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>