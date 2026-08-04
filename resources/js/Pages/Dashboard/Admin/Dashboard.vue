<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatIdr } from '@/lib/roomStatus';
import { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Stats {
    rooms: { total: number; available: number; occupied: number; held: number; maintenance: number; occupancyRate: number };
    tenants: { prospective: number; active: number };
    bookingsAwaitingPayment: number;
    paymentsPendingVerification: number;
    invoicesDue: number;
    invoicesOverdue: number;
    revenueThisMonth: number;
}
interface LeaseEndingSoon {
    id: number;
    lease_number: string;
    tenant_name: string;
    room_label: string;
    end_date: string;
}
interface FailedNotification {
    id: number;
    channel: string;
    recipient: string | null;
    error_message: string | null;
    attempts: number;
}

const props = defineProps<{
    stats: Stats;
    revenueTrend: { label: string; total: number }[];
    leasesEndingSoon: LeaseEndingSoon[];
    failedNotifications: FailedNotification[];
}>();

const page = usePage<PageProps>();
const can = (permission: string) => page.props.auth.permissions.includes(permission);

const maxRevenue = computed(() => Math.max(...props.revenueTrend.map((r) => r.total), 1));

const cards = [
    { key: 'rooms.view', label: 'Kamar', href: () => route('admin.rooms.index'), desc: 'Kelola properti, tipe kamar, dan kamar.' },
    { key: 'bookings.view', label: 'Booking', href: () => route('admin.bookings.index'), desc: 'Pantau dan kelola pemesanan.' },
    { key: 'tenants.view', label: 'Penyewa', href: () => route('admin.tenants.index'), desc: 'Kelola penyewa dan kontrak sewa.' },
    { key: 'invoices.view', label: 'Invoice', href: () => route('admin.invoices.index'), desc: 'Lihat dan kelola tagihan.' },
    { key: 'payments.view', label: 'Pembayaran', href: () => route('admin.payments.index'), desc: 'Verifikasi bukti pembayaran.' },
    { key: 'maintenance.view', label: 'Keluhan', href: () => route('admin.maintenance-requests.index'), desc: 'Tanggapi keluhan dan perawatan.' },
    { key: 'reports.view', label: 'Laporan', href: () => route('admin.reports.index'), desc: 'Okupansi, pendapatan, dan laporan lainnya.' },
    { key: 'content.manage', label: 'Hero & Halaman', href: () => route('admin.content-pages.index'), desc: 'Kelola banner beranda, info bisnis, dan halaman kebijakan.' },
    { key: 'content.manage', label: 'Galeri', href: () => route('admin.galleries.index'), desc: 'Unggah dan atur urutan foto properti.' },
    { key: 'content.manage', label: 'Testimoni', href: () => route('admin.testimonials.index'), desc: 'Kelola testimoni penghuni yang tampil di beranda.' },
    { key: 'content.manage', label: 'FAQ', href: () => route('admin.faqs.index'), desc: 'Kelola pertanyaan yang sering diajukan.' },
    { key: 'settings.view', label: 'Pengaturan', href: () => route('admin.settings.index'), desc: 'Kontak, pembayaran, dan SEO default.' },
    { key: 'users.view', label: 'Pengguna', href: () => route('admin.users.index'), desc: 'Kelola akun dan peran (role) pengguna.' },
    { key: 'audit-logs.view', label: 'Audit Log', href: () => route('admin.audit-logs.index'), desc: 'Riwayat aktivitas penting di seluruh sistem.' },
];
</script>

<template>
    <Head title="Dashboard Admin" />

    <AdminLayout>
        <h1 class="font-display text-2xl font-semibold text-charcoal-800">
            Selamat datang, {{ page.props.auth.user?.name }}
        </h1>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-beige-200 bg-white p-4 shadow-soft">
                <p class="text-xs font-medium uppercase tracking-wide text-charcoal-400">Okupansi</p>
                <p class="mt-1 font-display text-2xl font-semibold text-charcoal-800">{{ stats.rooms.occupancyRate }}%</p>
                <p class="mt-1 text-xs text-charcoal-500">{{ stats.rooms.occupied }} terisi dari {{ stats.rooms.total }} kamar</p>
            </div>
            <div class="rounded-xl border border-beige-200 bg-white p-4 shadow-soft">
                <p class="text-xs font-medium uppercase tracking-wide text-charcoal-400">Kamar Tersedia</p>
                <p class="mt-1 font-display text-2xl font-semibold text-charcoal-800">{{ stats.rooms.available }}</p>
                <p class="mt-1 text-xs text-charcoal-500">{{ stats.rooms.held }} ditahan &middot; {{ stats.rooms.maintenance }} perawatan</p>
            </div>
            <div class="rounded-xl border border-beige-200 bg-white p-4 shadow-soft">
                <p class="text-xs font-medium uppercase tracking-wide text-charcoal-400">Penyewa Aktif</p>
                <p class="mt-1 font-display text-2xl font-semibold text-charcoal-800">{{ stats.tenants.active }}</p>
                <p class="mt-1 text-xs text-charcoal-500">{{ stats.tenants.prospective }} calon penyewa</p>
            </div>
            <div class="rounded-xl border border-beige-200 bg-white p-4 shadow-soft">
                <p class="text-xs font-medium uppercase tracking-wide text-charcoal-400">Pendapatan Bulan Ini</p>
                <p class="mt-1 font-display text-2xl font-semibold text-terracotta-600">{{ formatIdr(stats.revenueThisMonth) }}</p>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-amber-700">Booking Menunggu Bayar</p>
                <p class="mt-1 font-display text-2xl font-semibold text-amber-800">{{ stats.bookingsAwaitingPayment }}</p>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-amber-700">Bukti Bayar Menunggu Verifikasi</p>
                <p class="mt-1 font-display text-2xl font-semibold text-amber-800">{{ stats.paymentsPendingVerification }}</p>
            </div>
            <div class="rounded-xl border border-beige-200 bg-white p-4 shadow-soft">
                <p class="text-xs font-medium uppercase tracking-wide text-charcoal-400">Tagihan Belum Lunas</p>
                <p class="mt-1 font-display text-2xl font-semibold text-charcoal-800">{{ stats.invoicesDue }}</p>
            </div>
            <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-red-700">Tagihan Terlambat</p>
                <p class="mt-1 font-display text-2xl font-semibold text-red-800">{{ stats.invoicesOverdue }}</p>
            </div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <div class="rounded-xl border border-beige-200 bg-white p-5 shadow-soft lg:col-span-2">
                <h2 class="font-display text-sm font-semibold uppercase tracking-wide text-charcoal-500">Tren Pendapatan (6 Bulan)</h2>
                <div class="mt-4 flex items-end gap-3" style="height: 140px">
                    <div v-for="point in revenueTrend" :key="point.label" class="flex flex-1 flex-col items-center justify-end gap-2">
                        <div
                            class="w-full rounded-t bg-terracotta-400"
                            :style="{ height: `${Math.max((point.total / maxRevenue) * 100, 2)}%` }"
                            :title="formatIdr(point.total)"
                        />
                        <span class="text-xs text-charcoal-400">{{ point.label }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-beige-200 bg-white p-5 shadow-soft">
                <h2 class="font-display text-sm font-semibold uppercase tracking-wide text-charcoal-500">Kontrak Akan Berakhir</h2>
                <ul class="mt-3 space-y-2">
                    <li v-for="lease in leasesEndingSoon" :key="lease.id" class="rounded-lg bg-cream-50 px-3 py-2 text-sm">
                        <p class="font-medium text-charcoal-800">{{ lease.tenant_name }}</p>
                        <p class="text-xs text-charcoal-500">{{ lease.room_label }} &middot; berakhir {{ lease.end_date }}</p>
                    </li>
                    <li v-if="leasesEndingSoon.length === 0" class="text-xs text-charcoal-400">Tidak ada kontrak yang akan berakhir dalam 30 hari.</li>
                </ul>
            </div>
        </div>

        <div v-if="failedNotifications.length" class="mt-6 rounded-xl border border-red-200 bg-red-50 p-5">
            <h2 class="font-display text-sm font-semibold uppercase tracking-wide text-red-700">Notifikasi Gagal Terkirim</h2>
            <ul class="mt-3 space-y-2 text-sm text-red-800">
                <li v-for="log in failedNotifications" :key="log.id">
                    {{ log.channel }} ke {{ log.recipient }} — {{ log.error_message ?? 'Gagal' }} (percobaan ke-{{ log.attempts }})
                </li>
            </ul>
        </div>

        <h2 class="mt-10 font-display text-lg font-semibold text-charcoal-800">Menu Cepat</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
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
