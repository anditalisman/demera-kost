<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';

interface GalleryItem {
    id: number;
    title: string | null;
    image_url: string;
    thumbnail_url: string | null;
    caption: string | null;
}

const props = defineProps<{ galleries: Record<string, GalleryItem[]> }>();

const CATEGORY_LABELS: Record<string, string> = {
    property: 'Properti & Eksterior',
    room_common_area: 'Ruang Bersama',
    facility: 'Fasilitas',
    event: 'Kegiatan',
    company: 'Perusahaan',
};
</script>

<template>
    <Head title="Galeri — Demera Living" />

    <PublicLayout>
        <section class="border-b border-beige-200 bg-beige-100 py-14">
            <div class="mx-auto max-w-4xl px-4 text-center sm:px-6">
                <p class="text-sm font-semibold uppercase tracking-widest text-terracotta-500">Demera Living</p>
                <h1 class="mt-2 font-display text-3xl font-semibold text-charcoal-800">Galeri</h1>
            </div>
        </section>

        <section class="py-12">
            <div class="mx-auto max-w-7xl space-y-12 px-4 sm:px-6 lg:px-8">
                <div v-for="(items, category) in galleries" :key="category">
                    <h2 class="font-display text-xl font-semibold text-charcoal-800">{{ CATEGORY_LABELS[category] ?? category }}</h2>
                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div v-for="item in items" :key="item.id" class="overflow-hidden rounded-xl bg-beige-200">
                            <img :src="item.thumbnail_url ?? item.image_url" :alt="item.title ?? ''" class="h-40 w-full object-cover" />
                        </div>
                    </div>
                </div>

                <p v-if="Object.keys(galleries).length === 0" class="text-center text-sm text-charcoal-400">
                    Galeri belum tersedia.
                </p>
            </div>
        </section>
    </PublicLayout>
</template>
