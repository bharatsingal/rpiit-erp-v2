<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Institute;
use Illuminate\Database\Seeder;

/**
 * Three institutions under R.P. Educational Trust, Karnal.
 *
 * Confirmed by Bharat 2026-08-15:
 *   RPETGI — D.Pharmacy and B.Pharmacy
 *   RPIP   — DMLT and D.Pharmacy
 *   RPIIT  — everything else
 *
 * D.Pharmacy is run by BOTH RPETGI and RPIP, so it exists as two course rows.
 */
class InstituteSeeder extends Seeder
{
    public function run(): void
    {
        $institutes = [
            ['RPIIT',  'RPIIT Technical & Medical Campus', 'RPIIT'],
            ['RPETGI', 'R.P. Educational Trust Group of Institutions', 'RPETGI'],
            ['RPIP',   'R.P. Institute of Pharmacy', 'RPIP'],
        ];

        foreach ($institutes as [$code, $name, $short]) {
            Institute::updateOrCreate(['code' => $code], ['name' => $name, 'short_name' => $short]);
        }

        $rpiit  = Institute::where('code', 'RPIIT')->first();
        $rpetgi = Institute::where('code', 'RPETGI')->first();
        $rpip   = Institute::where('code', 'RPIP')->first();

        // Everything defaults to the main campus...
        Course::whereNull('institute_id')->update(['institute_id' => $rpiit->id]);

        // ...then the pharmacy institutes claim theirs.
        Course::whereIn('code', ['BPHARM', 'BPHARM-LEET'])->update(['institute_id' => $rpetgi->id]);
        Course::whereIn('code', ['DMLT', 'DMLT-LEET'])->update(['institute_id' => $rpip->id]);

        // D.Pharmacy runs at both, so it needs a row per institute.
        $existing = Course::where('code', 'DPHARM')->first();

        if ($existing) {
            $existing->update([
                'code'         => 'DPHARM-RPETGI',
                'name'         => 'D.PHARMACY (RPETGI)',
                'institute_id' => $rpetgi->id,
            ]);
        }

        Course::updateOrCreate(
            ['code' => 'DPHARM-RPIP'],
            [
                'name'           => 'D.PHARMACY (RPIP)',
                'discipline'     => 'pharmacy',
                'term_type'      => 'annual',
                'duration_years' => 2,
                'total_terms'    => 2,
                'is_lateral'     => false,
                'is_active'      => true,
                'institute_id'   => $rpip->id,
            ]
        );
    }
}
