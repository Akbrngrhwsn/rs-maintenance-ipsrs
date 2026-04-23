<?php

namespace App\Http\Controllers;

use App\Models\NewItemRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode; 
use PDF;

class NewItemRequestController extends Controller
{
    // === FORM KEPALA RUANG ===
    public function create()
    {
        if(Auth::user()->role !== 'kepala_ruang') abort(403);
        $room = Auth::user()->room;
        if(!$room) return back()->with('error', 'Anda belum ditugaskan ke ruangan manapun.');
        
        return view('kepala_ruang.new_items.create', compact('room'));
    }

    // === SIMPAN PENGAJUAN KEPALA RUANG ===
    public function store(Request $request)
{
    $request->validate([
        'purpose' => 'required|string|max:255',
        'items.*.nama' => 'required|string',
        'items.*.harga_satuan' => 'required|numeric|min:0',
        'items.*.jumlah' => 'required|numeric|min:1',
    ]);

    // --- GENERATE QR UNTUK KARU ---
    $user = Auth::user();
    $qrText = "Diajukan oleh: " . $user->name . "\nRuangan: " . ($user->room->name ?? '-') . "\nTanggal: " . now()->format('d/m/Y H:i');
    $qrKaru = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(100)->generate($qrText));

    NewItemRequest::create([
        'user_id' => $user->id,
        'room_id' => $user->room->id,
        'qr_karu' => $qrKaru, // Simpan QR Karu
        'purpose' => $request->purpose,
        'items' => $request->items,
        'status' => 'pending_admin'
    ]);

    return redirect()->route('kepala-ruang.procurements.index')->with('success', 'Pengajuan barang baru berhasil dikirim.');
}

    // === METHOD APPROVAL UNTUK MASING-MASING ROLE ===
    public function approve(Request $request, $id, $role)
    {
        $itemRequest = NewItemRequest::findOrFail($id);
        $user = Auth::user();

        // Generate teks untuk QR Code
        $qrText = "Disetujui oleh " . $user->name . " (" . $user->role . ") pada " . now()->format('Y-m-d H:i:s') . "\nPengajuan: " . $itemRequest->purpose;
        $qrCodeBase64 = base64_encode(QrCode::format('png')->size(100)->generate($qrText));

        switch ($role) {
            case 'admin':
                if($user->role !== 'admin') abort(403);
                $itemRequest->status = 'pending_management';
                $itemRequest->qr_admin = $qrCodeBase64;
                break;
            case 'management':
                if($user->role !== 'management') abort(403);
                $itemRequest->status = 'pending_bendahara';
                $itemRequest->qr_management = $qrCodeBase64;
                break;
            case 'bendahara':
                if($user->role !== 'bendahara') abort(403);
                
                // Langsung ubah status menjadi approved (bypass direktur)
                $itemRequest->status = 'approved';
                $itemRequest->qr_bendahara = $qrCodeBase64;
                
                // Otomatis buat QR Direktur sebagai tanda sudah diwakilkan
                $qrDirekturText = "Diwakilkan & Disetujui oleh Direktur Utama pada " . now()->format('Y-m-d H:i:s') . "\nPengajuan: " . $itemRequest->purpose;
                $itemRequest->qr_direktur = base64_encode(QrCode::format('png')->size(100)->generate($qrDirekturText));
                break;
            case 'direktur':
                if($user->role !== 'direktur') abort(403);
                $itemRequest->status = 'approved';
                $itemRequest->qr_direktur = $qrCodeBase64;
                break;
            default:
                abort(400, 'Invalid Role Approval');
        }

        $itemRequest->save();
        return back()->with('success', 'Pengajuan barang baru berhasil disetujui.');
    }

    // === METHOD REJECT ===
    public function reject(Request $request, $id)
    {
        $itemRequest = NewItemRequest::findOrFail($id);
        $itemRequest->status = 'rejected';
        $itemRequest->reject_note = $request->catatan ?? 'Ditolak oleh ' . Auth::user()->role;
        $itemRequest->save();

        return back()->with('success', 'Pengajuan barang baru berhasil ditolak.');
    }

    // === METHOD EXPORT PDF ===
    public function exportSingle($id)
{
    $procurement = NewItemRequest::with(['user', 'room'])->findOrFail($id);

    $data = [
        'procurement' => $procurement,
        'qrKaru'      => $procurement->qr_karu,
        'qrAdmin'     => $procurement->qr_admin,
        'qrManagement'=> $procurement->qr_management,
        'qrBendahara' => $procurement->qr_bendahara,
        'qrDirektur'  => $procurement->qr_direktur,
    ];

    $pdf = PDF::loadView('pdf.new_item_single', $data);
    return $pdf->stream('Laporan_Barang_Baru_' . $procurement->id . '.pdf');
}
}