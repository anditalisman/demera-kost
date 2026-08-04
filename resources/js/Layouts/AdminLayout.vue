<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage<PageProps>();
const sidebarOpen = ref(false);

const can = (permission: string) => page.props.auth.permissions.includes(permission);

const navItems = computed(() => {
    const items: { label: string; href: string; active: boolean }[] = [
        { label: 'Dashboard', href: route('admin.dashboard'), active: route().current('admin.dashboard') },
    ];

    if (can('content.view') || can('content.manage')) {
        items.push(
            { label: 'Hero & Halaman', href: route('admin.content-pages.index'), active: route().current('admin.content-pages.index') },
            { label: 'Galeri', href: route('admin.galleries.index'), active: route().current('admin.galleries.index') },
            { label: 'Testimoni', href: route('admin.testimonials.index'), active: route().current('admin.testimonials.index') },
            { label: 'FAQ', href: route('admin.faqs.index'), active: route().current('admin.faqs.index') },
        );
    }

    if (route().has('admin.rooms.index') && (can('rooms.view') || can('rooms.manage'))) {
        items.push(
            { label: 'Kamar', href: route('admin.rooms.index'), active: route().current('admin.rooms.*') },
            { label: 'Struktur Properti', href: route('admin.properties.index'), active: route().current('admin.properties.index') },
            { label: 'Tipe Kamar', href: route('admin.room-types.index'), active: route().current('admin.room-types.index') },
            { label: 'Fasilitas', href: route('admin.facilities.index'), active: route().current('admin.facilities.index') },
        );
    }

    if (route().has('admin.invoices.index') && (can('invoices.view') || can('invoices.manage'))) {
        items.push({ label: 'Invoice', href: route('admin.invoices.index'), active: route().current('admin.invoices.*') });
    }

    if (route().has('admin.payments.index') && (can('payments.view') || can('payments.verify') || can('payments.manage'))) {
        items.push({ label: 'Pembayaran', href: route('admin.payments.index'), active: route().current('admin.payments.*') });
    }

    if (route().has('admin.users.index') && (can('users.view') || can('users.manage'))) {
        items.push({ label: 'Pengguna', href: route('admin.users.index'), active: route().current('admin.users.index') });
    }

    if (can('settings.view') || can('settings.manage')) {
        items.push({ label: 'Pengaturan', href: route('admin.settings.index'), active: route().current('admin.settings.index') });
    }

    if (can('audit-logs.view')) {
        items.push({ label: 'Audit Log', href: route('admin.audit-logs.index'), active: route().current('admin.audit-logs.index') });
    }

    return items;
});
</script>

<template>
    <div class="min-h-screen bg-cream-50">
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-30 bg-charcoal-900/40 lg:hidden"
            @click="sidebarOpen = false"
        />

        <aside
            class="fixed inset-y-0 left-0 z-40 w-64 transform border-r border-beige-200 bg-white transition-transform lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-16 items-center border-b border-beige-200 px-6">
                <Link :href="route('landing')">
                    <ApplicationLogo />
                </Link>
            </div>

            <nav class="space-y-1 px-3 py-4">
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    class="block rounded-lg px-3 py-2 text-sm font-medium transition"
                    :class="item.active ? 'bg-terracotta-50 text-terracotta-700' : 'text-charcoal-500 hover:bg-cream-100 hover:text-charcoal-800'"
                >
                    {{ item.label }}
                </Link>
            </nav>
        </aside>

        <div class="lg:pl-64">
            <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-beige-200 bg-white px-4 sm:px-6">
                <button
                    type="button"
                    class="text-charcoal-500 lg:hidden"
                    @click="sidebarOpen = true"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="flex-1" />

                <div class="flex items-center gap-4">
                    <span class="hidden text-sm text-charcoal-500 sm:inline">
                        {{ page.props.auth.user?.name }}
                    </span>
                    <Link
                        :href="route('profile.edit')"
                        class="text-sm font-medium text-charcoal-600 hover:text-terracotta-600"
                    >
                        Profil
                    </Link>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-sm font-medium text-charcoal-600 hover:text-terracotta-600"
                    >
                        Keluar
                    </Link>
                </div>
            </header>

            <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
                <div
                    v-if="page.props.flash.success"
                    class="mb-6 rounded-lg bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
                >
                    {{ page.props.flash.success }}
                </div>
                <div
                    v-if="page.props.flash.error"
                    class="mb-6 rounded-lg bg-red-50 px-4 py-3 text-sm font-medium text-red-700"
                >
                    {{ page.props.flash.error }}
                </div>

                <slot />
            </main>
        </div>
    </div>
</template>
