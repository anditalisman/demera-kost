<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { formatIdr } from '@/lib/roomStatus';
import { formatDate } from '@/lib/date';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

interface LeaseRow {
    id: number;
    lease_number: string;
    status: string;
    start_date: string;
    end_date: string;
    monthly_price: string;
    tenant: { user: { name: string } };
    room: { name: string | null; room_number: string; property: { name: string } };
}
interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{ leases: Paginated<LeaseRow>; filters: { status?: string; ending_soon?: string } }>();

const STATUS_LABEL: Record<string, string> = {
    draft: 'Draft', pending_approval: 'Menunggu Persetujuan', active: 'Aktif', ending_soon: 'Akan Berakhir',
    completed: 'Selesai', cancelled: 'Dibatalkan', extended: 'Diperpanjang',
};

const filterForm = reactive({ status: props.filters.status ?? '', ending_soon: props.filters.ending_soon === '1' });
function applyFilters() {
    router.get(route('admin.leases.index'), { status: filterForm.status || undefined, ending_soon: filterForm.ending_soon ? '1' : undefined }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Kontrak Sewa" />

    <AdminLayout>
        <h1 class="font-display text-2xl font-semibold text-charcoal-800">Kontrak Sewa</h1>

        <div class="mt-4 flex items-center gap-4">
            <select v-model="filterForm.status" class="rounded-lg border-beige-300 text-sm focus:border-terracotta-400 focus:ring-terracotta-400" @change="applyFilters">
                <option value="">Semua status</option>
                <option v-for="(label, value) in STATUS_LABEL" :key="value" :value="value">{{ label }}</option>
            </select>
            <label class="flex items-center gap-2 text-sm text-charcoal-600">
                <input v-model="filterForm.ending_soon" type="checkbox" class="rounded border-beige-300 text-terracotta-500 focus:ring-terracotta-400" @change="applyFilters" />
                Akan berakhir 30 hari ke depan
            </label>
        </div>

        <div class="mt-4 overflow-x-auto rounded-xl border border-beige-200 bg-white shadow-soft">
            <table class="min-w-full divide-y divide-beige-100 text-sm">
                <thead class="bg-cream-50 text-xs uppercase tracking-wide text-charcoal-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Nomor</th>
                        <th class="px-4 py-3 text-left">Penyewa</th>
                        <th class="px-4 py-3 text-left">Kamar</th>
                        <th class="px-4 py-3 text-left">Periode</th>
                        <th class="px-4 py-3 text-left">Harga</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-beige-100">
                    <tr v-for="lease in leases.data" :key="lease.id">
                        <td class="px-4 py-3 font-medium text-charcoal-800">{{ lease.lease_number }}</td>
                        <td class="px-4 py-3 text-charcoal-500">{{ lease.tenant.user.name }}</td>
                        <td class="px-4 py-3 text-charcoal-500">{{ lease.room.name ?? `Kamar ${lease.room.room_number}` }}</td>
                        <td class="px-4 py-3 text-charcoal-500">{{ formatDate(lease.start_date) }} s.d. {{ formatDate(lease.end_date) }}</td>
                        <td class="px-4 py-3 text-charcoal-600">{{ formatIdr(lease.monthly_price) }}</td>
                        <td class="px-4 py-3 text-charcoal-600">{{ STATUS_LABEL[lease.status] }}</td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="route('admin.leases.show', lease.id)" class="text-xs font-medium text-terracotta-600 hover:underline">Kelola</Link>
                        </td>
                    </tr>
                    <tr v-if="leases.data.length === 0">
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-charcoal-400">Tidak ada kontrak.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="leases.links" />
    </AdminLayout>
</template>
