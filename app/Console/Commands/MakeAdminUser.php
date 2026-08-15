<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * Creates the first sign-in account. The password is typed at a hidden prompt
 * so it never appears in the shell history or in a command argument.
 *
 *   php artisan rpiit:make-admin "Bharat Singal" bharatsingal@rpiit.com
 */
class MakeAdminUser extends Command
{
    protected $signature = 'rpiit:make-admin {name} {email}';
    protected $description = 'Create an administrator account';

    public function handle(): int
    {
        $password = $this->secret('Choose a password (min 12 characters)');

        if (strlen((string) $password) < 12) {
            $this->error('Too short — use at least 12 characters.');
            return self::FAILURE;
        }

        if ($password !== $this->secret('Type it again')) {
            $this->error('Passwords did not match.');
            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $this->argument('email')],
            [
                'name'      => $this->argument('name'),
                'password'  => Hash::make($password),
                'role'      => 'admin',
                'is_active' => true,
            ]
        );

        $this->info("Administrator ready: {$user->email}");
        $this->line('Sign in at https://rpiitacademics.com/login');

        return self::SUCCESS;
    }
}
