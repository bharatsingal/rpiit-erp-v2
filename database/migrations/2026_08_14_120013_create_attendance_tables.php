<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A session is one period, one class, one day — created the moment a
        // lecturer opens the screen. Splitting it from the per-student rows
        // is what lets a whole class be saved in a single request, and
        // records who marked it and when.
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_offering_id')->constrained()->cascadeOnDelete();
            $table->foreignId('timetable_slot_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->date('held_on');
            $table->unsignedTinyInteger('period_number')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('marked_at')->nullable();
            $table->enum('status', ['draft', 'final'])->default('draft')->index();
            $table->string('note')->nullable();
            $table->timestamps();

            // Marking the same class twice for the same period is a mistake,
            // not a feature. The database refuses it.
            $table->unique(
                ['subject_offering_id', 'held_on', 'period_number'],
                'attendance_session_unique'
            );
            $table->index(['held_on', 'section_id']);
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->timestamps();

            $table->unique(['attendance_session_id', 'student_id'], 'attendance_record_unique');
            // Drives every "attendance percentage" report.
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('attendance_sessions');
    }
};
