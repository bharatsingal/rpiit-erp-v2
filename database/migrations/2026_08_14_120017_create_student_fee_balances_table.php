<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // RPIIT's fees live in Tally and arrive as a weekly export carrying
        // Due / Receipt / Outstanding / Advance per student. This table holds
        // that snapshot so the ERP can show and chase dues without pretending
        // to be the accounting system.
        //
        // One row per student per import, keyed by date, so a history builds up
        // and "what changed since last week" is answerable.
        Schema::create('student_fee_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('as_of');
            $table->unsignedInteger('due')->default(0);
            $table->unsignedInteger('receipt')->default(0);
            $table->unsignedInteger('outstanding')->default(0);
            $table->unsignedInteger('advance')->default(0);
            $table->string('source', 30)->default('tally_export');
            $table->timestamps();

            $table->unique(['student_id', 'as_of']);
            $table->index(['as_of', 'outstanding']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fee_balances');
    }
};
