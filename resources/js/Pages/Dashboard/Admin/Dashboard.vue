<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';

const page = usePage<PageProps>();
const can = (permission: string) => page.props.auth.permissions.includes(permission);

const cards = [
    { key: 'content.manage', label: 'Hero & Halaman', href: () => route('admin.content-pages.index'), desc: 'Kelola banner beranda, info bisnis, dan halaman kebijakan.' },
    { key: 'content.manage', label: 'Galeri', href: () => route('admin.galleries.index'), desc: 'Unggah dan atur urutan foto properti.' },
    { key: 'content.manage', label: 'Testimoni', href: () => route('admin.testimonials.index'), desc: 'Kelola testimoni penghuni yang tampil di beranda.' },
    { key: 'content.manage', label: 'FAQ', href: () => route('admin.faqs.index'), desc: 'Kelola pertanyaan yang sering diajukan.' },
    { key: 'settings.view', label: 'Pengaturan', href: () => route('admin.settings.index'), desc: 'Kontak, media sosial, dan SEO default.' },
    { key: 'users.view', label: 'Pengguna', href: () => route('admin.users.index'), desc: 'Kelola akun dan peran (role) pengguna.' },
];
</script>

<template>
    <Head title="Dashboard Admin" />

    <AdminLayout>
        <h1 class="font-display text-2xl font-semibold text-charcoal-800">
            Selamat datang, {{ page.props.auth.user?.name }}
        </h1>
        <p class="mt-1 text-sm text-charcoal-400">
            Fondasi Demera Living Tahap 1 sudah aktif. Modul kamar, pemesanan, tagihan, dan laporan menyusul di tahap berikutnya.
        </p>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="card in cards.filter((c) => can(c.key))"
                :key="card.label"
                :href="card.href()"
                class="rounded-2xl border border-beige-200 bg-white p-6 shadow-soft transition hover:border-terracotta-300 hover:shadow-card"
            >
                <h3 class="font-display text-lg font-semibold text-charcoal-800">{{ card.label }}</h3>
                <p class="mt-2 text-sm text-charcoal-500">{{ card.desc }}</p>
            </Link>
        </div>
    </AdminLayout>
</template>
