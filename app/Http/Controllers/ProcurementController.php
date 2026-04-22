<?php

namespace App\Http\Controllers;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Report;
use App\Models\Procurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProcurementController extends Controller
{
    // === HELPER: Generate QR Codes untuk validasi ===
    private function generateQrCodes($procurementId)
    {
        $baseUrl = route('director.procurements.index') . "?procurement={$procurementId}";
        
        return [
            'qr_kepala_ruang' => base64_encode(QrCode::format('png')->size(150)->generate($baseUrl . '&approver=kepala_ruang')),
            'qr_management' => base64_encode(QrCode::format('png')->size(150)->generate($baseUrl . '&approver=management')),
            'qr_bendahara' => base64_encode(QrCode::format('png')->size(150)->generate($baseUrl . '&approver=bendahara')),
            'qr_direktur' => base64_encode(QrCode::format('png')->size(150)->generate($baseUrl . '&approver=direktur'))
        ];
    }

    // === ADMIN: Halaman Daftar Pengadaan ===
    public function index(Request $request)
    {
        $query = Procurement::with('report')->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('report', function($q) use ($s) {
                $q->where('ticket_number', 'like', "%{$s}%")
                  ->orWhere('ruangan', 'like', "%{$s}%");
            });
        }

        $procurements = $query->paginate(20)->withQueryString();

        // --- TAMBAHAN: Ambil data Pengadaan Barang Baru ---
        $newItemsQuery = \App\Models\NewItemRequest::with(['user', 'room'])->latest();
        if ($request->filled('search')) {
            $s = $request->search;
            $newItemsQuery->where('purpose', 'like', "%{$s}%")
                          ->orWhereHas('room', function($q) use ($s) {
                              $q->where('name', 'like', "%{$s}%"); // Sesuaikan jika nama kolom ruangan berbeda
                          });
        }
        $newItemRequests = $newItemsQuery->paginate(20, ['*'], 'new_page')->withQueryString();

        return view('admin.procurements.index', compact('procurements', 'newItemRequests'));
    }

    // === ADMIN: Form Buat Pengadaan ===
    public function create($id)
    {
        $report = Report::findOrFail($id);
        return view('procurement.form', compact('report'));
    }

    // === ADMIN: Convert laporan menjadi pengadaan ===
    public function convert($id)
    {
        $report = Report::findOrFail($id);
        // Redirect to the procurement creation form
        return redirect()->route('procurement.create', $id)->with('success', 'Siap untuk diproses sebagai pengadaan.');
    }

    // === ADMIN: Simpan Pengadaan Baru ===
    public function store(Request $request, $id)
    {
        $request->validate([
            'items.*.nama' => 'required',
            'items.*.jumlah' => 'required|numeric',
        ]);

        // If a procurement already exists for this report, update it instead of creating a new one.
        $existing = Procurement::where('report_id', $id)->latest()->first();
        if ($existing) {
            $existing->items = $request->items;
            $existing->status = 'submitted_to_kepala_ruang';
            $existing->director_note = null; // clear previous rejection note
            $existing->save();
            $proc = $existing;
        } else {
            $proc = Procurement::create([
                'report_id' => $id,
                'items' => $request->items, // Data array barang disimpan ke JSON
                'status' => 'submitted_to_kepala_ruang'
            ]);
        }

        // Generate QR codes untuk semua approval level
        $qrCodes = $this->generateQrCodes($proc->id);
        $proc->update($qrCodes);

        return redirect()->route('dashboard')->with('success', 'Pengadaan diajukan.');
    }

    // === ADMIN: Edit Pengadaan ===
    public function edit($id)
    {
        $proc = Procurement::with('report')->findOrFail($id);
        return view('procurement.edit', compact('proc'));
    }

    // === ADMIN: Update Pengadaan ===
    public function update(Request $request, $id)
    {
        $proc = Procurement::findOrFail($id);

        $request->validate([
            'items.*.nama' => 'required',
            'items.*.jumlah' => 'required|numeric',
        ]);

        $proc->items = $request->items;
        // when admin edits, reset status to submitted_to_kepala_ruang if it was rejected
        if($proc->status === 'rejected') {
            $proc->status = 'submitted_to_kepala_ruang';
            $proc->director_note = null;
        }
        $proc->save();

        return redirect()->route('dashboard')->with('success', 'Pengajuan pengadaan diperbarui.');
    }

    // === KEPALA RUANG: Halaman Daftar Pengadaan ===
    public function kepalaRuangIndex(Request $request)
    {
        if(Auth::user()->role !== 'kepala_ruang') abort(403);

        $tab = $request->get('tab', 'pending'); // 'pending' or 'history'

        $query = Procurement::with('report');

        // Hanya pengadaan untuk ruangan yang dikelola user ini
        $room = Auth::user()->room; // hasOne Room
        if ($room) {
            $query->whereHas('report', function($q) use ($room) {
                $q->where('room_id', $room->id);
            });
        } else {
            // Jika belum punya ruangan, kembalikan kosong
            $procurements = collect();
            return view('kepala_ruang.procurements', compact('procurements', 'tab'));
        }

        if($tab === 'history') {
            // History: Sudah diproses atau ditolak
            $query->whereIn('status', [
                'submitted_to_management',
                'submitted_to_bendahara', 
                'submitted_to_director', 
                'approved_by_director',
                'completed', 
                'rejected'
            ]);
        } else {
            // Pending: Menunggu persetujuan Kepala Ruang
            $query->where('status', 'submitted_to_kepala_ruang');
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } else {
            if ($request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function($q) use ($term) {
                $q->where('items', 'LIKE', "%{$term}%")
                  ->orWhereHas('report', function($r) use ($term) {
                      $r->where('ticket_number', 'LIKE', "%{$term}%")
                        ->orWhere('ruangan', 'LIKE', "%{$term}%");
                  });
            });
        }

        $procurements = $query->latest()->get();

        // --- TAMBAHAN: Ambil data Pengajuan Barang Baru milik Ruangan Karu ini ---
        $newItemsQuery = \App\Models\NewItemRequest::with(['user', 'room'])
            ->where('room_id', Auth::user()->room_id ?? Auth::user()->room->id); // Hanya ambil milik ruangannya sendiri

        if($tab === 'history') {
            $newItemsQuery->whereIn('status', ['approved', 'rejected', 'completed']);
        } else {
            // Pending berarti yang masih diproses oleh Admin/Management/Bendahara/Direktur
            $newItemsQuery->whereNotIn('status', ['approved', 'rejected', 'completed']);
        }
        $newItemRequests = $newItemsQuery->latest()->get();

        return view('kepala_ruang.procurements', compact('procurements', 'tab', 'newItemRequests'));
    }

    // === KEPALA RUANG: Approve Pengadaan (teruskan ke Management) ===
    public function kepalaRuangApprove($id)
    {
        if(Auth::user()->role !== 'kepala_ruang') abort(403);

        $proc = Procurement::with('report')->findOrFail($id);

        // Validasi: pastikan procurement untuk ruangan ini
        $room = Auth::user()->room;
        if (!$room || $proc->report->room_id != $room->id) {
            abort(403, 'Anda tidak berwenang memproses pengadaan untuk ruangan ini.');
        }

        $proc->status = 'submitted_to_management';
        $proc->save();
        
        // Tambahan: Simpan handler dari IT Staff yang bertugas
        if ($proc->report) {
            $onDutyStaff = \App\Models\ItStaff::where('is_on_duty', true)->first();
            if ($onDutyStaff) {
                $proc->report->handled_by_karu = $onDutyStaff->nama;
                $proc->report->save();
            }
        }

        return back()->with('success', 'Pengadaan berhasil diteruskan ke Management.');
    }

    // === KEPALA RUANG: Reject Pengadaan ===
    public function kepalaRuangReject(Request $request, $id)
    {
        if(Auth::user()->role !== 'kepala_ruang') abort(403);

        $proc = Procurement::with('report')->findOrFail($id);

        // Validasi: pastikan procurement untuk ruangan ini
        $room = Auth::user()->room;
        if (!$room || $proc->report->room_id != $room->id) {
            abort(403, 'Anda tidak berwenang memproses pengadaan untuk ruangan ini.');
        }

        $proc->status = 'rejected';
        $proc->director_note = $request->catatan ?? null;
        $proc->save();

        return back()->with('success', 'Pengadaan berhasil ditolak oleh kepala ruang.');
    }

    // === MANAGEMENT: Halaman Daftar Pengadaan ===
    public function managementIndex(Request $request)
    {
        if(Auth::user()->role !== 'management') abort(403);

        $tab = $request->get('tab', 'pending'); // 'pending' or 'history'
        $query = Procurement::with('report');

        if($tab === 'history') {
            // History: Sudah disetujui management (lanjut ke bendahara/direktur) atau ditolak
            $query->whereIn('status', ['submitted_to_bendahara', 'submitted_to_director', 'approved_by_director', 'completed', 'rejected']); // <-- TAMBAHKAN COMPLETED
        }else {
            // Pending: Menunggu persetujuan Management
            $query->where('status', 'submitted_to_management');
        }

        // Filter Tanggal
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } else {
            if ($request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }
        }

        // Filter Pencarian
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function($q) use ($term) {
                $q->where('items', 'LIKE', "%{$term}%")
                  ->orWhereHas('report', function($r) use ($term) {
                      $r->where('ticket_number', 'LIKE', "%{$term}%")
                        ->orWhere('ruangan', 'LIKE', "%{$term}%");
                  });
            });
        }

        $procurements = $query->latest()->get();

        // --- TAMBAHAN: Ambil data Pengadaan Barang Baru ---
        $newItemsQuery = \App\Models\NewItemRequest::with(['user', 'room']);
        if($tab === 'history') {
            $newItemsQuery->whereIn('status', ['pending_bendahara', 'pending_director', 'approved', 'rejected', 'completed']);
        } else {
            $newItemsQuery->where('status', 'pending_management');
        }
        $newItemRequests = $newItemsQuery->latest()->get();

        return view('management.procurements', compact('procurements', 'tab', 'newItemRequests'));
    }

    // === MANAGEMENT: Approve Pengadaan (teruskan ke Bendahara) ===
    public function managementApprove($id)
    {
        if(Auth::user()->role !== 'management') abort(403);

        $proc = Procurement::with('report')->findOrFail($id);
        $proc->status = 'submitted_to_bendahara';
        $proc->save();
        
        // Tambahan: Simpan handler dari IT Staff yang bertugas
        if ($proc->report) {
            $onDutyStaff = \App\Models\ItStaff::where('is_on_duty', true)->first();
            if ($onDutyStaff) {
                $proc->report->handled_by_management = $onDutyStaff->nama;
                $proc->report->save();
            }
        }

        return back()->with('success', 'Pengadaan disetujui oleh Management dan diteruskan ke Bendahara.');
    }

    // === MANAGEMENT: Reject Pengadaan ===
    public function managementReject(Request $request, $id)
    {
        if(Auth::user()->role !== 'management') abort(403);

        $proc = Procurement::findOrFail($id);
        $proc->status = 'rejected';
        $proc->director_note = $request->catatan ?? null; // Menggunakan kolom yang sama untuk alasan penolakan
        $proc->save();

        return back()->with('success', 'Pengadaan berhasil ditolak oleh Management.');
    }

    // === BENDAHARA: Halaman Daftar Pengadaan ===
    public function bendaharaIndex(Request $request)
    {
        if(Auth::user()->role !== 'bendahara') abort(403);

        $tab = $request->get('tab', 'pending'); // 'pending' or 'history'

        $query = Procurement::with('report');

        if($tab === 'history') {
            $query->whereIn('status', ['approved_by_director', 'completed', 'rejected']); // <-- TAMBAHKAN COMPLETED
        } else {
            $query->where('status', 'submitted_to_bendahara');
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } else {
            if ($request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function($q) use ($term) {
                $q->where('items', 'LIKE', "%{$term}%")
                  ->orWhereHas('report', function($r) use ($term) {
                      $r->where('ticket_number', 'LIKE', "%{$term}%")
                        ->orWhere('ruangan', 'LIKE', "%{$term}%");
                  });
            });
        }

        $procurements = $query->latest()->get();

        // --- TAMBAHAN: Ambil data Pengadaan Barang Baru ---
        $newItemsQuery = \App\Models\NewItemRequest::with(['user', 'room']);
        if($tab === 'history') {
            $newItemsQuery->whereIn('status', ['pending_director', 'approved', 'rejected', 'completed']);
        } else {
            $newItemsQuery->where('status', 'pending_bendahara');
        }
        $newItemRequests = $newItemsQuery->latest()->get();

        return view('bendahara.procurements', compact('procurements', 'tab', 'newItemRequests'));
    }

    // === BENDAHARA: Approve Pengadaan (teruskan ke Direktur) ===
    public function bendaharaApprove($id)
    {
        if(Auth::user()->role !== 'bendahara') abort(403);

        $proc = Procurement::with('report')->findOrFail($id);
        $proc->status = 'submitted_to_director';
        $proc->save();
        
        // Tambahan: Simpan handler dari IT Staff yang bertugas
        if ($proc->report) {
            $onDutyStaff = \App\Models\ItStaff::where('is_on_duty', true)->first();
            if ($onDutyStaff) {
                $proc->report->handled_by_bendahara = $onDutyStaff->nama;
                $proc->report->save();
            }
        }

        return back()->with('success', 'Pengadaan berhasil diteruskan ke Direktur.');
    }

    // === BENDAHARA: Reject Pengadaan ===
    public function bendaharaReject(Request $request, $id)
    {
        if(Auth::user()->role !== 'bendahara') abort(403);

        $proc = Procurement::findOrFail($id);
        $proc->status = 'rejected';
        $proc->director_note = $request->catatan ?? null;
        $proc->save();

        return back()->with('success', 'Pengadaan berhasil ditolak oleh Bendahara.');
    }

    // === DIREKTUR: Halaman Daftar Pengadaan ===
    public function directorIndex(Request $request)
    {
        if(Auth::user()->role !== 'direktur') abort(403);

        $tab = $request->get('tab', 'pending'); // 'pending' or 'history'

        $query = Procurement::with('report');

        if($tab === 'history') {
            $query->whereIn('status', ['approved_by_director', 'completed', 'rejected']); // <-- TAMBAHKAN COMPLETED
        } else {
            $query->where('status', 'submitted_to_director');
        }

        // If a single exact date is provided, filter to that date only
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } else {
            if ($request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function($q) use ($term) {
                $q->where('items', 'LIKE', "%{$term}%")
                  ->orWhereHas('report', function($r) use ($term) {
                      $r->where('ticket_number', 'LIKE', "%{$term}%")
                        ->orWhere('ruangan', 'LIKE', "%{$term}%");
                  });
            });
        }

        $procurements = $query->latest()->get();

        // --- TAMBAHAN: Ambil data Pengadaan Barang Baru ---
        $newItemsQuery = \App\Models\NewItemRequest::with(['user', 'room']);
        if($tab === 'history') {
            $newItemsQuery->whereIn('status', ['approved', 'rejected', 'completed']);
        } else {
            $newItemsQuery->where('status', 'pending_director');
        }
        $newItemRequests = $newItemsQuery->latest()->get();

        return view('director.procurements', compact('procurements', 'tab', 'newItemRequests'));
    }

    // === DIREKTUR: Approve Pengadaan ===
    public function directorApprove($id)
    {
        if(Auth::user()->role !== 'direktur') abort(403);

        $proc = Procurement::with('report')->findOrFail($id);
        $proc->status = 'approved_by_director';
        $proc->save();
        
        // Tambahan: Simpan handler dari IT Staff yang bertugas
        if ($proc->report) {
            $onDutyStaff = \App\Models\ItStaff::where('is_on_duty', true)->first();
            if ($onDutyStaff) {
                $proc->report->handled_by_director = $onDutyStaff->nama;
                $proc->report->save();
            }
        }

        return back()->with('success', 'Pengadaan berhasil di-ACC.');
    }

    // === DIREKTUR: Reject Pengadaan ===
    public function directorReject(Request $request, $id)
    {
        if(Auth::user()->role !== 'direktur') abort(403);

        $proc = Procurement::findOrFail($id);
        $proc->status = 'rejected';
        // save optional director note (catatan)
        $proc->director_note = $request->catatan ?? null;
        $proc->save();

        return back()->with('success', 'Pengadaan berhasil ditolak.');
    }

    // === EXPORT: Download Pengadaan Report as PDF ===
    public function downloadProcurementReportPdf($id)
{
    try {
        $procurement = Procurement::with('report')->findOrFail($id);

        // Helper Generate QR
        $generateQr = function($text) {
            return base64_encode(
                QrCode::format('png')
                    ->size(100)->margin(0)->errorCorrection('M')->generate($text)
            );
        };

        // Inisialisasi dengan string kosong (BUKAN null) agar tidak merusak src img di Blade
        $qrAdmin = '';
        $qrkepala_ruang = '';
        $qrManagement = '';
        $qrBendahara = '';
        $qrDirektur = '';

        $s = $procurement->status; 

        // A. QR Admin (Selalu Ada)
        $infoAdmin = "Diajukan oleh Admin IT. Tiket: " . ($procurement->report->ticket_number ?? '-') . ". Tgl: " . $procurement->created_at->format('d/m/Y');
        $qrAdmin = $generateQr($infoAdmin);

        // B. QR Kepala Ruang
        $statusSetelahKapro = ['submitted_to_management', 'submitted_to_bendahara', 'submitted_to_director', 'approved_by_director'];
        if (in_array($s, $statusSetelahKapro)) {
            $qrkepala_ruang = $generateQr("Divalidasi Kepala ruang Unit. ID: " . $procurement->id);
        }

        // C. QR Management
        $statusSetelahManagement = ['submitted_to_bendahara', 'submitted_to_director', 'approved_by_director'];
        if (in_array($s, $statusSetelahManagement)) {
            $qrManagement = $generateQr("Divalidasi oleh Management. Tanggal: " . date('d/m/Y'));
        }

        // D. QR Bendahara
        $statusSetelahBendahara = ['submitted_to_director', 'approved_by_director'];
        if (in_array($s, $statusSetelahBendahara)) {
            $qrBendahara = $generateQr("Diverifikasi Bendahara. Anggaran Tersedia.");
        }

        // E. QR Direktur
        if ($s === 'approved_by_director') {
            $qrDirektur = $generateQr("Disetujui Direktur Utama. " . date('d/m/Y'));
        }

        // Load View
        $pdf = Pdf::loadView('pdf.procurement_single', [
            'procurement' => $procurement, 
            'qrAdmin' => $qrAdmin, 
            'qrkepala_ruang' => $qrkepala_ruang, 
            'qrManagement' => $qrManagement,
            'qrBendahara' => $qrBendahara, 
            'qrDirektur' => $qrDirektur
        ]);
        
        $pdf->setPaper('A4');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);
        
        $filename = 'laporan-pengadaan-' . $procurement->id . '-' . now()->format('d-m-Y') . '.pdf';
        
        return $pdf->download($filename);
        
        
    } catch (\Exception $e) {
        \Log::error('Error download procurement PDF: ' . $e->getMessage());
        return back()->with('error', 'Error mengunduh PDF: ' . $e->getMessage());
    }
}
public function finish($id)
    {
        $procurement = \App\Models\Procurement::findOrFail($id);
        
        // Memastikan pengadaan hanya bisa diselesaikan jika sudah di-ACC Direktur
        if ($procurement->status === 'approved_by_director') {
            $procurement->update([
                'status' => 'completed'
            ]);
            
            return redirect()->back()->with('success', 'Pengadaan telah berhasil diselesaikan!');
        }
        
        return redirect()->back()->with('error', 'Status pengadaan tidak dapat diubah karena belum disetujui.');
    }
}