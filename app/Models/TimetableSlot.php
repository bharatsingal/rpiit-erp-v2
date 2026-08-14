<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimetableSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_offering_id', 'section_id', 'day_of_week',
        'period_number', 'starts_at', 'ends_at', 'room',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week'   => 'integer',
            'period_number' => 'integer',
        ];
    }

    public function subjectOffering()
    {
        return $this->belongsTo(SubjectOffering::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function attendanceSessions()
    {
        return $this->hasMany(AttendanceSession::class);
    }
}
