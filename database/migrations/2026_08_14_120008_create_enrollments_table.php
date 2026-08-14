<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Which term a student is in, in a given academic year. One row per
        // student per term, so progression through the course is a history
        // rather than a field that gets overwritten each year.
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained()->restrictOnDelete();
            $table->foreignId('term_id')->constrained()->restrictOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['enrolled', 'promoted', 'detained', 'withdrawn'])
                  ->default('enrolled')->index();
            $table->timestamps();
            $table->unique(['student_id', 'term_id', 'academic_year_id']);
            $table->index(['section_id', 'term_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
