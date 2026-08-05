<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { formatIdr } from '@/lib/roomStatus';
import { formatDate } from '@/lib/date';
import { Head, Link, router } from '@inertiajs/vue3';

interface InvoiceRow {
    id: number;
    invoice_number: string;
    invoice_type: string;
    status: string;
    total_amount: string;
    paid_amount: string;
    due_date: string;
    booking: { user: { name: string } } | null;
    tenant: { user: { name: string } } | null;
}
interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
}

defineProps<{ invoices: Paginated<InvoiceRow>; filters: { status?: string } }>();

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

function customerName(invoice: InvoiceRow): string {
    return invoice.booking?.user.name ?? invoice.tenant?.user.name ?? '-';
}

function filterByStatus(status: string) {
    router.get(route('admin.invoices.index'), { status: status || undefined }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Invoice" />

    <AdminLayout>
        <h1 class="font-display text-2xl font-semibold text-charcoal-800">Invoice</h1>

        <div class="mt-4 flex flex-wrap gap-2">
            <button
                v-for="s in ['', 'unpaid', 'partially_paid', 'paid', 'overdue', 'cancelled']"
                :key="s"
                class="rounded-full px-3 py-1.5 text-xs font-medium"
                :class="(filters.status ?? '') === s ? 'bg-terracotta-500 text-white' : 'bg-cream-100 text-charcoal-600'"
                @click="filterByStatus(s)"
            >
                {{ s === '' ? 'Semua' : STATUS_LABEL[s] }}
            </button>
        </div>

        <div class="mt-4 overflow-x-auto rounded-xl border border-beige-200 bg-white shadow-soft">
            <table class="min-w-full divide-y divide-beige-100 text-sm">
                <thead class="bg-cream-50 text-xs uppercase tracking-wide text-charcoal-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Nomor</th>
                        <th class="px-4 py-3 text-left">Pelanggan</th>
                        <th class="px-4 py-3 text-left">Tipe</th>
                        <th class="px-4 py-3 text-left">Jatuh Tempo</th>
                        <th class="px-4 py-3 text-left">Total</th>
                        <th class="px-4 py-3 text-left">Dibayar</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-beige-100">
                    <tr v-for="invoice in invoices.data" :key="invoice.id">
                        <td class="px-4 py-3 font-medium text-charcoal-800">{{ invoice.invoice_number }}</td>
                        <td class="px-4 py-3 text-charcoal-500">{{ customerName(invoice) }}</td>
                        <td class="px-4 py-3 text-charcoal-500">{{ invoice.invoice_type }}</td>
                        <td class="px-4 py-3 text-charcoal-500">{{ formatDate(invoice.due_date) }}</td>
                        <td class="px-4 py-3 text-charcoal-600">{{ formatIdr(invoice.total_amount) }}</td>
                        <td class="px-4 py-3 text-charcoal-600">{{ formatIdr(invoice.paid_amount) }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium" :class="STATUS_CLASS[invoice.status]">
                                {{ STATUS_LABEL[invoice.status] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="route('admin.invoices.show', invoice.id)" class="text-xs font-medium text-terracotta-600 hover:underline">Detail</Link>
                        </td>
                    </tr>
                    <tr v-if="invoices.data.length === 0">
                        <td colspan="8" class="px-4 py-6 text-center text-sm text-charcoal-400">Belum ada invoice.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="invoices.links" />
    </AdminLayout>
</template>
