<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Course;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    /** All courses, grouped the way the campus is organised. */
    public function index()
    {
        $year = AcademicYear::current();

        $counts = DB::table('enrollments')
            ->join('batches', 'batches.id', '=', 'enrollments.batch_id')
            ->join('students', 'students.id', '=', 'enrollments.student_id')
            ->when($year, fn ($q) => $q->where('enrollments.academic_year_id', $year->id))
            ->where('students.status', 'active')
            ->whereNull('students.deleted_at')
            ->select('batches.course_id', DB::raw('COUNT(DISTINCT students.id) as total'))
            ->groupBy('batches.course_id')
            ->pluck('total', 'course_id');

        $batchCounts = DB::table('batches')
            ->where('is_active', true)
            ->select('course_id', DB::raw('COUNT(*) as total'))
            ->groupBy('course_id')
            ->pluck('total', 'course_id');

        $courses = Course::orderBy('name')->get()
            ->each(function ($c) use ($counts, $batchCounts) {
                $c->student_total = (int) ($counts[$c->id] ?? 0);
                $c->batch_total   = (int) ($batchCounts[$c->id] ?? 0);
            })
            ->groupBy(fn ($c) => $c->discipline ?: 'other');

        return view('courses.index', compact('courses', 'year'));
    }

    public function show(Course $course)
    {
        $year = AcademicYear::current();

        $counts = DB::table('enrollments')
            ->join('students', 'students.id', '=', 'enrollments.student_id')
            ->when($year, fn ($q) => $q->where('enrollments.academic_year_id', $year->id))
            ->whereNull('students.deleted_at')
            ->select('enrollments.batch_id', DB::raw('COUNT(DISTINCT students.id) as total'))
            ->groupBy('enrollments.batch_id')
            ->pluck('total', 'batch_id');

        $batches = $course->batches()
            ->withCount('subjectOfferings')
            ->orderByDesc('start_year')
            ->get()
            ->each(fn ($b) => $b->student_total = (int) ($counts[$b->id] ?? 0));

        return view('courses.show', compact('course', 'batches', 'year'));
    }
}
