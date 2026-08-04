<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Living\Models\Booking;
use App\Domain\Living\Models\Invoice;
use App\Domain\Living\Models\Lease;
use App\Domain\Living\Models\Payment;
use App\Domain\Living\Models\Room;
use App\Domain\Living\Models\Tenant;
use App\Domain\Platform\Models\NotificationLog;
use App\Enums\BookingStatus;
use App\Enums\InvoiceStatus;
use App\Enums\NotificationDeliveryStatus;
use App\Enums\PaymentStatus;
use App\Enums\RoomStatus;
use App\Enums\TenantStatus;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        abort_unless(request()->user()->hasRole('admin'), 403);

        $roomCounts = Room::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $totalRooms = (int) $roomCounts->sum();
        $occupied = (int) ($roomCounts[RoomStatus::Occupied->value] ?? 0);

        return Inertia::render('Dashboard/Admin/Dashboard', [
            'stats' => [
                'rooms' => [
                    'total' => $totalRooms,
                    'available' => (int) ($roomCounts[RoomStatus::Available->value] ?? 0),
                    'occupied' => $occupied,
                    'held' => (int) ($roomCounts[RoomStatus::Held->value] ?? 0),
                    'maintenance' => (int) ($roomCounts[RoomStatus::Maintenance->value] ?? 0),
                    'occupancyRate' => $totalRooms > 0 ? round($occupied / $totalRooms * 100, 1) : 0,
                ],
                'tenants' => [
                    'prospective' => Tenant::query()->where('status', TenantStatus::Prospective)->count(),
                    'active' => Tenant::query()->where('status', TenantStatus::Active)->count(),
                ],
                'bookingsAwaitingPayment' => Booking::query()->where('status', BookingStatus::AwaitingPayment)->count(),
                'paymentsPendingVerification' => Payment::query()->where('status', 'pending')->count(),
                'invoicesDue' => Invoice::query()->whereIn('status', [InvoiceStatus::Unpaid, InvoiceStatus::PartiallyPaid])->count(),
                'invoicesOverdue' => Invoice::query()->where('status', InvoiceStatus::Overdue)->count(),
                'revenueThisMonth' => (float) Payment::query()
                    ->where('status', PaymentStatus::Paid)
                    ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->sum('amount'),
            ],
            'revenueTrend' => $this->revenueTrend(),
            'leasesEndingSoon' => Lease::query()
                ->where('status', 'active')
                ->whereDate('end_date', '<=', now()->addDays(30)->toDateString())
                ->with(['tenant.user', 'room'])
                ->orderBy('end_date')
                ->limit(10)
                ->get()
                ->map(fn (Lease $lease) => [
                    'id' => $lease->id,
                    'lease_number' => $lease->lease_number,
                    'tenant_name' => $lease->tenant->user->name,
                    'room_label' => $lease->room ? ($lease->room->name ?? "Kamar {$lease->room->room_number}") : '-',
                    'end_date' => $lease->end_date->toDateString(),
                ]),
            'failedNotifications' => NotificationLog::query()
                ->where('status', NotificationDeliveryStatus::Failed)
                ->with('user')
                ->latest('created_at')
                ->limit(10)
                ->get()
                ->map(fn (NotificationLog $log) => [
                    'id' => $log->id,
                    'channel' => $log->channel->label(),
                    'recipient' => $log->recipient,
                    'error_message' => $log->error_message,
                    'attempts' => $log->attempts,
                ]),
        ]);
    }

    private function revenueTrend(): array
    {
        $trend = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonthsNoOverflow($i);
            $total = (float) Payment::query()
                ->where('status', PaymentStatus::Paid)
                ->whereBetween('paid_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->sum('amount');

            $trend[] = ['label' => $month->translatedFormat('M Y'), 'total' => $total];
        }

        return $trend;
    }
}
