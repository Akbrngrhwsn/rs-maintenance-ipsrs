<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Schema;

class MeetingController extends Controller
{
    /**
     * Determine the division filter value for the given user.
     * Returns room names for when available, otherwise the user's role.
     */
    private function getUserDivisionNames($user)
    {
        if ($user->role === 'kepala_ruang') {
            $rooms = $user->rooms()->pluck('name')->toArray();
            if (!empty($rooms)) {
                return $rooms;
            }
        }
        return [$user->role];
    }
    
    /**
     * Check if user has access to a meeting based on division_role
     */
    private function userHasAccessToMeeting($user, $divisionRole)
    {
        if (in_array($user->role, ['admin', 'direktur'])) {
            return true;
        }
        
        $allowedDivisions = $this->getUserDivisionNames($user);
        return in_array($divisionRole, $allowedDivisions);
    }

    // List meetings (history)
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Meeting::with('user');

        // Prefer filtering by division_role if the column exists; otherwise fall back to ownership (created_by)
        if (!in_array($user->role, ['admin', 'direktur'])) {
            if (Schema::hasColumn('meetings', 'division_role')) {
                $allowedDivisions = $this->getUserDivisionNames($user);
                $query->whereIn('division_role', $allowedDivisions);
            } else {
                $query->where('created_by', $user->id);
            }
        }

