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
            $appCount = AppRequest::where('status', 'approved')->count();

            $response['counts'] = [
                'reports' => $reportCount,
                'apps' => $appCount
            ];
        } 
        // --- DIREKTUR (Tetap) ---
        elseif ($role === 'direktur') {
            $pendingApps = AppRequest::where('status', 'pending_director')->count();
            $pendingProcurements = Procurement::where('status', 'submitted_to_director')->count();

            $response['counts'] = [
                'pending_apps' => $pendingApps,
                'pending_procurements' => $pendingProcurements,
            ];

            if($pendingApps > 0 || $pendingProcurements > 0) $response['has_notification'] = true;
        }
        // --- MANAGER (BARU) ---
        elseif ($role === 'manager') {
            // Manager memantau pengadaan dari Admin IT, tapi hanya untuk ruangan yang dikelolanya
            $room = $user->room; // hasOne(Room::class)

            if ($room) {
                $pendingProcurements = Procurement::where('status', 'submitted_to_manager')
                    ->whereHas('report', function ($q) use ($room) {
                        $q->where('room_id', $room->id);
                    })->count();
            } else {
                $pendingProcurements = 0;
            }

            $response['counts'] = [
                'pending_procurements' => $pendingProcurements,
            ];

            if ($pendingProcurements > 0) $response['has_notification'] = true;
        }
        // --- BENDAHARA (UPDATE) ---
        elseif ($role === 'bendahara') {
            // Bendahara memantau pengadaan dari Manager
            $pendingProcurements = Procurement::where('status', 'submitted_to_bendahara')->count();
            
            $response['counts'] = [
                'pending_procurements' => $pendingProcurements,
            ];

            if($pendingProcurements > 0) $response['has_notification'] = true;
        }

        return response()->json($response);
    }
}