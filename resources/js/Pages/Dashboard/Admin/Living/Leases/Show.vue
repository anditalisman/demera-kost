<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { formatIdr } from '@/lib/roomStatus';
import { formatDate } from '@/lib/date';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface ExtensionRow {
    id: number;
    previous_end_date: string;
    new_end_date: string;
    price_at_extension: string;
    created_at: string;
}
interface DepositRow {
    id: number;
    amount: string;
    status: string;
    returned_amount: string;
}
interface LeaseDetail {
    id: number;
    lease_number: string;
    status: string;
    start_date: string;
    end_date: string;
    duration_months: number;
    monthly_price: string;
    deposit_amount: string;
    billing_cycle_day: number;
    tenant: { user: { name: string; email: string } | null };
    room: { name: string | null; room_number: string; property: { name: string } };
    extensions: ExtensionRow[];
    deposits: DepositRow[];
}

const props = defineProps<{
    lease: LeaseDetail;
    availableRooms: { id: number; room_number: string; name: string | null; property: { name: string } }[];
}>();

const STATUS_LABEL: Record<string, string> = {
    draft: 'Draft', pending_approval: 'Menunggu Persetujuan', active: 'Aktif', ending_soon: 'Akan Berakhir',
    completed: 'Selesai', cancelled: 'Dibatalkan', extended: 'Diperpanjang',
};
const isActive = props.lease.status === 'active';

const extendForm = useForm({ additional_months: 1, new_monthly_price: '' as string | number, notes: '' });
function submitExtend() {
    extendForm.post(route('admin.leases.extend', props.lease.id), { preserveScroll: true });
}

const transferForm = useForm({ room_id: '' as number | string, reason: '' });
function submitTransfer() {
    transferForm.post(route('admin.leases.transfer', props.lease.id));
}

const terminateForm = useForm({ reason: '', returned_amount: props.lease.deposit_amount, deduction_notes: '' });
function submitTerminate() {
    if (!confirm('Akhiri sewa ini? Kamar akan dilepas kembali menjadi tersedia.')) return;
    terminateForm.post(route('admin.leases.terminate', props.lease.id), { preserveScroll: true });
}
</script>

