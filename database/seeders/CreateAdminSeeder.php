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
        try {
            // Delete existing admin if it exists
            User::where('email', 'admin@growfunder.local')->delete();
            
            // Create new admin user
            $admin = User::create([
                'name' => 'Administrator',
                'email' => 'admin@growfunder.local',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]);
            
            // Assign super admin role with error handling
            try {
                $admin->assignRole('super_admin');
                echo "\n✓ Admin user created successfully:\n";
                echo "  Email: admin@growfunder.local\n";
                echo "  Password: admin123\n";
                echo "  Role: super_admin\n";
            } catch (\Exception $e) {
                echo "\n✗ WARNING: Admin user created but role assignment failed:\n";
                echo "  Error: " . $e->getMessage() . "\n";
                echo "  You may need to manually assign the super_admin role.\n";
                \Log::error('Admin role assignment failed', [
                    'email' => 'admin@growfunder.local',
                    'error' => $e->getMessage()
                ]);
            }
        } catch (\Exception $e) {
            echo "\n✗ ERROR: Failed to create admin user:\n";
            echo "  Error: " . $e->getMessage() . "\n";
            \Log::error('Admin user creation failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
