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

    // Assign a room to a manager (or remove assignment)
    public function assignRoom(Request $request, $id)
    {
        $request->validate([
            'room_id' => 'nullable|exists:rooms,id',
        ]);

        $user = User::findOrFail($id);

        // Only allow assigning if user is a manager
        if ($user->role !== 'manager') {
            return back()->with('error', 'Hanya pengguna dengan role Manager yang dapat ditetapkan ke ruangan.');
        }

        // Remove this user as manager from any rooms they currently manage
        Room::where('manager_id', $user->id)->update(['manager_id' => null]);

        if ($request->room_id) {
            $room = Room::findOrFail($request->room_id);
            // Overwrite previous manager (if any)
            $room->manager_id = $user->id;
            $room->save();
        }

        return back()->with('success', "Manager {$user->name} berhasil diperbarui untuk ruangan.");
    }

    // Admin: create new user from admin UI
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,direktur,manager,bendahara,staff',
            'room_id' => 'nullable|exists:rooms,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // If role is manager and room selected, assign the room to this manager
        if ($request->role === 'manager' && $request->room_id) {
            // Remove previous manager assignment for that room (if any)
            Room::where('id', $request->room_id)->update(['manager_id' => $user->id]);
        }

        return back()->with('success', "User {$user->name} berhasil dibuat.");
    }

    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:admin,direktur,manager,bendahara,staff', // Tambahkan bendahara
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
}