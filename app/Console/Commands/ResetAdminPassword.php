<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetAdminPassword extends Command
{
    protected $signature = 'admin:reset-password {password=password}';
    protected $description = 'Reset admin user password';

    public function handle()
    {
        $user = User::first();
        if (!$user) {
            $this->error('No users found');
            return 1;
        }

        $password = $this->argument('password');
        $user->password = bcrypt($password);
        $user->save();

        $this->info("Admin password reset to: {$password}");
        return 0;
    }
}
