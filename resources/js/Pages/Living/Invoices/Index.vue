<script setup lang="ts">
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { formatIdr } from '@/lib/roomStatus';
import { formatDate } from '@/lib/date';
import { Head, Link } from '@inertiajs/vue3';

interface InvoiceRow {
    id: number;
    invoice_number: string;
    invoice_type: string;
    status: string;
    total_amount: string;
    due_date: string;
    booking: { room: { name: string | null; room_number: string } } | null;
    tenant: { room: { name: string | null; room_number: string } } | null;
    payments: { status: string }[];
}
interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
}

defineProps<{ invoices: Paginated<InvoiceRow> }>();

const STATUS_LABEL: Record<string, string> = {
    draft: 'Belum Ditagih',
    unpaid: 'Belum Dibayar',
    partially_paid: 'Dibayar Sebagian',
    paid: 'Lunas',
    overdue: 'Terlambat',
    cancelled: 'Dibatalkan',
    refunded: 'Dikembalikan',
};
const STATUS_CLASS: Record<string, string> = {
    draft: 'bg-charcoal-100 text-charcoal-500',
    unpaid: 'bg-amber-50 text-amber-700',
    partially_paid: 'bg-amber-50 text-amber-700',
    paid: 'bg-green-50 text-green-700',
    overdue: 'bg-red-50 text-red-700',
    cancelled: 'bg-charcoal-100 text-charcoal-500',
    refunded: 'bg-charcoal-100 text-charcoal-500',
};

function roomLabel(invoice: InvoiceRow): string {
    const room = invoice.booking?.room ?? invoice.tenant?.room;
    if (!room) return '-';
    return room.name ?? `Kamar ${room.room_number}`;
}

// invoice.status stays "unpaid" until an admin verifies the payment — flag
// invoices with a pending proof separately so the list doesn't look
// identical to one nobody has paid anything towards yet.
function hasPendingPayment(invoice: InvoiceRow): boolean {
    return invoice.payments.some((payment) => payment.status === 'pending');
}
function statusLabel(invoice: InvoiceRow): string {
    if (hasPendingPayment(invoice) && ['unpaid', 'partially_paid', 'overdue'].includes(invoice.status)) {
        return 'Menunggu Verifikasi';
    }
    return STATUS_LABEL[invoice.status] ?? invoice.status;
}
function statusClass(invoice: InvoiceRow): string {
    if (hasPendingPayment(invoice) && ['unpaid', 'partially_paid', 'overdue'].includes(invoice.status)) {
        return 'bg-blue-50 text-blue-700';
    }
    return STATUS_CLASS[invoice.status] ?? '';
}
</script>

<template>
    <Head title="Tagihan Saya" />

    <CustomerLayout>
        <h1 class="font-display text-2xl font-semibold text-charcoal-800">Tagihan Saya</h1>

        <div class="mt-6 space-y-3">
            <Link
                v-for="invoice in invoices.data"
                :key="invoice.id"
                :href="route('invoices.show', invoice.id)"
                class="flex items-center justify-between rounded-xl border border-beige-200 bg-white p-4 shadow-soft transition hover:border-terracotta-300"
            >
                <div>
                    <p class="font-medium text-charcoal-800">{{ invoice.invoice_number }}</p>
                    <p class="text-sm text-charcoal-500">{{ roomLabel(invoice) }} &middot; Jatuh tempo {{ formatDate(invoice.due_date) }}</p>
                </div>
                <div class="text-right">
                    <p class="font-semibold text-charcoal-800">{{ formatIdr(invoice.total_amount) }}</p>
                    <span class="rounded-full px-2.5 py-1 text-xs font-medium" :class="statusClass(invoice)">
                        {{ statusLabel(invoice) }}
                    </span>
                </div>
            </Link>

            <p v-if="invoices.data.length === 0" class="py-12 text-center text-sm text-charcoal-400">Belum ada tagihan.</p>
        </div>

        <Pagination :links="invoices.links" />
    </CustomerLayout>
</template>
