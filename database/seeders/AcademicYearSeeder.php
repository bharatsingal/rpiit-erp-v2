<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    /**
     * Indian academic year runs July to June. Adjust the dates if RPIIT's
     * session actually starts elsewhere — everything that groups by year
     * (attendance, marks, fees) hangs off this.
     */
    public function run(): void
    {
        $years = [
            ['2024-25', '2024-07-01', '2025-06-30', false],
            ['2025-26', '2025-07-01', '2026-06-30', false],
            ['2026-27', '2026-07-01', '2027-06-30', true],
        ];

        foreach ($years as [$name, $start, $end, $current]) {
            AcademicYear::updateOrCreate(
                ['name' => $name],
                ['starts_on' => $start, 'ends_on' => $end, 'is_current' => $current]
            );
        }
    }
}
