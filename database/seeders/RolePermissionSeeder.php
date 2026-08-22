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
        $viewer = Role::firstOrCreate(['name' => 'مدير المحطة']);
        $viewerPermissions = Permission::where('name', 'like', '%.view')->pluck('name')->toArray();
        $viewerPermissions = array_merge($viewerPermissions, [
            'reports.debt',
            'reports.tuncker',
            'reports.machine_detail',
            'reports.deposit_detail',
            'reports.supplier',
            'reports.warehouse',
            'reports.machine_report',
            'reports.machine_report_time',
            'reports.stock_general',
            'reports.stock_movement',
        ]);
        $viewer->syncPermissions($viewerPermissions);

        // Assign roles to existing users based on type
        User::all()->each(function ($user) {
            $user->syncRoles([]);

            if ($user->type == 0 || $user->type == 1) {
                $user->assignRole('admin');
            } elseif ($user->type == 3) {
                $user->assignRole('مدير المحطة');
            } else {
                $user->assignRole('admin');
            }
        });
    }
}
