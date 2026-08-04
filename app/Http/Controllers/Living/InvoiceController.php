<?php

namespace App\Http\Controllers\Living;

use App\Domain\Living\Models\Invoice;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $invoices = Invoice::query()
            ->where(function ($q) use ($user) {
                $q->whereHas('booking', fn ($bq) => $bq->where('user_id', $user->id))
                    ->orWhereHas('tenant', fn ($tq) => $tq->where('user_id', $user->id));
            })
            ->with(['booking.room', 'tenant.room'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return Inertia::render('Living/Invoices/Index', [
            'invoices' => $invoices,
        ]);
    }

    public function show(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        $invoice->load(['items', 'payments', 'booking.room', 'tenant.room']);

        return Inertia::render('Living/Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    public function downloadPdf(Invoice $invoice): HttpResponse
    {
        $this->authorize('view', $invoice);

        $invoice->load(['items', 'booking.user', 'tenant.user']);
        $billedTo = $invoice->booking?->user->name ?? $invoice->tenant?->user->name ?? '-';

        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice, 'billedTo' => $billedTo]);

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }
}
