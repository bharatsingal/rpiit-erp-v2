<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The campus hosts more than one institution under R.P. Educational Trust.
     * RPETGI and RPIP each run their own courses — including D.Pharmacy, which
     * both offer — with separate affiliations, results and submissions.
     * Merging them, as v2 originally did, would send the wrong students to the
     * wrong board.
     */
    public function up(): void
    {
        Schema::create('institutes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();       // RPIIT, RPETGI, RPIP
            $table->string('name');
            $table->string('short_name', 40)->nullable();
            $table->string('affiliation')->nullable();  // board or university
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('institute_id')->nullable()->after('id')
                  ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('institute_id');
        });
        Schema::dropIfExists('institutes');
    }
};
