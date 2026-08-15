<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFeeBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'as_of', 'due', 'receipt',
        'outstanding', 'advance', 'source',
    ];

    protected function casts(): array
    {
        return ['as_of' => 'date'];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function scopeWithDues($query)
    {
        return $query->where('outstanding', '>', 0);
    }
}
