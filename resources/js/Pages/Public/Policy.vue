<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    page: {
        title: string;
        subtitle?: string;
        body?: string;
        published_at?: string;
    };
}>();
</script>

<template>
    <Head :title="page.title" />

    <PublicLayout>
        <div class="mx-auto max-w-3xl px-6 py-16">
            <h1 class="font-display text-3xl font-semibold text-charcoal-800">
                {{ page.title }}
            </h1>
            <p v-if="page.subtitle" class="mt-2 text-charcoal-500">{{ page.subtitle }}</p>

            <article
                class="prose prose-neutral mt-8 max-w-none prose-headings:font-display prose-a:text-terracotta-600"
                v-html="page.body"
            />
        </div>
    </PublicLayout>
</template>
