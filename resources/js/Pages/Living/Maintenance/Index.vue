<script setup lang="ts">
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatDateTime } from '@/lib/date';

interface RequestRow {
    id: number;
    title: string;
    status: string;
    priority: string;
    created_at: string;
}
interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
}

defineProps<{ requests: Paginated<RequestRow>; canCreate: boolean }>();

const STATUS_LABEL: Record<string, string> = { new: 'Baru', in_progress: 'Diproses', waiting: 'Menunggu', completed: 'Selesai', closed: 'Ditutup' };
const STATUS_CLASS: Record<string, string> = {
    new: 'bg-amber-50 text-amber-700', in_progress: 'bg-blue-50 text-blue-700', waiting: 'bg-charcoal-100 text-charcoal-600',
    completed: 'bg-green-50 text-green-700', closed: 'bg-charcoal-100 text-charcoal-500',
};
</script>

<template>
    <Head title="Keluhan & Perawatan" />

    <CustomerLayout>
        <div class="flex items-center justify-between">
            <h1 class="font-display text-2xl font-semibold text-charcoal-800">Keluhan &amp; Perawatan</h1>
            <Link v-if="canCreate" :href="route('maintenance-requests.create')">
                <PrimaryButton>+ Ajukan Keluhan</PrimaryButton>
            </Link>
        </div>
        <p v-if="!canCreate" class="mt-2 text-sm text-charcoal-400">Anda perlu menjadi penyewa aktif untuk mengajukan keluhan.</p>

        <div class="mt-6 space-y-3">
            <Link
                v-for="req in requests.data"
                :key="req.id"
                :href="route('maintenance-requests.show', req.id)"
                class="flex items-center justify-between rounded-xl border border-beige-200 bg-white p-4 shadow-soft transition hover:border-terracotta-300"
            >
                <div>
                    <p class="font-medium text-charcoal-800">{{ req.title }}</p>
                    <p class="text-xs text-charcoal-400">{{ formatDateTime(req.created_at) }}</p>
                </div>
                <span class="rounded-full px-2.5 py-1 text-xs font-medium" :class="STATUS_CLASS[req.status]">{{ STATUS_LABEL[req.status] }}</span>
            </Link>

            <p v-if="requests.data.length === 0" class="py-12 text-center text-sm text-charcoal-400">Belum ada keluhan yang diajukan.</p>
        </div>

        <Pagination :links="requests.links" />
    </CustomerLayout>
</template>
