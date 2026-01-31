@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4">
    <h1 class="text-2xl font-bold">Edit Rapat</h1>
    <form method="POST" action="{{ route('meetings.update', $meeting->id) }}">
        @csrf
        @method('PATCH')
        <div class="mb-3">
            <label class="block font-bold">Judul</label>
            <input name="title" value="{{ old('title', $meeting->title) }}" class="w-full border px-2 py-1" />
        </div>
        <div class="mb-3">
            <label class="block font-bold">Notulensi</label>
            <textarea name="minutes" class="w-full border px-2 py-1" rows="8">{{ old('minutes', $meeting->minutes) }}</textarea>
        </div>
        <div class="mb-3">
            <label class="block font-bold">Divisi</label>
            @php
                $roles = ['admin','direktur','manager','bendahara','staff'];
                $isAdmin = Auth::user() && Auth::user()->role === 'admin';
                $selectedRole = in_array($meeting->division_role, $roles) ? $meeting->division_role : 'manager';
                $selectedRoom = $selectedRole === 'manager' ? $meeting->division_role : '';
            @endphp
            @if($isAdmin)
                <select id="division_role_role" class="w-full border px-2 py-1">
                    @foreach($roles as $r)
                        <option value="{{ $r }}" {{ $selectedRole === $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
                <select id="division_room_select" class="w-full border px-2 py-1 mt-2" style="display:none;" aria-hidden="true">
                    @foreach($rooms as $r)
                        <option value="{{ $r->name }}" {{ $selectedRoom === $r->name ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="division_role" id="division_role_input" value="{{ $selectedRole === 'manager' ? $selectedRoom : $selectedRole }}" />
            @else
                @php
                    $divisionDisplay = Auth::user()->role;
                    if(Auth::user()->role === 'manager' && Auth::user()->room) {
                        $divisionDisplay = Auth::user()->room->name;
                    }
                @endphp
                <input type="text" readonly value="{{ $divisionDisplay }}" class="w-full border px-2 py-1 bg-gray-50" />
            @endif
        </div>
        <div>
            <button class="px-3 py-2 bg-blue-600 text-white rounded">Simpan</button>
            <a href="{{ route('meetings.index') }}" class="px-3 py-2 bg-gray-100 rounded">Batal</a>
        </div>
    </form>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function(){
    var roleSelect = document.getElementById('division_role_role');
    var roomSelect = document.getElementById('division_room_select');
    var hidden = document.getElementById('division_role_input');
    if(!roleSelect) return;
    function updateHidden(){
        var role = roleSelect.value;
        if(role === 'manager'){
            roomSelect.style.display = '';
            roomSelect.removeAttribute('aria-hidden');
            hidden.value = roomSelect.value || '';
        } else {
            roomSelect.style.display = 'none';
            roomSelect.setAttribute('aria-hidden','true');
            hidden.value = role;
        }
    }
    roleSelect.addEventListener('change', updateHidden);
    if(roomSelect){
        roomSelect.addEventListener('change', function(){ hidden.value = roomSelect.value; });
    }
    updateHidden();
});
</script>
