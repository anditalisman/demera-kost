<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Permission matrix per module. Every permission is named "{module}.{action}".
     * Kept granular (rather than collapsing to a single "admin.*" check)
     * so Policy classes stay meaningful and future roles can be reintroduced
     * without touching every controller — but Tahap 1 ships with exactly
     * two roles, and "admin" simply holds all of them.
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
        'admin' => ['*'],
        'customer' => [],
    ];

    /**
     * Legacy roles from the earlier 6-role design. Any user still holding
     * one of these gets remapped to its replacement below before the old
     * Role rows are deleted — keeps re-running this seeder safe on a
     * database that predates the 2-role simplification.
     */
    private const LEGACY_ROLE_REPLACEMENTS = [
        'super-admin' => 'admin',
        'property-manager' => 'admin',
        'finance' => 'admin',
        'tenant' => 'customer',
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

        $this->migrateLegacyRoles();
    }

    private function migrateLegacyRoles(): void
    {
        foreach (self::LEGACY_ROLE_REPLACEMENTS as $legacy => $replacement) {
            $legacyRole = Role::where('name', $legacy)->first();

            if (! $legacyRole) {
                continue;
            }

            User::role($legacy)->get()->each(fn (User $user) => $user->assignRole($replacement));

            $legacyRole->delete();
        }
    }
}
