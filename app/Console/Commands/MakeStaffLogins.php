<?php

namespace App\Console\Commands;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Creates sign-in accounts for staff.
 *
 * A staff member's department decides what they can see, so an HOD gets their
 * own department and nothing else. Passwords are generated, printed once, and
 * must be changed by the holder — this command is run by the administrator,
 * so the passwords are shown deliberately.
 *
 *   php artisan rpiit:make-staff-logins --hods
 *   php artisan rpiit:make-staff-logins --department="NURSING"
 */
class MakeStaffLogins extends Command
{
    protected $signature = 'rpiit:make-staff-logins
                            {--hods : Only heads of department}
                            {--department= : Limit to one department by name}
                            {--dry-run : List who would get an account}';

    protected $description = 'Create sign-in accounts for staff, scoped to their department';

    public function handle(): int
    {
        $query = Staff::query()
            ->with('department')
            ->where('category', 'staff')
            ->where('is_active', true)
            ->whereNull('user_id')
            ->when($this->option('hods'), fn ($q) => $q->where('is_hod', true))
            ->when($this->option('department'), fn ($q) => $q->whereHas('department',
                fn ($d) => $d->where('name', $this->option('department'))));

        $people = $query->orderBy('name')->get();

        if ($people->isEmpty()) {
            $this->warn('Nobody matched — they may already have accounts.');
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->table(['Staff no.', 'Name', 'Department', 'HOD'],
                $people->map(fn ($p) => [$p->staff_no, $p->name, $p->department?->name ?? '—', $p->is_hod ? 'yes' : ''])->all());
            return self::SUCCESS;
        }

        $rows = [];

        foreach ($people as $p) {
            // Login id from the staff number — stable, unique, and nothing to
            // do with a personal email address they may not have.
            $login = Str::lower($p->staff_no);
            $password = Str::password(12, symbols: false);

            $user = User::create([
                'name'      => $login,
                'email'     => $p->email ?: $login.'@rpiitacademics.com',
                'password'  => Hash::make($password),
                'role'      => $p->is_hod ? 'hod' : 'faculty',
                'phone'     => $p->mobile,
                'is_active' => true,
            ]);

            $p->update(['user_id' => $user->id]);

            $rows[] = [$p->name, $login, $password, $p->department?->name ?? '—'];
        }

        $this->newLine();
        $this->table(['Name', 'Login id', 'Password', 'Sees'], $rows);
        $this->newLine();
        $this->warn('  These passwords are shown once. Hand them out, then have them changed.');
        $this->line('  Staff sign in with the login id, not an email address.');

        return self::SUCCESS;
    }
}
