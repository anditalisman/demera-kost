<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps<{
    types: Record<string, string>;
    type: string;
    filters: { from?: string; to?: string; status?: string; days?: string };
    headings: string[];
    rows: (string | number)[][];
}>();

const filterForm = reactive({
    from: props.filters.from ?? '',
    to: props.filters.to ?? '',
    status: props.filters.status ?? '',
    days: props.filters.days ?? '30',
});

function switchType(type: string) {
    router.get(route('admin.reports.index'), { type, ...filterForm }, { preserveState: true });
}
function applyFilters() {
    router.get(route('admin.reports.index'), { type: props.type, ...filterForm }, { preserveState: true, replace: true });
}
function exportAs(format: 'pdf' | 'excel' | 'csv') {
    const query = new URLSearchParams({ type: props.type, format, ...filterForm } as Record<string, string>).toString();
    window.location.href = `${route('admin.reports.export')}?${query}`;
}

const showDateFilter = ['invoices', 'revenue_by_period', 'payments_by_method', 'cancellations', 'room_status_history'].includes(props.type);
const showStatusFilter = props.type === 'invoices';
const showDaysFilter = props.type === 'leases_ending_soon';
</script>

<template>
    <Head title="Laporan" />

    <AdminLayout>
        <h1 class="font-display text-2xl font-semibold text-charcoal-800">Laporan</h1>

        <div class="mt-4 flex flex-wrap gap-2">
            <button
                v-for="(label, key) in types"
                :key="key"
                class="rounded-full px-3 py-1.5 text-xs font-medium"
                :class="type === key ? 'bg-terracotta-500 text-white' : 'bg-cream-100 text-charcoal-600'"
                @click="switchType(key)"
            >
                {{ label }}
            </button>
        </div>

        <div class="mt-4 flex flex-wrap items-end gap-3 rounded-xl border border-beige-200 bg-white p-4 shadow-soft">
            <template v-if="showDateFilter">
                <div>
                    <label class="text-xs text-charcoal-500">Dari Tanggal</label>
                    <input v-model="filterForm.from" type="date" class="mt-1 block rounded-lg border-beige-300 text-sm focus:border-terracotta-400 focus:ring-terracotta-400" />
                </div>
                <div>
                    <label class="text-xs text-charcoal-500">Sampai Tanggal</label>
                    <input v-model="filterForm.to" type="date" class="mt-1 block rounded-lg border-beige-300 text-sm focus:border-terracotta-400 focus:ring-terracotta-400" />
                </div>
            </template>
            <div v-if="showStatusFilter">
                <label class="text-xs text-charcoal-500">Status</label>
                <select v-model="filterForm.status" class="mt-1 block rounded-lg border-beige-300 text-sm focus:border-terracotta-400 focus:ring-terracotta-400">
                    <option value="">Semua</option>
                    <option value="unpaid">Belum Dibayar</option>
                    <option value="partially_paid">Dibayar Sebagian</option>
                    <option value="paid">Lunas</option>
                    <option value="overdue">Terlambat</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>
            </div>
            <div v-if="showDaysFilter">
                <label class="text-xs text-charcoal-500">Dalam (hari)</label>
                <input v-model="filterForm.days" type="number" min="1" class="mt-1 block w-24 rounded-lg border-beige-300 text-sm focus:border-terracotta-400 focus:ring-terracotta-400" />
            </div>
            <button v-if="showDateFilter || showStatusFilter || showDaysFilter" class="rounded-lg bg-terracotta-500 px-4 py-2 text-sm font-semibold text-white hover:bg-terracotta-600" @click="applyFilters">
                Terapkan
            </button>

            <div class="ml-auto flex gap-2">
                <button class="rounded-lg border border-beige-300 px-3 py-2 text-xs font-medium text-charcoal-600 hover:bg-cream-100" @click="exportAs('pdf')">Ekspor PDF</button>
                <button class="rounded-lg border border-beige-300 px-3 py-2 text-xs font-medium text-charcoal-600 hover:bg-cream-100" @click="exportAs('excel')">Ekspor Excel</button>
                <button class="rounded-lg border border-beige-300 px-3 py-2 text-xs font-medium text-charcoal-600 hover:bg-cream-100" @click="exportAs('csv')">Ekspor CSV</button>
            </div>
        </div>

        <div class="mt-4 overflow-x-auto rounded-xl border border-beige-200 bg-white shadow-soft">
            <table class="min-w-full divide-y divide-beige-100 text-sm">
                <thead class="bg-cream-50 text-xs uppercase tracking-wide text-charcoal-500">
                    <tr>
                        <th v-for="h in headings" :key="h" class="px-4 py-3 text-left">{{ h }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-beige-100">
                    <tr v-for="(row, index) in rows" :key="index">
                        <td v-for="(cell, cellIndex) in row" :key="cellIndex" class="px-4 py-3 text-charcoal-600">{{ cell }}</td>
                    </tr>
                    <tr v-if="rows.length === 0">
                        <td :colspan="headings.length" class="px-4 py-6 text-center text-sm text-charcoal-400">Tidak ada data.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
