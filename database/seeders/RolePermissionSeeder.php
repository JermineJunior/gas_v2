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
        $viewerPermissions =  [
            //stations
            'stations.view',
            'stations.edit',
            //clients
            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',
            //machines
            'machines.view',
            'machines.create',
             'machines.edit',
             'machines.delete',
            // machines العدادت
            'machine_details.view',
            'machine_details.create',
            'machine_details.edit',
            'machine_details.delete',
            //tunckers
            'tunckers.view',
            'tunckers.create',
            'tunckers.edit',
            //deposits
            'deposit_details.view',
            'deposit_details.create',
            'deposit_details.edit',
            'deposit_details.delete',
            //expenses
            'expenses.view',
            'expenses.create',
            'expenses.update',
            'expenses.delete',
            //employees
            'employees.view',
            'employees.create',
            'employees.edit',
            'employees.delete',
            //stocks
            'stocks.view',
            'stocks.create',
            'stocks.edit',
            'stocks.delete',
            //revenue (customers)
            'revenue.view',
            'revenue.create',
            'revenue.edit',
            'revenue.delete',
            //prices
            'prices.view',
            'prices.manage',
            //reports
            'reports.debt',
            'reports.tuncker',
            'reports.machine_detail',
            'reports.deposit_detail',
            //'reports.supplier',
         //   'reports.warehouse',
            'reports.machine_report',
            'reports.machine_report_time',
            'reports.stock_general',
            'reports.stock_movement',
           // 'warehouse_reports.withdrawals',
            //'warehouse_reports.additions',
            //'warehouse_reports.transfers',
            //'warehouse_reports.summary',
            //'warehouse_reports.consumption',
            'reports.employee_account',
            'reports.expense_list',
            'reports.expense_summary',
        ];
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
