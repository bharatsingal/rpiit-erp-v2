<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The Board-approved rate card: F01-F34 for students, S01-S05 for staff.
        // Amounts are fixed by the Board of Trustees and revised occasionally,
        // so the rate is versioned by effective date rather than overwritten.
        Schema::create('fine_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();          // F01, S03
            $table->string('description');
            $table->string('basis', 40);                   // "Per day", "Per occasion", "Cap"
            $table->enum('applies_to', ['student', 'staff'])->default('student')->index();
            $table->unsignedInteger('amount');             // rupees
            // Several fines are "plus actual cost" — damage, breakage, lost book.
            $table->boolean('plus_actual_cost')->default(false);
            $table->unsignedInteger('cap_amount')->nullable();
            $table->date('effective_from')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // One row per fine imposed. Handbook rule: a fine is only valid if
        // entered on the day it was imposed, so imposed_on and imposed_by
        // are both recorded and neither is optional in practice.
        Schema::create('fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fine_code_id')->constrained()->restrictOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained()->cascadeOnDelete();
            $table->date('imposed_on')->index();
            $table->unsignedInteger('amount');             // copied from the rate card at the time
            $table->unsignedInteger('actual_cost')->default(0);
            $table->text('remarks')->nullable();
            $table->foreignId('imposed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'paid', 'waived'])->default('pending')->index();
            $table->string('receipt_no', 40)->nullable();
            $table->date('paid_on')->nullable();
            $table->foreignId('waived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fines');
        Schema::dropIfExists('fine_codes');
    }
};
