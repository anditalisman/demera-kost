<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatIdr } from '@/lib/roomStatus';
import { Head, Link } from '@inertiajs/vue3';

interface InvoiceItem {
    id: number;
    label: string;
    amount: string;
}
interface Invoice {
    id: number;
    invoice_number: string;
    status: string;
    total_amount: string;
    paid_amount: string;
    due_date: string;
    items: InvoiceItem[];
}
interface Guest {
    id: number;
    full_name: string;
    is_primary: boolean;
    relationship: string | null;
}
interface BookingDetail {
    id: number;
    booking_code: string;
    status: 'pending' | 'awaiting_payment' | 'confirmed' | 'expired' | 'cancelled' | 'converted_to_lease';
    start_date: string;
    duration_months: number;
    monthly_price: string;
    deposit_amount: string;
    admin_fee: string;
    total_amount: string;
    payment_due_at: string | null;
    room: {
        name: string | null;
        room_number: string;
        slug: string;
        primary_image: { url: string } | null;
        property: { name: string; city: string };
    };
    guests: Guest[];
    invoices: Invoice[];
}

defineProps<{ booking: BookingDetail }>();

const STATUS_LABEL: Record<string, string> = {
    pending: 'Menunggu Diproses',
    awaiting_payment: 'Menunggu Pembayaran',
    confirmed: 'Terkonfirmasi',
    expired: 'Kedaluwarsa',
    cancelled: 'Dibatalkan',
    converted_to_lease: 'Aktif Sebagai Kontrak Sewa',
};
const STATUS_CLASS: Record<string, string> = {
    pending: 'bg-amber-50 text-amber-700',
    awaiting_payment: 'bg-amber-50 text-amber-700',
    confirmed: 'bg-green-50 text-green-700',
    expired: 'bg-charcoal-100 text-charcoal-500',
    cancelled: 'bg-red-50 text-red-700',
    converted_to_lease: 'bg-green-50 text-green-700',
};
</script>

<template>
    <Head :title="`Pemesanan ${booking.booking_code} — Demera Living`" />

    <PublicLayout>
        <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-beige-200 bg-white p-6 shadow-soft sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-charcoal-400">Kode Pemesanan</p>
                        <h1 class="font-display text-2xl font-semibold text-charcoal-800">{{ booking.booking_code }}</h1>
                    </div>
                    <span class="rounded-full px-3 py-1 text-sm font-semibold" :class="STATUS_CLASS[booking.status]">
                        {{ STATUS_LABEL[booking.status] }}
                    </span>
                </div>

                <div class="mt-6 flex gap-4 border-t border-beige-100 pt-6">
                    <div class="h-16 w-16 shrink-0 overflow-hidden rounded-lg bg-beige-200">
                        <img v-if="booking.room.primary_image" :src="booking.room.primary_image.url" class="h-full w-full object-cover" />
                    </div>
                    <div>
                        <p class="font-medium text-charcoal-800">{{ booking.room.name ?? `Kamar ${booking.room.room_number}` }}</p>
                        <p class="text-sm text-charcoal-500">{{ booking.room.property.name }}, {{ booking.room.property.city }}</p>
                        <p class="mt-1 text-sm text-charcoal-500">
                            Mulai {{ booking.start_date }} &middot; {{ booking.duration_months }} bulan
                        </p>
                    </div>
                </div>

                <div class="mt-6 border-t border-beige-100 pt-6">
                    <h2 class="font-display text-lg font-semibold text-charcoal-800">Rincian Biaya</h2>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div v-for="item in booking.invoices[0]?.items ?? []" :key="item.id" class="flex justify-between">
                            <dt class="text-charcoal-500">{{ item.label }}</dt>
                            <dd class="font-medium text-charcoal-800">{{ formatIdr(item.amount) }}</dd>
                        </div>
                        <div class="flex justify-between border-t border-beige-100 pt-2 font-semibold">
                            <dt class="text-charcoal-800">Total</dt>
                            <dd class="text-terracotta-600">{{ formatIdr(booking.total_amount) }}</dd>
                        </div>
                    </dl>
                </div>

                <div v-if="booking.status === 'awaiting_payment' && booking.payment_due_at" class="mt-6 rounded-xl bg-amber-50 p-4 text-sm text-amber-800">
                    Selesaikan pembayaran sebelum <strong>{{ booking.payment_due_at }}</strong>, atau kamar akan dilepas
                    kembali secara otomatis. Fitur unggah bukti pembayaran tersedia di halaman ini setelah dirilis.
                </div>

                <div class="mt-6 border-t border-beige-100 pt-6">
                    <h2 class="font-display text-lg font-semibold text-charcoal-800">Data Penghuni</h2>
                    <ul class="mt-3 space-y-1 text-sm text-charcoal-600">
                        <li v-for="guest in booking.guests" :key="guest.id">
                            {{ guest.full_name }}
                            <span v-if="guest.is_primary" class="text-xs text-terracotta-500">(Penghuni Utama)</span>
                            <span v-else-if="guest.relationship" class="text-xs text-charcoal-400"> — {{ guest.relationship }}</span>
                        </li>
                    </ul>
                </div>

                <div class="mt-6 flex justify-end border-t border-beige-100 pt-6">
                    <Link :href="route('living.rooms.show', booking.room.slug)" class="text-sm font-medium text-terracotta-600 hover:underline">
                        Kembali ke Detail Kamar
                    </Link>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
