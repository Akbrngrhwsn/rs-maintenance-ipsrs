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
        
        // Standarisasi key JSON agar frontend (JavaScript) mudah membacanya.
        // Semua key dari kode lama dan kode baru dimasukkan ke sini agar tidak ada error 'undefined'
        $response = [
            'role' => $role,
            'has_notification' => false,
            'counts' => [
                // Key standard baru
                'reports'                   => 0,
                'request_apps'              => 0,
                'procurements'              => 0,
                // Key spesifik dari kode lama
                'apps'                      => 0, 
                'approved_apps'             => 0,
                'approved_app_procurements' => 0,
                'pending_apps'              => 0,
                'pending_procurements'      => 0,
                'app_procurements'          => 0,
                'submitted_apps'            => 0,
                'submitted_procurements'    => 0,
            ]
        ];

        // --- ADMIN ---
        if ($role === 'admin') {
            $response['counts']['reports'] = Report::where('status', 'Belum Diproses')->count();
            
            $reqApps = AppRequest::where('status', 'submitted_to_admin')->count();
            $response['counts']['request_apps'] = $reqApps;
            $response['counts']['apps']         = $reqApps; // Fitur kode lama

            // PENGADAAN (Gabungan 3 Sumber: Laporan IT, Barang Baru, dan Kebutuhan Aplikasi)
            $procGeneral = Procurement::whereIn('status', ['approved_by_director', 'approved'])->count();
            $procNewItem = NewItemRequest::whereIn('status', ['pending_admin', 'approved'])->count();
            $procApp     = AppRequest::where('needs_procurement', true)
                            ->whereIn('procurement_approval_status', ['approved_by_director', 'approved'])
                            ->count();
                            
            $response['counts']['procurements'] = $procGeneral + $procNewItem + $procApp;

            // FITUR YANG DIKEMBALIKAN (Dari kode pertama)
            $response['counts']['approved_apps'] = AppRequest::where('status', 'in_progress')->count();
            $response['counts']['approved_app_procurements'] = AppRequest::where('needs_procurement', true)
                ->where('procurement_approval_status', 'approved')
                ->count();
        } 
        
        // --- DIREKTUR ---
        elseif ($role === 'direktur') {
            $reqApps = AppRequest::whereIn('status', ['submitted_to_director', 'pending_director'])->count();
            $response['counts']['request_apps'] = $reqApps;
            
            // PENGADAAN (Gabungan 3 Sumber)
            $procGeneral = Procurement::where('status', 'submitted_to_director')->count();
            $procNewItem = NewItemRequest::where('status', 'pending_director')->count();
            $procApp     = AppRequest::where('needs_procurement', true)
                            ->where('procurement_approval_status', 'submitted_to_director')
                            ->count();

            $response['counts']['procurements'] = $procGeneral + $procNewItem + $procApp;

            // FITUR YANG DIKEMBALIKAN (Dari kode pertama)
            $response['counts']['pending_apps']         = $reqApps;
            $response['counts']['pending_procurements'] = $procGeneral + $procNewItem;
            $response['counts']['app_procurements']     = $procApp;
        }

        // --- MANAGEMENT ---
        elseif ($role === 'management') {
            $reqApps = AppRequest::where('status', 'submitted_to_management')->count();
            $response['counts']['request_apps'] = $reqApps;
            
            // PENGADAAN (Gabungan 3 Sumber)
            $procGeneral = Procurement::where('status', 'submitted_to_management')->count();
            $procNewItem = NewItemRequest::where('status', 'pending_management')->count();
            $procApp     = AppRequest::where('needs_procurement', true)
                            ->where('procurement_approval_status', 'submitted_to_management')
                            ->count();

            $response['counts']['procurements'] = $procGeneral + $procNewItem + $procApp;

            // FITUR YANG DIKEMBALIKAN (Dari kode pertama)
            $response['counts']['submitted_apps']         = $reqApps;
            $response['counts']['submitted_procurements'] = $procGeneral + $procNewItem;
        }
        
        // --- BENDAHARA ---
        elseif ($role === 'bendahara') {
            // Badge Anggaran Aplikasi
            $response['counts']['apps'] = AppRequest::where('procurement_approval_status', 'submitted_to_bendahara')->count();
            
            // Badge Validasi Keuangan (Pengadaan Umum + Barang Baru)
            $procGeneral = Procurement::where('status', 'submitted_to_bendahara')->count();
            $procNewItem = NewItemRequest::where('status', 'pending_bendahara')->count();
            
            $response['counts']['procurements'] = $procGeneral + $procNewItem;

            // FITUR YANG DIKEMBALIKAN (Dari kode pertama)
            $response['counts']['pending_procurements'] = $response['counts']['procurements'];
        }

        // --- KEPALA RUANG ---
        elseif ($role === 'kepala_ruang') {
            $roomIds = $user->rooms()->pluck('id')->toArray();

            if (!empty($roomIds)) {
                $procKepalaRuang = Procurement::where('status', 'submitted_to_kepala_ruang')
                    ->whereHas('report', function ($q) use ($roomIds) {
                        $q->whereIn('room_id', $roomIds);
                    })->count();

                $response['counts']['procurements'] = $procKepalaRuang;
                
                // FITUR YANG DIKEMBALIKAN (Dari kode pertama)
                $response['counts']['pending_procurements'] = $procKepalaRuang;
            }
        }

        // Cek secara otomatis apakah ada notifikasi secara keseluruhan (Jika total count > 0)
        // array_sum memastikan notifikasi aktif jika ada SATU saja angka yang lebih dari 0
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