        $meetings = $query->orderBy('meeting_date', 'desc')->paginate(15);
        $rooms = [];
        if ($user && $user->role === 'admin') {
            $rooms = Room::orderBy('name')->get();
        }
        return view('meetings.index', compact('meetings', 'rooms'));
    }

    // Store new meeting
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'meeting_date' => 'required|date',
            'minutes' => 'required|string',
            'division_role' => 'nullable|string'
        ]);

        $user = Auth::user();
        
        // Determine division value:
        // - If user is kepala_ruang and has rooms, use the division_role from request (must be one of their rooms)
        // - If admin and provided a division_role, allow it
        // - Otherwise use the user's role
        $division = $user->role;
        
        if ($user->role === 'kepala_ruang') {
            $allowedRooms = $user->rooms()->pluck('name')->toArray();
            if (!empty($allowedRooms)) {
                // If division_role provided, validate it's one of their rooms
                if ($request->filled('division_role') && in_array($request->input('division_role'), $allowedRooms)) {
                    $division = $request->input('division_role');
                } else {
                    // Default to first room if not provided
                    $division = $allowedRooms[0] ?? $user->role;
                }
            }
        } elseif ($user->role === 'admin' && $request->filled('division_role')) {
            $division = $request->input('division_role');
        }

        // Build data array conditionally to avoid DB errors if columns are missing
        $data = [];
        if (Schema::hasColumn('meetings', 'title')) $data['title'] = $request->title;
        if (Schema::hasColumn('meetings', 'meeting_date')) $data['meeting_date'] = $request->meeting_date;
        if (Schema::hasColumn('meetings', 'minutes')) $data['minutes'] = $request->minutes;
        if (Schema::hasColumn('meetings', 'created_by')) $data['created_by'] = $user->id;
        if (Schema::hasColumn('meetings', 'division_role')) $data['division_role'] = $division;

        // If none of the expected columns exist, abort to avoid silent failure
        if (empty($data)) {
            return back()->with('error', 'Tabel meetings tidak memiliki kolom yang diperlukan.');
        }

        $meeting = Meeting::create($data);

        return redirect()->route('meetings.index')->with('success', 'Rapat disimpan.');
    }

    public function show($id)
    {
        $user = Auth::user();
        $meeting = Meeting::findOrFail($id);
        // Authorization: allow admin/direktur; otherwise check division_role or ownership
        if (!in_array($user->role, ['admin', 'direktur'])) {
            if (Schema::hasColumn('meetings', 'division_role')) {
                if (!$this->userHasAccessToMeeting($user, $meeting->division_role)) {
                    abort(403);
                }
            } else {
                if ($meeting->created_by !== $user->id) abort(403);
            }
        }
        return view('meetings.show', compact('meeting'));
    }

    public function edit($id)
    {
        $user = Auth::user();
        $meeting = Meeting::findOrFail($id);
        if (!in_array($user->role, ['admin', 'direktur'])) {
            if (Schema::hasColumn('meetings', 'division_role')) {
                if (!$this->userHasAccessToMeeting($user, $meeting->division_role)) {
                    abort(403);
                }
            } else {
                if ($meeting->created_by !== $user->id) abort(403);
            }
        }
        
        $rooms = [];
        if ($user && $user->role === 'admin') {
            $rooms = Room::orderBy('name')->get();
        } elseif ($user && $user->role === 'kepala_ruang') {
            // For kepala_ruang, show only their managed rooms
            $rooms = $user->rooms()->orderBy('name')->get();
        }
        
        // 👇 TAMBAHKAN BARIS INI UNTUK MENGAMBIL DATA USER 👇
        $users = \App\Models\User::all(); 
        
        // 👇 TAMBAHKAN 'users' KE DALAM COMPACT 👇
        return view('meetings.edit', compact('meeting', 'rooms', 'users'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'minutes' => 'required|string',
        ]);

        $user = Auth::user();
        $meeting = Meeting::findOrFail($id);

        // Authorization: allow admin/direktur; otherwise check division_role or ownership
        if (!in_array($user->role, ['admin', 'direktur'])) {
            if (Schema::hasColumn('meetings', 'division_role')) {
                if (!$this->userHasAccessToMeeting($user, $meeting->division_role)) {
                    abort(403);
                }
            } else {
                if ($meeting->created_by !== $user->id) abort(403);
            }
        }

        if (Schema::hasColumn('meetings', 'title')) $meeting->title = $request->title;
        if (Schema::hasColumn('meetings', 'minutes')) $meeting->minutes = $request->minutes;
        
        // Allow admin/kepala_ruang to update division_role (may be a role or a room name)
        if (Schema::hasColumn('meetings', 'division_role') && $request->filled('division_role')) {
            if ($user->role === 'admin') {
                $meeting->division_role = $request->input('division_role');
            } elseif ($user->role === 'kepala_ruang') {
                $allowedRooms = $user->rooms()->pluck('name')->toArray();
                if (in_array($request->input('division_role'), $allowedRooms)) {
                    $meeting->division_role = $request->input('division_role');
                }
            }
        }
        
        if (Schema::hasColumn('meetings', 'edited_by')) $meeting->edited_by = $user->id;
        $meeting->save();

        return redirect()->route('meetings.index')->with('success', 'Rapat diperbarui.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $meeting = Meeting::findOrFail($id);
        if (!in_array($user->role, ['admin', 'direktur'])) {
            if (Schema::hasColumn('meetings', 'division_role')) {
                if (!$this->userHasAccessToMeeting($user, $meeting->division_role)) {
                    abort(403);
                }
            } else {
                if ($meeting->created_by !== $user->id) abort(403);
            }
        }
        $meeting->delete();
        return redirect()->route('meetings.index')->with('success', 'Rapat dihapus.');
    }

    public function exportPdf($id)
    {
        $user = Auth::user();
        $meeting = Meeting::findOrFail($id);
        if (!in_array($user->role, ['admin', 'direktur'])) {
            if (Schema::hasColumn('meetings', 'division_role')) {
                if (!$this->userHasAccessToMeeting($user, $meeting->division_role)) {
                    abort(403);
                }
            } else {
                if ($meeting->created_by !== $user->id) abort(403);
            }
        }

        $pdf = Pdf::loadView('meetings.pdf', compact('meeting'));
        return $pdf->download('rapat-'.$meeting->id.'-'.date('Ymd').'.pdf');
    }
}
