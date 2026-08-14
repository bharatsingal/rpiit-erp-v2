<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{
    use HasFactory;

    protected $fillable = [
        'fine_code_id', 'student_id', 'staff_id', 'imposed_on',
        'amount', 'actual_cost', 'remarks', 'imposed_by',
        'status', 'receipt_no', 'paid_on', 'waived_by',
    ];

    protected function casts(): array
    {
        return ['imposed_on' => 'date', 'paid_on' => 'date'];
    }

    public function fineCode()
    {
        return $this->belongsTo(FineCode::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function imposedBy()
    {
        return $this->belongsTo(User::class, 'imposed_by');
    }

    /** Rate-card amount plus any actual cost (damage, lost book, breakage). */
    public function totalAmount(): int
    {
        return $this->amount + $this->actual_cost;
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
