<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class AssignAdminRole extends Command
{
    protected $signature = 'admin:assign-role {role=super_admin}';
    protected $description = 'Assign a role to the first admin user';

    public function handle()
    {
        $user = User::first();
        if (!$user) {
            $this->error('No users found');
            return 1;
        }

        $role = $this->argument('role');
        
        // Remove all existing roles
        $user->syncRoles([]);
        
        // Assign new role
        $user->assignRole($role);

        $this->info("User {$user->email} assigned role: {$role}");
        return 0;
    }
}
