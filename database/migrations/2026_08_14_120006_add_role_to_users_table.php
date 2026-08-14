<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Everyone who can log in has exactly one row here, whatever they are.
            // Passwords live only in this table, hashed by Laravel. v1 kept a
            // password column on the student record itself — never repeat that.
            $table->string('role', 20)->default('student')->index()->after('email');
            $table->string('phone', 15)->nullable()->after('role');
            $table->boolean('is_active')->default(true)->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'is_active']);
        });
    }
};
