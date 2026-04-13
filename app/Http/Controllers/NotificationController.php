<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\AppRequest;
use App\Models\Procurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function check()
    {
        $user = Auth::user();
        $role = $user->role;
        $response = [
            'role' => $role,
            'has_notification' => false,
            'counts' => [] 
        ];

        // --- ADMIN (Tetap) ---
        if ($role === 'admin') {
            $reportCount = Report::where('status', 'Belum Diproses')->count();
            // Hitung AppRequest yang baru masuk ke alur Admin IT
            $appCount = AppRequest::where('status', 'submitted_to_admin')->count();
            // Hitung semua request apps yang pending
            $requestAppsCount = AppRequest::whereIn('status', ['submitted_to_admin' ])->count();

            // Hitung pengadaan yang menunggu diselesaikan admin (sudah di-ACC direktur)
            $approvedProcurements = Procurement::whereIn('status', ['approved_by_director', 'approved'])->count();

            // 2. TAMBAHAN: Hitung Aplikasi yang sudah disetujui Direktur (status 'in_progress' atau 'approved')
            // Sesuaikan status 'in_progress' jika itu menandakan aplikasi baru saja disetujui direktur
            $approvedApps = AppRequest::where('status', 'in_progress')->count();

            // 3. TAMBAHAN: Hitung Pengadaan Khusus Aplikasi yang sudah disetujui Direktur
            $approvedAppProcurements = AppRequest::where('needs_procurement', true)
                ->where('procurement_approval_status', 'approved')
                ->count();

            $response['counts'] = [
                'reports' => $reportCount,
                'apps' => $appCount,
                'request_apps' => $requestAppsCount,
                'procurements' => $approvedProcurements,
                'approved_apps' => $approvedApps, // Data baru
                'approved_app_procurements' => $approvedAppProcurements // Data baru
            ];

            // PERBAIKAN: Tambahkan $approvedProcurements ke dalam kondisi if
            if($reportCount > 0 || $appCount > 0 || $requestAppsCount > 0 || $approvedProcurements > 0) {
                $response['has_notification'] = true;
            }
        } 
        // --- DIREKTUR (TAMBAHAN: tangani submitted_to_director dan pending_director) ---
        elseif ($role === 'direktur') {
        // 1. Notifikasi Persetujuan Aplikasi (Alur Utama)
        $pendingApps = AppRequest::whereIn('status', ['submitted_to_director', 'pending_director'])->count();
        
        // 2. Notifikasi Pengadaan Barang Umum (Model Procurement)
        $pendingProcurements = Procurement::where('status', 'submitted_to_director')->count();
        
        // 3. Notifikasi Pengadaan Khusus Aplikasi (Model AppRequest - BARU)
        // Menghitung aplikasi yang butuh pengadaan dan statusnya sudah sampai di Direktur
        $appProcurementForDirector = AppRequest::where('needs_procurement', true)
            ->where('procurement_approval_status', 'submitted_to_director')
            ->count();

        $requestAppsCount = AppRequest::whereIn('status', ['submitted_to_director', 'pending_director'])->count();

        $response['counts'] = [
            'pending_apps' => $pendingApps,
            'pending_procurements' => $pendingProcurements,
            'request_apps' => $requestAppsCount,
            'app_procurements' => $appProcurementForDirector // Data baru untuk indikator
        ];

        // Notifikasi aktif jika ada aplikasi, pengadaan umum, ATAU pengadaan aplikasi yang pending
        if($pendingApps > 0 || $pendingProcurements > 0 || $requestAppsCount > 0 || $appProcurementForDirector > 0) {
            $response['has_notification'] = true;
        }
    }

        // --- MANAGEMENT (BARU) ---
        elseif ($role === 'management') {
            // Management harus melihat AppRequest yang diteruskan dari Admin
            $appsForManagement = AppRequest::where('status', 'submitted_to_management')->count();
            // dan juga pengadaan yang dialihkan ke management (jika ada)
            $procurementsForManagement = Procurement::where('status', 'submitted_to_management')->count();

            $response['counts'] = [
                'submitted_apps' => $appsForManagement,
                'submitted_procurements' => $procurementsForManagement,
                'request_apps' => $appsForManagement
            ];

            if($appsForManagement > 0 || $procurementsForManagement > 0) {
                $response['has_notification'] = true;
            }
        }
        // --- KEPALA RUANG ---
        elseif ($role === 'kepala_ruang') {
            // Memantau pengadaan dari Admin IT, tapi hanya untuk ruangan yang dikelolanya
            $room = $user->room; // hasOne(Room::class)

            if ($room) {
                $pendingProcurements = Procurement::where('status', 'submitted_to_kepala_ruang')
                    ->whereHas('report', function ($q) use ($room) {
                        $q->where('room_id', $room->id);
                    })->count();
            } else {
                $pendingProcurements = 0;
            }

            $response['counts'] = [
                'pending_procurements' => $pendingProcurements,
            ];

            if ($pendingProcurements > 0) {
                $response['has_notification'] = true;
            }
        }
        // --- BENDAHARA (SESUAI REVISI) ---
        elseif ($role === 'bendahara') {
            // 1. Hitung pengadaan barang umum (dari model Procurement)
            $pendingProcurements = Procurement::where('status', 'submitted_to_bendahara')->count();
            
            // 2. Hitung validasi anggaran untuk pengadaan aplikasi (dari model AppRequest)
            $appsProcurementCount = AppRequest::where('procurement_approval_status', 'submitted_to_bendahara')->count();
            
            $response['counts'] = [
                'apps' => $appsProcurementCount, // Label untuk pengadaan aplikasi
                'pending_procurements' => $pendingProcurements, // Label untuk pengadaan barang
            ];

            // Notifikasi aktif jika salah satu ada yang pending
            if($pendingProcurements > 0 || $appsProcurementCount > 0) {
                $response['has_notification'] = true;
            }
        }

        return response()->json($response);
    }

    // Method untuk mendapatkan detail laporan terbaru (untuk TTS)
    public function getLatestReport()
    {
        $user = Auth::user();
        
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $report = Report::where('status', 'Belum Diproses')
            ->latest('created_at')
            ->first();

        if (!$report) {
            return response()->json(['report' => null]);
        }

        return response()->json([
            'report' => [
                'id' => $report->id,
                'ruangan' => $report->ruangan,
                'ticket_number' => $report->ticket_number,
                'keluhan' => $report->keluhan,
                'urgency' => $report->urgency,
                'urgency_reason' => $report->urgency_reason,
            ]
        ]);
    }
}