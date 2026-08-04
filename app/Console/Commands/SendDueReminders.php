<?php

namespace App\Console\Commands;

use App\Domain\Living\Models\Invoice;
use App\Domain\Living\Models\Lease;
use App\Domain\Platform\Models\ApplicationSetting;
use App\Domain\Platform\Models\Notification;
use App\Domain\Platform\Services\NotificationDispatcher;
use App\Enums\InvoiceStatus;
use App\Enums\LeaseStatus;
use Illuminate\Console\Command;

class SendDueReminders extends Command
{
    protected $signature = 'notifications:send-due-reminders';

    protected $description = 'Send invoice due/overdue reminders and lease-ending-soon notices for today.';

    /**
     * Maps a signed day-offset from an invoice's due_date (negative = before
     * due, positive = overdue) to the NotificationTemplate code seeded for
     * that offset. Configurable via the "invoice_reminder_offsets" setting.
     */
    private const OFFSET_TEMPLATES = [
        -7 => 'invoice_due_reminder_h7',
        -3 => 'invoice_due_reminder_h3',
        -1 => 'invoice_due_reminder_h1',
        0 => 'invoice_due_reminder_h0',
        1 => 'invoice_overdue_h1',
        3 => 'invoice_overdue_h3',
        7 => 'invoice_overdue_h7',
    ];

    public function handle(NotificationDispatcher $dispatcher): int
    {
        $offsets = array_map('intval', explode(',', (string) ApplicationSetting::get('invoice_reminder_offsets', '-7,-3,-1,0,1,3,7')));

        $invoiceReminders = $this->sendInvoiceReminders($dispatcher, $offsets);
        $leaseReminders = $this->sendLeaseEndingSoonReminders($dispatcher);

        $this->info("{$invoiceReminders} pengingat tagihan dan {$leaseReminders} pengingat kontrak berakhir terkirim.");

        return self::SUCCESS;
    }

    private function sendInvoiceReminders(NotificationDispatcher $dispatcher, array $offsets): int
    {
        $sent = 0;

        $invoices = Invoice::query()
            ->whereIn('status', [InvoiceStatus::Unpaid, InvoiceStatus::PartiallyPaid, InvoiceStatus::Overdue])
            ->whereNotNull('due_date')
            ->with(['booking.user', 'tenant.user'])
            ->get();

        foreach ($invoices as $invoice) {
            $offset = (int) $invoice->due_date->copy()->startOfDay()->diffInDays(now()->startOfDay(), false);

            if (! in_array($offset, $offsets, true) || ! isset(self::OFFSET_TEMPLATES[$offset])) {
                continue;
            }

            $user = $invoice->booking?->user ?? $invoice->tenant?->user;

            if (! $user) {
                continue;
            }

            $templateCode = self::OFFSET_TEMPLATES[$offset];

            $alreadySent = Notification::query()
                ->where('user_id', $user->id)
                ->where('type', $templateCode)
                ->where('data->invoice_id', $invoice->id)
                ->whereDate('created_at', now()->toDateString())
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $dispatcher->dispatch($user, $templateCode, [
                'name' => $user->name,
                'invoice_number' => $invoice->invoice_number,
                'amount' => number_format((float) $invoice->total_amount, 0, ',', '.'),
                'due_date' => $invoice->due_date->format('d M Y'),
                'invoice_id' => $invoice->id,
            ]);

            $sent++;
        }

        return $sent;
    }

    private function sendLeaseEndingSoonReminders(NotificationDispatcher $dispatcher): int
    {
        $sent = 0;

        $leases = Lease::query()
            ->where('status', LeaseStatus::Active)
            ->whereDate('end_date', '<=', now()->addDays(30)->toDateString())
            ->whereDate('end_date', '>=', now()->toDateString())
            ->with('tenant.user')
            ->get();

        foreach ($leases as $lease) {
            $user = $lease->tenant?->user;

            if (! $user) {
                continue;
            }

            $alreadySentThisWeek = Notification::query()
                ->where('user_id', $user->id)
                ->where('type', 'lease_expiring_soon')
                ->where('data->lease_id', $lease->id)
                ->where('created_at', '>=', now()->subDays(7))
                ->exists();

            if ($alreadySentThisWeek) {
                continue;
            }

            $dispatcher->dispatch($user, 'lease_expiring_soon', [
                'name' => $user->name,
                'lease_number' => $lease->lease_number,
                'end_date' => $lease->end_date->format('d M Y'),
                'lease_id' => $lease->id,
            ]);

            $sent++;
        }

        return $sent;
    }
}
