<script setup lang="ts">
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';

const page = usePage<PageProps>();
</script>

<template>
    <Head title="Dashboard Saya" />

    <CustomerLayout>
        <h1 class="font-display text-2xl font-semibold text-charcoal-800">
            Halo, {{ page.props.auth.user?.name }}
        </h1>
        <p class="mt-2 max-w-xl text-sm text-charcoal-500">
            Jelajahi kamar yang tersedia, pantau tagihan dan status pembayaran pemesanan Anda di sini.
        </p>

        <div class="mt-8 grid gap-4 sm:grid-cols-3">
            <Link
                :href="route('living.rooms.index')"
                class="rounded-2xl border border-beige-200 bg-white p-6 shadow-soft transition hover:border-terracotta-300 hover:shadow-card"
            >
                <h3 class="font-display text-lg font-semibold text-charcoal-800">Jelajahi Kamar</h3>
                <p class="mt-2 text-sm text-charcoal-500">Lihat katalog kamar Demera Living yang tersedia.</p>
            </Link>
            <Link
                :href="route('invoices.index')"
                class="rounded-2xl border border-beige-200 bg-white p-6 shadow-soft transition hover:border-terracotta-300 hover:shadow-card"
            >
                <h3 class="font-display text-lg font-semibold text-charcoal-800">Tagihan Saya</h3>
                <p class="mt-2 text-sm text-charcoal-500">Lihat dan bayar tagihan pemesanan atau sewa Anda.</p>
            </Link>
            <Link
                :href="route('profile.edit')"
                class="rounded-2xl border border-beige-200 bg-white p-6 shadow-soft transition hover:border-terracotta-300 hover:shadow-card"
            >
                <h3 class="font-display text-lg font-semibold text-charcoal-800">Lengkapi Profil</h3>
                <p class="mt-2 text-sm text-charcoal-500">Perbarui data diri sebelum melakukan pemesanan.</p>
            </Link>
        </div>
    </CustomerLayout>
</template>
