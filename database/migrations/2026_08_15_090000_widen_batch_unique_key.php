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
     *
     * The new index is created BEFORE the old one is dropped: MySQL will not
     * drop an index while a foreign key depends on it, and the FK on
     * course_id can only move across once a replacement leads with that
     * column.
     */
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->unique(['course_id', 'branch_id', 'start_year', 'end_year'], 'batch_span_unique');
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->dropUnique('batch_unique');
        });
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->unique(['course_id', 'branch_id', 'start_year'], 'batch_unique');
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->dropUnique('batch_span_unique');
        });
    }
};
