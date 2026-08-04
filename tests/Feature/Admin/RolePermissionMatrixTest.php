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

    public function test_super_admin_has_every_permission(): void
    {
        $admin = $this->userWithRole('super-admin');

        $this->assertTrue($admin->can('content.manage'));
        $this->assertTrue($admin->can('settings.manage'));
        $this->assertTrue($admin->can('users.manage'));
        $this->assertTrue($admin->can('audit-logs.view'));
        $this->assertTrue($admin->can('payments.verify'));
    }

    public function test_admin_cannot_manage_settings_or_users_but_can_manage_content(): void
    {
        $admin = $this->userWithRole('admin');

        $this->assertTrue($admin->can('content.manage'));
        $this->assertTrue($admin->can('audit-logs.view'));
        $this->assertFalse($admin->can('settings.manage'));
        $this->assertFalse($admin->can('users.manage'));
    }

    public function test_property_manager_is_scoped_to_operations_only(): void
    {
        $manager = $this->userWithRole('property-manager');

        $this->assertTrue($manager->can('rooms.manage'));
        $this->assertTrue($manager->can('bookings.manage'));
        $this->assertTrue($manager->can('tenants.manage'));
        $this->assertFalse($manager->can('content.manage'));
        $this->assertFalse($manager->can('invoices.manage'));
        $this->assertFalse($manager->can('users.manage'));
    }

    public function test_finance_is_scoped_to_money_only(): void
    {
        $finance = $this->userWithRole('finance');

        $this->assertTrue($finance->can('invoices.manage'));
        $this->assertTrue($finance->can('payments.manage'));
        $this->assertTrue($finance->can('reports.export'));
        $this->assertFalse($finance->can('rooms.manage'));
        $this->assertFalse($finance->can('content.manage'));
    }

    public function test_customer_and_tenant_have_no_admin_permissions(): void
    {
        $customer = $this->userWithRole('customer');
        $tenant = $this->userWithRole('tenant');

        foreach (['content.manage', 'settings.manage', 'rooms.manage', 'invoices.manage', 'users.manage'] as $permission) {
            $this->assertFalse($customer->can($permission));
            $this->assertFalse($tenant->can($permission));
        }
    }

    public function test_admin_area_routes_reject_roles_without_the_matching_permission(): void
    {
        $propertyManager = $this->userWithRole('property-manager');

        $this->actingAs($propertyManager)->get('/admin/settings')->assertForbidden();
        $this->actingAs($propertyManager)->get('/admin/users')->assertForbidden();
        $this->actingAs($propertyManager)->get('/admin/content/pages')->assertForbidden();
    }
}
