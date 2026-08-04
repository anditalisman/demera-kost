<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Pagination from '@/Components/Pagination.vue';
import { formatIdr } from '@/lib/roomStatus';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface PaymentRow {
    id: number;
    payment_code: string;
    method: 'manual_transfer' | 'qris' | 'virtual_account' | 'ewallet' | 'cash';
    amount: string;
    status: 'pending' | 'paid' | 'failed' | 'expired' | 'cancelled' | 'refunded' | 'partially_paid';
    created_at: string;
    invoice: {
        invoice_number: string;
        booking: { user: { name: string } } | null;
        tenant: { user: { name: string } } | null;
    };
}
interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{ payments: Paginated<PaymentRow>; filters: { status: string } }>();

const METHOD_LABEL: Record<string, string> = {
    manual_transfer: 'Transfer Bank',
    qris: 'QRIS',
    virtual_account: 'Virtual Account',
    ewallet: 'E-Wallet',
    cash: 'Tunai',
};
const STATUS_LABEL: Record<string, string> = {
    pending: 'Menunggu',
    paid: 'Lunas',
    failed: 'Ditolak',
    expired: 'Kedaluwarsa',
    cancelled: 'Dibatalkan',
    refunded: 'Dikembalikan',
    partially_paid: 'Sebagian',
};
const STATUS_CLASS: Record<string, string> = {
    pending: 'bg-amber-50 text-amber-700',
    paid: 'bg-green-50 text-green-700',
    failed: 'bg-red-50 text-red-700',
    expired: 'bg-charcoal-100 text-charcoal-500',
    cancelled: 'bg-charcoal-100 text-charcoal-500',
    refunded: 'bg-charcoal-100 text-charcoal-500',
    partially_paid: 'bg-amber-50 text-amber-700',
};

function customerName(payment: PaymentRow): string {
    return payment.invoice.booking?.user.name ?? payment.invoice.tenant?.user.name ?? '-';
}

function filterByStatus(status: string) {
    router.get(route('admin.payments.index'), { status }, { preserveState: true, replace: true });
}

function verify(payment: PaymentRow) {
    if (confirm(`Verifikasi pembayaran ${payment.payment_code} sebesar ${formatIdr(payment.amount)}?`)) {
        router.put(route('admin.payments.verify', payment.id));
    }
}

const showRejectModal = ref(false);
const rejecting = ref<PaymentRow | null>(null);
const rejectForm = useForm({ reason: '' });

function openReject(payment: PaymentRow) {
    rejecting.value = payment;
    rejectForm.reset();
    showRejectModal.value = true;
}
function submitReject() {
    rejectForm.put(route('admin.payments.reject', rejecting.value!.id), {
        onSuccess: () => (showRejectModal.value = false),
    });
}
</script>

<template>
    <Head title="Verifikasi Pembayaran" />

    <AdminLayout>
        <h1 class="font-display text-2xl font-semibold text-charcoal-800">Verifikasi Pembayaran</h1>
        <p class="mt-1 text-sm text-charcoal-400">Transfer bank manual maupun QRIS diverifikasi dengan cara yang sama.</p>

        <div class="mt-4 flex gap-2">
            <button
                v-for="s in ['pending', 'paid', 'failed', 'all']"
                :key="s"
                class="rounded-full px-3 py-1.5 text-xs font-medium"
                :class="filters.status === s ? 'bg-terracotta-500 text-white' : 'bg-cream-100 text-charcoal-600'"
                @click="filterByStatus(s)"
            >
                {{ s === 'all' ? 'Semua' : STATUS_LABEL[s] }}
            </button>
        </div>

        <div class="mt-4 overflow-x-auto rounded-xl border border-beige-200 bg-white shadow-soft">
            <table class="min-w-full divide-y divide-beige-100 text-sm">
                <thead class="bg-cream-50 text-xs uppercase tracking-wide text-charcoal-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Kode</th>
                        <th class="px-4 py-3 text-left">Invoice</th>
                        <th class="px-4 py-3 text-left">Pelanggan</th>
                        <th class="px-4 py-3 text-left">Metode</th>
                        <th class="px-4 py-3 text-left">Jumlah</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-beige-100">
                    <tr v-for="payment in payments.data" :key="payment.id">
                        <td class="px-4 py-3 font-medium text-charcoal-800">{{ payment.payment_code }}</td>
                        <td class="px-4 py-3 text-charcoal-500">{{ payment.invoice.invoice_number }}</td>
                        <td class="px-4 py-3 text-charcoal-500">{{ customerName(payment) }}</td>
                        <td class="px-4 py-3 text-charcoal-500">{{ METHOD_LABEL[payment.method] }}</td>
                        <td class="px-4 py-3 text-charcoal-600">{{ formatIdr(payment.amount) }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium" :class="STATUS_CLASS[payment.status]">
                                {{ STATUS_LABEL[payment.status] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a :href="route('admin.payments.proof', payment.id)" target="_blank" class="text-xs font-medium text-terracotta-600 hover:underline">Lihat Bukti</a>
                            <template v-if="payment.status === 'pending'">
                                <button class="ml-3 text-xs font-medium text-green-700 hover:underline" @click="verify(payment)">Verifikasi</button>
                                <button class="ml-3 text-xs font-medium text-red-600 hover:underline" @click="openReject(payment)">Tolak</button>
                            </template>
                        </td>
                    </tr>
                    <tr v-if="payments.data.length === 0">
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-charcoal-400">Tidak ada pembayaran.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="payments.links" />

        <Modal :show="showRejectModal" @close="showRejectModal = false" max-width="md">
            <form class="p-6" @submit.prevent="submitReject">
                <h2 class="font-display text-lg font-semibold text-charcoal-800">Tolak Pembayaran {{ rejecting?.payment_code }}</h2>
                <div class="mt-4">
                    <InputLabel value="Alasan Penolakan" />
                    <textarea v-model="rejectForm.reason" rows="3" required class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400" />
                    <InputError :message="rejectForm.errors.reason" />
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="showRejectModal = false">Batal</SecondaryButton>
                    <PrimaryButton :disabled="rejectForm.processing">Tolak Pembayaran</PrimaryButton>
                </div>
            </form>
        </Modal>
    </AdminLayout>
</template>
