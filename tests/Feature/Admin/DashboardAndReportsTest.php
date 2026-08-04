<?php

namespace Tests\Feature\Admin;

use App\Domain\Living\Models\Deposit;
use App\Domain\Living\Models\Invoice;
use App\Domain\Living\Models\Lease;
use App\Domain\Living\Models\Payment;
use App\Domain\Living\Models\Room;
use App\Domain\Living\Models\Tenant;
use App\Enums\LeaseStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\RoomStatus;
use App\Enums\TenantStatus;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAndReportsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    private function customer(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('customer');

        return $user;
    }

    public function test_customer_cannot_access_admin_dashboard_or_reports(): void
    {
        $this->actingAs($this->customer())->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($this->customer())->get('/admin/reports')->assertForbidden();
    }

    public function test_dashboard_reports_correct_room_and_occupancy_counts(): void
    {
        $admin = $this->admin();
        Room::factory()->count(2)->create(['status' => RoomStatus::Available]);
        Room::factory()->count(3)->create(['status' => RoomStatus::Occupied]);
        Room::factory()->create(['status' => RoomStatus::Maintenance]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertOk();

        $stats = $response->viewData('page')['props']['stats'];
        $this->assertSame(6, $stats['rooms']['total']);
        $this->assertSame(3, $stats['rooms']['occupied']);
        $this->assertSame(2, $stats['rooms']['available']);
        $this->assertSame(1, $stats['rooms']['maintenance']);
        $this->assertSame(50.0, $stats['rooms']['occupancyRate']);
    }

    public function test_dashboard_sums_this_months_paid_payments_as_revenue(): void
    {
        $admin = $this->admin();
        $invoice = Invoice::factory()->create();

        Payment::factory()->create(['invoice_id' => $invoice->id, 'status' => PaymentStatus::Paid, 'amount' => 1000000, 'paid_at' => now()]);
        Payment::factory()->create(['invoice_id' => $invoice->id, 'status' => PaymentStatus::Paid, 'amount' => 500000, 'paid_at' => now()]);
        Payment::factory()->create(['invoice_id' => $invoice->id, 'status' => PaymentStatus::Pending, 'amount' => 999999, 'paid_at' => null]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $stats = $response->viewData('page')['props']['stats'];

        $this->assertSame(1500000.0, $stats['revenueThisMonth']);
    }

    public function test_occupancy_report_returns_expected_rows(): void
    {
        $admin = $this->admin();
        Room::factory()->count(2)->create(['status' => RoomStatus::Available]);
        Room::factory()->create(['status' => RoomStatus::Occupied]);

        $response = $this->actingAs($admin)->get('/admin/reports?type=occupancy');
        $response->assertOk();

        $rows = $response->viewData('page')['props']['rows'];
        $this->assertNotEmpty($rows);
    }

    public function test_active_tenants_report_lists_only_active_tenants(): void
    {
        $admin = $this->admin();
        $room = Room::factory()->create();
        $activeTenant = Tenant::factory()->create(['status' => TenantStatus::Active, 'room_id' => $room->id]);
        Tenant::factory()->create(['status' => TenantStatus::Inactive]);

        $response = $this->actingAs($admin)->get('/admin/reports?type=active_tenants');
        $rows = $response->viewData('page')['props']['rows'];

        $this->assertCount(1, $rows);
        $this->assertSame($activeTenant->user->name, $rows[0][0]);
    }

    public function test_revenue_by_period_report_sums_paid_payments_per_month(): void
    {
        $admin = $this->admin();
        $invoice = Invoice::factory()->create();

        Payment::factory()->create(['invoice_id' => $invoice->id, 'status' => PaymentStatus::Paid, 'amount' => 2000000, 'paid_at' => now()]);

        $response = $this->actingAs($admin)->get('/admin/reports?type=revenue_by_period');
        $rows = $response->viewData('page')['props']['rows'];

        $currentMonthRow = collect($rows)->last();
        $this->assertSame('2.000.000', $currentMonthRow[2]);
    }

    public function test_payments_by_method_report_groups_correctly(): void
    {
        $admin = $this->admin();
        $invoice = Invoice::factory()->create();

        Payment::factory()->create(['invoice_id' => $invoice->id, 'status' => PaymentStatus::Paid, 'method' => PaymentMethod::ManualTransfer, 'amount' => 1000000, 'paid_at' => now()]);
        Payment::factory()->create(['invoice_id' => $invoice->id, 'status' => PaymentStatus::Paid, 'method' => PaymentMethod::Qris, 'amount' => 500000, 'paid_at' => now()]);

        $response = $this->actingAs($admin)->get('/admin/reports?type=payments_by_method');
        $rows = $response->viewData('page')['props']['rows'];

        $this->assertCount(2, $rows);
    }

    public function test_deposits_report_lists_tenant_deposits(): void
    {
        $admin = $this->admin();
        $tenant = Tenant::factory()->create();
        Deposit::factory()->create(['tenant_id' => $tenant->id, 'amount' => 500000]);

        $response = $this->actingAs($admin)->get('/admin/reports?type=deposits');
        $rows = $response->viewData('page')['props']['rows'];

        $this->assertCount(1, $rows);
        $this->assertSame($tenant->user->name, $rows[0][0]);
    }

    public function test_leases_ending_soon_report_respects_days_filter(): void
    {
        $admin = $this->admin();
        $room = Room::factory()->create();
        $tenant = Tenant::factory()->create(['room_id' => $room->id]);
        Lease::factory()->create(['tenant_id' => $tenant->id, 'room_id' => $room->id, 'status' => LeaseStatus::Active, 'end_date' => now()->addDays(5)->toDateString()]);
        Lease::factory()->create(['tenant_id' => $tenant->id, 'room_id' => $room->id, 'status' => LeaseStatus::Active, 'end_date' => now()->addDays(60)->toDateString()]);

        $response = $this->actingAs($admin)->get('/admin/reports?type=leases_ending_soon&days=30');
        $rows = $response->viewData('page')['props']['rows'];

        $this->assertCount(1, $rows);
    }

    public function test_export_produces_correct_content_types(): void
    {
        $admin = $this->admin();
        Room::factory()->create(['status' => RoomStatus::Available]);

        $pdf = $this->actingAs($admin)->get('/admin/reports/export?type=occupancy&format=pdf');
        $pdf->assertOk();
        $this->assertSame('application/pdf', $pdf->headers->get('content-type'));

        $excel = $this->actingAs($admin)->get('/admin/reports/export?type=occupancy&format=excel');
        $excel->assertOk();

        $csv = $this->actingAs($admin)->get('/admin/reports/export?type=occupancy&format=csv');
        $csv->assertOk();
    }

    public function test_customer_cannot_export_reports(): void
    {
        $this->actingAs($this->customer())->get('/admin/reports/export?type=occupancy&format=pdf')->assertForbidden();
    }
}
