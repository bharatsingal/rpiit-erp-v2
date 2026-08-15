<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Batch;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\SubjectOffering;
use Illuminate\Support\Facades\DB;

class BatchController extends Controller
{
    public function show(Batch $batch)
    {
        $year = AcademicYear::current();
        $batch->load('course');

        // Which term this batch sits in now, and how many are in each.
        $byTerm = Enrollment::query()
            ->where('batch_id', $batch->id)
            ->when($year, fn ($q) => $q->where('academic_year_id', $year->id))
            ->select('term_id', DB::raw('COUNT(*) as total'))
            ->groupBy('term_id')
            ->with('term')
            ->get()
            ->filter(fn ($r) => $r->term)
            ->sortBy(fn ($r) => $r->term->number);

        $currentTerm = $byTerm->sortByDesc('total')->first()?->term;

        $offerings = SubjectOffering::with(['subject', 'term', 'faculty'])
            ->where('batch_id', $batch->id)
            ->when($year, fn ($q) => $q->where('academic_year_id', $year->id))
            ->withCount('attendanceSessions')
            ->get()
            ->sortBy(fn ($o) => [$o->term?->number, $o->subject?->code]);

        $students = Enrollment::query()
            ->where('batch_id', $batch->id)
            ->when($year, fn ($q) => $q->where('academic_year_id', $year->id))
            ->with('student')
            ->get()
            ->pluck('student')
            ->filter()
            ->sortBy('first_name');

        return view('batches.show', [
            'batch'       => $batch,
            'byTerm'      => $byTerm,
            'currentTerm' => $currentTerm,
            'offerings'   => $offerings,
            'students'    => $students,
            'subjects'    => Subject::where('is_active', true)->orderBy('code')->get(),
            'terms'       => $batch->course->terms()->orderBy('number')->get(),
            'year'        => $year,
        ]);
    }
}
