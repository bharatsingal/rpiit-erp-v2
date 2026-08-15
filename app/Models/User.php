<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    /** Roles that see the whole campus rather than a single department. */
    public function seesEverything(): bool
    {
        return in_array($this->role, ['admin', 'principal', 'director', 'registrar', 'accounts'], true);
    }

    public function department(): ?Department
    {
        return $this->staff?->department;
    }

    /**
     * Course ids this user may see.
     *
     * Null means "no restriction" — deliberately distinct from an empty array,
     * which means "restricted, and entitled to nothing". Collapsing the two
     * would silently show everything to a user with no department.
     *
     * @return array<int>|null
     */
    public function visibleCourseIds(): ?array
    {
        if ($this->seesEverything()) {
            return null;
        }

        $dept = $this->department();

        return $dept
            ? Course::where('department_id', $dept->id)->pluck('id')->all()
            : [];
    }

    public function isHod(): bool
    {
        return (bool) $this->staff?->is_hod;
    }
}
