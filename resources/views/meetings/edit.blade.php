@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Edit Rapat</h1>
    
    <form method="POST" action="{{ route('meetings.update', $meeting->id) }}" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        @csrf
        @method('PATCH')
        
        <div class="mb-4">
            <label class="block font-bold text-gray-700 mb-1.5">Judul</label>
            <input type="text" name="title" value="{{ old('title', $meeting->title) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" required />
        </div>
        
        <div class="mb-4">
            <label class="block font-bold text-gray-700 mb-1.5">Notulensi</label>
            <textarea name="minutes" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" rows="8" required>{{ old('minutes', $meeting->minutes) }}</textarea>
        </div>
        
        <div class="mb-6">
            <label class="block font-bold text-gray-700 mb-1.5">Divisi / Penanggung Jawab</label>
            @php
                $roles = ['admin', 'direktur', 'kepala_ruang', 'bendahara', 'staff'];
                $isAdmin = Auth::user() && Auth::user()->role === 'admin';
                
                // Ambil role dari user pembuat rapat untuk nilai default select pertama
                $selectedRole = $meeting->user->role ?? 'kepala_ruang';
                // Ambil ID user pembuat rapat untuk otomatis terpilih di select kedua
                $selectedUserId = $meeting->created_by ?? ''; 
            @endphp
            
            @if($isAdmin)
                <select id="role_select" class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    @foreach($roles as $r)
                        <option value="{{ $r }}" {{ $selectedRole === $r ? 'selected' : '' }}>
                            {{ strtoupper(str_replace('_', ' ', $r)) }}
                        </option>
                    @endforeach
                </select>

                <select id="user_select" name="created_by" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <option value="">-- Memuat user... --</option>
                </select>
            @else
                @php
                    $divisionDisplay = Auth::user()->role;
                    if(Auth::user()->role === 'kepala_ruang' && Auth::user()->room) {
                        $divisionDisplay = Auth::user()->room->name;
                    }
                @endphp
                <input type="text" readonly value="{{ strtoupper(str_replace('_', ' ', $divisionDisplay)) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 text-gray-500 cursor-not-allowed font-medium" />
                <input type="hidden" name="created_by" value="{{ Auth::id() }}">
            @endif
        </div>
        
        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg shadow transition-colors">
                Simpan Perubahan
            </button>
            <a href="{{ route('meetings.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-lg transition-colors border border-gray-200">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    var roleSelect = document.getElementById('role_select');
    var userSelect = document.getElementById('user_select');
    
    // Menerima data seluruh user dari Controller (diubah jadi format JSON untuk JavaScript)
    var allUsers = @json($users ?? []);
    var preSelectedUserId = "{{ $selectedUserId }}";

    if(!roleSelect || !userSelect) return;

    function updateUsersDropdown(){
        var selectedRole = roleSelect.value;
        userSelect.innerHTML = ''; // Kosongkan dropdown

        // Filter data user berdasarkan role yang dipilih
        var filteredUsers = allUsers.filter(function(u) {
            return u.role === selectedRole;
        });

        // Jika tidak ada user dengan role tersebut
        if (filteredUsers.length === 0) {
            var opt = document.createElement('option');
            opt.value = "";
            opt.text = "-- Tidak ada user untuk role ini --";
            userSelect.appendChild(opt);
            return;
        }

        // Isi dropdown dengan user yang sesuai
        filteredUsers.forEach(function(u) {
            var opt = document.createElement('option');
            opt.value = u.id; // Nilai ID yang disimpan ke DB
            opt.text = u.name; // Nama user yang tampil
            
            // Pilih otomatis (selected) jika cocok dengan data sebelumnya
            if (u.id == preSelectedUserId) {
                opt.selected = true;
            }
            userSelect.appendChild(opt);
        });
    }

    // Jalankan ketika role diganti
    roleSelect.addEventListener('change', updateUsersDropdown);
    
    // Jalankan sekali saat pertama kali halaman terbuka
    updateUsersDropdown();
});
</script>
@endsection