<template>
    <Head :title="`Kontrak ${lease.lease_number}`" />

    <AdminLayout>
        <div class="flex items-center justify-between">
            <h1 class="font-display text-2xl font-semibold text-charcoal-800">{{ lease.lease_number }}</h1>
            <Link :href="route('admin.leases.index')" class="text-sm text-charcoal-500 hover:underline">Kembali</Link>
        </div>

        <div class="mt-6 rounded-xl border border-beige-200 bg-white p-5 shadow-soft">
            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                <div><dt class="text-charcoal-400">Penyewa</dt><dd class="font-medium text-charcoal-800">{{ lease.tenant.user?.name ?? '(akun dihapus)' }}</dd></div>
                <div><dt class="text-charcoal-400">Kamar</dt><dd>{{ lease.room.name ?? `Kamar ${lease.room.room_number}` }} — {{ lease.room.property.name }}</dd></div>
                <div><dt class="text-charcoal-400">Periode</dt><dd>{{ formatDate(lease.start_date) }} s.d. {{ formatDate(lease.end_date) }} ({{ lease.duration_months }} bulan)</dd></div>
                <div><dt class="text-charcoal-400">Harga/Bulan</dt><dd>{{ formatIdr(lease.monthly_price) }}</dd></div>
                <div><dt class="text-charcoal-400">Deposit</dt><dd>{{ formatIdr(lease.deposit_amount) }}</dd></div>
                <div><dt class="text-charcoal-400">Status</dt><dd>{{ STATUS_LABEL[lease.status] }}</dd></div>
            </dl>
        </div>

        <template v-if="isActive">
            <div class="mt-6 grid gap-6 lg:grid-cols-3">
                <form class="rounded-xl border border-beige-200 bg-white p-5 shadow-soft" @submit.prevent="submitExtend">
                    <h2 class="font-display text-sm font-semibold uppercase tracking-wide text-charcoal-500">Perpanjang Kontrak</h2>
                    <div class="mt-3 space-y-3">
                        <div>
                            <InputLabel value="Tambahan Bulan" />
                            <TextInput v-model.number="extendForm.additional_months" type="number" min="1" class="mt-1 block w-full" required />
                            <InputError :message="extendForm.errors.additional_months" />
                        </div>
                        <div>
                            <InputLabel value="Harga Baru (opsional)" />
                            <TextInput v-model="extendForm.new_monthly_price" type="number" min="0" class="mt-1 block w-full" />
                        </div>
                        <PrimaryButton :disabled="extendForm.processing" class="w-full justify-center">Perpanjang</PrimaryButton>
                    </div>
                </form>

                <form class="rounded-xl border border-beige-200 bg-white p-5 shadow-soft" @submit.prevent="submitTransfer">
                    <h2 class="font-display text-sm font-semibold uppercase tracking-wide text-charcoal-500">Pindah Kamar</h2>
                    <div class="mt-3 space-y-3">
                        <div>
                            <InputLabel value="Kamar Tujuan" />
                            <select v-model="transferForm.room_id" required class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400">
                                <option value="" disabled>Pilih kamar tersedia</option>
                                <option v-for="r in availableRooms" :key="r.id" :value="r.id">{{ r.name ?? `Kamar ${r.room_number}` }} — {{ r.property.name }}</option>
                            </select>
                            <InputError :message="transferForm.errors.room_id" />
                        </div>
                        <div>
                            <InputLabel value="Alasan (opsional)" />
                            <TextInput v-model="transferForm.reason" class="mt-1 block w-full" />
                        </div>
                        <PrimaryButton :disabled="transferForm.processing" class="w-full justify-center">Pindahkan</PrimaryButton>
                    </div>
                </form>

                <form class="rounded-xl border border-beige-200 bg-white p-5 shadow-soft" @submit.prevent="submitTerminate">
                    <h2 class="font-display text-sm font-semibold uppercase tracking-wide text-charcoal-500">Akhiri Sewa</h2>
                    <div class="mt-3 space-y-3">
                        <div>
                            <InputLabel value="Jumlah Deposit Dikembalikan" />
                            <TextInput v-model="terminateForm.returned_amount" type="number" min="0" class="mt-1 block w-full" required />
                        </div>
                        <div>
                            <InputLabel value="Catatan Potongan (opsional)" />
                            <TextInput v-model="terminateForm.deduction_notes" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="Alasan (opsional)" />
                            <TextInput v-model="terminateForm.reason" class="mt-1 block w-full" />
                        </div>
                        <PrimaryButton :disabled="terminateForm.processing" class="w-full justify-center bg-red-600 hover:bg-red-700 focus:bg-red-700">Akhiri Sewa</PrimaryButton>
                    </div>
                </form>
            </div>
        </template>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-beige-200 bg-white p-5 shadow-soft">
                <h2 class="font-display text-sm font-semibold uppercase tracking-wide text-charcoal-500">Riwayat Perpanjangan</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    <li v-for="ext in lease.extensions" :key="ext.id" class="rounded-lg bg-cream-50 px-3 py-2">
                        {{ formatDate(ext.previous_end_date) }} &rarr; {{ formatDate(ext.new_end_date) }} ({{ formatIdr(ext.price_at_extension) }}/bulan)
                    </li>
                    <li v-if="lease.extensions.length === 0" class="text-xs text-charcoal-400">Belum pernah diperpanjang.</li>
                </ul>
            </div>
            <div class="rounded-xl border border-beige-200 bg-white p-5 shadow-soft">
                <h2 class="font-display text-sm font-semibold uppercase tracking-wide text-charcoal-500">Deposit</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    <li v-for="dep in lease.deposits" :key="dep.id" class="rounded-lg bg-cream-50 px-3 py-2">
                        {{ formatIdr(dep.amount) }} — {{ dep.status }}
                        <span v-if="Number(dep.returned_amount) > 0"> (dikembalikan {{ formatIdr(dep.returned_amount) }})</span>
                    </li>
                    <li v-if="lease.deposits.length === 0" class="text-xs text-charcoal-400">Belum ada deposit.</li>
                </ul>
            </div>
        </div>
    </AdminLayout>
</template>
