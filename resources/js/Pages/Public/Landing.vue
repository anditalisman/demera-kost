<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { waLink } from '@/lib/whatsapp';
import { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

interface ContentPageData {
    id: number;
    key: string | null;
    title: string | null;
    subtitle: string | null;
    body: string | null;
    cta_label: string | null;
    cta_url: string | null;
    image_url: string | null;
}

interface RoomCard {
    id: number;
    slug: string;
    name: string | null;
    room_number: string;
    monthly_price: string;
    capacity: number;
    size_sqm: string | null;
    primary_image: { url: string } | null;
    room_type: { name: string } | null;
    property: { name: string; city: string } | null;
}

interface GalleryItem {
    id: number;
    title: string | null;
    image_url: string;
    thumbnail_url: string | null;
    caption: string | null;
}

interface TestimonialItem {
    id: number;
    author_name: string;
    author_role: string | null;
    author_photo_url: string | null;
    rating: number | null;
    content: string;
}

interface FaqItem {
    id: number;
    question: string;
    answer: string;
}

const props = defineProps<{
    heroSlides: ContentPageData[];
    businessInfo: Record<string, ContentPageData>;
    featuredRooms: RoomCard[];
    galleries: GalleryItem[];
    testimonials: TestimonialItem[];
    faqs: FaqItem[];
}>();

const page = usePage<PageProps>();

const hero = props.heroSlides[0] ?? null;
const openFaqId = ref<number | null>(props.faqs[0]?.id ?? null);

function formatPrice(value: string): string {
    const num = Number(value);
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);
}

const livingRoomsUrl = route().has('living.rooms.index') ? route('living.rooms.index') : '/living/rooms';
const livingUrl = route().has('living.index') ? route('living.index') : '/living';
const fashionUrl = route().has('fashion.index') ? route('fashion.index') : '/fashion';
</script>

