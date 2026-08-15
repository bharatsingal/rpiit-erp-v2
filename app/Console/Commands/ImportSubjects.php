<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\SubjectOffering;
use Illuminate\Console\Command;

/**
 * Imports RPIIT's syllabus export.
 *
 * Subjects are always created. With --link, subjects whose name carried a
 * course and term hint ("DMLT-I-...", "B.CSE-I-...") are also attached to the
 * batch currently sitting in that term — which is what makes them appear on
 * the attendance screen without anyone assigning 761 subjects by hand.
 *
 *   php artisan rpiit:import-subjects storage/app/imports/subjects.csv --link
 */
class ImportSubjects extends Command
{
    protected $signature = 'rpiit:import-subjects
                            {file : Path to the subjects CSV}
                            {--link : Also attach subjects to the batch currently in that term}
                            {--dry-run : Report what would happen, change nothing}';

    protected $description = 'Import subjects, and optionally attach them to current batches';

    /** Course prefixes as they appear in subject names. */
    private const COURSE_MAP = [
        'B.CSE' => 'BTECH-CSE',
        'BCSE'  => 'BTECH-CSE',
        'B.CE'  => 'BTECH-CE',
        'BCE'   => 'BTECH-CE',
        'BBA'   => 'BBA',
        'BCA'   => 'BCA',
        'MBA'   => 'MBA',
        'BPT'   => 'BPT',
        'DMLT'  => 'DMLT',
        'DP'    => 'DPHARM',
        'HM'    => 'BHM',
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

        $rows = $this->readCsv($path);
        $this->info(count($rows).' rows read.');

        $created = $updated = 0;
        $linked = 0;
        $unlinked = [];
        $courses = Course::all()->keyBy('code');
        $year = AcademicYear::current();

        foreach ($rows as $row) {
            $code = trim($row['Subject Code'] ?? '');
            $name = trim($row['Subject Name'] ?? '');
            if ($code === '' || $name === '') {
                continue;
            }

            if ($dry) {
                $created++;
                continue;
            }

            $subject = Subject::updateOrCreate(
                ['code' => $code],
                [
                    'name'      => $name,
                    'type'      => in_array($row['Type'] ?? '', ['theory', 'practical', 'project'], true)
                                    ? $row['Type'] : 'theory',
                    'is_active' => true,
                ]
            );
            $subject->wasRecentlyCreated ? $created++ : $updated++;

            if (! $this->option('link') || ! $year) {
                continue;
            }

            $hint = strtoupper(trim($row['Course hint'] ?? ''));
            $termNo = (int) ($row['Term hint'] ?? 0);
            if ($hint === '' || $termNo < 1) {
                continue;
            }

            $course = $courses[self::COURSE_MAP[$hint] ?? ''] ?? null;
            if (! $course) {
                $unlinked[$hint] = ($unlinked[$hint] ?? 0) + 1;
                continue;
            }

            $term = $course->terms()->where('number', $termNo)->first();
            if (! $term) {
                continue;
            }

            // Only attach where exactly one batch is sitting in that term now.
            // More than one and it is a judgement call, not an import.
            $batchIds = Enrollment::where('term_id', $term->id)
                ->where('academic_year_id', $year->id)
                ->distinct()
                ->pluck('batch_id');

            if ($batchIds->count() !== 1) {
                continue;
            }

            SubjectOffering::updateOrCreate(
                [
                    'subject_id'       => $subject->id,
                    'batch_id'         => $batchIds->first(),
                    'term_id'          => $term->id,
                    'academic_year_id' => $year->id,
                    'section_id'       => null,
                ],
                []
            );
            $linked++;
        }

        $this->newLine();
        $this->table(['Subjects created', 'Updated', 'Attached to a batch'],
            [[$created, $updated, $linked]]);

        if ($unlinked) {
            $this->newLine();
            $this->warn('Course prefixes not recognised:');
            foreach ($unlinked as $k => $n) {
                $this->line("  {$k} — {$n} subjects");
            }
        }

        if ($this->option('link') && ! $year) {
            $this->warn('No current academic year, so nothing could be attached.');
        }

        return self::SUCCESS;
    }

    private function readCsv(string $path): array
    {
        $raw = str_replace(["\r\n", "\r"], "\n", preg_replace('/^\xEF\xBB\xBF/', '', file_get_contents($path)));
        $lines = array_filter(explode("\n", $raw), fn ($l) => trim($l) !== '');
        $header = null;
        $rows = [];

        foreach ($lines as $line) {
            $cells = array_map('trim', str_getcsv($line));
            if ($header === null) {
                if (in_array('Subject Code', $cells, true)) {
                    $header = $cells;
                }
                continue;
            }
            $rows[] = array_combine($header,
                array_pad(array_slice($cells, 0, count($header)), count($header), null));
        }

        return $rows;
    }
}
