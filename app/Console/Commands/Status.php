<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentFeeBalance;
use App\Models\Subject;
use App\Models\SubjectOffering;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/** One command that answers "what is actually in there?". */
class Status extends Command
{
    protected $signature = 'rpiit:status';
    protected $description = 'Summarise what is loaded in the ERP';

    public function handle(): int
    {
        $year = AcademicYear::current();
        $asOf = StudentFeeBalance::max('as_of');

        $this->newLine();
        $this->line('  <fg=white;options=bold>RPIIT ERP — status</>');
        $this->line('  Academic year: '.($year?->name ?? 'NONE SET'));
        $this->newLine();

        $this->table(['', 'Count'], [
            ['Students — active',      number_format(Student::where('status', 'active')->count())],
            ['Students — passed out',  number_format(Student::where('status', 'passed_out')->count())],
            ['Staff',                  number_format(Staff::where('category', 'staff')->count())],
            ['Support staff',          number_format(Staff::where('category', 'support')->count())],
            ['Courses',                number_format(Course::count())],
            ['Batches',                number_format(DB::table('batches')->count())],
            ['Subjects',               number_format(Subject::count())],
            ['Subjects attached to a batch', number_format(SubjectOffering::count())],
            ['Enrolments this year',   number_format(
                $year ? DB::table('enrollments')->where('academic_year_id', $year->id)->count() : 0)],
            ['Sign-in accounts',       number_format(User::count())],
        ]);

        $this->line('  <fg=white;options=bold>Term systems</>');
        foreach (Course::orderBy('term_type')->orderBy('name')->get()->groupBy('term_type') as $type => $set) {
            $this->line("  {$type}: ".$set->pluck('name')->join(', '));
        }

        if ($asOf) {
            $out = StudentFeeBalance::where('as_of', $asOf)->where('outstanding', '>', 0);
            $this->newLine();
            $this->line('  <fg=white;options=bold>Fees</> (as of '.$asOf.')');
            $this->line('  '.number_format($out->count()).' students owe ₹'.number_format((int) $out->sum('outstanding')));
        }

        $noSubjects = DB::table('batches')
            ->leftJoin('subject_offerings', 'subject_offerings.batch_id', '=', 'batches.id')
            ->whereNull('subject_offerings.id')
            ->where('batches.end_year', '>=', now()->year)
            ->count();

        if ($noSubjects) {
            $this->newLine();
            $this->warn("  {$noSubjects} current batches have no subjects attached — attendance cannot be marked for them.");
        }

        $this->newLine();

        return self::SUCCESS;
    }
}
