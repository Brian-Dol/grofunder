<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete existing admin if it exists
        User::where('email', 'admin@growfunder.local')->delete();
        
        // Create new admin user
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@growfunder.local',
            'password' => Hash::make('admin123'),
            'email_verified_at' => now(),
        ]);
        
        // Assign super admin role
        $admin->assignRole('super_admin');
        
        echo "\n✓ Admin user created:\n";
        echo "  Email: admin@growfunder.local\n";
        echo "  Password: admin123\n";
    }
}
