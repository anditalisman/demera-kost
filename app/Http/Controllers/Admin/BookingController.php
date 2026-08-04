<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Living\Models\Booking;
use App\Domain\Living\Services\BookingLifecycleService;
use App\Enums\BookingStatus;
use App\Enums\InvoiceStatus;
use App\Enums\RoomStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function __construct(private readonly BookingLifecycleService $bookingLifecycleService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Booking::class);

        $bookings = Booking::query()
            ->with(['user', 'room.property'])
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Dashboard/Admin/Living/Bookings/Index', [
            'bookings' => $bookings,
            'filters' => $request->only('status'),
        ]);
    }

    public function show(Booking $booking): Response
    {
        $this->authorize('view', $booking);

        $booking->load(['user', 'room.property', 'guests', 'documents', 'invoices.items', 'invoices.payments']);

        return Inertia::render('Dashboard/Admin/Living/Bookings/Show', [
            'booking' => $booking,
        ]);
    }

    /**
     * Manual fallback for confirming a booking outside the customer-driven
     * payment-proof flow (e.g. cash collected in person). Skips straight to
     * BookingLifecycleService::confirm() after marking the invoice paid.
     */
    public function approve(Booking $booking): RedirectResponse
    {
        $this->authorize('manage', Booking::class);

        if ($booking->status !== BookingStatus::AwaitingPayment) {
            return back()->withErrors(['booking' => 'Booking ini tidak dalam status menunggu pembayaran.']);
        }

        $invoice = $booking->invoices()->where('status', '!=', InvoiceStatus::Cancelled)->latest('id')->first();

        if ($invoice) {
            $invoice->update(['paid_amount' => $invoice->total_amount, 'status' => InvoiceStatus::Paid]);
        }

        $this->bookingLifecycleService->confirm($booking, request()->user());

        return back()->with('success', 'Booking berhasil disetujui secara manual.');
    }

    public function reject(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('manage', Booking::class);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        if (! in_array($booking->status, [BookingStatus::Pending, BookingStatus::AwaitingPayment], true)) {
            return back()->withErrors(['booking' => 'Booking ini tidak dapat ditolak pada status saat ini.']);
        }

        $booking->update([
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => now(),
            'cancellation_reason' => $validated['reason'],
        ]);

        $room = $booking->room;

        if ($room && $room->status === RoomStatus::Held) {
            $room->recordStatusChange(RoomStatus::Available, "Booking {$booking->booking_code} ditolak admin", $request->user()->id, $booking->id);
        }

        $booking->invoices()->where('status', InvoiceStatus::Unpaid)->update(['status' => InvoiceStatus::Cancelled]);

        return back()->with('success', 'Booking berhasil ditolak.');
    }
}
