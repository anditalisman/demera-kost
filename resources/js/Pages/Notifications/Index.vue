<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import { PageProps } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface NotificationRow {
    id: number;
    type: string;
    title: string;
    body: string | null;
    read_at: string | null;
    created_at: string;
}
interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
}

defineProps<{ notifications: Paginated<NotificationRow> }>();

const page = usePage<PageProps>();
const Layout = computed(() => (page.props.auth.roles.includes('admin') ? AdminLayout : CustomerLayout));

function markRead(notification: NotificationRow) {
    if (notification.read_at) return;
    router.put(route('notifications.read', notification.id), {}, { preserveScroll: true });
}
function markAllRead() {
    router.put(route('notifications.read-all'), {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Notifikasi" />

    <component :is="Layout">
        <div class="flex items-center justify-between">
            <h1 class="font-display text-2xl font-semibold text-charcoal-800">Notifikasi</h1>
            <PrimaryButton @click="markAllRead">Tandai Semua Dibaca</PrimaryButton>
        </div>

        <div class="mt-6 space-y-2">
            <button
                v-for="n in notifications.data"
                :key="n.id"
                class="block w-full rounded-xl border p-4 text-left shadow-soft transition"
                :class="n.read_at ? 'border-beige-200 bg-white' : 'border-terracotta-200 bg-terracotta-50'"
                @click="markRead(n)"
            >
                <div class="flex items-center justify-between">
                    <p class="font-medium text-charcoal-800">{{ n.title }}</p>
                    <span v-if="!n.read_at" class="h-2 w-2 rounded-full bg-terracotta-500"></span>
                </div>
                <p v-if="n.body" class="mt-1 text-sm text-charcoal-600">{{ n.body }}</p>
                <p class="mt-1 text-xs text-charcoal-400">{{ n.created_at }}</p>
            </button>

            <p v-if="notifications.data.length === 0" class="py-12 text-center text-sm text-charcoal-400">Belum ada notifikasi.</p>
        </div>

        <Pagination :links="notifications.links" />
    </component>
</template>
