<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Living\Exceptions\RoomNotAvailableException;
use App\Domain\Living\Models\Lease;
use App\Domain\Living\Models\Room;
use App\Domain\Living\Services\LeaseManagementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaseController extends Controller
{
    public function __construct(private readonly LeaseManagementService $leaseManagementService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Lease::class);

        $leases = Lease::query()
            ->with(['tenant.user', 'room.property'])
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->when($request->boolean('ending_soon'), fn ($q) => $q->where('status', 'active')->whereDate('end_date', '<=', now()->addDays(30)))
            ->orderBy('end_date')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Dashboard/Admin/Living/Leases/Index', [
            'leases' => $leases,
            'filters' => $request->only(['status', 'ending_soon']),
        ]);
    }

    public function show(Lease $lease): Response
    {
        $this->authorize('view', $lease);

        $lease->load(['tenant.user', 'room.property', 'extensions', 'deposits', 'invoices']);

        return Inertia::render('Dashboard/Admin/Living/Leases/Show', [
            'lease' => $lease,
            'availableRooms' => Room::query()->available()->with('property')->orderBy('room_number')->get(),
        ]);
    }

    public function extend(Request $request, Lease $lease): RedirectResponse
    {
        $this->authorize('manage', Lease::class);

        $validated = $request->validate([
            'additional_months' => ['required', 'integer', 'min:1', 'max:24'],
            'new_monthly_price' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->leaseManagementService->extend(
            $lease,
            $validated['additional_months'],
            $validated['new_monthly_price'] ?? null,
            $request->user(),
            $validated['notes'] ?? null,
        );

        return back()->with('success', 'Kontrak berhasil diperpanjang.');
    }

    public function transferRoom(Request $request, Lease $lease): RedirectResponse
    {
        $this->authorize('manage', Lease::class);

        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $newLease = $this->leaseManagementService->transferRoom(
                $lease,
                Room::query()->findOrFail($validated['room_id']),
                $request->user(),
                $validated['reason'] ?? null,
            );
        } catch (RoomNotAvailableException $e) {
            return back()->withErrors(['room_id' => $e->getMessage()]);
        }

        return redirect()->route('admin.leases.show', $newLease)->with('success', 'Penyewa berhasil dipindahkan ke kamar baru.');
    }

    public function terminate(Request $request, Lease $lease): RedirectResponse
    {
        $this->authorize('manage', Lease::class);

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
            'returned_amount' => ['required', 'numeric', 'min:0'],
            'deduction_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->leaseManagementService->terminate(
            $lease,
            $request->user(),
            $validated['reason'] ?? null,
            (string) $validated['returned_amount'],
            $validated['deduction_notes'] ?? null,
        );

        return back()->with('success', 'Sewa berhasil diakhiri dan deposit diproses.');
    }
}
