<?php

namespace Tests\Feature\Living;

use App\Domain\Living\Models\Room;
use App\Domain\Living\Models\Tenant;
use App\Enums\TenantStatus;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MaintenanceRequestTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    private function tenantUser(): array
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('customer');
        $room = Room::factory()->create();
        $tenant = Tenant::factory()->create(['user_id' => $user->id, 'status' => TenantStatus::Active, 'room_id' => $room->id]);

        return [$user, $tenant, $room];
    }

    public function test_non_tenant_cannot_create_a_maintenance_request(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('customer');

        $this->actingAs($user)->get('/maintenance-requests/create')->assertForbidden();
    }

    public function test_tenant_can_submit_a_complaint_with_photo(): void
    {
        Storage::fake('public_media');
        [$user] = $this->tenantUser();

        $response = $this->actingAs($user)->post('/maintenance-requests', [
            'title' => 'AC bocor',
            'description' => 'Air menetes dari unit AC sejak kemarin malam.',
            'priority' => 'high',
            'photos' => [UploadedFile::fake()->image('leak.jpg', 800, 600)],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('maintenance_requests', ['title' => 'AC bocor', 'priority' => 'high', 'status' => 'new']);

        $mr = \App\Domain\Living\Models\MaintenanceRequest::where('title', 'AC bocor')->firstOrFail();
        $this->assertCount(1, $mr->attachments);
    }

    public function test_stranger_cannot_view_someone_elses_complaint(): void
    {
        [$owner] = $this->tenantUser();
        [$stranger] = $this->tenantUser();

        $mr = \App\Domain\Living\Models\MaintenanceRequest::factory()->create([
            'tenant_id' => $owner->tenant->id,
            'room_id' => $owner->tenant->room_id,
        ]);

        $this->actingAs($stranger)->get("/maintenance-requests/{$mr->id}")->assertForbidden();
    }

    public function test_tenant_and_admin_can_both_comment_on_the_same_thread(): void
    {
        [$user] = $this->tenantUser();
        $admin = $this->admin();

        $mr = \App\Domain\Living\Models\MaintenanceRequest::factory()->create([
            'tenant_id' => $user->tenant->id,
            'room_id' => $user->tenant->room_id,
        ]);

        $this->actingAs($user)->post("/maintenance-requests/{$mr->id}/comments", ['comment' => 'Kapan bisa diperbaiki?'])->assertRedirect();
        $this->actingAs($admin)->post("/admin/maintenance-requests/{$mr->id}/comments", ['comment' => 'Teknisi akan datang besok pagi.'])->assertRedirect();

        $this->assertSame(2, $mr->comments()->count());
    }

    public function test_admin_can_update_status_and_it_marks_resolved_at(): void
    {
        $admin = $this->admin();
        $mr = \App\Domain\Living\Models\MaintenanceRequest::factory()->create();

        $this->actingAs($admin)->put("/admin/maintenance-requests/{$mr->id}/status", [
            'status' => 'completed',
            'resolution_notes' => 'Sudah diperbaiki teknisi.',
        ])->assertRedirect();

        $mr->refresh();
        $this->assertSame(\App\Enums\MaintenanceStatus::Completed, $mr->status);
        $this->assertNotNull($mr->resolved_at);
        $this->assertSame($admin->id, $mr->assigned_to);
    }
}
