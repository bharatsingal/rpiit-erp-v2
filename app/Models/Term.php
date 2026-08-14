<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    use HasFactory;

    protected $fillable = ['course_id', 'number', 'name'];

    protected function casts(): array
    {
        return ['number' => 'integer'];
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function subjectOfferings()
    {
        return $this->hasMany(SubjectOffering::class);
    }
}
