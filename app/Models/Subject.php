<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'credits', 'type', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'credits' => 'integer'];
    }

    public function offerings()
    {
        return $this->hasMany(SubjectOffering::class);
    }
}
