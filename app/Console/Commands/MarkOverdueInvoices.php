<?php

namespace App\Console\Commands;

use App\Domain\Living\Models\Invoice;
use App\Domain\Living\Services\InvoiceService;
use App\Enums\InvoiceStatus;
use Illuminate\Console\Command;

class MarkOverdueInvoices extends Command
{
    protected $signature = 'invoices:mark-overdue';

    protected $description = 'Mark unpaid invoices past their due date as overdue and apply the configured late fee once.';

    public function handle(InvoiceService $invoiceService): int
    {
        $invoices = Invoice::query()
            ->whereIn('status', [InvoiceStatus::Unpaid, InvoiceStatus::PartiallyPaid])
            ->where('due_date', '<', now()->toDateString())
            ->get();

        foreach ($invoices as $invoice) {
            $invoiceService->markOverdue($invoice);
        }

        $this->info("{$invoices->count()} invoice diperiksa untuk status terlambat.");

        return self::SUCCESS;
    }
}
