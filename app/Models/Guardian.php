<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'phone', 'email', 'occupation'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Many-to-many: one parent may have several children at RPIIT. */
    public function students()
    {
        return $this->belongsToMany(Student::class)
                    ->withPivot('relation', 'is_primary');
    }
}
