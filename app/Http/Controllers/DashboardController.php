<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentFeeBalance;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $asOf = StudentFeeBalance::max('as_of');

        return view('dashboard', [
            'year'          => AcademicYear::current(),
            'students'      => Student::where('status', 'active')->count(),
            'passedOut'     => Student::where('status', 'passed_out')->count(),
            'staffCount'    => Staff::where('category', 'staff')->count(),
            'supportCount'  => Staff::where('category', 'support')->count(),
            'courses'       => Course::where('is_active', true)->count(),
            'duesCount'     => $asOf ? StudentFeeBalance::where('as_of', $asOf)->where('outstanding', '>', 0)->count() : 0,
            'duesTotal'     => $asOf ? (int) StudentFeeBalance::where('as_of', $asOf)->sum('outstanding') : 0,
            'feesAsOf'      => $asOf,
            // Both students and enrollments have a `status` column, so every
            // column in this join has to be table-qualified.
            'byCourse'      => Student::where('students.status', 'active')
                ->join('enrollments', 'enrollments.student_id', '=', 'students.id')
                ->join('batches', 'batches.id', '=', 'enrollments.batch_id')
                ->join('courses', 'courses.id', '=', 'batches.course_id')
                ->select('courses.name', DB::raw('COUNT(DISTINCT students.id) as total'))
                ->groupBy('courses.name')
                ->orderByDesc('total')
                ->get(),
        ]);
    }
}
