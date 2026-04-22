<?php

namespace App\Http\Controllers;

use App\Models\ItStaff;
use Illuminate\Http\Request;

class ItStaffController extends Controller
{
    public function index()
    {
        $staffs = ItStaff::orderBy('nama', 'asc')->get();
        return view('admin.it_staff.index', compact('staffs'));
    }

    public function store(Request $request)
    {
        $request->validate(['nama' => 'required|string|max:255']);
        ItStaff::create(['nama' => $request->nama]);
        return back()->with('success', 'Teknisi berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate(['nama' => 'required|string|max:255']);
        $staff = ItStaff::findOrFail($id);
        $staff->update(['nama' => $request->nama]);
        return back()->with('success', 'Data teknisi diperbarui.');
    }

    public function destroy($id)
    {
        ItStaff::findOrFail($id)->delete();
        return back()->with('success', 'Teknisi dihapus.');
    }

    public function setOnDuty($id)
    {
        // Reset semua teknisi agar tidak on duty
        ItStaff::query()->update(['is_on_duty' => false]);
        
        // Set teknisi terpilih menjadi on duty
        $staff = ItStaff::findOrFail($id);
        $staff->update(['is_on_duty' => true]);

        return back()->with('success', $staff->nama . ' sekarang ditugaskan sebagai Teknisi Jaga (On-Duty).');
    }
}