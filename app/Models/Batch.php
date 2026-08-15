<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id', 'branch_id', 'start_year', 'end_year', 'name', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'start_year' => 'integer',
            'end_year'   => 'integer',
        ];
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function subjectOfferings()
    {
        return $this->hasMany(SubjectOffering::class);
    }

    /** Years the course runs for, from the batch itself. */
    public function spanYears(): int
    {
        return max(0, $this->end_year - $this->start_year);
    }
}
