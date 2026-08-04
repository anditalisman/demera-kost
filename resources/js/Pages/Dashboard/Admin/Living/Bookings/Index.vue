<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { formatIdr } from '@/lib/roomStatus';
import { Head, Link, router } from '@inertiajs/vue3';

interface BookingRow {
    id: number;
    booking_code: string;
    status: string;
    total_amount: string;
    payment_due_at: string | null;
    user: { name: string };
    room: { name: string | null; room_number: string; property: { name: string } };
}
interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
}

defineProps<{ bookings: Paginated<BookingRow>; filters: { status?: string } }>();

const STATUS_LABEL: Record<string, string> = {
    pending: 'Menunggu Diproses', awaiting_payment: 'Menunggu Pembayaran', confirmed: 'Terkonfirmasi',
    expired: 'Kedaluwarsa', cancelled: 'Dibatalkan', converted_to_lease: 'Menjadi Kontrak Sewa',
};
const STATUS_CLASS: Record<string, string> = {
    pending: 'bg-amber-50 text-amber-700', awaiting_payment: 'bg-amber-50 text-amber-700',
    confirmed: 'bg-green-50 text-green-700', expired: 'bg-charcoal-100 text-charcoal-500',
    cancelled: 'bg-red-50 text-red-700', converted_to_lease: 'bg-green-50 text-green-700',
};

function filterByStatus(status: string) {
    router.get(route('admin.bookings.index'), { status: status || undefined }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Booking" />

    <AdminLayout>
        <h1 class="font-display text-2xl font-semibold text-charcoal-800">Booking</h1>

        <div class="mt-4 flex flex-wrap gap-2">
            <button
                v-for="s in ['', 'awaiting_payment', 'confirmed', 'converted_to_lease', 'expired', 'cancelled']"
                :key="s"
                class="rounded-full px-3 py-1.5 text-xs font-medium"
                :class="(filters.status ?? '') === s ? 'bg-terracotta-500 text-white' : 'bg-cream-100 text-charcoal-600'"
                @click="filterByStatus(s)"
            >
                {{ s === '' ? 'Semua' : STATUS_LABEL[s] }}
            </button>
        </div>

        <div class="mt-4 overflow-x-auto rounded-xl border border-beige-200 bg-white shadow-soft">
            <table class="min-w-full divide-y divide-beige-100 text-sm">
                <thead class="bg-cream-50 text-xs uppercase tracking-wide text-charcoal-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Kode</th>
                        <th class="px-4 py-3 text-left">Pelanggan</th>
                        <th class="px-4 py-3 text-left">Kamar</th>
                        <th class="px-4 py-3 text-left">Total</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-beige-100">
                    <tr v-for="booking in bookings.data" :key="booking.id">
                        <td class="px-4 py-3 font-medium text-charcoal-800">{{ booking.booking_code }}</td>
                        <td class="px-4 py-3 text-charcoal-500">{{ booking.user.name }}</td>
                        <td class="px-4 py-3 text-charcoal-500">{{ booking.room.name ?? `Kamar ${booking.room.room_number}` }}</td>
                        <td class="px-4 py-3 text-charcoal-600">{{ formatIdr(booking.total_amount) }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium" :class="STATUS_CLASS[booking.status]">{{ STATUS_LABEL[booking.status] }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="route('admin.bookings.show', booking.id)" class="text-xs font-medium text-terracotta-600 hover:underline">Detail</Link>
                        </td>
                    </tr>
                    <tr v-if="bookings.data.length === 0">
                        <td colspan="6" class="px-4 py-6 text-center text-sm text-charcoal-400">Belum ada booking.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="bookings.links" />
    </AdminLayout>
</template>
