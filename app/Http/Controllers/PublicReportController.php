<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Room;
use Illuminate\Http\Request;

class PublicReportController extends Controller
{
    // Halaman Form Input
    public function index()
    {
        // Ambil daftar ruangan dari DB
        $rooms = Room::orderBy('name')->get();
        return view('public.form', compact('rooms'));
    }

    // Proses Simpan Laporan
    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'keluhan' => 'required',
            'urgency' => 'required|in:rendah,sedang,tinggi',
            'urgency_reason' => 'nullable|required_if:urgency,sedang,tinggi',
        ]);

        $room = Room::find($request->room_id);

        Report::create([
            'ruangan' => $room?->name ?? null,
            'room_id' => $request->room_id,
            'keluhan' => $request->keluhan,
            'urgency' => $request->urgency,
            'urgency_reason' => $request->urgency_reason,
            'status'  => 'Belum Diproses' // Default
        ]);

        return redirect()->route('public.tracking')->with('success', 'Laporan berhasil dikirim!');
    }

    // Halaman Tracking
    public function tracking(Request $request)
    {
        $completedStatus = ['Selesai', 'Ditolak', 'Tidak Selesai'];

        // 1. Query Dasar
        $query = Report::query();
        if ($request->has('ticket') && $request->ticket != '') {
            $query->where('ticket_number', 'LIKE', '%' . $request->ticket . '%');
        }

        // 2. Ambil Pending (Belum Selesai)
        // Clone query agar filter tiket tetap terbawa
        $pendingReports = (clone $query)->whereNotIn('status', $completedStatus)
            ->orderByRaw("CASE 
                WHEN urgency = 'tinggi' THEN 3 
                WHEN urgency = 'sedang' THEN 2 
                ELSE 1 END DESC")
            ->orderBy('created_at', 'asc')
            ->paginate(10, ['*'], 'pending_page');

        // 3. Ambil Completed (Riwayat)
        $completedReports = (clone $query)->whereIn('status', $completedStatus)
            ->latest()
            ->paginate(10, ['*'], 'history_page');

        return view('public.tracking', compact('pendingReports', 'completedReports'));
    }
}