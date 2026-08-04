<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage<PageProps>();
</script>

<template>
    <div class="min-h-screen bg-cream-50">
        <nav class="border-b border-beige-200 bg-white">
            <div class="mx-auto flex h-16 max-w-5xl items-center justify-between px-4 sm:px-6">
                <Link :href="route('dashboard')">
                    <ApplicationLogo />
                </Link>

                <div class="flex items-center gap-4">
                    <Link :href="route('dashboard')" class="text-sm font-medium text-charcoal-500 hover:text-terracotta-600">
                        &larr; Kembali ke Dashboard
                    </Link>

                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button
                                type="button"
                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium text-charcoal-600 transition hover:text-terracotta-600 focus:outline-none"
                            >
                                {{ page.props.auth.user!.name }}
                                <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </template>

                        <template #content>
                            <DropdownLink :href="route('profile.edit')">Profil</DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">Keluar</DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </div>
        </nav>

        <header v-if="$slots.header" class="border-b border-beige-200 bg-white">
            <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6">
                <slot name="header" />
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
            <slot />
        </main>
    </div>
</template>
