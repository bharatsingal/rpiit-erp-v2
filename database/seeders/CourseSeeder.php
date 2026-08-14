<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * RPIIT's 23 courses. Durations are NOT guessed — they were derived from
     * the year ranges in RPIIT's own batch names (e.g. "ANM 2019-21" = 2 years)
     * across all 3,240 student records.
     *
     * term_type confirmed by Bharat 2026-08-14: engineering (B.Tech, M.Tech,
     * Diploma Civil) runs semesters; every other course is annual.
     */
    public function run(): void
    {
        $courses = [
            // code,          name,                 discipline,      years, term_type, lateral, parent
            ['ANM',           'ANM',                'nursing',       2, 'annual',   false, null],
            ['GNM',           'GNM',                'nursing',       3, 'annual',   false, null],
            ['BSCN',          'BSC NURSING',        'nursing',       4, 'annual',   false, null],
            ['POSTBSCN',      'POST BSC',           'nursing',       2, 'annual',   false, null],

            ['BPHARM',        'B.PHARMACY',         'pharmacy',      4, 'annual', false, null],
            ['BPHARM-LEET',   'B.PHARMACY LEET',    'pharmacy',      3, 'annual', true,  'BPHARM'],
            ['DPHARM',        'D.PHARMACY',         'pharmacy',      2, 'annual',   false, null],

            ['DMLT',          'DMLT',               'paramedical',   3, 'annual',   false, null],
            ['DMLT-LEET',     'DMLT LEET',          'paramedical',   2, 'annual',   true,  'DMLT'],
            ['BPT',           'BPT',                'physiotherapy', 4, 'annual', false, null],

            ['BTECH-CSE',     'B.TECH CSE',         'engineering',   4, 'semester', false, null],
            ['BTECH-CSE-LEET','B.TECH CSE LEET',    'engineering',   3, 'semester', true,  'BTECH-CSE'],
            ['BTECH-CE',      'B.TECH CIVIL',       'engineering',   4, 'semester', false, null],
            ['BTECH-CE-LEET', 'B.TECH CIVIL LEET',  'engineering',   3, 'semester', true,  'BTECH-CE'],
            ['DIP-CE',        'DIP CIVIL',          'engineering',   3, 'semester', false, null],
            ['DIP-CE-LEET',   'DIP CIVIL LEET',     'engineering',   2, 'semester', true,  'DIP-CE'],
            ['MTECH',         'M.TECH',             'engineering',   2, 'semester', false, null],
            ['MTECH-CSE',     'M.TECH CSE',         'engineering',   2, 'semester', false, null],

            ['MBA',           'MBA',                'management',    2, 'annual', false, null],
            ['BBA',           'BBA',                'management',    3, 'annual', false, null],
            ['BCA',           'BCA',                'computer',      3, 'annual', false, null],

            ['BHM',           'BHM',                'hotel',         4, 'annual', false, null],
            ['DIP-HM',        'DIP HM',             'hotel',         2, 'annual', false, null],
        ];

        // First pass: create everything without parent links.
        foreach ($courses as [$code, $name, $discipline, $years, $termType, $isLateral, $parent]) {
            Course::updateOrCreate(
                ['code' => $code],
                [
                    'name'           => $name,
                    'discipline'     => $discipline,
                    'duration_years' => $years,
                    'term_type'      => $termType,
                    'total_terms'    => $termType === 'semester' ? $years * 2 : $years,
                    'is_lateral'     => $isLateral,
                    'is_active'      => true,
                ]
            );
        }

        // Second pass: link lateral-entry courses to the course they shorten.
        foreach ($courses as [$code, , , , , $isLateral, $parent]) {
            if ($isLateral && $parent) {
                Course::where('code', $code)->update([
                    'parent_course_id' => Course::where('code', $parent)->value('id'),
                ]);
            }
        }
    }
}
