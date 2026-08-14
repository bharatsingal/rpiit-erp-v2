<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'admission_no', 'roll_no',
        'first_name', 'last_name', 'date_of_birth', 'gender',
        'phone', 'email', 'address', 'city', 'state', 'pincode',
        'photo_path', 'admitted_on', 'status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'admitted_on'   => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function guardians()
    {
        return $this->belongsToMany(Guardian::class)
                    ->withPivot('relation', 'is_primary');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /** Where the student is right now. */
    public function currentEnrollment()
    {
        return $this->hasOne(Enrollment::class)
                    ->whereHas('academicYear', fn ($q) => $q->where('is_current', true));
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
