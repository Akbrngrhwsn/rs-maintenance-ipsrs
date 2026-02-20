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
     * Returns room name for when available, otherwise the user's role.
     */
    private function getUserDivision($user)
    {
        if ($user->role === 'kepala_ruang' && isset($user->room) && $user->room && $user->room->name) {
            return $user->room->name;
        }
        return $user->role;
    }

    // List meetings (history)
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Meeting::query();

        // Prefer filtering by division_role if the column exists; otherwise fall back to ownership (created_by)
        if (!in_array($user->role, ['admin', 'direktur'])) {
            if (Schema::hasColumn('meetings', 'division_role')) {
                // For 
                $divisionFilter = $user->role;
                if ($user->role === 'kepala_ruang' && isset($user->room) && $user->room && $user->room->name) {
                    $divisionFilter = $user->room->name;
                }
                $query->where('division_role', $divisionFilter);
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
        // - If user isand has a room, use the room name
        // - If admin and provided a division_role, allow it
        // - Otherwise use the user's role
        if ($user->role === 'kepala_ruang' && isset($user->room) && $user->room && $user->room->name) {
            $division = $user->room->name;
        } elseif ($user->role === 'admin' && $request->filled('division_role')) {
            $division = $request->input('division_role');
        } else {
            $division = $user->role;
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
                $divisionFilter = $this->getUserDivision($user);
                if ($meeting->division_role !== $divisionFilter) abort(403);
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
                $divisionFilter = $this->getUserDivision($user);
                if ($meeting->division_role !== $divisionFilter) abort(403);
            } else {
                if ($meeting->created_by !== $user->id) abort(403);
            }
        }
        $rooms = [];
        if ($user && $user->role === 'admin') {
            $rooms = Room::orderBy('name')->get();
        }
        return view('meetings.edit', compact('meeting', 'rooms'));
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
                $divisionFilter = $this->getUserDivision($user);
                if ($meeting->division_role !== $divisionFilter) abort(403);
            } else {
                if ($meeting->created_by !== $user->id) abort(403);
            }
        }

        if (Schema::hasColumn('meetings', 'title')) $meeting->title = $request->title;
        if (Schema::hasColumn('meetings', 'minutes')) $meeting->minutes = $request->minutes;
        // Allow admin to update division_role (may be a role or a room name)
        if ($user->role === 'admin' && Schema::hasColumn('meetings', 'division_role') && $request->filled('division_role')) {
            $meeting->division_role = $request->input('division_role');
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
                $divisionFilter = $this->getUserDivision($user);
                if ($meeting->division_role !== $divisionFilter) abort(403);
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
                $divisionFilter = $this->getUserDivision($user);
                if ($meeting->division_role !== $divisionFilter) abort(403);
            } else {
                if ($meeting->created_by !== $user->id) abort(403);
            }
        }

        $pdf = Pdf::loadView('meetings.pdf', compact('meeting'));
        return $pdf->download('rapat-'.$meeting->id.'-'.date('Ymd').'.pdf');
    }
}
