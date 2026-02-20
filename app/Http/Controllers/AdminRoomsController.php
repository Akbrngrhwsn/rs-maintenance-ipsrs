<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class AdminRoomsController extends Controller
{
    public function index()
    {
        $rooms = Room::with('kepala_ruang')->orderBy('name')->get()->map(function ($r) {
            $r->kepala_ruangs_count = $r->kepala_ruang ? 1 : 0;
            return $r;
        });
        $kepala_ruangs = User::where('role', 'kepala_ruang')->orderBy('name')->get();
        return view('admin.rooms', compact('rooms', 'kepala_ruangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:rooms,name',
        ]);

        Room::create(['name' => $request->name]);

        return back()->with('success', 'Ruangan baru berhasil ditambahkan.');
    }

    // === UPDATE (Dimodifikasi untuk Handle Nama &) ===
    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        // A. Jika Request Update Nama Ruangan
        if ($request->has('name')) {
            $request->validate([
                // unique:table,column,except_id (agar tidak error jika nama sama dengan diri sendiri)
                'name' => 'required|string|unique:rooms,name,' . $id, 
            ]);
            $room->name = $request->name;
            $pesan = 'Nama ruangan diperbarui.';
        }

        // B. Jika Request
        if ($request->has('kepala_ruang_id')) {
            $request->validate([
                'kepala_ruang_id' => 'nullable|exists:users,id',
            ]);
            $room->kepala_ruang_id = $request->kepala_ruang_id;
            $pesan = 'kepala_ruang ruangan diperbarui.';
        }

        $room->save();

        return redirect()->route('admin.rooms.index')->with('success', $pesan ?? 'Data berhasil disimpan.');
    }

    // === DESTROY (Baru: Hapus Ruangan) ===
    public function destroy($id)
    {
        $room = Room::findOrFail($id);
        
        // Opsional: Cek apakah ada data terkait sebelum dihapus (jika perlu)
        
        $room->delete();

        return back()->with('success', 'Ruangan berhasil dihapus.');
    }
}