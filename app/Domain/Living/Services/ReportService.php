<?php

namespace App\Domain\Living\Services;

use App\Domain\Living\Models\Booking;
use App\Domain\Living\Models\Deposit;
use App\Domain\Living\Models\Invoice;
use App\Domain\Living\Models\Lease;
use App\Domain\Living\Models\Payment;
use App\Domain\Living\Models\Room;
use App\Domain\Living\Models\RoomStatusHistory;
use App\Domain\Living\Models\Tenant;
use App\Domain\Platform\Models\NotificationLog;
use App\Enums\BookingStatus;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Enums\RoomStatus;
use App\Enums\TenantStatus;
use Illuminate\Support\Carbon;

/**
 * Every admin report as a single {headings, rows} shape so the controller
 * can render, and export to PDF/Excel/CSV, any of them identically.
 */
class ReportService
{
    public const TYPES = [
        'occupancy' => 'Okupansi Kamar',
        'active_tenants' => 'Penyewa Aktif',
        'leases_ending_soon' => 'Kontrak Akan Berakhir',
        'invoices' => 'Tagihan',
        'revenue_by_period' => 'Pendapatan per Periode',
        'payments_by_method' => 'Pembayaran per Metode',
        'deposits' => 'Deposit',
        'cancellations' => 'Pembatalan & Kedaluwarsa',
        'room_status_history' => 'Riwayat Perubahan Kamar',
        'notification_performance' => 'Performa Notifikasi',
    ];

    /**
     * @return array{headings: array<int, string>, rows: array<int, array<int, string>>}
     */
    public function generate(string $type, array $filters = []): array
    {
        return match ($type) {
            'occupancy' => $this->occupancy(),
            'active_tenants' => $this->activeTenants(),
            'leases_ending_soon' => $this->leasesEndingSoon((int) ($filters['days'] ?? 30)),
            'invoices' => $this->invoices($filters),
            'revenue_by_period' => $this->revenueByPeriod($filters),
            'payments_by_method' => $this->paymentsByMethod($filters),
            'deposits' => $this->deposits(),
            'cancellations' => $this->cancellations($filters),
            'room_status_history' => $this->roomStatusHistory($filters),
            'notification_performance' => $this->notificationPerformance(),
            default => ['headings' => [], 'rows' => []],
        };
    }

    private function occupancy(): array
    {
        $counts = Room::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $total = (int) $counts->sum();
        $occupied = (int) ($counts[RoomStatus::Occupied->value] ?? 0);

        $rows = [];
        foreach (RoomStatus::cases() as $status) {
            $count = (int) ($counts[$status->value] ?? 0);
            $rows[] = [$status->label(), (string) $count, $total > 0 ? number_format($count / $total * 100, 1).'%' : '0%'];
        }
        $rows[] = ['Total', (string) $total, $total > 0 ? number_format($occupied / $total * 100, 1).'% terisi' : '-'];

        return ['headings' => ['Status', 'Jumlah Kamar', 'Persentase'], 'rows' => $rows];
    }

    private function activeTenants(): array
    {
        $tenants = Tenant::query()
            ->where('status', TenantStatus::Active)
            ->with(['user', 'room.property'])
            ->orderBy('joined_at')
            ->get();

        $rows = $tenants->map(fn (Tenant $t) => [
            $t->user->name,
            $t->room ? ($t->room->name ?? "Kamar {$t->room->room_number}") : '-',
            $t->room?->property->name ?? '-',
            optional($t->joined_at)->toDateString() ?? '-',
        ])->all();

        return ['headings' => ['Nama', 'Kamar', 'Properti', 'Bergabung'], 'rows' => $rows];
    }

    private function leasesEndingSoon(int $days): array
    {
        $leases = Lease::query()
            ->where('status', 'active')
            ->whereDate('end_date', '<=', now()->addDays($days)->toDateString())
            ->with(['tenant.user', 'room'])
            ->orderBy('end_date')
            ->get();

        $rows = $leases->map(fn (Lease $l) => [
            $l->lease_number,
            $l->tenant->user->name,
            $l->room ? ($l->room->name ?? "Kamar {$l->room->room_number}") : '-',
            $l->end_date->toDateString(),
            (string) now()->diffInDays($l->end_date, false),
        ])->all();

        return ['headings' => ['Nomor Kontrak', 'Penyewa', 'Kamar', 'Berakhir', 'Sisa Hari'], 'rows' => $rows];
    }

