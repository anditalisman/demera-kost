<?php

namespace App\Domain\Living\Services;

use App\Domain\Living\Models\Invoice;
use App\Domain\Living\Models\Lease;
use App\Domain\Platform\Models\ApplicationSetting;
use App\Enums\InvoiceStatus;
use App\Enums\LeaseStatus;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

/**
 * Generates recurring rent invoices for active leases and applies late
 * fees. The lease's very first month is already paid via the booking flow
 * (BookingLifecycleService::createHold's initial invoice covers exactly
 * one month from start_date), so the first period this service ever bills
 * for a lease starts the day after that — and is prorated whenever it
 * doesn't land on the lease's billing_cycle_day.
 */
class InvoiceService
{
    public function generateMonthlyInvoice(Lease $lease, ?CarbonInterface $asOf = null): ?Invoice
    {
        $asOf = $asOf ? Carbon::instance($asOf) : now();

        if ($lease->status !== LeaseStatus::Active) {
            return null;
        }

        $periodStart = $this->nextPeriodStart($lease);

        if ($periodStart->gt($asOf->copy()->startOfDay())) {
            return null;
        }

        if ($lease->invoices()->whereDate('period_start', $periodStart->toDateString())->exists()) {
            return null;
        }

        $periodEnd = $this->periodEnd($lease, $periodStart);
        $isFullMonth = $periodStart->day === $lease->billing_cycle_day
            && $periodEnd->copy()->addDay()->day === $lease->billing_cycle_day;

        $amount = $isFullMonth
            ? number_format((float) $lease->monthly_price, 2, '.', '')
            : bcdiv(bcmul((string) $lease->monthly_price, (string) ($periodStart->diffInDays($periodEnd) + 1), 4), (string) $periodStart->daysInMonth, 2);

        $invoice = Invoice::create([
            'invoice_number' => $this->generateInvoiceNumber(),
            'lease_id' => $lease->id,
            'tenant_id' => $lease->tenant_id,
            'invoice_type' => 'rent',
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
            'due_date' => $periodStart->toDateString(),
            'subtotal_amount' => $amount,
            'discount_amount' => 0,
            'late_fee_amount' => 0,
            'total_amount' => $amount,
            'paid_amount' => 0,
            'status' => InvoiceStatus::Unpaid,
            'issued_at' => now(),
        ]);

        $invoice->items()->create([
            'label' => $isFullMonth ? 'Sewa Bulanan' : 'Sewa Prorata',
            'item_type' => 'rent',
            'quantity' => 1,
            'unit_price' => $amount,
            'amount' => $amount,
        ]);

        return $invoice;
    }

    public function markOverdue(Invoice $invoice): Invoice
    {
        if (! in_array($invoice->status, [InvoiceStatus::Unpaid, InvoiceStatus::PartiallyPaid], true)) {
            return $invoice;
        }

        if ($invoice->due_date->isFuture() || (float) $invoice->late_fee_amount > 0) {
            return $invoice;
        }

        $type = ApplicationSetting::get('invoice_late_fee_type', 'flat');
        $configuredAmount = (float) ApplicationSetting::get('invoice_late_fee_amount', 0);

        $lateFee = $type === 'percentage'
            ? round(((float) $invoice->total_amount) * $configuredAmount / 100, 2)
            : $configuredAmount;

        if ($lateFee > 0) {
            $invoice->update([
                'late_fee_amount' => $lateFee,
                'total_amount' => bcadd((string) $invoice->total_amount, (string) $lateFee, 2),
            ]);
        }

        $invoice->update(['status' => InvoiceStatus::Overdue]);

        return $invoice->fresh();
    }

    private function nextPeriodStart(Lease $lease): Carbon
    {
        $lastInvoice = $lease->invoices()->whereNotNull('period_end')->orderByDesc('period_end')->first();

        if ($lastInvoice) {
            return Carbon::parse($lastInvoice->period_end)->addDay()->startOfDay();
        }

        return Carbon::parse($lease->start_date)->addMonthNoOverflow()->startOfDay();
    }

    private function periodEnd(Lease $lease, Carbon $periodStart): Carbon
    {
        if ($periodStart->day === $lease->billing_cycle_day) {
            return $periodStart->copy()->addMonthNoOverflow()->subDay();
        }

        $candidate = $periodStart->copy()->day(min($lease->billing_cycle_day, $periodStart->daysInMonth));

        if ($candidate->lte($periodStart)) {
            $candidate = $candidate->addMonthNoOverflow();
        }

        return $candidate->subDay();
    }

    private function generateInvoiceNumber(): string
    {
        do {
            $number = sprintf('INV-%s-%s', now()->format('ymd'), Str::upper(Str::random(5)));
        } while (Invoice::withTrashed()->where('invoice_number', $number)->exists());

        return $number;
    }
}
