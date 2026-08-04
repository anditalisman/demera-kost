<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

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

    <div class="min-h-screen bg-cream-50">
        <header class="border-b border-beige-200 bg-white">
            <div class="mx-auto flex max-w-3xl items-center justify-between px-6 py-4">
                <Link href="/" class="font-display text-xl font-semibold text-charcoal-800">
                    Demera<span class="text-terracotta-500">.</span>
                </Link>
                <Link href="/" class="text-sm text-charcoal-500 hover:text-terracotta-600">
                    &larr; Kembali ke Beranda
                </Link>
            </div>
        </header>

        <main class="mx-auto max-w-3xl px-6 py-12">
            <h1 class="font-display text-3xl font-semibold text-charcoal-800">
                {{ page.title }}
            </h1>
            <p v-if="page.subtitle" class="mt-2 text-charcoal-500">{{ page.subtitle }}</p>

            <article
                class="prose prose-neutral mt-8 max-w-none prose-headings:font-display prose-a:text-terracotta-600"
                v-html="page.body"
            />
        </main>
    </div>
</template>
