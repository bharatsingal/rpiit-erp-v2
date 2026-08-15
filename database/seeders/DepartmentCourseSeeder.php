<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Department;
use Illuminate\Database\Seeder;

/**
 * Attach each course to the department that runs it, using the department
 * names as they appear in RPIIT's own staff register. Runs after the staff
 * import, since that is what creates the departments.
 */
class DepartmentCourseSeeder extends Seeder
{
    private const MAP = [
        'NURSING'      => ['ANM', 'GNM', 'BSCN', 'POSTBSCN'],
        'B.PHARMACY'   => ['BPHARM', 'BPHARM-LEET'],
        'D.PHARMACY'   => ['DPHARM'],
        'DMLT'         => ['DMLT', 'DMLT-LEET'],
        'BPT'          => ['BPT'],
        'H.MGMT'       => ['BHM', 'DIP-HM'],
        'PGDM'         => ['MBA', 'BBA'],
        'CSE DEPT'     => ['BTECH-CSE', 'BTECH-CSE-LEET', 'MTECH', 'MTECH-CSE', 'BCA'],
        'CIVIL DEPT'   => ['BTECH-CE', 'BTECH-CE-LEET', 'DIP-CE', 'DIP-CE-LEET'],
    ];

    public function run(): void
    {
        foreach (self::MAP as $deptName => $courseCodes) {
            $dept = Department::where('name', $deptName)->first();

            if (! $dept) {
                // The staff import creates departments; if it has not run yet,
                // create a placeholder so course scoping still works.
                $dept = Department::firstOrCreate(
                    ['code' => \Illuminate\Support\Str::slug($deptName, '_')],
                    ['name' => $deptName, 'kind' => 'academic']
                );
            }

            Course::whereIn('code', $courseCodes)->update(['department_id' => $dept->id]);
        }
    }
}
