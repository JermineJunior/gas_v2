<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->hasRole('admin')) {
                return redirect()->back()->with('error', 'غير مصرح لك بالوصول لهذه الصفحة');
            }
            return $next($request);
        });
    }

    private array $groupLabels = [
        'stations'              => 'المحطات',
        'users'                 => 'المستخدمين',
        'roles'                 => 'الأدوار',
        'clients'               => 'العملاء',
        'suppliers'             => 'الموردين',
        'fuel_orders'           => 'طلبات الوقود',
        'machines'              => 'الماكينات',
        'machine_details'       => 'العدادات',
        'tunckers'              => 'التناكر',
        'deposit_details'       => 'التوريدات',
        'employees'             => 'الموظفين',
        'stocks'                => 'الابار',
        'revenue'               => 'الإيرادات',
        'prices'                => 'الاسعار',
        'reports'               => 'التقارير',
        'warehouses'            => 'المستودعات',
        'warehouse_withdrawals' => 'سحب المستودع',
        'warehouse_transactions'=> 'حركات المستودع',
        'warehouse_transfers'   => 'تحويلات المستودع',
    ];

    public function index()
    {
        $roles = Role::withCount('permissions')->get();
        return view('role.index', compact('roles'));
    }

    public function create()
    {
        $permissions = config('permissions');
        return view('role.create', ['permissions' => $permissions, 'groupLabels' => $this->groupLabels]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'required|array',
        ]);

        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.index')->with('success', 'تمت إنشاء الدور بنجاح');
    }

    public function edit(Role $role)
    {
        $permissions = config('permissions');
        $role->load('permissions');
        return view('role.edit', ['role' => $role, 'permissions' => $permissions, 'groupLabels' => $this->groupLabels]);
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'required|array',
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.index')->with('success', 'تم تحديث الدور بنجاح');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'تم حذف الدور بنجاح');
    }
}
