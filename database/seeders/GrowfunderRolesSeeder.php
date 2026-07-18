<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class GrowfunderRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates Growfunder-specific roles: Agent and Investor
     */
    public function run(): void
    {
        // Create Agent role (manage farmers in their cooperative)
        $agentRole = Role::firstOrCreate(
            ['name' => 'agent', 'guard_name' => 'web']
        );

        // Create Investor role (view-only access to loan portfolio)
        $investorRole = Role::firstOrCreate(
            ['name' => 'investor', 'guard_name' => 'web']
        );

        // Agent permissions: Can view and manage borrowers/farmers in their cooperative
        $agentPermissions = [
            'view_borrower',
            'view_any_borrower',
            'create_borrower',
            'update_borrower',
            'view_loan',
            'view_any_loan',
            'create_loan',
            'view_payment',
            'view_any_payment',
            'create_payment',
            'view_repayment',
            'view_any_repayment',
            'view_cooperative',
        ];

        // Get or create permissions and assign to agent role
        foreach ($agentPermissions as $permissionName) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web']
            );
            $agentRole->givePermissionTo($permission);
        }

        // Investor permissions: View-only access to loans and repayments
        $investorPermissions = [
            'view_loan',
            'view_any_loan',
            'view_borrower',
            'view_any_borrower',
            'view_payment',
            'view_any_payment',
            'view_repayment',
            'view_any_repayment',
            'view_cooperative',
        ];

        // Get or create permissions and assign to investor role
        foreach ($investorPermissions as $permissionName) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web']
            );
            $investorRole->givePermissionTo($permission);
        }

        $this->command->info('✓ Agent role created with farmer management permissions');
        $this->command->info('✓ Investor role created with portfolio view permissions');
    }
}
