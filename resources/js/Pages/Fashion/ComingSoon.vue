<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

interface Category {
    name: string;
    description: string;
}

interface Product {
    name: string;
    category: string;
    slug: string;
}

const props = defineProps<{
    categories: Category[];
    products: Product[];
    singleProduct?: Product;
}>();

const form = useForm({
    name: '',
    email: '',
    whatsapp_number: '',
});

function submit() {
    form.post(route('fashion.subscribe'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <Head title="Demera Fashion — Segera Hadir" />

    <PublicLayout>
        <!-- Editorial hero -->
        <section class="bg-charcoal-900 py-24 text-cream-50">
            <div class="mx-auto max-w-4xl px-4 text-center sm:px-6">
                <span class="inline-block rounded-full border border-cream-50/30 px-4 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-cream-100">
                    Segera Hadir
                </span>
                <h1 class="mt-6 font-display text-4xl font-semibold leading-tight sm:text-5xl">
                    Demera Fashion
                </h1>
                <p class="mx-auto mt-5 max-w-xl text-cream-200">
                    Koleksi editorial dan minimalis yang dirancang untuk gaya hidup modern.
                    Kami sedang menyiapkan pengalaman belanja terbaik untuk Anda.
                </p>
            </div>
        </section>

        <!-- Notify me -->
        <section class="border-b border-beige-200 bg-white py-14">
            <div class="mx-auto max-w-lg px-4 text-center sm:px-6">
                <h2 class="font-display text-2xl font-semibold text-charcoal-800">Jadi yang Pertama Tahu</h2>
                <p class="mt-2 text-sm text-charcoal-500">
                    Daftarkan email atau nomor WhatsApp Anda, kami kabari begitu Demera Fashion resmi meluncur.
                </p>

                <form class="mt-6 space-y-3 text-left" @submit.prevent="submit">
                    <div>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Nama (opsional)"
                            class="w-full rounded-lg border-beige-300 text-sm focus:border-charcoal-500 focus:ring-charcoal-500"
                        />
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="Alamat email"
                            class="w-full rounded-lg border-beige-300 text-sm focus:border-charcoal-500 focus:ring-charcoal-500"
                        />
                        <input
                            v-model="form.whatsapp_number"
                            type="text"
                            placeholder="Nomor WhatsApp"
                            class="w-full rounded-lg border-beige-300 text-sm focus:border-charcoal-500 focus:ring-charcoal-500"
                        />
                    </div>
                    <p v-if="form.errors.email" class="text-xs text-red-600">{{ form.errors.email }}</p>
                    <p v-if="form.errors.whatsapp_number" class="text-xs text-red-600">{{ form.errors.whatsapp_number }}</p>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-lg bg-charcoal-800 px-5 py-3 text-sm font-semibold text-white transition hover:bg-charcoal-700 disabled:opacity-50"
                    >
                        Beri Tahu Saya Saat Peluncuran
                    </button>

                    <p v-if="form.recentlySuccessful" class="text-center text-sm font-medium text-green-600">
                        Terima kasih! Kami akan mengabari Anda.
                    </p>
                </form>
            </div>
        </section>

        <!-- Preview categories -->
        <section class="py-16">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <p class="text-center text-sm font-semibold uppercase tracking-widest text-charcoal-400">Pratinjau Kategori</p>
                <h2 class="mt-2 text-center font-display text-3xl font-semibold text-charcoal-800">Yang Akan Datang</h2>

                <div class="mt-10 grid gap-6 sm:grid-cols-3">
                    <div
                        v-for="category in categories"
                        :key="category.name"
                        class="group relative overflow-hidden rounded-2xl border border-beige-200 bg-beige-100 p-8 text-center"
                    >
                        <div class="absolute inset-0 flex items-center justify-center bg-charcoal-900/0 transition group-hover:bg-charcoal-900/5" />
                        <h3 class="font-display text-lg font-semibold text-charcoal-800">{{ category.name }}</h3>
                        <p class="mt-2 text-sm text-charcoal-500">{{ category.description }}</p>
                        <span class="mt-4 inline-block rounded-full bg-charcoal-800 px-3 py-1 text-[10px] font-semibold uppercase tracking-wider text-cream-50">
                            Segera Hadir
                        </span>
                    </div>
                </div>

                <div v-if="products.length" class="mt-14">
                    <p class="text-center text-sm font-semibold uppercase tracking-widest text-charcoal-400">Pratinjau Produk</p>
                    <div class="mt-8 grid gap-6 sm:grid-cols-3">
                        <div
                            v-for="product in products"
                            :key="product.slug"
                            class="overflow-hidden rounded-2xl border border-beige-200"
                        >
                            <div class="flex aspect-[3/4] items-center justify-center bg-beige-200 text-charcoal-400">
                                <span class="text-xs font-medium uppercase tracking-widest">Pratinjau</span>
                            </div>
                            <div class="p-4">
                                <p class="text-xs uppercase tracking-wide text-charcoal-400">{{ product.category }}</p>
                                <h3 class="mt-1 font-medium text-charcoal-800">{{ product.name }}</h3>
                                <span class="mt-2 inline-block text-xs font-semibold text-terracotta-500">Segera Hadir</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
