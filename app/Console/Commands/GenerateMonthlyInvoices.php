<?php

namespace App\Console\Commands;

use App\Domain\Living\Models\Lease;
use App\Domain\Living\Services\InvoiceService;
use App\Enums\LeaseStatus;
use Illuminate\Console\Command;

class GenerateMonthlyInvoices extends Command
{
    protected $signature = 'invoices:generate-monthly';

    protected $description = 'Generate the next due rent invoice for every active lease (idempotent).';

    public function handle(InvoiceService $invoiceService): int
    {
        $leases = Lease::query()->where('status', LeaseStatus::Active)->get();
        $generated = 0;

        foreach ($leases as $lease) {
            if ($invoiceService->generateMonthlyInvoice($lease)) {
                $generated++;
            }
        }

        $this->info("{$generated} invoice bulanan baru dibuat dari {$leases->count()} kontrak aktif.");

        return self::SUCCESS;
    }
}
