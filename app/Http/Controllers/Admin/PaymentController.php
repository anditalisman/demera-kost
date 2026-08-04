<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Living\Models\Payment;
use App\Domain\Living\Services\PaymentVerificationService;
use App\Domain\Platform\Services\PrivateDocumentUrlService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentVerificationService $paymentVerificationService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Payment::class);

        $status = $request->string('status')->toString() ?: 'pending';

        $payments = Payment::query()
            ->with(['invoice.booking.user', 'invoice.tenant.user'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Dashboard/Admin/Living/Payments/Index', [
            'payments' => $payments,
            'filters' => ['status' => $status],
        ]);
    }

    public function proof(Payment $payment, PrivateDocumentUrlService $urlService): RedirectResponse
    {
        $this->authorize('view', $payment);

        abort_if(! $payment->proof_file_path, 404);

        return redirect()->away($urlService->temporaryUrl($payment->proof_file_path));
    }

    public function verify(Payment $payment): RedirectResponse
    {
        $this->authorize('verify', Payment::class);

        $this->paymentVerificationService->verify($payment, request()->user());

        return back()->with('success', 'Pembayaran berhasil diverifikasi.');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorize('verify', Payment::class);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $this->paymentVerificationService->reject($payment, $request->user(), $validated['reason']);

        return back()->with('success', 'Pembayaran ditolak. Pelanggan dapat mengunggah ulang bukti pembayaran.');
    }
}
