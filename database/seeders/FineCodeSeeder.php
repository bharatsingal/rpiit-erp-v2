<?php

namespace Database\Seeders;

use App\Models\FineCode;
use Illuminate\Database\Seeder;

class FineCodeSeeder extends Seeder
{
    /**
     * The Board-approved fine rate card (Student Handbook Annexure S-1).
     * Amounts are fixed by the Board of Trustees — the values below come from
     * RPIIT_fine_rates.csv. Where the card says "plus actual cost", the flag
     * is set and the actual amount is entered per incident.
     */
    public function run(): void
    {
        $codes = [
            // code, description, basis, amount, plusActualCost, cap
            ['F01', 'Tuition fee late fine — per day', 'Per day', 50, false, null],
            ['F02', 'Tuition fee late fine — maximum', 'Cap', 30000, false, 30000],
            ['F03', 'Re-admission after fee default beyond 30 days', 'Per occasion', 100, false, null],
            ['F04', 'Examination form / fee late charge (institutional)', 'Per occasion', 100, false, null],
            ['F05', 'University / Board registration late charge', 'Per occasion', 100, false, null],
            ['F06', 'Dishonour of cheque or online payment', 'Per occasion', 100, false, null],
            ['F07', 'Duplicate identity card', 'Per card', 100, false, null],
            ['F08', 'Duplicate receipt / bonafide / other certificate', 'Per document', 100, false, null],
            ['F09', 'Attendance below 75% in a subject (2nd review)', 'Per subject', 100, false, null],
            ['F10', 'Attendance below 65% in a subject', 'Per subject', 100, false, null],
            ['F11', 'Absent from a declared internal test', 'Per test', 100, false, null],
            ['F12', 'Absent from clinical / lab / workshop session', 'Per session', 100, false, null],
            ['F13', 'Habitual late entry (after 3rd occasion in a month)', 'Per occasion', 100, false, null],
            ['F14', 'Leaving campus without a gate pass', 'Per occasion', 100, false, null],
            ['F15', 'Identity card not displayed', 'Per occasion', 100, false, null],
            ['F16', 'Dress code / uniform default — student', 'Per occasion', 100, false, null],
            ['F17', 'Mobile phone used in class, lab, library or clinical area', 'Per occasion', 100, false, null],
            ['F18', 'Littering, spitting, defacing property', 'Per occasion', 100, false, null],
            ['F19', 'Damage to institutional property', 'Plus actual cost', 100, true, null],
            ['F20', 'Smoking, tobacco, alcohol or prohibited substance', 'Per occasion', 100, false, null],
            ['F21', 'Misbehaviour with staff or support personnel', 'Per occasion', 100, false, null],
            ['F22', 'Unauthorised entry into a restricted area', 'Per occasion', 100, false, null],
            ['F23', 'Misuse of ERP, fee portal or Wi-Fi', 'Per occasion', 100, false, null],
            ['F24', 'Overdue library book — per day per book', 'Per day', 100, false, null],
            ['F25', 'Lost or damaged library book — processing charge', 'Plus book price', 100, true, null],
            ['F26', 'Laboratory breakage or loss — handling charge', 'Plus actual cost', 100, true, null],
            ['F27', 'Hostel room or property damage — handling charge', 'Plus actual cost', 100, true, null],
            ['F28', 'Loss of hostel room key', 'Per key', 100, false, null],
            ['F29', 'Transport fee late payment — per day', 'Per day', 100, false, null],
            ['F30', 'Transport fee late payment — maximum', 'Cap', 100, false, 100],
            ['F31', 'Duplicate transport pass', 'Per pass', 100, false, null],
            ['F32', 'Travelling without a valid pass', 'Per occasion', 100, false, null],
            ['F33', 'Damage to bus fittings — handling charge', 'Plus actual cost', 100, true, null],
            ['F34', 'Misconduct in the bus', 'Per occasion', 100, false, null],
        ];

        foreach ($codes as [$code, $desc, $basis, $amount, $plus, $cap]) {
            FineCode::updateOrCreate(['code' => $code], [
                'description'      => $desc,
                'basis'            => $basis,
                'applies_to'       => 'student',
                'amount'           => $amount,
                'plus_actual_cost' => $plus,
                'cap_amount'       => $cap,
                'is_active'        => true,
            ]);
        }

        $staffCodes = [
            ['S01', 'Staff — reporting for duty without prescribed uniform', 'Per occasion', 100],
            ['S02', 'Staff — identity card not displayed', 'Per occasion', 100],
            ['S03', 'Staff — PPE not worn in lab, workshop, kitchen, clinical area', 'Per occasion', 100],
            ['S04', 'Staff — grooming standard not met in food or clinical area', 'Per occasion', 100],
            ['S05', 'Driver / security personnel on duty out of uniform', 'Per occasion', 100],
        ];

        foreach ($staffCodes as [$code, $desc, $basis, $amount]) {
            FineCode::updateOrCreate(['code' => $code], [
                'description'      => $desc,
                'basis'            => $basis,
                'applies_to'       => 'staff',
                'amount'           => $amount,
                'plus_actual_cost' => false,
                'is_active'        => true,
            ]);
        }
    }
}
