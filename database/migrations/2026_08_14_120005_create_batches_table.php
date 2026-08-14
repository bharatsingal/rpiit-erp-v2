<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A cohort, named exactly as every existing RPIIT document names it:
        // "ANM 2019-21", "B.PHARMACY LEET 2024-27". 131 of these exist today.
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            // Only B.Tech and Diploma actually use a branch. Everything else
            // is the course itself, so this stays optional.
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->year('start_year');
            $table->year('end_year');
            $table->string('name')->index();   // "B.PHARMACY 2025-29"
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['course_id', 'branch_id', 'start_year'], 'batch_unique');
        });

        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->string('name', 10);
            $table->timestamps();
            $table->unique(['batch_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
        Schema::dropIfExists('batches');
    }
};
