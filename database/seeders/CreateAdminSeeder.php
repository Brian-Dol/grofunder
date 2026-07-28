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
            // 1. Create/update the primary admin user
            $admin = User::firstOrCreate(
                ['email' => 'admin@growfunder.local'],
                [
                    'name' => 'Administrator',
                    'password' => Hash::make('admin123'),
                    'email_verified_at' => now(),
                ]
            );
            
            // 2. Also ensure dol.brian23@gmail.com has super_admin if it exists
            $briansUser = User::where('email', 'dol.brian23@gmail.com')->first();
            if ($briansUser) {
                try {
                    if (!$briansUser->hasRole('super_admin')) {
                        $briansUser->assignRole('super_admin');
                    }
                    echo "\n✓ User dol.brian23@gmail.com assigned super_admin role\n";
                } catch (\Exception $e) {
                    echo "\n✗ WARNING: Could not assign role to dol.brian23@gmail.com:\n";
                    echo "  Error: " . $e->getMessage() . "\n";
                }
            }
            
            // 3. Assign super admin role to primary admin user
            try {
                if (!$admin->hasRole('super_admin')) {
                    $admin->assignRole('super_admin');
                }
                echo "✓ Admin user created/updated successfully:\n";
                echo "  Email: admin@growfunder.local\n";
                echo "  Password: admin123\n";
                echo "  Role: super_admin\n";
            } catch (\Exception $e) {
                echo "\n✗ WARNING: Admin user exists but role assignment failed:\n";
                echo "  Error: " . $e->getMessage() . "\n";
                \Log::error('Admin role assignment failed', [
                    'email' => 'admin@growfunder.local',
                    'error' => $e->getMessage()
                ]);
            }
        } catch (\Exception $e) {
            echo "\n✗ ERROR: Failed to setup admin:\n";
            echo "  Error: " . $e->getMessage() . "\n";
            \Log::error('Admin setup failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