    private function invoices(array $filters): array
    {
        $invoices = Invoice::query()
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('due_date', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('due_date', '<=', $to))
            ->with(['booking.user', 'tenant.user'])
            ->orderByDesc('due_date')
            ->get();

        $rows = $invoices->map(fn (Invoice $i) => [
            $i->invoice_number,
            $i->booking?->user->name ?? $i->tenant?->user->name ?? '-',
            $i->due_date->toDateString(),
            number_format((float) $i->total_amount, 0, ',', '.'),
            number_format((float) $i->paid_amount, 0, ',', '.'),
            $i->status->label(),
        ])->all();

        return ['headings' => ['Nomor Invoice', 'Pelanggan', 'Jatuh Tempo', 'Total', 'Dibayar', 'Status'], 'rows' => $rows];
    }

    private function revenueByPeriod(array $filters): array
    {
        $from = Carbon::parse($filters['from'] ?? now()->subMonths(5)->startOfMonth()->toDateString());
        $to = Carbon::parse($filters['to'] ?? now()->endOfMonth()->toDateString());

        $payments = Payment::query()
            ->where('status', PaymentStatus::Paid)
            ->whereBetween('paid_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->get()
            ->groupBy(fn (Payment $p) => $p->paid_at->format('Y-m'));

        $rows = [];
        $cursor = $from->copy()->startOfMonth();

        while ($cursor->lte($to)) {
            $key = $cursor->format('Y-m');
            $monthPayments = $payments->get($key, collect());
            $rows[] = [
                $cursor->translatedFormat('F Y'),
                (string) $monthPayments->count(),
                number_format((float) $monthPayments->sum('amount'), 0, ',', '.'),
            ];
            $cursor->addMonthNoOverflow();
        }

        return ['headings' => ['Bulan', 'Jumlah Transaksi', 'Total Pendapatan (Rp)'], 'rows' => $rows];
    }

    private function paymentsByMethod(array $filters): array
    {
        $payments = Payment::query()
            ->where('status', PaymentStatus::Paid)
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('paid_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('paid_at', '<=', $to))
            ->get()
            ->groupBy(fn (Payment $p) => $p->method->value);

        $rows = $payments->map(fn ($group, $method) => [
            \App\Enums\PaymentMethod::from($method)->label(),
            (string) $group->count(),
            number_format((float) $group->sum('amount'), 0, ',', '.'),
        ])->values()->all();

        return ['headings' => ['Metode', 'Jumlah Transaksi', 'Total (Rp)'], 'rows' => $rows];
    }

    private function deposits(): array
    {
        $deposits = Deposit::query()->with(['tenant.user'])->orderByDesc('created_at')->get();

        $rows = $deposits->map(fn (Deposit $d) => [
            $d->tenant->user->name,
            number_format((float) $d->amount, 0, ',', '.'),
            $d->status->label(),
            number_format((float) $d->returned_amount, 0, ',', '.'),
            optional($d->returned_at)->toDateString() ?? '-',
        ])->all();

        return ['headings' => ['Penyewa', 'Jumlah Deposit', 'Status', 'Dikembalikan', 'Tanggal Kembali'], 'rows' => $rows];
    }

    private function cancellations(array $filters): array
    {
        $bookings = Booking::query()
            ->whereIn('status', [BookingStatus::Cancelled, BookingStatus::Expired])
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->with(['user', 'room'])
            ->orderByDesc('cancelled_at')
            ->get();

        $rows = $bookings->map(fn (Booking $b) => [
            $b->booking_code,
            $b->user->name,
            $b->room ? ($b->room->name ?? "Kamar {$b->room->room_number}") : '-',
            $b->status->label(),
            $b->cancellation_reason ?? '-',
            optional($b->cancelled_at)->toDateString() ?? '-',
        ])->all();

        return ['headings' => ['Kode Booking', 'Pelanggan', 'Kamar', 'Status', 'Alasan', 'Tanggal'], 'rows' => $rows];
    }

    private function roomStatusHistory(array $filters): array
    {
        $histories = RoomStatusHistory::query()
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->with(['room', 'changedBy'])
            ->orderByDesc('created_at')
            ->limit(500)
            ->get();

        $rows = $histories->map(fn (RoomStatusHistory $h) => [
            $h->room ? ($h->room->name ?? "Kamar {$h->room->room_number}") : '-',
            $h->from_status ?? '-',
            $h->to_status,
            $h->reason ?? '-',
            $h->changedBy?->name ?? 'Sistem',
            $h->created_at->toDateTimeString(),
        ])->all();

        return ['headings' => ['Kamar', 'Dari', 'Ke', 'Alasan', 'Diubah Oleh', 'Waktu'], 'rows' => $rows];
    }

    private function notificationPerformance(): array
    {
        $logs = NotificationLog::query()
            ->selectRaw('channel, status, count(*) as total')
            ->groupBy('channel', 'status')
            ->get();

        $rows = $logs->map(fn ($log) => [
            $log->channel->label(),
            $log->status->label(),
            (string) $log->total,
        ])->all();

        return ['headings' => ['Kanal', 'Status', 'Jumlah'], 'rows' => $rows];
    }
}
