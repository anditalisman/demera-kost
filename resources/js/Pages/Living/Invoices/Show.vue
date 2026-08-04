<script setup lang="ts">
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { formatIdr } from '@/lib/roomStatus';
import { Head, Link } from '@inertiajs/vue3';

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
            <p class="mt-1 text-sm text-charcoal-500">Jatuh tempo {{ invoice.due_date }} &middot; {{ STATUS_LABEL[invoice.status] }}</p>

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
