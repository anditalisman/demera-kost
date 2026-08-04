<?php

namespace App\Http\Controllers\Admin;

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
        $this->authorize('viewAny', Invoice::class);

        $invoices = Invoice::query()
            ->with(['booking.user', 'tenant.user'])
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Dashboard/Admin/Living/Invoices/Index', [
            'invoices' => $invoices,
            'filters' => $request->only('status'),
        ]);
    }

    public function show(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        $invoice->load(['items', 'payments.verifiedBy', 'booking.user', 'tenant.user']);

        return Inertia::render('Dashboard/Admin/Living/Invoices/Show', [
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
