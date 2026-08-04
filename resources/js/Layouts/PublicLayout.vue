<script setup lang="ts">
import PublicHeader from '@/Components/layout/PublicHeader.vue';
import PublicFooter from '@/Components/layout/PublicFooter.vue';
import WhatsAppFloatingButton from '@/Components/layout/WhatsAppFloatingButton.vue';
import { PageProps } from '@/types';
import { usePage } from '@inertiajs/vue3';

const page = usePage<PageProps>();
</script>

<template>
    <div class="flex min-h-screen flex-col bg-cream-50">
        <div
            v-if="page.props.flash.success"
            class="bg-green-50 px-4 py-2 text-center text-sm font-medium text-green-700"
        >
            {{ page.props.flash.success }}
        </div>

        <PublicHeader />

        <main class="flex-1">
            <slot />
        </main>

        <PublicFooter />
        <WhatsAppFloatingButton />
    </div>
</template>
