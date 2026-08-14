<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_offering_id', 'timetable_slot_id', 'section_id',
        'held_on', 'period_number', 'marked_by', 'marked_at', 'status', 'note',
    ];

    protected function casts(): array
    {
        return [
            'held_on'   => 'date',
            'marked_at' => 'datetime',
        ];
    }

    public function subjectOffering()
    {
        return $this->belongsTo(SubjectOffering::class);
    }

    public function timetableSlot()
    {
        return $this->belongsTo(TimetableSlot::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function records()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function presentCount(): int
    {
        return $this->records()->whereIn('status', ['present', 'late'])->count();
    }

    public function absentCount(): int
    {
        return $this->records()->where('status', 'absent')->count();
    }
}
