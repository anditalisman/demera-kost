<script setup lang="ts">
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { formatIdr } from '@/lib/roomStatus';
import { formatDate } from '@/lib/date';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface InvoiceItem {
    id: number;
    label: string;
    amount: string;
}
interface PaymentRow {
    id: number;
    payment_code: string;
    method: string;
    amount: string;
    status: string;
    created_at: string;
}
interface InvoiceDetail {
    id: number;
    invoice_number: string;
    status: string;
    subtotal_amount: string;
    late_fee_amount: string;
    total_amount: string;
    paid_amount: string;
    due_date: string;
    items: InvoiceItem[];
    payments: PaymentRow[];
    booking: { booking_code: string; room: { name: string | null; room_number: string } } | null;
}

const props = defineProps<{ invoice: InvoiceDetail }>();

const STATUS_LABEL: Record<string, string> = {
    draft: 'Belum Ditagih',
    unpaid: 'Belum Dibayar',
    partially_paid: 'Dibayar Sebagian',
    paid: 'Lunas',
    overdue: 'Terlambat',
    cancelled: 'Dibatalkan',
    refunded: 'Dikembalikan',
};
const PAYMENT_STATUS_LABEL: Record<string, string> = {
    pending: 'Menunggu Verifikasi',
    paid: 'Lunas',
    failed: 'Ditolak',
};

const canPay = props.invoice.status === 'unpaid' || props.invoice.status === 'partially_paid' || props.invoice.status === 'overdue';

// invoice.status has no "awaiting verification" state of its own — it only
// changes once an admin verifies a payment — so without this, the header
// keeps reading "Belum Dibayar" right after a proof upload with no visible
// sign the upload was received, easy to mistake for a failed upload.
const hasPendingPayment = computed(() => props.invoice.payments.some((payment) => payment.status === 'pending'));
const displayStatusLabel = computed(() =>
    hasPendingPayment.value && canPay ? 'Menunggu Verifikasi' : (STATUS_LABEL[props.invoice.status] ?? props.invoice.status),
);
</script>

<template>
    <Head :title="`Invoice ${invoice.invoice_number}`" />

    <CustomerLayout>
        <div class="rounded-2xl border border-beige-200 bg-white p-6 shadow-soft sm:p-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="font-display text-2xl font-semibold text-charcoal-800">{{ invoice.invoice_number }}</h1>
                <div class="flex items-center gap-3">
                    <a :href="route('invoices.pdf', invoice.id)" class="text-sm font-medium text-terracotta-600 hover:underline">Unduh PDF</a>
                    <Link v-if="invoice.booking" :href="route('bookings.show', invoice.booking.booking_code)" class="text-sm font-medium text-charcoal-500 hover:underline">
                        Lihat Pemesanan
                    </Link>
                </div>
            </div>
            <p class="mt-1 text-sm text-charcoal-500">Jatuh tempo {{ formatDate(invoice.due_date) }} &middot; {{ displayStatusLabel }}</p>

            <div v-if="hasPendingPayment" class="mt-4 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                <span class="mt-0.5 h-2 w-2 flex-shrink-0 rounded-full bg-amber-500"></span>
                <p>
                    Bukti pembayaran kamu sudah diterima dan sedang <strong>menunggu diverifikasi</strong> oleh admin. Status akan
                    berubah menjadi Lunas setelah diverifikasi — biasanya tidak lama.
                </p>
            </div>

            <table class="mt-6 w-full text-sm">
                <tbody class="divide-y divide-beige-100">
                    <tr v-for="item in invoice.items" :key="item.id">
                        <td class="py-2 text-charcoal-600">{{ item.label }}</td>
                        <td class="py-2 text-right text-charcoal-800">{{ formatIdr(item.amount) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-4 space-y-1 border-t border-beige-100 pt-4 text-sm">
                <div v-if="Number(invoice.late_fee_amount) > 0" class="flex justify-between"><span class="text-charcoal-500">Denda Keterlambatan</span><span>{{ formatIdr(invoice.late_fee_amount) }}</span></div>
                <div class="flex justify-between border-t border-beige-100 pt-2 font-semibold"><span>Total</span><span class="text-terracotta-600">{{ formatIdr(invoice.total_amount) }}</span></div>
                <div class="flex justify-between text-charcoal-500"><span>Sudah Dibayar</span><span>{{ formatIdr(invoice.paid_amount) }}</span></div>
            </div>

            <Link v-if="canPay" :href="route('payments.create', invoice.id)">
                <PrimaryButton class="mt-6 w-full justify-center sm:w-auto">Bayar Sekarang</PrimaryButton>
            </Link>

            <div v-if="invoice.payments.length" class="mt-8 border-t border-beige-100 pt-6">
                <h2 class="font-display text-lg font-semibold text-charcoal-800">Riwayat Pembayaran</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    <li v-for="payment in invoice.payments" :key="payment.id" class="flex items-center justify-between rounded-lg bg-cream-50 px-3 py-2">
                        <span>{{ payment.payment_code }} &middot; {{ formatIdr(payment.amount) }}</span>
                        <span class="text-xs text-charcoal-500">{{ PAYMENT_STATUS_LABEL[payment.status] ?? payment.status }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </CustomerLayout>
</template>
