<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Term;
use Illuminate\Database\Seeder;

class TermSeeder extends Seeder
{
    /**
     * Builds the terms for every course from its own duration and term type,
     * so a semester course gets "Semester 1..8" and an annual one "Year 1..3".
     * Nothing in the system assumes semesters.
     *
     * Lateral-entry courses start at the term they actually join — a LEET
     * student enters in year 2, so their terms are numbered from 3 (semester)
     * or 2 (annual), not from 1.
     */
    public function run(): void
    {
        foreach (Course::all() as $course) {
            $label = $course->term_type === 'semester' ? 'Semester' : 'Year';
            $start = 1;

            if ($course->is_lateral) {
                $start = $course->term_type === 'semester' ? 3 : 2;
            }

            for ($i = 0; $i < $course->total_terms; $i++) {
                $number = $start + $i;
                Term::updateOrCreate(
                    ['course_id' => $course->id, 'number' => $number],
                    ['name' => "{$label} {$number}"]
                );
            }
        }
    }
}
