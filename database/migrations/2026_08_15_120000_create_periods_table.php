<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The campus bell schedule. Periods are defined once here rather than
     * typed into every timetable slot, so changing the second-period time
     * changes it everywhere.
     */
    public function up(): void
    {
        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('number')->unique();
            $table->string('label', 40)->nullable();     // "Lunch", "Break"
            $table->time('starts_at');
            $table->time('ends_at');
            $table->boolean('is_teaching')->default(true);   // false for breaks
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periods');
    }
};
