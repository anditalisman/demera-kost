<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';

interface PropertyItem {
    id: number;
    name: string;
    address: string;
    city: string;
    province: string;
}

defineProps<{ properties: PropertyItem[] }>();
const page = usePage<PageProps>();
</script>

<template>
    <Head title="Lokasi — Demera Living" />

    <PublicLayout>
        <section class="border-b border-beige-200 bg-beige-100 py-14">
            <div class="mx-auto max-w-4xl px-4 text-center sm:px-6">
                <p class="text-sm font-semibold uppercase tracking-widest text-terracotta-500">Demera Living</p>
                <h1 class="mt-2 font-display text-3xl font-semibold text-charcoal-800">Lokasi</h1>
            </div>
        </section>

        <section class="py-14">
            <div class="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
                <div class="space-y-6">
                    <div v-for="property in properties" :key="property.id" class="rounded-xl border border-beige-200 bg-white p-5">
                        <h2 class="font-display text-lg font-semibold text-charcoal-800">{{ property.name }}</h2>
                        <p class="mt-2 text-sm text-charcoal-500">{{ property.address }}, {{ property.city }}, {{ property.province }}</p>
                    </div>
                    <p v-if="properties.length === 0" class="text-sm text-charcoal-400">Informasi lokasi belum tersedia.</p>
                </div>

                <div class="overflow-hidden rounded-2xl border border-beige-200 shadow-soft">
                    <iframe
                        v-if="page.props.settings.mapEmbedUrl"
                        :src="page.props.settings.mapEmbedUrl"
                        class="h-96 w-full"
                        style="border: 0"
                        loading="lazy"
                    />
                    <div v-else class="flex h-96 w-full items-center justify-center bg-beige-100 text-sm text-charcoal-400">
                        Peta lokasi akan tampil setelah diatur oleh admin.
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
