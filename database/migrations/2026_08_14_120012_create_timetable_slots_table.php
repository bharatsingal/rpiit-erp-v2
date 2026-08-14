<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_offering_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('day_of_week');     // 1 = Monday .. 7 = Sunday
            $table->unsignedTinyInteger('period_number');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->string('room', 30)->nullable();
            $table->timestamps();

            // "What am I teaching right now?" — the query behind the
            // one-tap attendance screen.
            $table->index(['day_of_week', 'period_number']);
            $table->index(['section_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_slots');
    }
};
