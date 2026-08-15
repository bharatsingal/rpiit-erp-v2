<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\SubjectOffering;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Marking attendance is the one screen that has to be faster than paper.
 * v1's attendance was abandoned after 178 records; the whole design here is
 * built around a lecturer holding a phone in one hand with a class waiting:
 *
 *   - everyone starts Present, so a full class is one tap (Save)
 *   - only absentees are touched
 *   - the whole class saves in ONE request, not one per student
 *   - marked_by and marked_at are always recorded
 */
class AttendanceController extends Controller
{
    public function index()
    {
        $year = AcademicYear::current();

        $offerings = SubjectOffering::with(['subject', 'batch.course', 'section', 'faculty'])
            ->when($year, fn ($q) => $q->where('academic_year_id', $year->id))
            ->get()
            ->sortBy(fn ($o) => $o->batch?->name.' '.$o->subject?->name);

        return view('attendance.index', compact('offerings', 'year'));
    }

    public function create(SubjectOffering $offering, Request $request)
    {
        $date = $request->date('date')?->toDateString() ?? now()->toDateString();

        $students = $offering->students()->get();

        $session = AttendanceSession::where('subject_offering_id', $offering->id)
            ->whereDate('held_on', $date)
            ->first();

        // Re-opening an already-marked class shows what was saved before,
        // rather than silently resetting everyone to present.
        $existing = $session
            ? $session->records()->pluck('status', 'student_id')->all()
            : [];

        $offering->load(['subject', 'batch.course', 'section']);

        return view('attendance.mark', compact('offering', 'students', 'date', 'session', 'existing'));
    }

    public function store(SubjectOffering $offering, Request $request)
    {
        $data = $request->validate([
            'date'            => ['required', 'date'],
            'period'          => ['nullable', 'integer', 'min:1', 'max:12'],
            'absent'          => ['array'],
            'absent.*'        => ['integer'],
            'late'            => ['array'],
            'late.*'          => ['integer'],
        ]);

        $students = $offering->students()->pluck('students.id');
        $absent   = array_map('intval', $data['absent'] ?? []);
        $late     = array_map('intval', $data['late'] ?? []);

        DB::transaction(function () use ($offering, $data, $students, $absent, $late) {
            $session = AttendanceSession::updateOrCreate(
                [
                    'subject_offering_id' => $offering->id,
                    'held_on'             => $data['date'],
                    'period_number'       => $data['period'] ?? null,
                ],
                [
                    'section_id' => $offering->section_id,
                    'marked_by'  => auth()->id(),
                    'marked_at'  => now(),
                    'status'     => 'final',
                ]
            );

            // One bulk write for the whole class — this is what makes it fast
            // enough to survive contact with a real lecture.
            $now = now();
            $rows = $students->map(fn ($id) => [
                'attendance_session_id' => $session->id,
                'student_id'            => $id,
                'status'                => in_array($id, $absent, true) ? 'absent'
                                          : (in_array($id, $late, true) ? 'late' : 'present'),
                'created_at'            => $now,
                'updated_at'            => $now,
            ])->all();

            AttendanceRecord::where('attendance_session_id', $session->id)->delete();
            foreach (array_chunk($rows, 500) as $chunk) {
                AttendanceRecord::insert($chunk);
            }
        });

        $present = $students->count() - count($absent);

        return redirect()->route('attendance.index')
            ->with('status', "Saved — {$present} present, ".count($absent)." absent.");
    }
}
