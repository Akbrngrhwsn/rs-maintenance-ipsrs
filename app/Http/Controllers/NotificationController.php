<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\AppRequest;
use App\Models\Procurement;
use App\Models\NewItemRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function check()
    {
        $user = Auth::user();
        $role = $user->role;
        
        // Standarisasi key JSON agar frontend (JavaScript) mudah membacanya 
        // tanpa mempedulikan siapa role yang sedang login.
        $response = [
            'role' => $role,
            'has_notification' => false,
            'counts' => [
                'reports'      => 0,
                'request_apps' => 0,
                'procurements' => 0,
                'apps'         => 0, // Khusus badge anggaran aplikasi Bendahara
            ]
        ];

        // --- ADMIN ---
        if ($role === 'admin') {
            $response['counts']['reports'] = Report::where('status', 'Belum Diproses')->count();
            $response['counts']['request_apps'] = AppRequest::where('status', 'submitted_to_admin')->count();

            // PENGADAAN (Gabungan 3 Sumber: Laporan IT, Barang Baru, dan Kebutuhan Aplikasi)
            $procGeneral = Procurement::whereIn('status', ['approved_by_director', 'approved'])->count();
            $procNewItem = NewItemRequest::whereIn('status', ['pending_admin', 'approved'])->count();
            $procApp     = AppRequest::where('needs_procurement', true)
                            ->whereIn('procurement_approval_status', ['approved_by_director', 'approved'])
                            ->count();
                            
            $response['counts']['procurements'] = $procGeneral + $procNewItem + $procApp;
        } 
        
        // --- DIREKTUR ---
        elseif ($role === 'direktur') {
            $response['counts']['request_apps'] = AppRequest::whereIn('status', ['submitted_to_director', 'pending_director'])->count();
            
            // PENGADAAN (Gabungan 3 Sumber)
            $procGeneral = Procurement::where('status', 'submitted_to_director')->count();
            $procNewItem = NewItemRequest::where('status', 'pending_director')->count();
            $procApp     = AppRequest::where('needs_procurement', true)
                            ->where('procurement_approval_status', 'submitted_to_director')
                            ->count();

            $response['counts']['procurements'] = $procGeneral + $procNewItem + $procApp;
        }

        // --- MANAGEMENT ---
        elseif ($role === 'management') {
            $response['counts']['request_apps'] = AppRequest::where('status', 'submitted_to_management')->count();
            
            // PENGADAAN (Gabungan 3 Sumber)
            $procGeneral = Procurement::where('status', 'submitted_to_management')->count();
            $procNewItem = NewItemRequest::where('status', 'pending_management')->count();
            $procApp     = AppRequest::where('needs_procurement', true)
                            ->where('procurement_approval_status', 'submitted_to_management')
                            ->count();

            $response['counts']['procurements'] = $procGeneral + $procNewItem + $procApp;
        }
        
        // --- BENDAHARA ---
        elseif ($role === 'bendahara') {
            // Badge Anggaran Aplikasi
            $response['counts']['apps'] = AppRequest::where('procurement_approval_status', 'submitted_to_bendahara')->count();
            
            // Badge Validasi Keuangan (Pengadaan Umum + Barang Baru)
            $procGeneral = Procurement::where('status', 'submitted_to_bendahara')->count();
            $procNewItem = NewItemRequest::where('status', 'pending_bendahara')->count();
            
            $response['counts']['procurements'] = $procGeneral + $procNewItem;
        }

        // --- KEPALA RUANG ---
        elseif ($role === 'kepala_ruang') {
            $roomIds = $user->rooms()->pluck('id')->toArray();

            if (!empty($roomIds)) {
                $response['counts']['procurements'] = Procurement::where('status', 'submitted_to_kepala_ruang')
                    ->whereHas('report', function ($q) use ($roomIds) {
                        $q->whereIn('room_id', $roomIds);
                    })->count();
            }
        }

        // Cek secara otomatis apakah ada notifikasi secara keseluruhan (Jika total count > 0)
        $response['has_notification'] = array_sum($response['counts']) > 0;

        return response()->json($response);
    }

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