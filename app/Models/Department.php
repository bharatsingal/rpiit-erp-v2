<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'kind', 'head_staff_id', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function staff()
    {
        return $this->hasMany(Staff::class);
    }

    public function head()
    {
        return $this->belongsTo(Staff::class, 'head_staff_id');
    }
}
