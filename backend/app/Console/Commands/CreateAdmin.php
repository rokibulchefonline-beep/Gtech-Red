<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends Command
{
    protected $signature = 'gtech:create-admin {email?} {--name=}';

    protected $description = 'Create a super admin for the panel (or reset an existing user\'s password)';

    public function handle(): int
    {
        $email = strtolower((string) ($this->argument('email') ?: $this->ask('Email')));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->error('Enter a valid email.'); return self::FAILURE; }
        $pass = (string) $this->secret('Password (at least 10 characters, letters and numbers)');
        if (strlen($pass) < 10 || ! preg_match('/[A-Za-z]/', $pass) || ! preg_match('/\d/', $pass)) { $this->error('Password too weak.'); return self::FAILURE; }
        $u = User::query()->firstOrNew(['email' => $email]);
        $u->forceFill(['name' => $this->option('name') ?: ($u->name ?: explode('@', $email)[0]), 'role' => 'super_admin', 'active' => true, 'password' => Hash::make($pass)])->save();
        $this->info("Super admin ready: $email. Sign in at ".url('/admin'));
        return self::SUCCESS;
    }
}
