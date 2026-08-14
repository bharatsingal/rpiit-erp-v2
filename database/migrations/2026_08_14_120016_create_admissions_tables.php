<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mirrors the printed RPIIT APPLICATION FORM, so a scanned form maps
        // field-for-field onto a row here.
        Schema::create('admission_applications', function (Blueprint $table) {
            $table->id();
            $table->string('serial_no', 20)->nullable()->index();   // "Sr.No" on the form
            $table->string('candidate_name');
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('blood_group', 5)->nullable();
            $table->string('student_mobile', 15)->nullable()->index();
            $table->string('parent_mobile', 15)->nullable();
            $table->string('email')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            // Aadhaar: last four only. Storing the full number is a liability
            // we do not need — nothing in the ERP requires it.
            $table->string('aadhaar_last4', 4)->nullable();
            $table->string('family_id', 20)->nullable();
            $table->text('permanent_address')->nullable();
            $table->text('correspondence_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode', 10)->nullable();
            // GEN / SC / ST / BC-A / BC-B / OBC / Others, as printed on the form.
            $table->string('category', 10)->nullable()->index();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('status', ['draft', 'submitted', 'verified', 'admitted', 'rejected'])
                  ->default('draft')->index();
            // Set once the application becomes a student record.
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();

            // Provenance of the data, so nobody has to guess later whether a
            // field was typed by a human or guessed by a machine.
            $table->enum('source', ['manual', 'pdf_import'])->default('manual');
            $table->json('extracted_data')->nullable();      // raw extraction, before review
            $table->boolean('is_reviewed')->default(false)->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // The 10th / 12th / Diploma table on the form.
        Schema::create('applicant_qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_application_id')->constrained()->cascadeOnDelete();
            $table->string('level', 20);                 // 10th, 12th, Diploma
            $table->string('board_university')->nullable();
            $table->string('institution')->nullable();
            $table->string('roll_no', 40)->nullable();
            $table->year('year_of_passing')->nullable();
            $table->unsignedInteger('marks_obtained')->nullable();
            $table->unsignedInteger('marks_max')->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->string('stream', 20)->nullable();     // PCM / PCB / PCBE
            $table->timestamps();
        });

        // The "Documents to be Attached" checklist, plus the uploaded file.
        // Stored outside the web root and served through an authorised route.
        Schema::create('applicant_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_application_id')->constrained()->cascadeOnDelete();
            $table->string('doc_type', 40);   // photo, 10th_dmc, 12th_dmc, aadhaar, family_id,
                                              // slc_migration, character, category, residence
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->unsignedInteger('size_bytes')->nullable();
            $table->unsignedSmallInteger('page_from')->nullable();  // if split from one PDF
            $table->unsignedSmallInteger('page_to')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['admission_application_id', 'doc_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicant_documents');
        Schema::dropIfExists('applicant_qualifications');
        Schema::dropIfExists('admission_applications');
    }
};
