<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { ROOM_STATUS_CLASS, ROOM_STATUS_LABEL, RoomStatusValue, formatIdr } from '@/lib/roomStatus';
import { Head, Link } from '@inertiajs/vue3';

interface RoomListItem {
    id: number;
    slug: string;
    name: string | null;
    room_number: string;
    status: RoomStatusValue;
    monthly_price: string;
    capacity: number;
    size_sqm: string | null;
    primary_image: { url: string } | null;
    room_type: { name: string } | null;
    property: { name: string; city: string } | null;
}

interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
}

defineProps<{ rooms: Paginated<RoomListItem> }>();
</script>

<template>
    <Head title="Katalog Kamar — Demera Living" />

    <PublicLayout>
        <section class="border-b border-beige-200 bg-beige-100 py-14">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <p class="text-sm font-semibold uppercase tracking-widest text-terracotta-500">Demera Living</p>
                <h1 class="mt-2 font-display text-3xl font-semibold text-charcoal-800 sm:text-4xl">Katalog Kamar</h1>
                <p class="mt-3 max-w-2xl text-sm text-charcoal-500">
                    Menampilkan {{ rooms.total }} kamar. Pencarian dan filter lanjutan akan hadir di tahap
                    pengembangan berikutnya — untuk saat ini Anda dapat menjelajahi seluruh kamar kami di bawah ini.
                </p>
            </div>
        </section>

        <section class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="room in rooms.data"
                        :key="room.id"
                        :href="route('living.rooms.show', room.slug)"
                        class="group overflow-hidden rounded-2xl border border-beige-200 bg-white shadow-soft transition hover:shadow-card"
                    >
                        <div class="relative aspect-[4/3] overflow-hidden bg-beige-200">
                            <img
                                v-if="room.primary_image"
                                :src="room.primary_image.url"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                :alt="room.name ?? room.room_number"
                            />
                            <span
                                class="absolute left-3 top-3 rounded-full px-2.5 py-1 text-xs font-semibold"
                                :class="ROOM_STATUS_CLASS[room.status]"
                            >
                                {{ ROOM_STATUS_LABEL[room.status] }}
                            </span>
                        </div>
                        <div class="p-5">
                            <p class="text-xs font-medium uppercase tracking-wide text-charcoal-400">{{ room.room_type?.name }}</p>
                            <h3 class="mt-1 font-display text-lg font-semibold text-charcoal-800">
                                {{ room.name ?? `Kamar ${room.room_number}` }}
                            </h3>
                            <p class="mt-1 text-sm text-charcoal-500">
                                {{ room.property?.city }} &middot; {{ room.capacity }} orang
                                <template v-if="room.size_sqm"> &middot; {{ room.size_sqm }} m&sup2;</template>
                            </p>
                            <p class="mt-3 font-display text-lg font-semibold text-terracotta-600">
                                {{ formatIdr(room.monthly_price) }}<span class="text-xs font-normal text-charcoal-400">/bulan</span>
                            </p>
                        </div>
                    </Link>
                </div>

                <p v-if="rooms.data.length === 0" class="py-16 text-center text-sm text-charcoal-400">
                    Belum ada kamar yang ditampilkan saat ini.
                </p>

                <Pagination :links="rooms.links" />
            </div>
        </section>
    </PublicLayout>
</template>
