<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatIdr } from '@/lib/roomStatus';
import { Head, Link } from '@inertiajs/vue3';

interface ContentPageData {
    title: string | null;
    body: string | null;
}

interface RoomCard {
    id: number;
    slug: string;
    name: string | null;
    room_number: string;
    monthly_price: string;
    primary_image: { url: string } | null;
    room_type: { name: string } | null;
    property: { city: string } | null;
}

interface TestimonialItem {
    id: number;
    author_name: string;
    content: string;
}

defineProps<{
    intro: ContentPageData | null;
    availableCount: number;
    featuredRooms: RoomCard[];
    testimonials: TestimonialItem[];
}>();

const links = [
    { label: 'Katalog Kamar', href: () => route('living.rooms.index') },
    { label: 'Galeri', href: () => route('living.gallery') },
    { label: 'Fasilitas', href: () => route('living.facilities') },
    { label: 'Lokasi', href: () => route('living.location') },
    { label: 'FAQ', href: () => route('living.faq') },
    { label: 'Kontak', href: () => route('living.contact') },
];
</script>

<template>
    <Head title="Demera Living" />

    <PublicLayout>
        <section class="border-b border-beige-200 bg-beige-100 py-16">
            <div class="mx-auto max-w-4xl px-4 text-center sm:px-6">
                <p class="text-sm font-semibold uppercase tracking-widest text-terracotta-500">Demera Living</p>
                <h1 class="mt-3 font-display text-4xl font-semibold text-charcoal-800">
                    {{ intro?.title ?? 'Kost Nyaman, Aman, dan Terpercaya' }}
                </h1>
                <p class="mt-4 text-charcoal-500">
                    {{ intro?.body ?? `Saat ini tersedia ${availableCount} kamar siap huni. Jelajahi katalog, fasilitas, dan lokasi kami di bawah ini.` }}
                </p>
                <Link :href="route('living.rooms.index')" class="mt-6 inline-flex rounded-lg bg-terracotta-500 px-6 py-3 text-sm font-semibold text-white hover:bg-terracotta-600">
                    Lihat Katalog Kamar
                </Link>
            </div>
        </section>

        <section class="py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    <Link
                        v-for="link in links"
                        :key="link.label"
                        :href="link.href()"
                        class="rounded-xl border border-beige-200 bg-white p-4 text-center text-sm font-medium text-charcoal-600 shadow-soft transition hover:border-terracotta-300 hover:text-terracotta-600"
                    >
                        {{ link.label }}
                    </Link>
                </div>
            </div>
        </section>

        <section v-if="featuredRooms.length" class="bg-white py-14">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="font-display text-2xl font-semibold text-charcoal-800">Kamar Tersedia</h2>
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="room in featuredRooms"
                        :key="room.id"
                        :href="route('living.rooms.show', room.slug)"
                        class="overflow-hidden rounded-xl border border-beige-200 shadow-soft transition hover:shadow-card"
                    >
                        <div class="aspect-[4/3] bg-beige-200">
                            <img v-if="room.primary_image" :src="room.primary_image.url" class="h-full w-full object-cover" />
                        </div>
                        <div class="p-4">
                            <p class="text-xs text-charcoal-400">{{ room.room_type?.name }} &middot; {{ room.property?.city }}</p>
                            <p class="mt-1 font-medium text-charcoal-800">{{ room.name ?? `Kamar ${room.room_number}` }}</p>
                            <p class="mt-1 text-sm font-semibold text-terracotta-600">{{ formatIdr(room.monthly_price) }}/bulan</p>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <section v-if="testimonials.length" class="py-14">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-center font-display text-2xl font-semibold text-charcoal-800">Kata Penghuni Kami</h2>
                <div class="mt-6 grid gap-5 sm:grid-cols-3">
                    <div v-for="t in testimonials" :key="t.id" class="rounded-xl border border-beige-200 bg-white p-5">
                        <p class="text-sm text-charcoal-600">&ldquo;{{ t.content }}&rdquo;</p>
                        <p class="mt-3 text-sm font-semibold text-charcoal-800">{{ t.author_name }}</p>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
