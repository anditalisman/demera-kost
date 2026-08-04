<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    links: { url: string | null; label: string; active: boolean }[];
}>();
</script>

<template>
    <nav v-if="links.length > 3" class="mt-8 flex flex-wrap items-center justify-center gap-1">
        <template v-for="(link, index) in links" :key="index">
            <Link
                v-if="link.url"
                :href="link.url"
                preserve-scroll
                class="min-w-9 rounded-lg px-3 py-2 text-center text-sm font-medium transition"
                :class="link.active ? 'bg-terracotta-500 text-white' : 'text-charcoal-500 hover:bg-cream-100'"
                v-html="link.label"
            />
            <span
                v-else
                class="min-w-9 rounded-lg px-3 py-2 text-center text-sm font-medium text-charcoal-300"
                v-html="link.label"
            />
        </template>
    </nav>
</template>
