<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

interface FaqItem {
    id: number;
    question: string;
    answer: string;
}

const props = defineProps<{ faqs: FaqItem[] }>();
const openId = ref<number | null>(props.faqs[0]?.id ?? null);
</script>

<template>
    <Head title="FAQ — Demera Living" />

    <PublicLayout>
        <section class="border-b border-beige-200 bg-beige-100 py-14">
            <div class="mx-auto max-w-4xl px-4 text-center sm:px-6">
                <p class="text-sm font-semibold uppercase tracking-widest text-terracotta-500">Demera Living</p>
                <h1 class="mt-2 font-display text-3xl font-semibold text-charcoal-800">Pertanyaan Umum</h1>
            </div>
        </section>

        <section class="py-14">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="divide-y divide-beige-200 rounded-2xl border border-beige-200 bg-white">
                    <div v-for="faq in faqs" :key="faq.id" class="p-5">
                        <button class="flex w-full items-center justify-between text-left" @click="openId = openId === faq.id ? null : faq.id">
                            <span class="font-medium text-charcoal-800">{{ faq.question }}</span>
                            <svg class="h-5 w-5 shrink-0 text-charcoal-400 transition" :class="openId === faq.id ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <p v-if="openId === faq.id" class="mt-3 text-sm text-charcoal-500">{{ faq.answer }}</p>
                    </div>
                </div>
                <p v-if="faqs.length === 0" class="text-center text-sm text-charcoal-400">Belum ada FAQ.</p>
            </div>
        </section>
    </PublicLayout>
</template>
