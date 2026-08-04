<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_super_admin_can_view_user_list(): void
    {
        $admin = $this->superAdmin();
        User::factory()->count(3)->create();

        $this->actingAs($admin)->get('/admin/users')->assertOk();
    }

    public function test_customer_cannot_view_user_list(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $this->actingAs($customer)->get('/admin/users')->assertForbidden();
    }

    public function test_super_admin_can_change_user_roles(): void
    {
        $admin = $this->superAdmin();
        $target = User::factory()->create();
        $target->assignRole('customer');

        $this->actingAs($admin)->put("/admin/users/{$target->id}/roles", [
            'roles' => ['admin'],
        ])->assertRedirect();

        $this->assertTrue($target->fresh()->hasRole('admin'));
        $this->assertFalse($target->fresh()->hasRole('customer'));
    }

    public function test_super_admin_can_deactivate_another_user(): void
    {
        $admin = $this->superAdmin();
        $target = User::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->put("/admin/users/{$target->id}/toggle-active")->assertRedirect();

        $this->assertFalse($target->fresh()->is_active);
    }

    public function test_admin_cannot_deactivate_themselves(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->put("/admin/users/{$admin->id}/toggle-active")->assertForbidden();
    }
}
