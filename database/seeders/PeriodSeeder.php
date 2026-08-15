<?php

namespace Database\Seeders;

use App\Models\Period;
use Illuminate\Database\Seeder;

/**
 * A starting bell schedule. Adjust the times to RPIIT's actual timetable —
 * everything downstream reads from here, so this is the only place to change.
 */
class PeriodSeeder extends Seeder
{
    public function run(): void
    {
        $periods = [
            [1, null,     '09:00', '09:50', true],
            [2, null,     '09:50', '10:40', true],
            [3, null,     '10:40', '11:30', true],
            [4, 'Break',  '11:30', '11:45', false],
            [5, null,     '11:45', '12:35', true],
            [6, 'Lunch',  '12:35', '13:15', false],
            [7, null,     '13:15', '14:05', true],
            [8, null,     '14:05', '14:55', true],
            [9, null,     '14:55', '15:45', true],
        ];

        foreach ($periods as [$n, $label, $from, $to, $teaching]) {
            Period::updateOrCreate(['number' => $n], [
                'label'       => $label,
                'starts_at'   => $from,
                'ends_at'     => $to,
                'is_teaching' => $teaching,
            ]);
        }
    }
}
