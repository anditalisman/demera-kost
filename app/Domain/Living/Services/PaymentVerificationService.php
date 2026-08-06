<?php

namespace App\Domain\Living\Services;

use App\Domain\Living\Models\Invoice;
use App\Domain\Living\Models\Payment;
use App\Domain\Platform\Services\NotificationDispatcher;
use App\Domain\Platform\Services\PrivateDocumentUploadService;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Manual-payment verification: customers upload proof of a bank transfer or
 * QRIS scan, an admin reviews the same way for both methods, and — once an
 * invoice is fully paid — a related booking is converted into a lease. No
 * payment gateway/webhook is involved by design (see docs/ARSITEKTUR.md).
 */
class PaymentVerificationService
{
    public function __construct(
        private readonly PrivateDocumentUploadService $documentUploadService,
        private readonly BookingLifecycleService $bookingLifecycleService,
        private readonly NotificationDispatcher $notificationDispatcher,
    ) {}

    /**
     * A pending payment (still awaiting admin review) is replaced in place
     * rather than duplicated — re-uploading proof updates the same record's
     * file instead of creating a second one for the admin to review. Once a
     * payment is rejected (failed) or verified (paid), it's no longer
     * "pending" and the next submission starts a genuinely new one, so a
     * rejected attempt always requires a fresh upload rather than silently
     * reusing the rejected file.
     */
    public function submitProof(Invoice $invoice, PaymentMethod $method, UploadedFile $proof): Payment
    {
        if (in_array($invoice->status, [InvoiceStatus::Paid, InvoiceStatus::Cancelled, InvoiceStatus::Refunded], true)) {
            throw new \RuntimeException('Invoice ini sudah tidak dapat menerima pembayaran.');
        }

        $pending = $invoice->payments()->where('status', PaymentStatus::Pending)->first();

        $uploaded = $this->documentUploadService->upload($proof, 'payment-proofs');

        if ($pending) {
            $previousPath = $pending->proof_file_path;

            $pending->update([
                'method' => $method,
                'amount' => $invoice->remainingAmount(),
                'proof_file_path' => $uploaded['path'],
            ]);

            if ($previousPath && $previousPath !== $uploaded['path']) {
                $this->documentUploadService->delete($previousPath);
            }

            return $pending->fresh();
        }

        return $invoice->payments()->create([
            'payment_code' => $this->generateCode(),
            'method' => $method,
            'amount' => $invoice->remainingAmount(),
            'status' => PaymentStatus::Pending,
            'proof_file_path' => $uploaded['path'],
            'idempotency_key' => (string) Str::uuid(),
        ]);
    }

    public function verify(Payment $payment, User $admin): Invoice
    {
        return DB::transaction(function () use ($payment, $admin) {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            $lockedPayment->update([
                'status' => PaymentStatus::Paid,
                'paid_at' => now(),
                'verified_by' => $admin->id,
                'verified_at' => now(),
            ]);

            $invoice = Invoice::query()->lockForUpdate()->findOrFail($lockedPayment->invoice_id);
            $newPaidAmount = bcadd((string) $invoice->paid_amount, (string) $lockedPayment->amount, 2);
            $isFullyPaid = bccomp($newPaidAmount, (string) $invoice->total_amount, 2) >= 0;

            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'status' => $isFullyPaid ? InvoiceStatus::Paid : InvoiceStatus::PartiallyPaid,
            ]);

            if ($isFullyPaid && $invoice->booking_id) {
                $this->bookingLifecycleService->confirm($invoice->booking, $admin);
            }

            $user = $invoice->booking?->user ?? $invoice->tenant?->user;

            if ($user) {
                $this->notificationDispatcher->dispatch($user, 'payment_verified', [
                    'payment_code' => $lockedPayment->payment_code,
                    'amount' => number_format((float) $lockedPayment->amount, 0, ',', '.'),
                ]);
            }

            return $invoice->fresh(['items', 'payments']);
        });
    }

    public function reject(Payment $payment, User $admin, string $reason): Payment
    {
        $payment->update([
            'status' => PaymentStatus::Failed,
            'verified_by' => $admin->id,
            'verified_at' => now(),
            'notes' => $reason,
        ]);

        return $payment->fresh();
    }

    private function generateCode(): string
    {
        do {
            $code = sprintf('PAY-%s-%s', now()->format('ymd'), Str::upper(Str::random(5)));
        } while (Payment::withTrashed()->where('payment_code', $code)->exists());

        return $code;
    }
}
