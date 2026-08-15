<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Staff;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

/**
 * The public face of rpiitacademics.com.
 *
 * Course listings and figures come from the ERP rather than being typed into
 * the page, so the prospectus cannot drift out of date the way a static site
 * does — add a course in the ERP and it appears here.
 */
class HomeController extends Controller
{
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

        $courses = Course::where('is_active', true)
            ->where('is_lateral', false)      // lateral variants are an entry route, not a separate programme
            ->orderBy('name')
            ->get()
            ->each(fn ($c) => $c->student_total = (int) ($counts[$c->id] ?? 0))
            ->groupBy(fn ($c) => $c->discipline ?: 'other');

        return view('home', [
            'courses'  => $courses,
            'students' => Student::where('status', 'active')->count(),
            'staff'    => Staff::where('is_active', true)->count(),
            'programmes' => Course::where('is_active', true)->where('is_lateral', false)->count(),
        ]);
    }
}
