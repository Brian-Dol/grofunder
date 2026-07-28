<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // CRITICAL: Seed roles and permissions FIRST (required for user assignment)
        $this->call(NativeShieldSeeder::class);
        $this->call(PagePermissionsSeeder::class);
        $this->call(GrowfunderRolesSeeder::class);
        
        // Then create admin user with proper role assignment
        $this->call(CreateAdminSeeder::class);
        
        // Create test users (optional)
        \App\Models\User::factory(5)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
