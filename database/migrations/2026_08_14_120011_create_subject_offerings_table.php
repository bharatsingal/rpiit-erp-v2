<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One subject, taught to one batch in one term of one academic year,
        // by one faculty member. The same subject in a different year or a
        // different section is a separate offering — which is what makes
        // "who taught this, and to whom" answerable years later.
        Schema::create('subject_offerings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            // Null = taught to the whole batch rather than one section.
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('faculty_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_elective')->default(false);
            $table->timestamps();

            $table->unique(
                ['subject_id', 'batch_id', 'term_id', 'academic_year_id', 'section_id'],
                'offering_unique'
            );
            // The lookup the attendance screen does on every load.
            $table->index(['faculty_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_offerings');
    }
};
