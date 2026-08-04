<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage<PageProps>();
</script>

<template>
    <div class="min-h-screen bg-cream-50">
        <header class="border-b border-beige-200 bg-white">
            <div class="mx-auto flex h-16 max-w-5xl items-center justify-between px-4 sm:px-6">
                <Link :href="route('landing')">
                    <ApplicationLogo />
                </Link>

                <nav class="flex items-center gap-6">
                    <Link
                        :href="route('customer.dashboard')"
                        class="text-sm font-medium"
                        :class="route().current('customer.dashboard') ? 'text-terracotta-600' : 'text-charcoal-500 hover:text-terracotta-600'"
                    >
                        Dashboard
                    </Link>
                    <Link
                        :href="route('invoices.index')"
                        class="text-sm font-medium"
                        :class="route().current('invoices.*') || route().current('payments.*') ? 'text-terracotta-600' : 'text-charcoal-500 hover:text-terracotta-600'"
                    >
                        Tagihan Saya
                    </Link>
                    <Link
                        :href="route('maintenance-requests.index')"
                        class="text-sm font-medium"
                        :class="route().current('maintenance-requests.*') ? 'text-terracotta-600' : 'text-charcoal-500 hover:text-terracotta-600'"
                    >
                        Keluhan
                    </Link>
                    <Link
                        :href="route('profile.edit')"
                        class="text-sm font-medium"
                        :class="route().current('profile.edit') ? 'text-terracotta-600' : 'text-charcoal-500 hover:text-terracotta-600'"
                    >
                        Profil
                    </Link>
                    <Link :href="route('logout')" method="post" as="button" class="text-sm font-medium text-charcoal-500 hover:text-terracotta-600">
                        Keluar
                    </Link>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
            <div
                v-if="page.props.flash.success"
                class="mb-6 rounded-lg bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
            >
                {{ page.props.flash.success }}
            </div>

            <slot />
        </main>
    </div>
</template>
