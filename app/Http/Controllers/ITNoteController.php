<?php

namespace App\Http\Controllers;

use App\Models\ITNote;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ITNoteController extends Controller
{
    public function index()
    {
        $notes = ITNote::latest()->get(); // Mengambil catatan terbaru
        return view('admin.it_notes.index', compact('notes'));
    }

    public function store(Request $request)
    {
        $request->validate(['note' => 'required']);
        ITNote::create(['note' => $request->note]);
        return back()->with('success', 'Catatan berhasil ditambahkan');
    }

    public function exportPdf(Request $request)
    {
        // Memecah format YYYY-MM dari input 'period'
        $period = $request->input('period', now()->format('Y-m'));
        $dateParts = explode('-', $period);
        $year = $dateParts[0];
        $month = $dateParts[1];

        $notes = ITNote::whereMonth('created_at', $month)
                        ->whereYear('created_at', $year)
                        ->latest()
                        ->get();

        $startDate = Carbon::createFromDate($year, $month, 1);
        $validator = auth()->user()->name;

        // Generate QR Code Validasi
        $qrData = "VALIDASI LAPORAN IT\nPeriode: " . $startDate->translatedFormat('F Y') . "\nOleh: " . $validator . "\nWaktu Cetak: " . now()->format('d/m/Y H:i');
        
        // Generate PNG QR Code dalam bentuk Base64
        $qrCode = base64_encode(QrCode::format('png')->size(150)->errorCorrection('H')->generate($qrData));

        $pdf = Pdf::loadView('admin.it_notes.pdf', [
            'notes' => $notes,
            'startDate' => $startDate,
            'validator' => $validator,
            'qrCode' => $qrCode
        ]);

        return $pdf->download('Laporan_IT_' . $startDate->format('F_Y') . '.pdf');
    }
}