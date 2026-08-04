<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{ status: number }>();

const content = computed(() => {
    return (
        {
            404: {
                title: 'Halaman Tidak Ditemukan',
                message: 'Halaman yang Anda cari tidak ada atau sudah dipindahkan.',
            },
            403: {
                title: 'Akses Ditolak',
                message: 'Anda tidak memiliki izin untuk mengakses halaman ini.',
            },
            419: {
                title: 'Sesi Berakhir',
                message: 'Halaman ini sudah kedaluwarsa. Silakan muat ulang dan coba lagi.',
            },
            500: {
                title: 'Terjadi Kesalahan',
                message: 'Terjadi kesalahan pada server kami. Tim kami sudah diberi tahu — silakan coba lagi sesaat lagi.',
            },
            503: {
                title: 'Sedang Pemeliharaan',
                message: 'Demera sedang dalam pemeliharaan singkat. Silakan kembali beberapa saat lagi.',
            },
        }[props.status] ?? {
            title: 'Terjadi Kesalahan',
            message: 'Sesuatu tidak berjalan sesuai rencana.',
        }
    );
});
</script>

<template>
    <Head :title="content.title" />

    <div class="flex min-h-screen flex-col items-center justify-center bg-cream-50 px-4 text-center">
        <Link href="/" class="mb-8">
            <ApplicationLogo />
        </Link>

        <p class="font-display text-7xl font-semibold text-terracotta-400">{{ status }}</p>
        <h1 class="mt-4 font-display text-2xl font-semibold text-charcoal-800">{{ content.title }}</h1>
        <p class="mt-3 max-w-md text-sm text-charcoal-500">{{ content.message }}</p>

        <Link
            href="/"
            class="mt-8 inline-flex items-center rounded-lg bg-terracotta-500 px-6 py-3 text-sm font-semibold text-white transition hover:bg-terracotta-600"
        >
            Kembali ke Beranda
        </Link>
    </div>
</template>
