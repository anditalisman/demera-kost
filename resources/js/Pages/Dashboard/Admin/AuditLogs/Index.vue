<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { formatDateTime } from '@/lib/date';

interface AuditLogRow {
    id: number;
    action: string;
    auditable_type: string | null;
    description: string | null;
    ip_address: string | null;
    created_at: string;
    user: { id: number; name: string; email: string } | null;
}

interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    logs: Paginated<AuditLogRow>;
    filters: { action?: string };
    availableActions: string[];
}>();

const actionFilter = ref(props.filters.action ?? '');

watch(actionFilter, (value) => {
    router.get(route('admin.audit-logs.index'), { action: value || undefined }, { preserveState: true, replace: true });
});

function shortModel(type: string | null): string {
    if (!type) return '';
    return type.split('\\').pop() ?? type;
}
</script>

<template>
    <Head title="Audit Log" />

    <AdminLayout>
        <h1 class="font-display text-2xl font-semibold text-charcoal-800">Audit Log</h1>
        <p class="mt-1 text-sm text-charcoal-400">Riwayat aktivitas penting di seluruh sistem.</p>

        <div class="mt-4">
            <select v-model="actionFilter" class="rounded-lg border-beige-300 text-sm focus:border-terracotta-400 focus:ring-terracotta-400">
                <option value="">Semua aksi</option>
                <option v-for="action in availableActions" :key="action" :value="action">{{ action }}</option>
            </select>
        </div>

        <div class="mt-6 overflow-x-auto rounded-2xl border border-beige-200 bg-white shadow-soft">
            <table class="min-w-full divide-y divide-beige-200 text-sm">
                <thead class="bg-cream-100 text-left text-xs font-semibold uppercase tracking-wide text-charcoal-500">
                    <tr>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">Pengguna</th>
                        <th class="px-4 py-3">Aksi</th>
                        <th class="px-4 py-3">Objek</th>
                        <th class="px-4 py-3">Keterangan</th>
                        <th class="px-4 py-3">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-beige-100">
                    <tr v-for="log in logs.data" :key="log.id">
                        <td class="whitespace-nowrap px-4 py-3 text-charcoal-500">{{ formatDateTime(log.created_at) }}</td>
                        <td class="px-4 py-3 text-charcoal-700">{{ log.user?.name ?? 'Sistem' }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full bg-terracotta-50 px-2 py-0.5 text-xs font-medium text-terracotta-700">{{ log.action }}</span>
                        </td>
                        <td class="px-4 py-3 text-charcoal-500">{{ shortModel(log.auditable_type) }}</td>
                        <td class="px-4 py-3 text-charcoal-500">{{ log.description }}</td>
                        <td class="px-4 py-3 text-xs text-charcoal-400">{{ log.ip_address }}</td>
                    </tr>
                </tbody>
            </table>

            <p v-if="logs.data.length === 0" class="p-6 text-center text-sm text-charcoal-400">Belum ada aktivitas tercatat.</p>
        </div>

        <Pagination :links="logs.links" />
    </AdminLayout>
</template>
