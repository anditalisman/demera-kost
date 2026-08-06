<?php

namespace Tests\Feature\Living;

use App\Domain\Living\Models\Room;
use App\Domain\Living\Services\BookingLifecycleService;
use App\Domain\Living\Services\PaymentVerificationService;
use App\Enums\BookingStatus;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\RoomStatus;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('customer');

        return $user;
    }

    private function admin(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    private function bookingData(): array
    {
        return [
            'start_date' => now()->addDay()->toDateString(),
            'duration_months' => 3,
            'guests' => [['full_name' => 'Budi Santoso']],
        ];
    }

    public function test_customer_can_submit_payment_proof(): void
    {
        Storage::fake('private_documents');
        $customer = $this->customer();
        $room = Room::factory()->create(['status' => RoomStatus::Available]);
        $booking = app(BookingLifecycleService::class)->createHold($customer, $room, $this->bookingData());
        $invoice = $booking->invoices->first();

        $payment = app(PaymentVerificationService::class)->submitProof(
            $invoice,
            PaymentMethod::ManualTransfer,
            UploadedFile::fake()->create('bukti.jpg', 200, 'image/jpeg'),
        );

        $this->assertSame(PaymentStatus::Pending, $payment->status);
        $this->assertSame($invoice->total_amount, $payment->amount);
        $this->assertNotEmpty($payment->payment_code);
    }

    public function test_verifying_a_payment_marks_invoice_paid_and_converts_booking_to_lease(): void
    {
        Storage::fake('private_documents');
        $customer = $this->customer();
        $admin = $this->admin();
        $room = Room::factory()->create(['status' => RoomStatus::Available]);
        $booking = app(BookingLifecycleService::class)->createHold($customer, $room, $this->bookingData());
        $invoice = $booking->invoices->first();

        $payment = app(PaymentVerificationService::class)->submitProof(
            $invoice,
            PaymentMethod::Qris,
            UploadedFile::fake()->create('bukti.jpg', 200, 'image/jpeg'),
        );

        $updatedInvoice = app(PaymentVerificationService::class)->verify($payment, $admin);

        $this->assertSame(InvoiceStatus::Paid, $updatedInvoice->status);
        $this->assertSame(PaymentStatus::Paid, $payment->fresh()->status);
        $this->assertSame(BookingStatus::ConvertedToLease, $booking->fresh()->status);
        $this->assertSame(RoomStatus::Occupied, $room->fresh()->status);
    }

    public function test_resubmitting_proof_while_pending_replaces_it_instead_of_duplicating(): void
    {
        Storage::fake('private_documents');
        $customer = $this->customer();
        $room = Room::factory()->create(['status' => RoomStatus::Available]);
        $booking = app(BookingLifecycleService::class)->createHold($customer, $room, $this->bookingData());
        $invoice = $booking->invoices->first();

        $first = app(PaymentVerificationService::class)->submitProof(
            $invoice, PaymentMethod::ManualTransfer, UploadedFile::fake()->create('bukti1.jpg', 200, 'image/jpeg'),
        );

        $second = app(PaymentVerificationService::class)->submitProof(
            $invoice, PaymentMethod::Qris, UploadedFile::fake()->create('bukti2.jpg', 200, 'image/jpeg'),
        );

        $this->assertSame(1, $invoice->payments()->count());
        $this->assertSame($first->id, $second->id);
        $this->assertSame(PaymentMethod::Qris, $second->fresh()->method);
        $this->assertNotSame($first->proof_file_path, $second->proof_file_path);
        Storage::disk('private_documents')->assertMissing($first->proof_file_path);
    }

    public function test_rejecting_a_payment_allows_resubmission(): void
    {
        Storage::fake('private_documents');
        $customer = $this->customer();
        $admin = $this->admin();
        $room = Room::factory()->create(['status' => RoomStatus::Available]);
        $booking = app(BookingLifecycleService::class)->createHold($customer, $room, $this->bookingData());
        $invoice = $booking->invoices->first();

        $payment = app(PaymentVerificationService::class)->submitProof(
            $invoice, PaymentMethod::ManualTransfer, UploadedFile::fake()->create('bukti.jpg', 200, 'image/jpeg'),
        );

        app(PaymentVerificationService::class)->reject($payment, $admin, 'Nominal tidak sesuai');

        $this->assertSame(PaymentStatus::Failed, $payment->fresh()->status);
        $this->assertSame(InvoiceStatus::Unpaid, $invoice->fresh()->status);

        $secondPayment = app(PaymentVerificationService::class)->submitProof(
            $invoice, PaymentMethod::ManualTransfer, UploadedFile::fake()->create('bukti2.jpg', 200, 'image/jpeg'),
        );

        $this->assertSame(2, $invoice->payments()->count());
        $this->assertSame(PaymentStatus::Pending, $secondPayment->status);
    }

    public function test_customer_can_submit_proof_via_http_and_admin_can_verify(): void
    {
        Storage::fake('private_documents');
        $customer = $this->customer();
        $admin = $this->admin();
        $room = Room::factory()->create(['status' => RoomStatus::Available]);
        $booking = app(BookingLifecycleService::class)->createHold($customer, $room, $this->bookingData());
        $invoice = $booking->invoices->first();

        $this->actingAs($customer)->post("/invoices/{$invoice->id}/payments", [
            'method' => 'manual_transfer',
            'proof' => UploadedFile::fake()->create('bukti.jpg', 200, 'image/jpeg'),
        ])->assertRedirect();

        $payment = $invoice->payments()->firstOrFail();

        $this->actingAs($admin)->put("/admin/payments/{$payment->id}/verify")->assertRedirect();

        $this->assertSame(InvoiceStatus::Paid, $invoice->fresh()->status);
    }

    public function test_stranger_cannot_view_or_pay_someone_elses_invoice(): void
    {
        $customer = $this->customer();
        $stranger = $this->customer();
        $room = Room::factory()->create(['status' => RoomStatus::Available]);
        $booking = app(BookingLifecycleService::class)->createHold($customer, $room, $this->bookingData());
        $invoice = $booking->invoices->first();

        $this->actingAs($stranger)->get("/invoices/{$invoice->id}")->assertForbidden();
        $this->actingAs($stranger)->get("/invoices/{$invoice->id}/pay")->assertForbidden();
    }
}