<template>
    <Head title="Demera — Fashion & Living" />

    <PublicLayout>
        <!-- Hero -->
        <section class="relative overflow-hidden bg-gradient-to-b from-beige-100 via-cream-50 to-cream-50">
            <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
                <div class="mx-auto max-w-3xl text-center">
                    <p class="text-sm font-semibold uppercase tracking-widest text-terracotta-500">
                        {{ hero?.subtitle ?? 'Demera Fashion & Demera Living' }}
                    </p>
                    <h1 class="mt-4 font-display text-4xl font-semibold leading-tight text-charcoal-800 sm:text-5xl">
                        {{ hero?.title ?? 'Gaya hidup modern, hunian yang terasa seperti rumah.' }}
                    </h1>
                    <p class="mt-6 text-lg text-charcoal-500">
                        {{ hero?.body ?? 'Demera menghadirkan dua dunia dalam satu platform: fashion editorial yang akan segera hadir, dan kost nyaman terpercaya yang siap Anda huni hari ini.' }}
                    </p>
                </div>

                <!-- Two business cards -->
                <div class="mx-auto mt-14 grid max-w-4xl gap-6 sm:grid-cols-2">
                    <Link
                        :href="livingUrl"
                        class="group relative overflow-hidden rounded-3xl bg-charcoal-800 p-8 text-white shadow-card transition hover:-translate-y-1"
                    >
                        <p class="text-xs font-semibold uppercase tracking-widest text-terracotta-300">Tersedia Sekarang</p>
                        <h2 class="mt-3 font-display text-2xl font-semibold">Find Your Room with Demera Living</h2>
                        <p class="mt-3 text-sm text-cream-200">
                            Cari dan pesan kamar kost nyaman, aman, dan siap huni.
                        </p>
                        <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-terracotta-300 group-hover:gap-3">
                            Jelajahi Kamar
                            <svg class="h-4 w-4 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                        </span>
                    </Link>

                    <Link
                        :href="fashionUrl"
                        class="group relative overflow-hidden rounded-3xl border border-beige-300 bg-white p-8 shadow-card transition hover:-translate-y-1"
                    >
                        <p class="text-xs font-semibold uppercase tracking-widest text-charcoal-400">Segera Hadir</p>
                        <h2 class="mt-3 font-display text-2xl font-semibold text-charcoal-800">Explore Demera Fashion</h2>
                        <p class="mt-3 text-sm text-charcoal-500">
                            Koleksi fashion editorial dan minimalis — daftar untuk info peluncuran.
                        </p>
                        <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-charcoal-700 group-hover:gap-3">
                            Lihat Pratinjau
                            <svg class="h-4 w-4 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                        </span>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Business summary -->
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-2">
                <div class="rounded-3xl border border-beige-200 bg-white p-8">
                    <h3 class="font-display text-xl font-semibold text-charcoal-800">
                        {{ businessInfo['living']?.title ?? 'Demera Living' }}
                    </h3>
                    <p class="mt-3 text-sm leading-relaxed text-charcoal-500">
                        {{ businessInfo['living']?.body ?? 'Kost yang hangat, nyaman, dan terpercaya — dilengkapi fasilitas lengkap dan proses sewa yang transparan dari pemesanan hingga pembayaran.' }}
                    </p>
                </div>
                <div class="rounded-3xl border border-beige-200 bg-cream-100 p-8">
                    <h3 class="font-display text-xl font-semibold text-charcoal-800">
                        {{ businessInfo['fashion']?.title ?? 'Demera Fashion' }}
                    </h3>
                    <p class="mt-3 text-sm leading-relaxed text-charcoal-500">
                        {{ businessInfo['fashion']?.body ?? 'Lini fashion editorial dan minimalis Demera sedang dipersiapkan. Daftarkan diri Anda untuk menjadi yang pertama tahu saat kami meluncur.' }}
                    </p>
                </div>
            </div>
        </section>

        <!-- Featured rooms -->
        <section v-if="featuredRooms.length" class="bg-white py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-widest text-terracotta-500">Kamar Unggulan</p>
                        <h2 class="mt-2 font-display text-3xl font-semibold text-charcoal-800">Kamar yang Tersedia Sekarang</h2>
                    </div>
                    <Link :href="livingRoomsUrl" class="hidden text-sm font-semibold text-terracotta-600 hover:underline sm:block">
                        Lihat Semua &rarr;
                    </Link>
                </div>

                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="room in featuredRooms"
                        :key="room.id"
                        :href="route().has('living.rooms.show') ? route('living.rooms.show', room.slug) : livingRoomsUrl"
                        class="group overflow-hidden rounded-2xl border border-beige-200 bg-cream-50 shadow-soft transition hover:shadow-card"
                    >
                        <div class="aspect-[4/3] overflow-hidden bg-beige-200">
                            <img
                                v-if="room.primary_image"
                                :src="room.primary_image.url"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                :alt="room.name ?? room.room_number"
                            />
                        </div>
                        <div class="p-5">
                            <p class="text-xs font-medium uppercase tracking-wide text-charcoal-400">{{ room.room_type?.name }}</p>
                            <h3 class="mt-1 font-display text-lg font-semibold text-charcoal-800">
                                {{ room.name ?? `Kamar ${room.room_number}` }}
                            </h3>
                            <p class="mt-1 text-sm text-charcoal-500">{{ room.property?.city }} &middot; {{ room.capacity }} orang</p>
                            <p class="mt-3 font-display text-lg font-semibold text-terracotta-600">
                                {{ formatPrice(room.monthly_price) }}<span class="text-xs font-normal text-charcoal-400">/bulan</span>
                            </p>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Gallery -->
        <section v-if="galleries.length" class="py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <p class="text-sm font-semibold uppercase tracking-widest text-terracotta-500">Galeri</p>
                <h2 class="mt-2 font-display text-3xl font-semibold text-charcoal-800">Suasana Demera Living</h2>

                <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div
                        v-for="(item, index) in galleries"
                        :key="item.id"
                        class="overflow-hidden rounded-xl bg-beige-200"
                        :class="index === 0 ? 'col-span-2 row-span-2' : ''"
                    >
                        <img :src="item.thumbnail_url ?? item.image_url" :alt="item.title ?? ''" class="h-full w-full object-cover" />
                    </div>
                </div>
            </div>
        </section>

        <!-- Keunggulan -->
        <section class="bg-charcoal-800 py-16 text-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <p class="text-sm font-semibold uppercase tracking-widest text-terracotta-300">Keunggulan</p>
                <h2 class="mt-2 font-display text-3xl font-semibold">Kenapa Memilih Demera Living</h2>

                <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="feature in [
                        { title: 'Lokasi Strategis', desc: 'Dekat kampus, perkantoran, dan transportasi umum.' },
                        { title: 'Keamanan Terjaga', desc: 'Akses terkontrol dan pengawasan area properti.' },
                        { title: 'Fasilitas Lengkap', desc: 'Kamar dan ruang bersama yang nyaman digunakan.' },
                        { title: 'Proses Transparan', desc: 'Booking, tagihan, dan pembayaran jelas dari awal.' },
                    ]" :key="feature.title">
                        <h3 class="font-display text-lg font-semibold">{{ feature.title }}</h3>
                        <p class="mt-2 text-sm text-cream-200">{{ feature.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section v-if="testimonials.length" class="py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <p class="text-sm font-semibold uppercase tracking-widest text-terracotta-500">Testimoni</p>
                <h2 class="mt-2 font-display text-3xl font-semibold text-charcoal-800">Kata Mereka Tentang Demera</h2>

                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="t in testimonials"
                        :key="t.id"
                        class="rounded-2xl border border-beige-200 bg-white p-6 shadow-soft"
                    >
                        <div class="flex items-center gap-3">
                            <img
                                v-if="t.author_photo_url"
                                :src="t.author_photo_url"
                                class="h-10 w-10 rounded-full object-cover"
                                :alt="t.author_name"
                            />
                            <div v-else class="flex h-10 w-10 items-center justify-center rounded-full bg-beige-200 font-semibold text-charcoal-600">
                                {{ t.author_name.charAt(0) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-charcoal-800">{{ t.author_name }}</p>
                                <p class="text-xs text-charcoal-400">{{ t.author_role }}</p>
                            </div>
                        </div>
                        <p class="mt-4 text-sm leading-relaxed text-charcoal-600">&ldquo;{{ t.content }}&rdquo;</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <section v-if="faqs.length" class="bg-white py-16">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <p class="text-center text-sm font-semibold uppercase tracking-widest text-terracotta-500">FAQ</p>
                <h2 class="mt-2 text-center font-display text-3xl font-semibold text-charcoal-800">Pertanyaan Umum</h2>

                <div class="mt-10 divide-y divide-beige-200 rounded-2xl border border-beige-200">
                    <div v-for="faq in faqs" :key="faq.id" class="p-5">
                        <button
                            class="flex w-full items-center justify-between text-left"
                            @click="openFaqId = openFaqId === faq.id ? null : faq.id"
                        >
                            <span class="font-medium text-charcoal-800">{{ faq.question }}</span>
                            <svg
                                class="h-5 w-5 shrink-0 text-charcoal-400 transition"
                                :class="openFaqId === faq.id ? 'rotate-180' : ''"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <p v-if="openFaqId === faq.id" class="mt-3 text-sm leading-relaxed text-charcoal-500">
                            {{ faq.answer }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Location & contact -->
        <section class="py-16">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-widest text-terracotta-500">Lokasi</p>
                    <h2 class="mt-2 font-display text-3xl font-semibold text-charcoal-800">Kunjungi Demera Living</h2>
                    <p class="mt-4 text-sm text-charcoal-500">{{ page.props.settings.address }}</p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a
                            :href="waLink(page.props.settings.whatsapp, 'Halo Demera, saya ingin bertanya seputar kamar yang tersedia.')"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center gap-2 rounded-lg bg-green-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-600"
                        >
                            Chat via WhatsApp
                        </a>
                        <Link
                            :href="route('register')"
                            class="inline-flex items-center gap-2 rounded-lg border border-beige-300 bg-white px-5 py-2.5 text-sm font-semibold text-charcoal-700 hover:bg-cream-100"
                        >
                            Daftar Akun
                        </Link>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-beige-200 shadow-soft">
                    <iframe
                        v-if="page.props.settings.mapEmbedUrl"
                        :src="page.props.settings.mapEmbedUrl"
                        class="h-80 w-full"
                        style="border: 0"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                    />
                    <div v-else class="flex h-80 w-full items-center justify-center bg-beige-100 text-sm text-charcoal-400">
                        Peta lokasi akan tampil setelah diatur oleh admin.
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
