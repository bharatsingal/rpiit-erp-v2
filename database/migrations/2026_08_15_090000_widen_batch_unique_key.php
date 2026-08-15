<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A batch is identified by its full year span, not just the start year.
     * RPIIT genuinely runs two intakes starting the same year — a four-year
     * B.Pharmacy 2025-29 and a three-year lateral-entry 2025-28 — so keying
     * on start_year alone rejected real data.
     */
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropUnique('batch_unique');
            $table->unique(['course_id', 'branch_id', 'start_year', 'end_year'], 'batch_unique');
        });
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropUnique('batch_unique');
            $table->unique(['course_id', 'branch_id', 'start_year'], 'batch_unique');
        });
    }
};
