<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Catatan Tim IT') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex gap-2 mb-4">
    <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'add-it-note')">
        {{ __('Tambah Catatan Baru') }}
    </x-primary-button>

    <x-secondary-button type="button" onclick="openAppMonthlyModal()">
        {{ __('Ekspor PDF') }}
    </x-secondary-button>
</div>

{{-- MODAL POP-UP PEMILIHAN BULAN --}}
<div id="app-monthly-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full m-4 overflow-hidden relative">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-lg text-gray-800">Unduh Laporan Bulanan</h3>
            <button type="button" onclick="closeAppMonthlyModal()" class="text-gray-400 hover:text-red-500">✕</button>
        </div>
        
        <form action="{{ route('it-notes.export') }}" method="GET">
            <div class="p-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Bulan & Tahun</label>
                <input type="month" name="period" value="{{ date('Y-m') }}" 
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required />
                <p class="text-xs text-gray-400 mt-2">Data laporan akan mencakup semua catatan tim IT pada periode yang dipilih.</p>
            </div>
            
            <div class="px-6 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
                <button type="button" onclick="closeAppMonthlyModal()" class="bg-white border px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Batal</button>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-bold text-sm hover:bg-indigo-700 shadow-md transition">Unduh PDF</button>
            </div>
        </form>
    </div>
</div>



            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal & Waktu</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($notes as $note)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $note->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $note->note }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    

    <x-modal name="add-it-note" focusable>
        <div class="bg-white p-6 rounded-lg shadow-xl border border-gray-100">
            <form method="post" action="{{ route('it-notes.store') }}">
                @csrf
                
                <h2 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">
                    {{ __('Tambah Catatan Tim IT') }}
                </h2>

                <div class="mt-4">
                    <x-input-label for="note" value="{{ __('Isi Catatan') }}" class="text-gray-700" />
                    <textarea 
                        id="note" 
                        name="note" 
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-gray-900 bg-white" 
                        rows="5" 
                        placeholder="Masukkan detail teknis atau catatan di sini..." 
                        required></textarea>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button 
                        type="button"
                        x-on:click="$dispatch('close')" 
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-md transition duration-150">
                        Batal
                    </button>
                    
                    <x-primary-button class="bg-indigo-600 hover:bg-indigo-700">
                        {{ __('Simpan Catatan') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- SCRIPT MODAL --}}
<script>
    function openAppMonthlyModal() {
        document.getElementById('app-monthly-modal').classList.remove('hidden');
    }

    function closeAppMonthlyModal() {
        document.getElementById('app-monthly-modal').classList.add('hidden');
    }

    window.onclick = function(event) {
        let modal = document.getElementById('app-monthly-modal');
        if (event.target == modal) {
            closeAppMonthlyModal();
        }
    }
</script>
</x-app-layout>