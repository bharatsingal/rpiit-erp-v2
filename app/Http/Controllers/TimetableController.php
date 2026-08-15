<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Batch;
use App\Models\Period;
use App\Models\SubjectOffering;
use App\Models\TimetableSlot;
use Illuminate\Http\Request;

/**
 * The weekly grid for one batch: periods down, days across.
 *
 * It exists mainly to serve attendance — once a batch has a timetable, a
 * lecturer can open "what am I teaching now" instead of hunting for the class.
 */
class TimetableController extends Controller
{
    private const DAYS = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
                          4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];

    public function show(Batch $batch)
    {
        $visible = auth()->user()->visibleCourseIds();
        abort_if($visible !== null && ! in_array($batch->course_id, $visible), 403);

        $year = AcademicYear::current();

        $offerings = SubjectOffering::with(['subject', 'faculty', 'term'])
            ->where('batch_id', $batch->id)
            ->when($year, fn ($q) => $q->where('academic_year_id', $year->id))
            ->get()
            ->sortBy(fn ($o) => $o->subject?->code);

        // Keyed by "day-period" so the grid can look each cell up directly.
        $slots = TimetableSlot::with('subjectOffering.subject', 'subjectOffering.faculty')
            ->whereIn('subject_offering_id', $offerings->pluck('id'))
            ->get()
            ->keyBy(fn ($s) => $s->day_of_week.'-'.$s->period_number);

        return view('timetable.show', [
            'batch'     => $batch->load('course'),
            'periods'   => Period::orderBy('number')->get(),
            'days'      => self::DAYS,
            'offerings' => $offerings,
            'slots'     => $slots,
            'year'      => $year,
        ]);
    }

    public function store(Batch $batch, Request $request)
    {
        $visible = auth()->user()->visibleCourseIds();
        abort_if($visible !== null && ! in_array($batch->course_id, $visible), 403);

        $data = $request->validate([
            'day'      => ['required', 'integer', 'min:1', 'max:7'],
            'period'   => ['required', 'integer', 'min:1', 'max:12'],
            'offering' => ['nullable', 'integer'],
            'room'     => ['nullable', 'string', 'max:30'],
        ]);

        $period = Period::where('number', $data['period'])->firstOrFail();

        // Empty selection clears the cell.
        if (empty($data['offering'])) {
            TimetableSlot::whereHas('subjectOffering', fn ($q) => $q->where('batch_id', $batch->id))
                ->where('day_of_week', $data['day'])
                ->where('period_number', $data['period'])
                ->delete();

            return back()->with('status', 'Slot cleared.');
        }

        $offering = SubjectOffering::where('batch_id', $batch->id)
            ->findOrFail($data['offering']);

        // One subject per cell — replace whatever was there.
        TimetableSlot::whereHas('subjectOffering', fn ($q) => $q->where('batch_id', $batch->id))
            ->where('day_of_week', $data['day'])
            ->where('period_number', $data['period'])
            ->delete();

        TimetableSlot::create([
            'subject_offering_id' => $offering->id,
            'section_id'          => $offering->section_id,
            'day_of_week'         => $data['day'],
            'period_number'       => $data['period'],
            'starts_at'           => $period->starts_at,
            'ends_at'             => $period->ends_at,
            'room'                => $data['room'] ?? null,
        ]);

        return back()->with('status', 'Timetable updated.');
    }
}
