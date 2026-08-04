<script setup lang="ts">
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import { formatIdr } from '@/lib/roomStatus';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface InvoiceSummary {
    id: number;
    invoice_number: string;
    total_amount: string;
    paid_amount: string;
}

const props = defineProps<{
    invoice: InvoiceSummary;
    bank: { name: string | null; account_number: string | null; account_holder: string | null };
    qrisImageUrl: string | null;
}>();

const method = ref<'manual_transfer' | 'qris'>('manual_transfer');

const form = useForm({
    method: 'manual_transfer' as 'manual_transfer' | 'qris',
    proof: null as File | null,
});

function selectMethod(value: 'manual_transfer' | 'qris') {
    method.value = value;
    form.method = value;
}

function submit() {
    form.post(route('payments.store', props.invoice.id), {
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="Bayar Tagihan" />

    <CustomerLayout>
        <div class="mx-auto max-w-2xl rounded-2xl border border-beige-200 bg-white p-6 shadow-soft sm:p-8">
            <h1 class="font-display text-2xl font-semibold text-charcoal-800">Bayar Tagihan {{ invoice.invoice_number }}</h1>
            <p class="mt-1 text-sm text-charcoal-500">
                Sisa tagihan: <span class="font-semibold text-terracotta-600">{{ formatIdr(Number(invoice.total_amount) - Number(invoice.paid_amount)) }}</span>
            </p>

            <div class="mt-6 flex gap-3">
                <button
                    type="button"
                    class="flex-1 rounded-lg border px-4 py-3 text-sm font-medium"
                    :class="method === 'manual_transfer' ? 'border-terracotta-400 bg-terracotta-50 text-terracotta-700' : 'border-beige-300 text-charcoal-600'"
                    @click="selectMethod('manual_transfer')"
                >
                    Transfer Bank
                </button>
                <button
                    type="button"
                    class="flex-1 rounded-lg border px-4 py-3 text-sm font-medium"
                    :class="method === 'qris' ? 'border-terracotta-400 bg-terracotta-50 text-terracotta-700' : 'border-beige-300 text-charcoal-600'"
                    @click="selectMethod('qris')"
                >
                    QRIS
                </button>
            </div>

            <div v-if="method === 'manual_transfer'" class="mt-6 rounded-xl bg-cream-50 p-4 text-sm">
                <p class="text-charcoal-500">Transfer ke rekening berikut, lalu unggah bukti transfer:</p>
                <dl class="mt-3 space-y-1">
                    <div class="flex justify-between"><dt class="text-charcoal-500">Bank</dt><dd class="font-medium text-charcoal-800">{{ bank.name ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-charcoal-500">Nomor Rekening</dt><dd class="font-medium text-charcoal-800">{{ bank.account_number ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-charcoal-500">Atas Nama</dt><dd class="font-medium text-charcoal-800">{{ bank.account_holder ?? '-' }}</dd></div>
                </dl>
            </div>

            <div v-else class="mt-6 rounded-xl bg-cream-50 p-4 text-center text-sm">
                <p class="text-charcoal-500">Pindai kode QRIS berikut, lalu unggah bukti pembayaran:</p>
                <img v-if="qrisImageUrl" :src="qrisImageUrl" class="mx-auto mt-3 h-56 w-56 object-contain" />
                <p v-else class="mt-3 text-charcoal-400">Gambar QRIS belum tersedia. Silakan gunakan transfer bank.</p>
            </div>

            <form class="mt-6" @submit.prevent="submit">
                <label class="block text-sm font-medium text-charcoal-700">Unggah Bukti Pembayaran</label>
                <input
                    type="file"
                    accept=".jpg,.jpeg,.png,.pdf"
                    required
                    class="mt-2 block w-full text-sm"
                    @change="form.proof = ($event.target as HTMLInputElement).files?.[0] ?? null"
                />
                <InputError class="mt-2" :message="form.errors.proof" />

                <PrimaryButton :disabled="form.processing" class="mt-6 w-full justify-center">Kirim Bukti Pembayaran</PrimaryButton>
            </form>
        </div>
    </CustomerLayout>
</template>
