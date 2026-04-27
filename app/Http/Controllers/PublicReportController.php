<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Room;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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

        $needsProcurement = $request->has('needs_procurement');

        $items = [];
        if ($request->has('needs_procurement')) {
            foreach ($request->item_names as $key => $name) {
                if ($name) {
                    $items[] = [
                        'name' => $name,
                        'quantity' => $request->item_qtys[$key] ?? 1
                    ];
                }
            }
        }

        Report::create([
            'room_id' => $request->room_id,
            'ruangan' => Room::find($request->room_id)?->name,
            'keluhan' => $request->keluhan,
            'urgency' => $request->urgency,
            'urgency_reason' => $request->urgency_reason,
            'status' => 'Belum Diproses',
            'needs_procurement' => $request->has('needs_procurement'),
            'procurement_items_request' => $items, // Simpan array ke JSON
            'procurement_status' => $request->has('needs_procurement') ? 'pending_admin' : null,
        ]);

        return redirect()->route('public.tracking')->with('success', 'Laporan dan Permintaan Pengadaan dikirim!');
    }

    // Halaman Tracking
    public function tracking(Request $request)
    {
        $completedStatus = ['Selesai', 'Ditolak', 'Tidak Selesai'];

        // --- TAMBAHAN: Ambil teknisi yang sedang bertugas ---
        $onDutyStaff = \App\Models\ItStaff::where('is_on_duty', true)->first();

        // 1. Query Dasar - Tambahkan with('itStaff') agar data teknisi terbawa
        $query = Report::with('itStaff'); 
        
        if ($request->has('ticket') && $request->ticket != '') {
            $query->where('ticket_number', 'LIKE', '%' . $request->ticket . '%');
        }

        // 2. Ambil Pending (Belum Selesai)
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

        // Kirim variabel $onDutyStaff ke view
        return view('public.tracking', compact('pendingReports', 'completedReports', 'onDutyStaff'));
    }

    // === EXPORT MONTHLY REPORT (untuk semua role yang login) ===
    public function exportMonthlyReport(Request $request)
    {
        // 1. Validasi: hanya authenticated user yang bisa export
        if (!Auth::check()) {
            abort(403, 'Anda harus login untuk mengunduh laporan.');
        }

        // 2. Ambil input bulan dari request
        $monthInput = $request->input('month', date('Y-m'));
        $startDate = Carbon::parse($monthInput)->startOfMonth();
        $endDate = Carbon::parse($monthInput)->endOfMonth();

        // 3. Ambil data laporan selesai dalam periode bulan tersebut
        $reports = Report::whereIn('status', ['Selesai', 'Ditolak', 'Tidak Selesai'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();

        // 4. Generate QR Code dengan informasi validator
        $validator = Auth::user()->name ?? 'User';
        $role = Auth::user()->role ?? 'guest';
        $dateString = $startDate->locale('id')->translatedFormat('F Y');

        $qrData = "Laporan Riwayat Kerusakan\n" .
                 "Periode: " . $dateString . "\n" .
                 "Diunduh oleh: " . $validator . " ({$role})\n" .
                 "Total Laporan: " . $reports->count();

        $qrCode = base64_encode(QrCode::format('png')
            ->size(200)
            ->margin(1)
            ->generate($qrData));

        // 5. Load View dan generate PDF
        $pdf = Pdf::loadView('pdf.tracking_monthly_report', [
            'reports' => $reports,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'validator' => $validator,
            'role' => $role,
            'qrCode' => $qrCode,
            'dateString' => $dateString
        ]);

        return $pdf->download('Riwayat_Kerusakan_' . $startDate->format('M_Y') . '.pdf');
    }

    // === TAMBAHKAN METHOD INI UNTUK EKSPOR PDF BULANAN SESUAI ROLE ===
    public function exportTrackingMonthly(Request $request)
    {
        $monthInput = $request->input('month', date('Y-m'));
        
        $startDate = \Carbon\Carbon::parse($monthInput)->startOfMonth();
        $endDate = \Carbon\Carbon::parse($monthInput)->endOfMonth();

        // Hanya ambil laporan yang sudah selesai/diputuskan
        $completedStatus = ['Selesai', 'Ditolak', 'Tidak Selesai'];

        $query = Report::with(['room', 'itStaff'])
            ->whereIn('status', $completedStatus)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc');

        // --- FILTER KHUSUS KEPALA RUANG ---
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user->role === 'kepala_ruang') {
            $roomIds = $user->rooms()->pluck('id')->toArray();
            if (!empty($roomIds)) {
                // Hanya ambil laporan dari ruangan Karu tersebut
                $query->whereIn('room_id', $roomIds);
            } else {
                // Jika Karu belum diassign ruangan, jangan tampilkan apa-apa
                $query->where('room_id', 0); 
            }
        }

        $reports = $query->get();
        $validator = $user->name;
        $dateString = $startDate->locale('id')->translatedFormat('F Y');

        // Detail QR Code - untuk multiple rooms, tampilkan jumlah ruangan yang dilaporkan
        $ruanganNames = [];
        if ($user->role === 'kepala_ruang') {
            $ruanganNames = $user->rooms()->pluck('name')->toArray();
            $ruanganCetak = !empty($ruanganNames) ? implode(', ', $ruanganNames) : 'Belum ada ruang';
        } else {
            $ruanganCetak = 'Semua Ruangan (RS)';
        }
        
        $qrData = "Diunduh oleh: " . $validator . " (" . strtoupper($user->role) . ")\n" .
                  "Ruangan: " . $ruanganCetak . "\n" .
                  "Periode: " . $dateString . "\n" .
                  "Total Laporan: " . $reports->count();

        $qrCode = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
            ->size(200)
            ->margin(1)
            ->generate($qrData));

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.monthly_report', [
            'reports' => $reports,
            'startDate' => $startDate,
            'validator' => $validator,
            'qrCode' => $qrCode
        ]);

        $namaFile = $user->role === 'kepala_ruang' ? 'Riwayat_Kerusakan_' . str_replace(' ', '_', $ruanganCetak) . '_' . $startDate->format('M_Y') : 'Riwayat_Kerusakan_RS_' . $startDate->format('M_Y');

        return $pdf->download($namaFile . '.pdf');
    }
}