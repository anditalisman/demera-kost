<?php

namespace Tests\Feature\Living;

use App\Domain\Living\Models\Lease;
use App\Domain\Living\Services\InvoiceService;
use App\Domain\Platform\Models\ApplicationSetting;
use App\Enums\InvoiceStatus;
use App\Enums\LeaseStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_full_month_invoice_when_lease_starts_on_billing_day(): void
    {
        $lease = Lease::factory()->create([
            'status' => LeaseStatus::Active,
            'start_date' => '2026-01-01',
            'billing_cycle_day' => 1,
            'monthly_price' => 1500000,
        ]);

        $invoice = app(InvoiceService::class)->generateMonthlyInvoice($lease, \Carbon\Carbon::parse('2026-02-01'));

        $this->assertNotNull($invoice);
        $this->assertSame('2026-02-01', $invoice->period_start->toDateString());
        $this->assertSame('2026-02-28', $invoice->period_end->toDateString());
        $this->assertSame('1500000.00', $invoice->total_amount);
        $this->assertSame(InvoiceStatus::Unpaid, $invoice->status);
    }

    public function test_prorates_the_transitional_period_when_start_date_is_not_the_billing_day(): void
    {
        $lease = Lease::factory()->create([
            'status' => LeaseStatus::Active,
            'start_date' => '2026-01-15',
            'billing_cycle_day' => 1,
            'monthly_price' => 3000000,
        ]);

        // First month (Jan 15 - Feb 14) already covered by the booking invoice.
        // The next period the service must bill is Feb 15 - Feb 28 (prorated to the 1st).
        $invoice = app(InvoiceService::class)->generateMonthlyInvoice($lease, \Carbon\Carbon::parse('2026-02-15'));

        $this->assertNotNull($invoice);
        $this->assertSame('2026-02-15', $invoice->period_start->toDateString());
        $this->assertSame('2026-02-28', $invoice->period_end->toDateString());
        // 14 days out of 28 days in February = half price.
        $this->assertSame('1500000.00', $invoice->total_amount);
    }

    public function test_generation_is_idempotent_for_the_same_period(): void
    {
        $lease = Lease::factory()->create([
            'status' => LeaseStatus::Active,
            'start_date' => '2026-01-01',
            'billing_cycle_day' => 1,
        ]);

        $first = app(InvoiceService::class)->generateMonthlyInvoice($lease, \Carbon\Carbon::parse('2026-02-01'));
        $second = app(InvoiceService::class)->generateMonthlyInvoice($lease, \Carbon\Carbon::parse('2026-02-01'));

        $this->assertNotNull($first);
        $this->assertNull($second);
        $this->assertSame(1, $lease->invoices()->count());
    }

    public function test_does_not_generate_invoice_before_period_start(): void
    {
        $lease = Lease::factory()->create([
            'status' => LeaseStatus::Active,
            'start_date' => '2026-01-01',
            'billing_cycle_day' => 1,
        ]);

        $invoice = app(InvoiceService::class)->generateMonthlyInvoice($lease, \Carbon\Carbon::parse('2026-01-15'));

        $this->assertNull($invoice);
    }

    public function test_mark_overdue_applies_flat_late_fee_once(): void
    {
        ApplicationSetting::set('invoice_late_fee_type', 'flat', ['type' => 'string', 'group' => 'payment', 'label' => 'x']);
        ApplicationSetting::set('invoice_late_fee_amount', '50000', ['type' => 'number', 'group' => 'payment', 'label' => 'x']);

        $lease = Lease::factory()->create(['status' => LeaseStatus::Active, 'start_date' => '2026-01-01']);
        $invoice = app(InvoiceService::class)->generateMonthlyInvoice($lease, \Carbon\Carbon::parse('2026-02-01'));
        $invoice->forceFill(['due_date' => now()->subDay()])->save();

        $updated = app(InvoiceService::class)->markOverdue($invoice);
        $this->assertSame(InvoiceStatus::Overdue, $updated->status);
        $this->assertSame('50000.00', $updated->late_fee_amount);

        $updatedAgain = app(InvoiceService::class)->markOverdue($updated);
        $this->assertSame('50000.00', $updatedAgain->late_fee_amount);
    }
}
