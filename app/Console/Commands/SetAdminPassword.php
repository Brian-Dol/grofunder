<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SetAdminPassword extends Command
{
    protected $signature = 'admin:set-password {email} {password}';
    protected $description = 'Set password for an admin user';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User not found: $email");
            return 1;
        }

        $user->password = bcrypt($password);
        $user->save();

        $this->info("Password updated for: $email");
        return 0;
    }
}
