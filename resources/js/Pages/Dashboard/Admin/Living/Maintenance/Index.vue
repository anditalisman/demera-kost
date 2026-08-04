<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';

interface RequestRow {
    id: number;
    title: string;
    priority: string;
    status: string;
    created_at: string;
    tenant: { user: { name: string } } | null;
    room: { name: string | null; room_number: string };
}
interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
}

defineProps<{ requests: Paginated<RequestRow>; filters: { status?: string; priority?: string } }>();

const STATUS_LABEL: Record<string, string> = { new: 'Baru', in_progress: 'Diproses', waiting: 'Menunggu', completed: 'Selesai', closed: 'Ditutup' };
const STATUS_CLASS: Record<string, string> = {
    new: 'bg-amber-50 text-amber-700', in_progress: 'bg-blue-50 text-blue-700', waiting: 'bg-charcoal-100 text-charcoal-600',
    completed: 'bg-green-50 text-green-700', closed: 'bg-charcoal-100 text-charcoal-500',
};
const PRIORITY_LABEL: Record<string, string> = { low: 'Rendah', normal: 'Normal', high: 'Tinggi', urgent: 'Mendesak' };

function filterByStatus(status: string) {
    router.get(route('admin.maintenance-requests.index'), { status: status || undefined }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Keluhan & Perawatan" />

    <AdminLayout>
        <h1 class="font-display text-2xl font-semibold text-charcoal-800">Keluhan &amp; Perawatan</h1>

        <div class="mt-4 flex flex-wrap gap-2">
            <button
                v-for="s in ['', 'new', 'in_progress', 'waiting', 'completed', 'closed']"
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
                        <th class="px-4 py-3 text-left">Judul</th>
                        <th class="px-4 py-3 text-left">Penyewa</th>
                        <th class="px-4 py-3 text-left">Kamar</th>
                        <th class="px-4 py-3 text-left">Prioritas</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-beige-100">
                    <tr v-for="req in requests.data" :key="req.id">
                        <td class="px-4 py-3 font-medium text-charcoal-800">{{ req.title }}</td>
                        <td class="px-4 py-3 text-charcoal-500">{{ req.tenant?.user.name ?? '-' }}</td>
                        <td class="px-4 py-3 text-charcoal-500">{{ req.room.name ?? `Kamar ${req.room.room_number}` }}</td>
                        <td class="px-4 py-3 text-charcoal-500">{{ PRIORITY_LABEL[req.priority] }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium" :class="STATUS_CLASS[req.status]">{{ STATUS_LABEL[req.status] }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="route('admin.maintenance-requests.show', req.id)" class="text-xs font-medium text-terracotta-600 hover:underline">Detail</Link>
                        </td>
                    </tr>
                    <tr v-if="requests.data.length === 0">
                        <td colspan="6" class="px-4 py-6 text-center text-sm text-charcoal-400">Belum ada keluhan.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="requests.links" />
    </AdminLayout>
</template>
