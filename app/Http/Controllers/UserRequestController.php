<?php

namespace App\Http\Controllers;

use App\Models\UserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class UserRequestController extends Controller
{
    // Mengecek apakah user berhak mengakses halaman ini
    private function checkAccess()
    {
        if (!in_array(Auth::user()->role, ['admin', 'kepala_ruang'])) {
            abort(403, 'Anda tidak memiliki akses ke fitur ini.');
        }
    }

    public function index(Request $request)
    {
        $this->checkAccess();
        $user = Auth::user();

        // Admin lihat semua, Karu lihat miliknya sendiri
        $query = UserRequest::with('user')->orderBy('created_at', 'desc');
        
        if ($user->role !== 'admin') {
            $query->where('created_by', $user->id);
        }

        $requests = $query->paginate(15);
        
        return view('user_requests.index', compact('requests'));
    }

    public function store(Request $request)
    {
        $this->checkAccess();
        $request->validate([
            'nip' => 'required|string|max:50',
            'nama' => 'required|string|max:255',
            'unit' => 'required|string|max:100',
            'status_karyawan' => 'required|string|max:100',
        ]);

        UserRequest::create([
            'nip' => $request->nip,
            'nama' => $request->nama,
            'unit' => $request->unit,
            'status_karyawan' => $request->status_karyawan,
            'status' => 'diproses',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('user-requests.index')->with('success', 'Request User berhasil diajukan.');
    }

    // Menampilkan halaman Edit (Hanya Admin)
    public function edit($id)
    {
        if (Auth::user()->role !== 'admin') abort(403);
        
        $requestData = UserRequest::findOrFail($id);
        return view('user_requests.edit', compact('requestData'));
    }

    // Menyimpan perubahan data (Hanya Admin)
    public function update(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') abort(403);
        
        $request->validate([
            'nip' => 'required|string|max:50',
            'nama' => 'required|string|max:255',
            'unit' => 'required|string|max:100',
            'status_karyawan' => 'required|string|max:100',
        ]);

        $userReq = UserRequest::findOrFail($id);
        $userReq->update([
            'nip' => $request->nip,
            'nama' => $request->nama,
            'unit' => $request->unit,
            'status_karyawan' => $request->status_karyawan,
        ]);

        return redirect()->route('user-requests.index')->with('success', 'Data request berhasil diperbarui.');
    }

    // Hanya Admin yang bisa mengubah status menjadi Selesai
    public function updateStatus($id)
    {
        if (Auth::user()->role !== 'admin') abort(403);

        $userReq = UserRequest::findOrFail($id);
        $userReq->update(['status' => 'selesai']);

        return back()->with('success', 'Status berhasil diubah menjadi Selesai.');
    }

    // Hanya admin yang bisa menghapus (CRUD)
    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin') abort(403);
        
        UserRequest::findOrFail($id)->delete();
        return back()->with('success', 'Data request berhasil dihapus.');
    }

    // Download PDF Bulanan
    public function exportPdf(Request $request)
    {
        $this->checkAccess();
        
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        $user = Auth::user();

        $query = UserRequest::whereMonth('created_at', $month)
                            ->whereYear('created_at', $year)
                            ->orderBy('created_at', 'asc');

        // Jika Karu, hanya download data miliknya
        if ($user->role !== 'admin') {
            $query->where('created_by', $user->id);
        }

        $requests = $query->get();
        $bulanNama = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');

        $pdf = Pdf::loadView('user_requests.pdf', compact('requests', 'bulanNama'));
        return $pdf->download('Laporan-Request-User-'.$bulanNama.'.pdf');
    }
}