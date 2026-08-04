<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_admin_has_every_permission(): void
    {
        $admin = $this->userWithRole('admin');

        $this->assertTrue($admin->can('content.manage'));
        $this->assertTrue($admin->can('settings.manage'));
        $this->assertTrue($admin->can('users.manage'));
        $this->assertTrue($admin->can('audit-logs.view'));
        $this->assertTrue($admin->can('payments.verify'));
        $this->assertTrue($admin->can('rooms.manage'));
        $this->assertTrue($admin->can('invoices.manage'));
    }

    public function test_customer_has_no_admin_permissions(): void
    {
        $customer = $this->userWithRole('customer');

        foreach (['content.manage', 'settings.manage', 'rooms.manage', 'invoices.manage', 'users.manage', 'audit-logs.view'] as $permission) {
            $this->assertFalse($customer->can($permission));
        }
    }

    public function test_admin_area_routes_reject_customers(): void
    {
        $customer = $this->userWithRole('customer');

        $this->actingAs($customer)->get('/admin/settings')->assertForbidden();
        $this->actingAs($customer)->get('/admin/users')->assertForbidden();
        $this->actingAs($customer)->get('/admin/content/pages')->assertForbidden();
        $this->actingAs($customer)->get('/admin/audit-logs')->assertForbidden();
    }

    public function test_only_admin_and_customer_roles_exist(): void
    {
        $this->assertSame(
            ['admin', 'customer'],
            \Spatie\Permission\Models\Role::pluck('name')->sort()->values()->all(),
        );
    }
}
