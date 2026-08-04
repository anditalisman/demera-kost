<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { formatIdr } from '@/lib/roomStatus';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface GuestRow {
    id: number;
    full_name: string;
    is_primary: boolean;
}
interface BookingDetail {
    id: number;
    booking_code: string;
    status: string;
    start_date: string;
    duration_months: number;
    total_amount: string;
    payment_due_at: string | null;
    user: { name: string; email: string };
    room: { name: string | null; room_number: string; property: { name: string } };
    guests: GuestRow[];
    invoices: { id: number; invoice_number: string; status: string; total_amount: string }[];
}

const props = defineProps<{ booking: BookingDetail }>();

const STATUS_LABEL: Record<string, string> = {
    pending: 'Menunggu Diproses', awaiting_payment: 'Menunggu Pembayaran', confirmed: 'Terkonfirmasi',
    expired: 'Kedaluwarsa', cancelled: 'Dibatalkan', converted_to_lease: 'Menjadi Kontrak Sewa',
};
const canDecide = props.booking.status === 'awaiting_payment' || props.booking.status === 'pending';

function approve() {
    if (confirm('Setujui booking ini secara manual? Gunakan hanya bila pembayaran sudah diterima di luar sistem (mis. tunai).')) {
        router.put(route('admin.bookings.approve', props.booking.id));
    }
}

const showRejectModal = ref(false);
const rejectForm = useForm({ reason: '' });
function submitReject() {
    rejectForm.put(route('admin.bookings.reject', props.booking.id), { onSuccess: () => (showRejectModal.value = false) });
}
</script>

<template>
    <Head :title="`Booking ${booking.booking_code}`" />

    <AdminLayout>
        <div class="flex items-center justify-between">
            <h1 class="font-display text-2xl font-semibold text-charcoal-800">{{ booking.booking_code }}</h1>
            <Link :href="route('admin.bookings.index')" class="text-sm text-charcoal-500 hover:underline">Kembali</Link>
        </div>

        <div class="mt-6 rounded-xl border border-beige-200 bg-white p-5 shadow-soft">
            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                <div><dt class="text-charcoal-400">Pelanggan</dt><dd class="font-medium text-charcoal-800">{{ booking.user.name }} ({{ booking.user.email }})</dd></div>
                <div><dt class="text-charcoal-400">Kamar</dt><dd>{{ booking.room.name ?? `Kamar ${booking.room.room_number}` }} — {{ booking.room.property.name }}</dd></div>
                <div><dt class="text-charcoal-400">Mulai Sewa</dt><dd>{{ booking.start_date }} ({{ booking.duration_months }} bulan)</dd></div>
                <div><dt class="text-charcoal-400">Total</dt><dd>{{ formatIdr(booking.total_amount) }}</dd></div>
                <div><dt class="text-charcoal-400">Status</dt><dd>{{ STATUS_LABEL[booking.status] }}</dd></div>
                <div v-if="booking.payment_due_at"><dt class="text-charcoal-400">Batas Bayar</dt><dd>{{ booking.payment_due_at }}</dd></div>
            </dl>

            <div class="mt-4 border-t border-beige-100 pt-4">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-charcoal-500">Penghuni</h3>
                <ul class="mt-2 text-sm text-charcoal-600">
                    <li v-for="g in booking.guests" :key="g.id">{{ g.full_name }}<span v-if="g.is_primary" class="text-xs text-terracotta-500"> (Utama)</span></li>
                </ul>
            </div>

            <div class="mt-4 border-t border-beige-100 pt-4">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-charcoal-500">Invoice</h3>
                <ul class="mt-2 space-y-1 text-sm">
                    <li v-for="inv in booking.invoices" :key="inv.id">
                        <Link :href="route('admin.invoices.show', inv.id)" class="text-terracotta-600 hover:underline">{{ inv.invoice_number }}</Link>
                        — {{ formatIdr(inv.total_amount) }} ({{ inv.status }})
                    </li>
                </ul>
            </div>

            <div v-if="canDecide" class="mt-6 flex gap-3 border-t border-beige-100 pt-4">
                <PrimaryButton @click="approve">Setujui Manual</PrimaryButton>
                <SecondaryButton @click="showRejectModal = true">Tolak Booking</SecondaryButton>
            </div>
        </div>

        <Modal :show="showRejectModal" @close="showRejectModal = false" max-width="md">
            <form class="p-6" @submit.prevent="submitReject">
                <h2 class="font-display text-lg font-semibold text-charcoal-800">Tolak Booking {{ booking.booking_code }}</h2>
                <div class="mt-4">
                    <InputLabel value="Alasan Penolakan" />
                    <textarea v-model="rejectForm.reason" rows="3" required class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400" />
                    <InputError :message="rejectForm.errors.reason" />
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="showRejectModal = false">Batal</SecondaryButton>
                    <PrimaryButton :disabled="rejectForm.processing">Tolak Booking</PrimaryButton>
                </div>
            </form>
        </Modal>
    </AdminLayout>
</template>
