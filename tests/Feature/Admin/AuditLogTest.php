<?php

namespace Tests\Feature\Admin;

use App\Domain\Platform\Models\Faq;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_changes_are_recorded_in_audit_log(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->actingAs($admin)->post('/admin/content/faqs', [
            'question' => 'Apakah ada biaya admin?',
            'answer' => 'Ya, sebesar Rp50.000.',
            'category' => 'payment',
            'is_published' => true,
            'sort_order' => 0,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'auditable_type' => Faq::class,
            'user_id' => $admin->id,
        ]);
    }

    public function test_login_is_recorded_in_audit_log(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['login' => $user->email, 'password' => 'password']);

        $this->assertDatabaseHas('audit_logs', ['action' => 'login', 'user_id' => $user->id]);
    }

    public function test_super_admin_can_view_audit_log(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->actingAs($admin)->get('/admin/audit-logs')->assertOk();
    }

    public function test_customer_cannot_view_audit_log(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $this->actingAs($customer)->get('/admin/audit-logs')->assertForbidden();
    }
}
