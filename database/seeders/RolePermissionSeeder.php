<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Build the list of valid permission keys from config
        $configPermissions = [];
        $permissions = config('permissions');
        foreach ($permissions as $group => $perms) {
            foreach ($perms as $key => $label) {
                $configPermissions[] = $key;
            }
        }

        // Remove orphaned permissions not in config
        Permission::whereNotIn('name', $configPermissions)->delete();

        // Create permissions from config
        foreach ($permissions as $group => $perms) {
            foreach ($perms as $key => $label) {
                Permission::firstOrCreate(['name' => $key]);
            }
        }

        // Create admin role with all permissions
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        // Create viewer role with view-only permissions
        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewerPermissions = [
            'stations.view',
            'users.view',
            'clients.view',
            'suppliers.view',
            'machines.view',
            'machine_details.view',
            'tunckers.view',
            'deposit_details.view',
            'employees.view',
            'stocks.view',
            'revenue.view',
            'prices.view',
            'reports.debt',
            'reports.tuncker',
            'reports.machine_detail',
            'reports.deposit_detail',
            'reports.supplier',
            'reports.warehouse',
            'warehouses.view',
            'warehouse_withdrawals.view',
            'warehouse_transactions.view',
            'warehouse_transfers.view',
        ];
        $viewer->syncPermissions($viewerPermissions);

        // Assign roles to existing users based on type
        User::all()->each(function ($user) {
            $user->syncRoles([]);

            if ($user->type == 0 || $user->type == 1) {
                $user->assignRole('admin');
            } elseif ($user->type == 3) {
                $user->assignRole('viewer');
            } else {
                $user->assignRole('admin');
            }
        });
    }
}
