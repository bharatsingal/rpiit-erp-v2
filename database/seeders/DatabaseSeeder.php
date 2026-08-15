<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AcademicYearSeeder::class,
            CourseSeeder::class,
            InstituteSeeder::class,
            TermSeeder::class,
            PeriodSeeder::class,
            FineCodeSeeder::class,
            DepartmentCourseSeeder::class,
        ]);
    }
}
