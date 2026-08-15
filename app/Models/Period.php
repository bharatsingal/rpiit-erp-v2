<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    use HasFactory;

    protected $fillable = ['number', 'label', 'starts_at', 'ends_at', 'is_teaching'];

    protected function casts(): array
    {
        return ['is_teaching' => 'boolean'];
    }

    public function timeRange(): string
    {
        return substr($this->starts_at, 0, 5).'–'.substr($this->ends_at, 0, 5);
    }

    public function scopeTeaching($q)
    {
        return $q->where('is_teaching', true);
    }
}
