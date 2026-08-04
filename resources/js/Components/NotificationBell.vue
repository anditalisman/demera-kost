<script setup lang="ts">
import { PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage<PageProps>();
</script>

<template>
    <Link
        :href="route('notifications.index')"
        class="relative inline-flex items-center text-charcoal-500 hover:text-terracotta-600"
        title="Notifikasi"
    >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <span
            v-if="page.props.auth.unreadNotificationsCount > 0"
            class="absolute -right-1.5 -top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-terracotta-500 px-1 text-[10px] font-semibold text-white"
        >
            {{ page.props.auth.unreadNotificationsCount > 9 ? '9+' : page.props.auth.unreadNotificationsCount }}
        </span>
    </Link>
</template>
