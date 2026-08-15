<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Imports RPIIT's student master export.
 *
 * Deliberately conservative: it never merges two records it isn't sure about.
 * Admission numbers in the source are inconsistent — "BPH2203" vs "Bph2203",
 * "Bph 2622" with a space — so they are normalised, and anything that then
 * collides is reported for a human to resolve rather than silently merged.
 *
 *   php artisan rpiit:import-students storage/app/imports/students.csv --dry-run
 */
class ImportStudents extends Command
{
    protected $signature = 'rpiit:import-students
                            {file : Path to the CSV export}
                            {--dry-run : Report what would happen, change nothing}';

    protected $description = 'Import students, batches and enrolments from the RPIIT master CSV';

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

        $year = AcademicYear::current();
        if (! $year && ! $dry) {
            $this->error('No current academic year. Create one with is_current = true first.');
            return self::FAILURE;
        }

        $rows = $this->readCsv($path);
        if (! $rows) {
            $this->error('No data rows found — is the header row present?');
            return self::FAILURE;
        }
        $this->info(count($rows).' rows read.');

        $courses = Course::all()->keyBy(fn ($c) => $this->key($c->name));

        $seen = [];
        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0];
        $collisions = [];
        $unknownCourses = [];

        $bar = $this->output->createProgressBar(count($rows));

        foreach ($rows as $i => $row) {
            $bar->advance();

            $admission = $this->normaliseAdmissionNo($row['Admission no.'] ?? '');
            $name      = trim($row['Student name'] ?? '');
            $courseRaw = trim($row['Course'] ?? '');
            $batchRaw  = trim($row['Branch / batch'] ?? '');

            if ($admission === '' || $name === '') {
                $stats['skipped']++;
                continue;
            }

            // Two source rows normalising to the same admission number is a
            // data problem for a human, not something to guess at.
            if (isset($seen[$admission])) {
                $collisions[] = [$admission, $seen[$admission], $name, 'row '.($i + 2)];
                $stats['skipped']++;
                continue;
            }
            $seen[$admission] = $name;

            $course = $courses[$this->key($courseRaw)] ?? null;
            if (! $course) {
                $unknownCourses[$courseRaw] = ($unknownCourses[$courseRaw] ?? 0) + 1;
                $stats['skipped']++;
                continue;
            }

            if ($dry) {
                $stats['created']++;
                continue;
            }

            DB::transaction(function () use ($row, $admission, $name, $course, $batchRaw, $year, &$stats) {
                $batch = $this->resolveBatch($course, $batchRaw);

                $student = Student::withTrashed()->firstOrNew(['admission_no' => $admission]);
                $existed = $student->exists;

                $student->fill([
                    'first_name'  => $name,
                    'roll_no'     => trim($row['SR no.'] ?? '') ?: null,
                    'phone'       => $this->cleanPhone($row['Mobile'] ?? ''),
                    'status'      => $this->statusFor($batch),
                ]);
                $student->save();

                // Fee snapshot from the Tally export. Stored per import date so
                // a history builds up rather than being overwritten each week.
                $due     = $this->money($row['Due'] ?? 0);
                $receipt = $this->money($row['Receipt'] ?? 0);
                if ($due || $receipt) {
                    \App\Models\StudentFeeBalance::updateOrCreate(
                        ['student_id' => $student->id, 'as_of' => now()->toDateString()],
                        [
                            'due'         => $due,
                            'receipt'     => $receipt,
                            'outstanding' => max($due - $receipt, 0),
                            'advance'     => max($receipt - $due, 0),
                            'source'      => 'tally_export',
                        ]
                    );
                }

                if ($batch) {
                    $term = $this->currentTermFor($course, $batch, $year);
                    if ($term) {
                        Enrollment::updateOrCreate(
                            [
                                'student_id'       => $student->id,
                                'term_id'          => $term->id,
                                'academic_year_id' => $year->id,
                            ],
                            ['batch_id' => $batch->id, 'status' => 'enrolled']
                        );
                    }
                }

                $existed ? $stats['updated']++ : $stats['created']++;
            });
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(['Created', 'Updated', 'Skipped'],
            [[$stats['created'], $stats['updated'], $stats['skipped']]]);

        if ($unknownCourses) {
            $this->newLine();
            $this->error('Course names not recognised — add them to CourseSeeder or fix the source:');
            foreach ($unknownCourses as $c => $n) {
                $this->line(sprintf('  %-28s %d rows', $c, $n));
            }
        }

        if ($collisions) {
            $this->newLine();
            $this->error(count($collisions).' duplicate admission numbers — NOT imported, resolve these:');
            $this->table(['Admission no.', 'First seen as', 'Also seen as', 'Where'],
                array_slice($collisions, 0, 40));
            if (count($collisions) > 40) {
                $this->line('  ... and '.(count($collisions) - 40).' more');
            }
        }

        return self::SUCCESS;
    }

    /**
     * RPIIT's export has three preamble rows before the real header, a UTF-8
     * BOM, and Windows line endings. Read the whole file, normalise the line
     * endings, then find the header by looking for a column we know must
     * exist rather than matching the first cell exactly.
     */
    private function readCsv(string $path): array
    {
        $raw = file_get_contents($path);
        if ($raw === false) {
            return [];
        }

        // Strip BOM, normalise CRLF and lone CR to LF.
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
        $raw = str_replace(["\r\n", "\r"], "\n", $raw);

        $lines  = preg_split('/\n/', $raw);
        $header = null;
        $rows   = [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $cells = str_getcsv($line);
            $cells = array_map(fn ($c) => trim((string) $c), $cells);

            if ($header === null) {
                // The header is whichever row actually names the key columns.
                if (in_array('Admission no.', $cells, true)
                    || in_array('Student name', $cells, true)) {
                    $header = $cells;
                }
                continue;
            }

            if (count($cells) < 3) {
                continue;
            }

            $rows[] = array_combine(
                $header,
                array_pad(array_slice($cells, 0, count($header)), count($header), null)
            );
        }

        if ($header === null) {
            $this->error('Could not find a header row containing "Admission no." or "Student name".');
            $this->line('First 5 non-empty lines seen:');
            $shown = 0;
            foreach ($lines as $l) {
                if (trim($l) === '') { continue; }
                $this->line('  '.substr($l, 0, 120));
                if (++$shown >= 5) { break; }
            }
        }

        return $rows;
    }

    /** "Bph 2622" and "BPH2622" are the same student. */
    private function normaliseAdmissionNo(string $raw): string
    {
        $v = strtoupper(preg_replace('/\s+/', '', trim($raw)));
        // The Tally export writes some numbers as floats: "2025001.0".
        return preg_replace('/\.0$/', '', $v);
    }

    /** Tally writes blanks, dashes and floats. All of them mean a number. */
    private function money($raw): int
    {
        $s = trim((string) $raw);
        if ($s === '' || $s === '-') {
            return 0;
        }
        $s = preg_replace('/[^0-9.\-]/', '', $s);
        return $s === '' ? 0 : (int) round((float) $s);
    }

    private function key(string $s): string
    {
        return strtoupper(preg_replace('/[^A-Z0-9]/i', '', $s));
    }

    private function cleanPhone(string $raw): ?string
    {
        $d = preg_replace('/\D/', '', $raw);
        // 9999999999 is the placeholder used throughout the source data.
        if (strlen($d) !== 10 || $d === '9999999999') {
            return null;
        }
        return $d;
    }

    /** "B.PHARMACY 2025-29" -> the batch row, created if needed. */
    private function resolveBatch(Course $course, string $batchRaw): ?Batch
    {
        if (! preg_match('/(\d{4})\s*-\s*(\d{2,4})\s*$/', $batchRaw, $m)) {
            return null;
        }
        $start = (int) $m[1];
        $end   = strlen($m[2]) === 2 ? (int) (substr($m[1], 0, 2).$m[2]) : (int) $m[2];

        return Batch::firstOrCreate(
            ['course_id' => $course->id, 'branch_id' => null, 'start_year' => $start],
            ['end_year' => $end, 'name' => trim($batchRaw), 'is_active' => true]
        );
    }

    /** Passed out if the batch ended before the current academic year began. */
    private function statusFor(?Batch $batch): string
    {
        if (! $batch) {
            return 'active';
        }
        return $batch->end_year < (int) date('Y') ? 'passed_out' : 'active';
    }

    /**
     * Which term the student is in now, from how far through the course they
     * are. Lateral-entry courses are numbered from where students join, so
     * their first term is not term 1.
     */
    private function currentTermFor(Course $course, Batch $batch, AcademicYear $year): ?Term
    {
        $yearsIn = max(1, (int) $year->starts_on->format('Y') - $batch->start_year + 1);
        $yearsIn = min($yearsIn, $course->duration_years);

        $offset = $course->term_type === 'semester' ? ($yearsIn * 2) - 1 : $yearsIn;
        $first  = $course->terms()->min('number') ?? 1;

        return $course->terms()->where('number', $first + $offset - 1)->first()
            ?? $course->terms()->orderBy('number')->first();
    }
}
