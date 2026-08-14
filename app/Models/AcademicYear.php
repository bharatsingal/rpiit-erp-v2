<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'starts_on', 'ends_on', 'is_current'];

    protected function casts(): array
    {
        return [
            'starts_on'  => 'date',
            'ends_on'    => 'date',
            'is_current' => 'boolean',
        ];
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public static function current(): ?self
    {
        return static::where('is_current', true)->first();
    }
}
