<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();            // BTECH, DIP
            $table->string('name');                          // B.Tech, Diploma
            // RPIIT runs both semester and annual courses, so the term style
            // belongs to the course rather than being assumed globally.
            $table->enum('term_type', ['semester', 'annual']);
            $table->unsignedTinyInteger('total_terms');      // 8 semesters, or 3 years
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
