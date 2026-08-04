<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { formatIdr } from '@/lib/roomStatus';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface RoomSummary {
    id: number;
    slug: string;
    name: string | null;
    room_number: string;
    monthly_price: string;
    deposit_amount: string;
    primary_image: { url: string } | null;
    room_type: { name: string } | null;
    property: { name: string; city: string } | null;
}

const props = defineProps<{
    room: RoomSummary;
    holdHours: number;
    adminFee: number;
    defaultGuest: { full_name: string; phone: string | null; email: string };
    hasIdentityDocumentOnFile: boolean;
}>();

const form = useForm({
    room_id: props.room.id,
    start_date: new Date().toISOString().slice(0, 10),
    duration_months: 1,
    notes: '',
    identity_document: null as File | null,
    terms_accepted: false as boolean,
    guests: [
        {
            full_name: props.defaultGuest.full_name,
            identity_number: '',
            phone: props.defaultGuest.phone ?? '',
            email: props.defaultGuest.email,
            relationship: '',
        },
    ],
});

const totalDue = computed(() => Number(props.room.monthly_price) + Number(props.room.deposit_amount) + props.adminFee);

function addGuest() {
    form.guests.push({ full_name: '', identity_number: '', phone: '', email: '', relationship: '' });
}
function removeGuest(index: number) {
    if (form.guests.length > 1) form.guests.splice(index, 1);
}

function submit() {
    form.post(route('bookings.store'), { forceFormData: true });
}
</script>

