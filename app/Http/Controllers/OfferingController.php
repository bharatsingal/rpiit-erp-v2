<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Batch;
use App\Models\Staff;
use App\Models\Subject;
use App\Models\SubjectOffering;
use App\Models\Term;
use Illuminate\Http\Request;

/**
 * An offering is "this subject, taught to this batch, in this term, this year,
 * by this person". Attendance and marks both hang off it, so it is the piece
 * that has to exist before either module does anything.
 */
class OfferingController extends Controller
{
    public function index()
    {
        $year = AcademicYear::current();

        return view('offerings.index', [
            'year'      => $year,
            'offerings' => SubjectOffering::with(['subject', 'batch.course', 'term', 'section', 'faculty'])
                ->when($year, fn ($q) => $q->where('academic_year_id', $year->id))
                ->withCount('attendanceSessions')
                ->get()
                ->sortBy(fn ($o) => $o->batch?->name.' '.$o->subject?->code),
            'subjects'  => Subject::where('is_active', true)->orderBy('code')->get(),
            'batches'   => Batch::with('course')->where('is_active', true)
                            ->get()->sortBy('name'),
            'teachers'  => Staff::where('category', 'staff')->where('is_active', true)
                            ->whereNotNull('user_id')->with('user')->orderBy('name')->get(),
        ]);
    }

    /** Terms depend on the batch's course, so the form fetches them on demand. */
    public function termsFor(Batch $batch)
    {
        return $batch->course->terms()->orderBy('number')->get(['id', 'name', 'number']);
    }

    public function store(Request $request)
    {
        $year = AcademicYear::current();
        abort_unless($year, 422, 'No current academic year is set.');

        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'batch_id'   => ['required', 'exists:batches,id'],
            'term_id'    => ['required', 'exists:terms,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'faculty_id' => ['nullable', 'exists:users,id'],
        ]);

        $term = Term::findOrFail($data['term_id']);
        $batch = Batch::findOrFail($data['batch_id']);

        if ($term->course_id !== $batch->course_id) {
            return back()->withErrors(['term_id' => 'That term belongs to a different course.']);
        }

        SubjectOffering::updateOrCreate(
            [
                'subject_id'       => $data['subject_id'],
                'batch_id'         => $data['batch_id'],
                'term_id'          => $data['term_id'],
                'academic_year_id' => $year->id,
                'section_id'       => $data['section_id'] ?? null,
            ],
            ['faculty_id' => $data['faculty_id'] ?? null]
        );

        return back()->with('status', 'Class added — it now appears on the attendance screen.');
    }
}
