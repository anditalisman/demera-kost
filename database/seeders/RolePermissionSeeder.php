<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Permission matrix per module. Every permission is named "{module}.{action}".
     */
    private const PERMISSIONS = [
        'users' => ['view', 'manage'],
        'content' => ['view', 'manage'],
        'settings' => ['view', 'manage'],
        'rooms' => ['view', 'manage'],
        'bookings' => ['view', 'manage'],
        'tenants' => ['view', 'manage'],
        'leases' => ['view', 'manage'],
        'maintenance' => ['view', 'manage'],
        'invoices' => ['view', 'manage'],
        'payments' => ['view', 'verify', 'manage'],
        'reports' => ['view', 'export'],
        'audit-logs' => ['view'],
    ];

    /**
     * Role => permissions granted. "*" grants every permission.
     */
    private const ROLES = [
        'super-admin' => ['*'],
        'admin' => [
            'users.view', 'content.manage', 'settings.view',
            'rooms.manage', 'bookings.manage', 'tenants.manage', 'leases.manage',
            'maintenance.manage', 'invoices.view', 'payments.view',
            'reports.view', 'reports.export', 'audit-logs.view',
        ],
        'property-manager' => [
            'rooms.manage', 'bookings.manage', 'tenants.manage', 'leases.manage',
            'maintenance.manage', 'reports.view',
        ],
        'finance' => [
            'invoices.manage', 'payments.manage', 'reports.view', 'reports.export',
        ],
        'customer' => [],
        'tenant' => [],
    ];

    public function run(): void
    {
        Cache::forget('spatie.permission.cache');

        foreach (self::PERMISSIONS as $module => $actions) {
            foreach ($actions as $action) {
                Permission::findOrCreate("{$module}.{$action}", 'web');
            }
        }

        foreach (self::ROLES as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');

            if ($permissions === ['*']) {
                $role->syncPermissions(Permission::all());
            } else {
                $role->syncPermissions($permissions);
            }
        }
    }
}
