<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Models\Staff;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Imports RPIIT's staff list — 95 staff and 78 support staff.
 * Departments are created from the "Department / role" column, and HODs are
 * detected from designations like "HOD — CSE DEPT" and linked back as the
 * department head.
 *
 *   php artisan rpiit:import-staff storage/app/imports/staff.csv --dry-run
 */
class ImportStaff extends Command
{
    protected $signature = 'rpiit:import-staff
                            {file : Path to the staff CSV}
                            {--dry-run : Report what would happen, change nothing}';

    protected $description = 'Import departments and staff from the RPIIT staff CSV';

    /** Departments that are functional or support rather than academic. */
    private const FUNCTIONAL = [
        'ADMINISTRATION', 'ACCOUNTS', 'LIBRARY', 'WARDEN', 'SECURITY OFFICER',
        'RECEPTIONIST', 'PA', 'DESIGNER', 'FIELD', 'LEGAL', 'REGISTRAR',
        'MAINTENANCE', 'TECHNICIANS', 'SENIOR STAFF',
    ];

    public function handle(): int
    {
        $path = $this->argument('file');
        if (! is_file($path)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');
        if ($dry) {
            $this->warn('DRY RUN — nothing will be written.');
        }

        $fh = fopen($path, 'r');
        $header = fgetcsv($fh);
        $header = array_map(fn ($h) => trim((string) $h, " \t\n\r\0\x0B\xEF\xBB\xBF"), $header);

        $created = 0; $updated = 0; $skipped = 0;
        $hods = [];

        while (($line = fgetcsv($fh)) !== false) {
            if (count($line) < 2) { $skipped++; continue; }
            $row = array_combine($header, array_pad(array_slice($line, 0, count($header)), count($header), null));

            $staffNo = trim($row['Staff no.'] ?? '');
            $name    = trim($row['Name'] ?? '');
            if ($staffNo === '' || $name === '') { $skipped++; continue; }

            $deptName    = strtoupper(trim($row['Department / role'] ?? '')) ?: null;
            $designation = trim($row['Designation'] ?? '') ?: null;
            $category    = Str::contains(strtolower($row['Category'] ?? ''), 'support') ? 'support' : 'staff';
            $isHod       = $designation && Str::startsWith(strtoupper($designation), 'HOD');

            if ($dry) {
                $created++;
                if ($isHod) { $hods[] = "{$name} — {$deptName}"; }
                continue;
            }

            $dept = null;
            if ($deptName) {
                $dept = Department::firstOrCreate(
                    ['code' => Str::slug($deptName, '_')],
                    [
                        'name' => $deptName,
                        'kind' => $category === 'support' ? 'support'
                            : (in_array($deptName, self::FUNCTIONAL, true) ? 'functional' : 'academic'),
                    ]
                );
            }

            $staff = Staff::withTrashed()->firstOrNew(['staff_no' => $staffNo]);
            $existed = $staff->exists;

            $staff->fill([
                'name'          => $name,
                'department_id' => $dept?->id,
                'category'      => $category,
                'designation'   => $designation,
                'is_hod'        => $isHod,
                'joined_on'     => $this->parseDate($row['Date of joining'] ?? ''),
                'mobile'        => $this->cleanPhone($row['Mobile'] ?? ''),
                'is_active'     => true,
            ]);
            $staff->save();

            if ($isHod && $dept) {
                $dept->update(['head_staff_id' => $staff->id]);
                $hods[] = "{$name} — {$dept->name}";
            }

            $existed ? $updated++ : $created++;
        }
        fclose($fh);

        $this->newLine();
        $this->table(['Created', 'Updated', 'Skipped'], [[$created, $updated, $skipped]]);

        if ($hods) {
            $this->newLine();
            $this->info(count($hods).' department heads identified:');
            foreach ($hods as $h) { $this->line("  {$h}"); }
        }

        if (! $dry) {
            $this->newLine();
            $this->info('Departments: '.Department::count().'  ·  Staff: '.Staff::count());
            $this->comment('Reporting lines are not in this CSV — import them from the hierarchy workbook separately.');
        }

        return self::SUCCESS;
    }

    /** Source uses 26.03.25, 17.03.2025, or nothing at all. */
    private function parseDate(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }
        foreach (['d.m.Y', 'd.m.y', 'd/m/Y', 'd-m-Y', 'Y-m-d'] as $fmt) {
            $d = \DateTime::createFromFormat($fmt, $raw);
            if ($d && $d->format($fmt) === $raw) {
                return $d->format('Y-m-d');
            }
        }
        return null;
    }

    private function cleanPhone(string $raw): ?string
    {
        $d = preg_replace('/\D/', '', $raw);
        return strlen($d) === 10 ? $d : null;
    }
}
