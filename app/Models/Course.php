<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'term_type', 'total_terms', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'total_terms' => 'integer'];
    }

    public function terms()
    {
        return $this->hasMany(Term::class)->orderBy('number');
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class)->withPivot('intake');
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    public function isSemesterBased(): bool
    {
        return $this->term_type === 'semester';
    }

    /** "Semester" or "Year" — for labels, so no screen hardcodes either. */
    public function termLabel(): string
    {
        return $this->isSemesterBased() ? 'Semester' : 'Year';
    }
}
