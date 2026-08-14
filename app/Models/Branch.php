<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class)->withPivot('intake');
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }
}
