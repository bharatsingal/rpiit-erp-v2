<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'staff';

    protected $fillable = [
        'user_id', 'staff_no', 'name', 'department_id', 'category',
        'designation', 'is_hod', 'joined_on', 'mobile', 'email',
        'reports_to_id', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_hod'    => 'boolean',
            'is_active' => 'boolean',
            'joined_on' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function reportsTo()
    {
        return $this->belongsTo(Staff::class, 'reports_to_id');
    }

    public function reports()
    {
        return $this->hasMany(Staff::class, 'reports_to_id');
    }

    public function scopeTeaching($query)
    {
        return $query->where('category', 'staff');
    }
}
