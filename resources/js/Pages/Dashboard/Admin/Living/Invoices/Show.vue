<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatIdr } from '@/lib/roomStatus';
import { formatDate } from '@/lib/date';
import { Head } from '@inertiajs/vue3';

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
    verified_by: { name: string } | null;
}
interface InvoiceDetail {
    id: number;
    invoice_number: string;
    invoice_type: string;
    status: string;
    subtotal_amount: string;
    discount_amount: string;
    late_fee_amount: string;
    total_amount: string;
    paid_amount: string;
    due_date: string;
    period_start: string | null;
    period_end: string | null;
    items: InvoiceItem[];
    payments: PaymentRow[];
    booking: { user: { name: string; email: string } } | null;
    tenant: { user: { name: string; email: string } } | null;
}

const props = defineProps<{ invoice: InvoiceDetail }>();
const customer = props.invoice.booking?.user ?? props.invoice.tenant?.user;
</script>

<template>
    <Head :title="`Invoice ${invoice.invoice_number}`" />

    <AdminLayout>
        <div class="flex items-center justify-between">
            <h1 class="font-display text-2xl font-semibold text-charcoal-800">Invoice {{ invoice.invoice_number }}</h1>
            <a :href="route('admin.invoices.pdf', invoice.id)" class="text-sm font-medium text-terracotta-600 hover:underline">Unduh PDF</a>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-xl border border-beige-200 bg-white p-6 shadow-soft">
                <p class="text-sm text-charcoal-500">Pelanggan: <span class="font-medium text-charcoal-800">{{ customer?.name }}</span> ({{ customer?.email }})</p>
                <p class="mt-1 text-sm text-charcoal-500">Periode: {{ formatDate(invoice.period_start) }} s.d. {{ formatDate(invoice.period_end) }}</p>
                <p class="mt-1 text-sm text-charcoal-500">Jatuh Tempo: {{ formatDate(invoice.due_date) }}</p>

                <table class="mt-4 w-full text-sm">
                    <tbody class="divide-y divide-beige-100">
                        <tr v-for="item in invoice.items" :key="item.id">
                            <td class="py-2 text-charcoal-600">{{ item.label }}</td>
                            <td class="py-2 text-right text-charcoal-800">{{ formatIdr(item.amount) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="mt-4 space-y-1 border-t border-beige-100 pt-4 text-sm">
                    <div class="flex justify-between"><span class="text-charcoal-500">Subtotal</span><span>{{ formatIdr(invoice.subtotal_amount) }}</span></div>
                    <div v-if="Number(invoice.discount_amount) > 0" class="flex justify-between"><span class="text-charcoal-500">Diskon</span><span>-{{ formatIdr(invoice.discount_amount) }}</span></div>
                    <div v-if="Number(invoice.late_fee_amount) > 0" class="flex justify-between"><span class="text-charcoal-500">Denda</span><span>{{ formatIdr(invoice.late_fee_amount) }}</span></div>
                    <div class="flex justify-between border-t border-beige-100 pt-2 font-semibold"><span>Total</span><span class="text-terracotta-600">{{ formatIdr(invoice.total_amount) }}</span></div>
                    <div class="flex justify-between text-charcoal-500"><span>Sudah Dibayar</span><span>{{ formatIdr(invoice.paid_amount) }}</span></div>
                </div>
            </div>

            <div class="rounded-xl border border-beige-200 bg-white p-6 shadow-soft">
                <h2 class="font-display text-lg font-semibold text-charcoal-800">Riwayat Pembayaran</h2>
                <ul class="mt-3 space-y-3 text-sm">
                    <li v-for="payment in invoice.payments" :key="payment.id" class="rounded-lg bg-cream-50 p-3">
                        <p class="font-medium text-charcoal-800">{{ payment.payment_code }}</p>
                        <p class="text-charcoal-500">{{ formatIdr(payment.amount) }} &middot; {{ payment.status }}</p>
                        <p class="text-xs text-charcoal-400">{{ payment.created_at }}</p>
                    </li>
                    <li v-if="invoice.payments.length === 0" class="text-xs text-charcoal-400">Belum ada pembayaran.</li>
                </ul>
            </div>
        </div>
    </AdminLayout>
</template>