<template>
    <Head :title="`Pesan ${room.name ?? 'Kamar ' + room.room_number} — Demera Living`" />

    <PublicLayout>
        <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
            <h1 class="font-display text-2xl font-semibold text-charcoal-800 sm:text-3xl">Pemesanan Kamar</h1>

            <div class="mt-6 grid gap-8 lg:grid-cols-3">
                <form class="space-y-6 lg:col-span-2" @submit.prevent="submit">
                    <div class="rounded-2xl border border-beige-200 bg-white p-6 shadow-soft">
                        <h2 class="font-display text-lg font-semibold text-charcoal-800">Detail Sewa</h2>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel value="Tanggal Mulai Sewa" />
                                <TextInput v-model="form.start_date" type="date" class="mt-1 block w-full" required />
                                <InputError :message="form.errors.start_date" />
                            </div>
                            <div>
                                <InputLabel value="Durasi (bulan)" />
                                <TextInput v-model.number="form.duration_months" type="number" min="1" max="24" class="mt-1 block w-full" required />
                                <InputError :message="form.errors.duration_months" />
                            </div>
                        </div>
                        <InputError class="mt-2" :message="(form.errors as any).room" />
                    </div>

                    <div class="rounded-2xl border border-beige-200 bg-white p-6 shadow-soft">
                        <div class="flex items-center justify-between">
                            <h2 class="font-display text-lg font-semibold text-charcoal-800">Data Penghuni</h2>
                            <button type="button" class="text-xs font-medium text-terracotta-600 hover:underline" @click="addGuest">+ Tambah Penghuni</button>
                        </div>
                        <div v-for="(guest, index) in form.guests" :key="index" class="mt-4 grid gap-3 rounded-lg bg-cream-50 p-4 sm:grid-cols-2">
                            <div class="sm:col-span-2 flex items-center justify-between">
                                <span class="text-xs font-semibold uppercase tracking-wide text-charcoal-500">
                                    {{ index === 0 ? 'Penghuni Utama' : `Penghuni ${index + 1}` }}
                                </span>
                                <button v-if="index > 0" type="button" class="text-xs text-red-600 hover:underline" @click="removeGuest(index)">Hapus</button>
                            </div>
                            <div>
                                <InputLabel value="Nama Lengkap" />
                                <TextInput v-model="guest.full_name" class="mt-1 block w-full" required />
                                <InputError :message="(form.errors as any)[`guests.${index}.full_name`]" />
                            </div>
                            <div>
                                <InputLabel value="Nomor KTP" />
                                <TextInput v-model="guest.identity_number" class="mt-1 block w-full" />
                            </div>
                            <div>
                                <InputLabel value="Telepon" />
                                <TextInput v-model="guest.phone" class="mt-1 block w-full" />
                            </div>
                            <div>
                                <InputLabel value="Email" />
                                <TextInput v-model="guest.email" type="email" class="mt-1 block w-full" />
                            </div>
                            <div v-if="index > 0">
                                <InputLabel value="Hubungan dengan Penyewa Utama" />
                                <TextInput v-model="guest.relationship" class="mt-1 block w-full" />
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-beige-200 bg-white p-6 shadow-soft">
                        <h2 class="font-display text-lg font-semibold text-charcoal-800">Dokumen Identitas</h2>
                        <p v-if="hasIdentityDocumentOnFile" class="mt-1 text-sm text-green-700">
                            Kami sudah memiliki dokumen identitas Anda dari profil. Unggah ulang di sini bila ingin memperbarui.
                        </p>
                        <p v-else class="mt-1 text-sm text-charcoal-500">Unggah foto/scan KTP untuk melanjutkan pemesanan.</p>
                        <input
                            type="file"
                            accept=".jpg,.jpeg,.png,.pdf"
                            class="mt-3 block text-sm"
                            :required="!hasIdentityDocumentOnFile"
                            @change="form.identity_document = ($event.target as HTMLInputElement).files?.[0] ?? null"
                        />
                        <InputError class="mt-2" :message="form.errors.identity_document" />
                    </div>

                    <div class="rounded-2xl border border-beige-200 bg-white p-6 shadow-soft">
                        <InputLabel value="Catatan Tambahan (opsional)" />
                        <textarea v-model="form.notes" rows="3" class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400" />
                    </div>

                    <label class="flex items-start gap-2 text-sm text-charcoal-600">
                        <input v-model="form.terms_accepted" type="checkbox" required class="mt-1 rounded border-beige-300 text-terracotta-500 focus:ring-terracotta-400" />
                        <span>Saya menyetujui peraturan kost dan kebijakan pembayaran Demera Living.</span>
                    </label>
                    <InputError :message="form.errors.terms_accepted" />

                    <PrimaryButton :disabled="form.processing" class="w-full justify-center sm:w-auto">Buat Pemesanan</PrimaryButton>
                </form>

                <div class="lg:col-span-1">
                    <div class="sticky top-24 rounded-2xl border border-beige-200 bg-white p-6 shadow-soft">
                        <div class="flex gap-3">
                            <div class="h-16 w-16 shrink-0 overflow-hidden rounded-lg bg-beige-200">
                                <img v-if="room.primary_image" :src="room.primary_image.url" class="h-full w-full object-cover" />
                            </div>
                            <div>
                                <p class="text-xs text-charcoal-400">{{ room.room_type?.name }}</p>
                                <p class="font-medium text-charcoal-800">{{ room.name ?? `Kamar ${room.room_number}` }}</p>
                                <p class="text-xs text-charcoal-500">{{ room.property?.name }}</p>
                            </div>
                        </div>

                        <dl class="mt-4 space-y-2 border-t border-beige-100 pt-4 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-charcoal-500">Sewa Bulan Pertama</dt>
                                <dd class="font-medium text-charcoal-800">{{ formatIdr(room.monthly_price) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-charcoal-500">Deposit</dt>
                                <dd class="font-medium text-charcoal-800">{{ formatIdr(room.deposit_amount) }}</dd>
                            </div>
                            <div v-if="adminFee > 0" class="flex justify-between">
                                <dt class="text-charcoal-500">Biaya Admin</dt>
                                <dd class="font-medium text-charcoal-800">{{ formatIdr(adminFee) }}</dd>
                            </div>
                            <div class="flex justify-between border-t border-beige-100 pt-2 font-semibold">
                                <dt class="text-charcoal-800">Total Dibayar Sekarang</dt>
                                <dd class="text-terracotta-600">{{ formatIdr(totalDue) }}</dd>
                            </div>
                        </dl>

                        <p class="mt-4 text-xs text-charcoal-400">
                            Kamar akan ditahan selama {{ holdHours }} jam setelah pemesanan dibuat. Selesaikan pembayaran
                            sebelum batas waktu agar kamar tidak dilepas kembali.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
