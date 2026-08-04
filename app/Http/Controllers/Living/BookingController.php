<?php

namespace App\Http\Controllers\Living;

use App\Domain\Living\Exceptions\RoomNotAvailableException;
use App\Domain\Living\Models\Booking;
use App\Domain\Living\Models\Room;
use App\Domain\Living\Services\BookingLifecycleService;
use App\Domain\Platform\Models\ApplicationSetting;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function __construct(private readonly BookingLifecycleService $bookingLifecycleService) {}

    public function create(string $slug): Response
    {
        $room = Room::query()
            ->publiclyVisible()
            ->available()
            ->with(['primaryImage', 'roomType', 'property'])
            ->where('slug', $slug)
            ->firstOrFail();

        $user = request()->user();

        return Inertia::render('Living/Bookings/Create', [
            'room' => $room,
            'holdHours' => (int) ApplicationSetting::get('booking_hold_hours', 24),
            'adminFee' => (float) ApplicationSetting::get('booking_admin_fee', 0),
            'defaultGuest' => [
                'full_name' => $user->name,
                'phone' => $user->whatsapp_number,
                'email' => $user->email,
            ],
            'hasIdentityDocumentOnFile' => filled($user->profile?->identity_document_path),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:24'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'identity_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'guests' => ['required', 'array', 'min:1'],
            'guests.*.full_name' => ['required', 'string', 'max:150'],
            'guests.*.identity_number' => ['nullable', 'string', 'max:32'],
            'guests.*.phone' => ['nullable', 'string', 'max:20'],
            'guests.*.email' => ['nullable', 'email', 'max:255'],
            'guests.*.relationship' => ['nullable', 'string', 'max:50'],
            'terms_accepted' => ['accepted'],
        ]);

        $user = $request->user();
        $room = Room::query()->findOrFail($validated['room_id']);

        if (empty($user->profile?->identity_document_path) && empty($validated['identity_document'])) {
            throw ValidationException::withMessages([
                'identity_document' => 'Unggah dokumen identitas (KTP) untuk melanjutkan pemesanan.',
            ]);
        }

        try {
            $booking = $this->bookingLifecycleService->createHold($user, $room, [
                'start_date' => $validated['start_date'],
                'duration_months' => $validated['duration_months'],
                'notes' => $validated['notes'] ?? null,
                'guests' => $validated['guests'],
                'identity_document' => $request->file('identity_document'),
            ]);
        } catch (RoomNotAvailableException $e) {
            return back()->withErrors(['room' => $e->getMessage()])->withInput();
        }

        return redirect()->route('bookings.show', $booking->booking_code)
            ->with('success', 'Pemesanan berhasil dibuat. Silakan selesaikan pembayaran sebelum batas waktu.');
    }

    public function show(string $code): Response
    {
        $booking = Booking::query()
            ->with(['room.primaryImage', 'room.property', 'guests', 'invoices.items', 'invoices.payments'])
            ->where('booking_code', $code)
            ->firstOrFail();

        $this->authorize('view', $booking);

        return Inertia::render('Living/Bookings/Show', [
            'booking' => $booking,
        ]);
    }
}
