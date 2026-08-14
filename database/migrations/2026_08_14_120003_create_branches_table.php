<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();   // CSE, ECE, ME
            $table->string('name');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // A branch can be offered under several courses (CSE in both
        // Diploma and B.Tech), each with its own sanctioned intake.
        Schema::create('branch_course', function (Blueprint $table) {
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('intake')->nullable();
            $table->primary(['branch_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_course');
        Schema::dropIfExists('branches');
    }
};
