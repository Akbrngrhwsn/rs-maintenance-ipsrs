<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    public function index()
    {
        // Ambil semua user, urutkan dari yang terbaru
        // Kecualikan diri sendiri agar admin tidak tidak sengaja menghapus aksesnya sendiri
        $users = User::where('id', '!=', Auth::id())->latest()->paginate(10);
        $rooms = Room::orderBy('name')->get();
        return view('admin.users.index', compact('users', 'rooms'));
    }

    // Assign a room to a man (or remove assignment)
    public function assignRoom(Request $request, $id)
    {
        $request->validate([
            'room_id' => 'nullable|exists:rooms,id',
        ]);

        $user = User::findOrFail($id);

        // Only allow assigning if user is a manag
        if ($user->role !== 'kepala_ruang') {
            return back()->with('error', 'Hanya pengguna dengan role Kepala Ruang yang dapat ditetapkan ke ruangan.');
        }

        // Remove this user as kepala ruang from any rooms they currently manage
        Room::where('kepala_ruang_id', $user->id)->update(['kepala_ruang_id' => null]);

        if ($request->room_id) {
            $room = Room::findOrFail($request->room_id);
            // Overwrite previous kepala ruang (if any)
            $room->kepala_ruang_id = $user->id;
            $room->save();
        }

        return back()->with('success', "Kepala Ruang {$user->name} berhasil diperbarui untuk ruangan.");
    }

    // Admin: create new user from admin UI
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,direktur,kepala_ruang,bendahara,staff',
            'room_id' => 'nullable|exists:rooms,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        
        if ($request->role === 'kepala_ruang' && $request->room_id) {
            // Remove previou assignment for that room (if any)
            Room::where('id', $request->room_id)->update(['kepala_ruang_id' => $user->id]);
        }

        return back()->with('success', "User {$user->name} berhasil dibuat.");
    }

    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:admin,direktur,kepala_ruang,bendahara,staff', // Tambahkan bendahara
        ]);

        $user = User::findOrFail($id);
        
        // Update Role
        $user->role = $request->role;
        $user->save();

        return back()->with('success', "Role pengguna {$user->name} berhasil diubah menjadi " . ucfirst($request->role));
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return back()->with('success', 'User berhasil dihapus dari sistem.');
    }

    // Admin: update/reset user password
    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::findOrFail($id);
        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', "Password pengguna {$user->name} berhasil diubah.");
    }
}