<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // RPIIT runs 23 courses across pharmacy, nursing, paramedical,
        // physiotherapy, hotel management, management and engineering.
        // The course is the primary unit here — most courses have no branch.
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();     // BPHARM, DPHARM, DMLT, BSCN
            $table->string('name');                   // "B.PHARMACY", "BSC NURSING"
            $table->string('discipline', 40)->nullable()->index();  // pharmacy, nursing, paramedical...
            // Semester or annual — RPIIT runs both.
            $table->enum('term_type', ['semester', 'annual']);
            $table->unsignedTinyInteger('duration_years');   // ANM 2, GNM 3, B.Pharm 4
            $table->unsignedTinyInteger('total_terms');
            // Lateral entry is a separate course, one year shorter, entering
            // at year 2. "B.PHARMACY LEET" is its own row, not a student flag.
            $table->boolean('is_lateral')->default(false)->index();
            $table->foreignId('parent_course_id')->nullable()
                  ->constrained('courses')->nullOnDelete();   // LEET -> its parent course
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
