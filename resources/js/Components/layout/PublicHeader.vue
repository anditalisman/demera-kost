<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const page = usePage<PageProps>();
const mobileOpen = ref(false);

const navLinks = [
    { label: 'Demera Living', href: () => (route().has('living.index') ? route('living.index') : '/living') },
    { label: 'Demera Fashion', href: () => (route().has('fashion.index') ? route('fashion.index') : '/fashion') },
];
</script>

<template>
    <header class="sticky top-0 z-40 border-b border-beige-200/80 bg-cream-50/90 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <Link :href="route('landing')">
                <ApplicationLogo />
            </Link>

            <nav class="hidden items-center gap-8 md:flex">
                <Link
                    v-for="link in navLinks"
                    :key="link.label"
                    :href="link.href()"
                    class="text-sm font-medium text-charcoal-600 transition hover:text-terracotta-600"
                >
                    {{ link.label }}
                </Link>
            </nav>

            <div class="hidden items-center gap-3 md:flex">
                <template v-if="page.props.auth.user">
                    <Link
                        :href="route('dashboard')"
                        class="rounded-lg bg-terracotta-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-terracotta-600"
                    >
                        Dashboard
                    </Link>
                </template>
                <template v-else>
                    <Link
                        :href="route('login')"
                        class="text-sm font-medium text-charcoal-600 hover:text-terracotta-600"
                    >
                        Masuk
                    </Link>
                    <Link
                        :href="route('register')"
                        class="rounded-lg bg-terracotta-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-terracotta-600"
                    >
                        Daftar
                    </Link>
                </template>
            </div>

            <button class="text-charcoal-600 md:hidden" @click="mobileOpen = !mobileOpen">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        <div v-if="mobileOpen" class="border-t border-beige-200 bg-white px-4 py-4 md:hidden">
            <nav class="flex flex-col gap-3">
                <Link v-for="link in navLinks" :key="link.label" :href="link.href()" class="text-sm font-medium text-charcoal-600">
                    {{ link.label }}
                </Link>
                <hr class="border-beige-200" />
                <template v-if="page.props.auth.user">
                    <Link :href="route('dashboard')" class="text-sm font-semibold text-terracotta-600">Dashboard</Link>
                </template>
                <template v-else>
                    <Link :href="route('login')" class="text-sm font-medium text-charcoal-600">Masuk</Link>
                    <Link :href="route('register')" class="text-sm font-semibold text-terracotta-600">Daftar</Link>
                </template>
            </nav>
        </div>
    </header>
</template>
