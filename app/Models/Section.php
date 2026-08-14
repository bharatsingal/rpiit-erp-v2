<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = ['batch_id', 'name'];

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }
}
