<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per term of a course: "Semester 1".."Semester 8" for B.Tech,
        // "Year 1".."Year 3" for an annual diploma. Nothing elsewhere in the
        // system assumes semesters.
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('number');
            $table->string('name');
            $table->timestamps();
            $table->unique(['course_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terms');
    }
};
