<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';

interface FacilityItem {
    id: number;
    name: string;
    description: string | null;
}

defineProps<{ roomFacilities: FacilityItem[]; sharedFacilities: FacilityItem[] }>();
</script>

<template>
    <Head title="Fasilitas — Demera Living" />

    <PublicLayout>
        <section class="border-b border-beige-200 bg-beige-100 py-14">
            <div class="mx-auto max-w-4xl px-4 text-center sm:px-6">
                <p class="text-sm font-semibold uppercase tracking-widest text-terracotta-500">Demera Living</p>
                <h1 class="mt-2 font-display text-3xl font-semibold text-charcoal-800">Fasilitas</h1>
            </div>
        </section>

        <section class="py-14">
            <div class="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
                <div>
                    <h2 class="font-display text-xl font-semibold text-charcoal-800">Fasilitas Kamar</h2>
                    <ul class="mt-4 space-y-2">
                        <li v-for="f in roomFacilities" :key="f.id" class="flex items-center gap-2 text-sm text-charcoal-600">
                            <span class="h-1.5 w-1.5 rounded-full bg-terracotta-500" /> {{ f.name }}
                        </li>
                    </ul>
                </div>
                <div>
                    <h2 class="font-display text-xl font-semibold text-charcoal-800">Fasilitas Bersama</h2>
                    <ul class="mt-4 space-y-2">
                        <li v-for="f in sharedFacilities" :key="f.id" class="flex items-center gap-2 text-sm text-charcoal-600">
                            <span class="h-1.5 w-1.5 rounded-full bg-terracotta-500" /> {{ f.name }}
                        </li>
                    </ul>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
