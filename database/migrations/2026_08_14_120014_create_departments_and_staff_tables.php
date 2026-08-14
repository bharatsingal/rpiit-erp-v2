<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ~26 departments: academic (CSE, NURSING, B.PHARMACY, DMLT, BPT...)
        // and functional (ACCOUNTS, LIBRARY, SECURITY, TRANSPORT, WARDEN...).
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');                       // "CSE DEPT", "ACCOUNTS"
            $table->enum('kind', ['academic', 'functional', 'support'])->default('academic');
            $table->foreignId('head_staff_id')->nullable();   // FK added below, after staff exists
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 173 people: 95 staff + 78 support staff.
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('staff_no', 20)->unique();     // ADM-001, STF-047, SUP-012
            $table->string('name');
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            // The workbook's own split — it drives which registers apply.
            $table->enum('category', ['staff', 'support'])->default('staff')->index();
            $table->string('designation')->nullable();    // "HOD — CSE DEPT", "Dean Academic"
            $table->boolean('is_hod')->default(false)->index();
            $table->date('joined_on')->nullable();
            $table->string('mobile', 15)->nullable()->index();
            $table->string('email')->nullable();
            // Reporting line, from the staff hierarchy workbook.
            $table->foreignId('reports_to_id')->nullable()
                  ->constrained('staff')->nullOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('head_staff_id')->references('id')->on('staff')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['head_staff_id']);
        });
        Schema::dropIfExists('staff');
        Schema::dropIfExists('departments');
    }
};
