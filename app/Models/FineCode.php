<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FineCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'description', 'basis', 'applies_to',
        'amount', 'plus_actual_cost', 'cap_amount',
        'effective_from', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'plus_actual_cost' => 'boolean',
            'is_active'        => 'boolean',
            'effective_from'   => 'date',
        ];
    }

    public function fines()
    {
        return $this->hasMany(Fine::class);
    }
}
