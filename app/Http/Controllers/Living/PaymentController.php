<?php

namespace App\Http\Controllers\Living;

use App\Domain\Living\Models\Invoice;
use App\Domain\Living\Models\Payment;
use App\Domain\Living\Services\PaymentVerificationService;
use App\Domain\Platform\Models\ApplicationSetting;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentVerificationService $paymentVerificationService) {}

    public function create(Invoice $invoice): Response
    {
        $this->authorize('pay', $invoice);

        return Inertia::render('Living/Payments/Create', [
            'invoice' => $invoice->load('items'),
            'bank' => [
                'name' => ApplicationSetting::get('payment_bank_name'),
                'account_number' => ApplicationSetting::get('payment_bank_account_number'),
                'account_holder' => ApplicationSetting::get('payment_bank_account_holder'),
            ],
            'qrisImageUrl' => ApplicationSetting::get('payment_qris_image')
                ? Storage::disk('public_media')->url(ApplicationSetting::get('payment_qris_image'))
                : null,
        ]);
    }

    public function store(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('pay', $invoice);

        $validated = $request->validate([
            'method' => ['required', 'in:manual_transfer,qris'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ]);

        try {
            $this->paymentVerificationService->submitProof(
                $invoice,
                PaymentMethod::from($validated['method']),
                $request->file('proof'),
            );
        } catch (\RuntimeException $e) {
            return back()->withErrors(['proof' => $e->getMessage()]);
        }

        return back()->with('success', 'Bukti pembayaran berhasil diunggah. Admin akan segera memverifikasi.');
    }

    public function receipt(Payment $payment): HttpResponse
    {
        $this->authorize('view', $payment);

        if ($payment->status !== PaymentStatus::Paid) {
            abort(404);
        }

        $payment->load(['invoice', 'verifiedBy']);

        $pdf = Pdf::loadView('pdf.receipt', ['payment' => $payment]);

        return $pdf->download("kuitansi-{$payment->payment_code}.pdf");
    }
}
