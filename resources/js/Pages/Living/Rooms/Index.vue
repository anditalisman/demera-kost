<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { ROOM_STATUS_CLASS, ROOM_STATUS_LABEL, RoomStatusValue, formatIdr } from '@/lib/roomStatus';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

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

interface OptionItem {
    id: number;
    name: string;
}

const props = defineProps<{
    rooms: Paginated<RoomListItem>;
    roomTypes: OptionItem[];
    floors: { id: number; name: string; level: number; building_id: number }[];
    facilities: { id: number; name: string; type: string }[];
    statuses: { value: string; label: string }[];
    filters: {
        status?: string;
        min_price?: string;
        max_price?: string;
        room_type?: string;
        floor?: string;
        capacity?: string;
        facilities?: string[];
        sort?: string;
    };
}>();

const filterPanelOpen = ref(false);

const form = reactive({
    status: props.filters.status ?? '',
    min_price: props.filters.min_price ?? '',
    max_price: props.filters.max_price ?? '',
    room_type: props.filters.room_type ?? '',
    floor: props.filters.floor ?? '',
    capacity: props.filters.capacity ?? '',
    facilities: (props.filters.facilities ?? []).map(String),
    sort: props.filters.sort ?? 'newest',
});

function applyFilters() {
    router.get(route('living.rooms.index'), { ...form }, { preserveState: true, replace: true });
}

function resetFilters() {
    form.status = '';
    form.min_price = '';
    form.max_price = '';
    form.room_type = '';
    form.floor = '';
    form.capacity = '';
    form.facilities = [];
    form.sort = 'newest';
    applyFilters();
}
</script>

<template>
    <Head title="Katalog Kamar — Demera Living" />

    <PublicLayout>
        <section class="border-b border-beige-200 bg-beige-100 py-14">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <p class="text-sm font-semibold uppercase tracking-widest text-terracotta-500">Demera Living</p>
                <h1 class="mt-2 font-display text-3xl font-semibold text-charcoal-800 sm:text-4xl">Katalog Kamar</h1>
                <p class="mt-3 max-w-2xl text-sm text-charcoal-500">
                    Menampilkan {{ rooms.total }} kamar. Gunakan filter untuk menemukan kamar yang sesuai kebutuhan Anda.
                </p>
            </div>
        </section>

        <section class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between gap-4 lg:hidden">
                    <button
                        type="button"
                        class="rounded-lg border border-beige-300 px-4 py-2 text-sm font-medium text-charcoal-700"
                        @click="filterPanelOpen = !filterPanelOpen"
                    >
                        {{ filterPanelOpen ? 'Sembunyikan Filter' : 'Tampilkan Filter' }}
                    </button>
                </div>

                <div class="mt-6 grid gap-8 lg:grid-cols-4">
                    <aside class="lg:col-span-1" :class="filterPanelOpen ? 'block' : 'hidden lg:block'">
                        <div class="space-y-6 rounded-2xl border border-beige-200 bg-white p-5 shadow-soft">
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wide text-charcoal-500">Status</label>
                                <select v-model="form.status" class="mt-1 block w-full rounded-lg border-beige-300 text-sm focus:border-terracotta-400 focus:ring-terracotta-400">
                                    <option value="">Semua</option>
                                    <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wide text-charcoal-500">Rentang Harga (Rp/bulan)</label>
                                <div class="mt-1 flex items-center gap-2">
                                    <input v-model="form.min_price" type="number" min="0" placeholder="Min" class="w-full rounded-lg border-beige-300 text-sm focus:border-terracotta-400 focus:ring-terracotta-400" />
                                    <span class="text-charcoal-400">—</span>
                                    <input v-model="form.max_price" type="number" min="0" placeholder="Maks" class="w-full rounded-lg border-beige-300 text-sm focus:border-terracotta-400 focus:ring-terracotta-400" />
                                </div>
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wide text-charcoal-500">Tipe Kamar</label>
                                <select v-model="form.room_type" class="mt-1 block w-full rounded-lg border-beige-300 text-sm focus:border-terracotta-400 focus:ring-terracotta-400">
                                    <option value="">Semua tipe</option>
                                    <option v-for="rt in roomTypes" :key="rt.id" :value="rt.id">{{ rt.name }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wide text-charcoal-500">Lantai</label>
                                <select v-model="form.floor" class="mt-1 block w-full rounded-lg border-beige-300 text-sm focus:border-terracotta-400 focus:ring-terracotta-400">
                                    <option value="">Semua lantai</option>
                                    <option v-for="f in floors" :key="f.id" :value="f.id">{{ f.name }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wide text-charcoal-500">Kapasitas Minimal</label>
                                <input v-model="form.capacity" type="number" min="1" class="mt-1 block w-full rounded-lg border-beige-300 text-sm focus:border-terracotta-400 focus:ring-terracotta-400" />
                            </div>

                            <div v-if="facilities.length">
                                <label class="text-xs font-semibold uppercase tracking-wide text-charcoal-500">Fasilitas</label>
                                <div class="mt-2 max-h-40 space-y-2 overflow-y-auto">
                                    <label v-for="fac in facilities" :key="fac.id" class="flex items-center gap-2 text-sm text-charcoal-600">
                                        <input v-model="form.facilities" type="checkbox" :value="String(fac.id)" class="rounded border-beige-300 text-terracotta-500 focus:ring-terracotta-400" />
                                        {{ fac.name }}
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wide text-charcoal-500">Urutkan</label>
                                <select v-model="form.sort" class="mt-1 block w-full rounded-lg border-beige-300 text-sm focus:border-terracotta-400 focus:ring-terracotta-400">
                                    <option value="newest">Terbaru</option>
                                    <option value="price_asc">Harga Terendah</option>
                                    <option value="price_desc">Harga Tertinggi</option>
                                </select>
                            </div>

                            <div class="flex gap-2">
                                <button type="button" class="w-full rounded-lg bg-terracotta-500 px-4 py-2 text-sm font-semibold text-white hover:bg-terracotta-600" @click="applyFilters">
                                    Terapkan
                                </button>
                                <button type="button" class="w-full rounded-lg border border-beige-300 px-4 py-2 text-sm font-medium text-charcoal-600 hover:bg-cream-100" @click="resetFilters">
                                    Reset
                                </button>
                            </div>
                        </div>
                    </aside>

                    <div class="lg:col-span-3">
                        <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
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
                            Tidak ada kamar yang cocok dengan filter Anda.
                        </p>

                        <Pagination :links="rooms.links" />
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
