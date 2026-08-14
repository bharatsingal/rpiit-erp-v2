<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectOffering extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id', 'batch_id', 'term_id', 'academic_year_id',
        'section_id', 'faculty_id', 'is_elective',
    ];

    protected function casts(): array
    {
        return ['is_elective' => 'boolean'];
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function timetableSlots()
    {
        return $this->hasMany(TimetableSlot::class);
    }

    public function attendanceSessions()
    {
        return $this->hasMany(AttendanceSession::class);
    }

    /**
     * The students this offering is actually taught to — section if it has
     * one, otherwise the whole batch for that term.
     */
    public function students()
    {
        return Student::query()
            ->whereHas('enrollments', function ($q) {
                $q->where('batch_id', $this->batch_id)
                  ->where('term_id', $this->term_id)
                  ->where('academic_year_id', $this->academic_year_id);

                if ($this->section_id) {
                    $q->where('section_id', $this->section_id);
                }
            })
            ->where('status', 'active')
            ->orderBy('roll_no');
    }
}
