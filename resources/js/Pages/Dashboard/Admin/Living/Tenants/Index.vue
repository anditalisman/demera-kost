<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { formatDate } from '@/lib/date';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

interface TenantRow {
    id: number;
    status: 'prospective' | 'active' | 'inactive';
    joined_at: string | null;
    user: { name: string; email: string } | null;
    room: { name: string | null; room_number: string; property: { name: string } } | null;
}
interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{ tenants: Paginated<TenantRow>; filters: { status?: string; search?: string } }>();

const STATUS_LABEL: Record<string, string> = { prospective: 'Calon Penyewa', active: 'Penyewa Aktif', inactive: 'Tidak Aktif' };
const STATUS_CLASS: Record<string, string> = {
    prospective: 'bg-amber-50 text-amber-700',
    active: 'bg-green-50 text-green-700',
    inactive: 'bg-charcoal-100 text-charcoal-500',
};

const filterForm = reactive({ status: props.filters.status ?? '', search: props.filters.search ?? '' });
function applyFilters() {
    router.get(route('admin.tenants.index'), { ...filterForm }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Penyewa" />

    <AdminLayout>
        <h1 class="font-display text-2xl font-semibold text-charcoal-800">Penyewa</h1>

        <div class="mt-4 flex gap-3">
            <input
                v-model="filterForm.search"
                type="text"
                placeholder="Cari nama..."
                class="rounded-lg border-beige-300 text-sm focus:border-terracotta-400 focus:ring-terracotta-400"
                @keyup.enter="applyFilters"
            />
            <select v-model="filterForm.status" class="rounded-lg border-beige-300 text-sm focus:border-terracotta-400 focus:ring-terracotta-400" @change="applyFilters">
                <option value="">Semua status</option>
                <option value="prospective">Calon Penyewa</option>
                <option value="active">Penyewa Aktif</option>
                <option value="inactive">Tidak Aktif</option>
            </select>
        </div>

        <div class="mt-4 overflow-x-auto rounded-xl border border-beige-200 bg-white shadow-soft">
            <table class="min-w-full divide-y divide-beige-100 text-sm">
                <thead class="bg-cream-50 text-xs uppercase tracking-wide text-charcoal-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-left">Kamar</th>
                        <th class="px-4 py-3 text-left">Bergabung</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-beige-100">
                    <tr v-for="tenant in tenants.data" :key="tenant.id">
                        <td class="px-4 py-3">
                            <p class="font-medium text-charcoal-800">{{ tenant.user?.name ?? '(akun dihapus)' }}</p>
                            <p class="text-xs text-charcoal-400">{{ tenant.user?.email ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3 text-charcoal-500">
                            <template v-if="tenant.room">{{ tenant.room.name ?? `Kamar ${tenant.room.room_number}` }} &middot; {{ tenant.room.property.name }}</template>
                            <template v-else>-</template>
                        </td>
                        <td class="px-4 py-3 text-charcoal-500">{{ formatDate(tenant.joined_at) }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium" :class="STATUS_CLASS[tenant.status]">{{ STATUS_LABEL[tenant.status] }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="route('admin.tenants.show', tenant.id)" class="text-xs font-medium text-terracotta-600 hover:underline">Detail</Link>
                        </td>
                    </tr>
                    <tr v-if="tenants.data.length === 0">
                        <td colspan="5" class="px-4 py-6 text-center text-sm text-charcoal-400">Belum ada penyewa.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="tenants.links" />
    </AdminLayout>
</template>
