<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { waLink } from '@/lib/whatsapp';
import { ROOM_STATUS_CLASS, ROOM_STATUS_LABEL, RoomStatusValue, formatIdr } from '@/lib/roomStatus';
import { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

interface RoomImageItem {
    id: number;
    url: string;
    caption: string | null;
    is_primary: boolean;
}

interface FacilityItem {
    id: number;
    name: string;
    type: string;
}

interface RoomDetail {
    id: number;
    slug: string;
    name: string | null;
    room_number: string;
    status: RoomStatusValue;
    monthly_price: string;
    deposit_amount: string;
    additional_fees: { label: string; amount: number }[] | null;
    capacity: number;
    size_sqm: string | null;
    description: string | null;
    available_from: string | null;
    images: RoomImageItem[];
    facilities: FacilityItem[];
    room_type: { name: string } | null;
    property: { name: string; address: string; city: string; house_rules: string | null; contact_whatsapp: string | null };
    building: { name: string } | null;
    floor: { name: string } | null;
}

interface RelatedRoom {
    id: number;
    slug: string;
    name: string | null;
    room_number: string;
    monthly_price: string;
    primary_image: { url: string } | null;
    room_type: { name: string } | null;
}

const props = defineProps<{ room: RoomDetail; relatedRooms: RelatedRoom[] }>();
const page = usePage<PageProps>();

const lightboxIndex = ref<number | null>(null);
const isAvailable = props.room.status === 'available';

function openLightbox(index: number) {
    lightboxIndex.value = index;
}
function closeLightbox() {
    lightboxIndex.value = null;
}
function nextImage() {
    if (lightboxIndex.value === null) return;
    lightboxIndex.value = (lightboxIndex.value + 1) % props.room.images.length;
}
function prevImage() {
    if (lightboxIndex.value === null) return;
    lightboxIndex.value = (lightboxIndex.value - 1 + props.room.images.length) % props.room.images.length;
}
</script>

<template>
    <Head :title="`${room.name ?? 'Kamar ' + room.room_number} — Demera Living`" />

    <PublicLayout>
        <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
            <!-- Gallery -->
            <div class="grid gap-2 sm:grid-cols-4 sm:grid-rows-2">
                <button
                    v-for="(image, index) in room.images.slice(0, 5)"
                    :key="image.id"
                    class="overflow-hidden rounded-xl bg-beige-200"
                    :class="index === 0 ? 'sm:col-span-2 sm:row-span-2' : ''"
                    @click="openLightbox(index)"
                >
                    <img :src="image.url" :alt="image.caption ?? ''" class="h-full w-full object-cover" />
                </button>
                <div v-if="room.images.length === 0" class="col-span-4 flex h-64 items-center justify-center rounded-xl bg-beige-200 text-charcoal-400">
                    Belum ada foto
                </div>
            </div>

            <div class="mt-10 grid gap-10 lg:grid-cols-3">
                <!-- Main info -->
                <div class="lg:col-span-2">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-charcoal-400">{{ room.room_type?.name }}</p>
                            <h1 class="mt-1 font-display text-3xl font-semibold text-charcoal-800">
                                {{ room.name ?? `Kamar ${room.room_number}` }}
                            </h1>
                            <p class="mt-1 text-sm text-charcoal-500">
                                {{ room.property.name }}, {{ room.property.city }}
                                <template v-if="room.building"> &middot; {{ room.building.name }}</template>
                                <template v-if="room.floor"> &middot; {{ room.floor.name }}</template>
                            </p>
                        </div>
                        <span class="whitespace-nowrap rounded-full px-3 py-1 text-sm font-semibold" :class="ROOM_STATUS_CLASS[room.status]">
                            {{ ROOM_STATUS_LABEL[room.status] }}
                        </span>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-4 text-sm text-charcoal-600">
                        <span>{{ room.capacity }} orang</span>
                        <span v-if="room.size_sqm">{{ room.size_sqm }} m&sup2;</span>
                        <span v-if="room.available_from">Tersedia mulai {{ room.available_from }}</span>
                    </div>

                    <div v-if="room.description" class="prose prose-neutral mt-6 max-w-none text-sm">
                        <p>{{ room.description }}</p>
                    </div>

                    <div v-if="room.facilities.length" class="mt-8">
                        <h2 class="font-display text-lg font-semibold text-charcoal-800">Fasilitas</h2>
                        <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                            <span
                                v-for="facility in room.facilities"
                                :key="facility.id"
                                class="rounded-lg bg-cream-100 px-3 py-2 text-sm text-charcoal-600"
                            >
                                {{ facility.name }}
                            </span>
                        </div>
                    </div>

                    <div v-if="room.property.house_rules" class="mt-8">
                        <h2 class="font-display text-lg font-semibold text-charcoal-800">Peraturan Kost</h2>
                        <p class="mt-3 whitespace-pre-line text-sm text-charcoal-600">{{ room.property.house_rules }}</p>
                    </div>
                </div>

                <!-- Booking card -->
                <div class="lg:col-span-1">
                    <div class="sticky top-24 rounded-2xl border border-beige-200 bg-white p-6 shadow-soft">
                        <p class="font-display text-2xl font-semibold text-terracotta-600">
                            {{ formatIdr(room.monthly_price) }}
                            <span class="text-sm font-normal text-charcoal-400">/bulan</span>
                        </p>

                        <dl class="mt-4 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-charcoal-500">Deposit</dt>
                                <dd class="font-medium text-charcoal-800">{{ formatIdr(room.deposit_amount) }}</dd>
                            </div>
                            <div v-for="fee in room.additional_fees ?? []" :key="fee.label" class="flex justify-between">
                                <dt class="text-charcoal-500">{{ fee.label }}</dt>
                                <dd class="font-medium text-charcoal-800">{{ formatIdr(fee.amount) }}</dd>
                            </div>
                        </dl>

                        <p class="mt-4 text-xs text-charcoal-400">
                            Deposit dikembalikan penuh saat masa sewa berakhir sesuai kebijakan pembatalan, dikurangi
                            biaya kerusakan bila ada.
                        </p>

                        <Link
                            v-if="isAvailable"
                            :href="route('living.rooms.book', room.slug)"
                            class="mt-6 flex w-full items-center justify-center rounded-lg bg-terracotta-500 px-4 py-3 text-sm font-semibold text-white hover:bg-terracotta-600"
                        >
                            Pesan Sekarang
                        </Link>
                        <button
                            v-else
                            type="button"
                            disabled
                            class="mt-6 w-full cursor-not-allowed rounded-lg bg-beige-200 px-4 py-3 text-sm font-semibold text-charcoal-400"
                        >
                            Kamar Tidak Tersedia
                        </button>

                        <a
                            :href="waLink(room.property.contact_whatsapp ?? page.props.settings.whatsapp, `Halo, saya tertarik dengan ${room.name ?? 'Kamar ' + room.room_number} di ${room.property.name}.`)"
                            target="_blank"
                            rel="noopener"
                            class="mt-3 flex w-full items-center justify-center gap-2 rounded-lg bg-green-500 px-4 py-3 text-sm font-semibold text-white hover:bg-green-600"
                        >
                            Tanya via WhatsApp
                        </a>

                        <p v-if="!isAvailable" class="mt-3 text-center text-xs text-charcoal-400">
                            Kamar ini saat ini berstatus "{{ ROOM_STATUS_LABEL[room.status] }}".
                        </p>
                    </div>
                </div>
            </div>

            <!-- Related rooms -->
            <div v-if="relatedRooms.length" class="mt-16">
                <h2 class="font-display text-xl font-semibold text-charcoal-800">Kamar Lainnya</h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-3">
                    <Link
                        v-for="related in relatedRooms"
                        :key="related.id"
                        :href="route('living.rooms.show', related.slug)"
                        class="overflow-hidden rounded-xl border border-beige-200 bg-white shadow-soft transition hover:shadow-card"
                    >
                        <div class="aspect-[4/3] bg-beige-200">
                            <img v-if="related.primary_image" :src="related.primary_image.url" class="h-full w-full object-cover" />
                        </div>
                        <div class="p-4">
                            <p class="text-xs text-charcoal-400">{{ related.room_type?.name }}</p>
                            <p class="font-medium text-charcoal-800">{{ related.name ?? `Kamar ${related.room_number}` }}</p>
                            <p class="mt-1 text-sm font-semibold text-terracotta-600">{{ formatIdr(related.monthly_price) }}/bulan</p>
                        </div>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Lightbox -->
        <div
            v-if="lightboxIndex !== null"
            class="fixed inset-0 z-50 flex items-center justify-center bg-charcoal-900/90 p-4"
            @click.self="closeLightbox"
        >
            <button class="absolute right-6 top-6 text-white" @click="closeLightbox">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <button class="absolute left-4 text-white" @click="prevImage">
                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </button>
            <img :src="room.images[lightboxIndex].url" :alt="room.images[lightboxIndex].caption ?? ''" class="max-h-[85vh] max-w-full rounded-lg object-contain" />
            <button class="absolute right-4 text-white" @click="nextImage">
                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
        </div>
    </PublicLayout>
</template>
