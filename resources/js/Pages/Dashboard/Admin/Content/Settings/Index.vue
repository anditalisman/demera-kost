<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

interface SettingField {
    key: string;
    group: string;
    label: string;
    type: string;
    value: string;
}

const props = defineProps<{ groups: Record<string, SettingField[]>; qrisImageUrl: string | null }>();

const qrisForm = useForm({ image: null as File | null });
function submitQris() {
    qrisForm.post(route('admin.settings.qris.upload'), { forceFormData: true, onSuccess: () => (qrisForm.image = null) });
}

const GROUP_LABELS: Record<string, string> = {
    contact: 'Kontak & Lokasi',
    social: 'Media Sosial',
    seo: 'SEO',
    booking: 'Pemesanan',
    payment: 'Pembayaran',
};

const initialValues: Record<string, string> = {};
Object.values(props.groups)
    .flat()
    .forEach((field) => (initialValues[field.key] = field.value));

const form = useForm({ values: initialValues });

function submit() {
    form.put(route('admin.settings.update'));
}
</script>

<template>
    <Head title="Pengaturan Aplikasi" />

    <AdminLayout>
        <h1 class="font-display text-2xl font-semibold text-charcoal-800">Pengaturan</h1>
        <p class="mt-1 text-sm text-charcoal-400">
            Kontak, media sosial, dan SEO default yang tampil di landing page dan halaman publik.
        </p>

        <form class="mt-6 space-y-8" @submit.prevent="submit">
            <div v-for="(fields, group) in groups" :key="group" class="rounded-2xl border border-beige-200 bg-white p-6 shadow-soft">
                <h2 class="font-display text-lg font-semibold text-charcoal-800">{{ GROUP_LABELS[group] ?? group }}</h2>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div v-for="field in fields" :key="field.key">
                        <InputLabel :value="field.label" />
                        <TextInput v-model="form.values[field.key]" class="mt-1 block w-full" />
                    </div>
                </div>
            </div>

            <PrimaryButton :disabled="form.processing">Simpan Pengaturan</PrimaryButton>
        </form>

        <div class="mt-8 rounded-2xl border border-beige-200 bg-white p-6 shadow-soft">
            <h2 class="font-display text-lg font-semibold text-charcoal-800">Gambar QRIS</h2>
            <p class="mt-1 text-sm text-charcoal-400">Diunggah admin — pelanggan memindai kode ini lalu mengunggah bukti pembayaran.</p>

            <div class="mt-4 flex items-center gap-6">
                <div class="h-40 w-40 shrink-0 overflow-hidden rounded-lg border border-beige-200 bg-cream-50">
                    <img v-if="qrisImageUrl" :src="qrisImageUrl" class="h-full w-full object-contain" />
                    <p v-else class="flex h-full items-center justify-center text-center text-xs text-charcoal-400">Belum ada gambar QRIS</p>
                </div>
                <form class="flex flex-col gap-3" @submit.prevent="submitQris">
                    <input type="file" accept="image/*" class="text-sm" @change="qrisForm.image = ($event.target as HTMLInputElement).files?.[0] ?? null" />
                    <PrimaryButton :disabled="qrisForm.processing || !qrisForm.image" class="w-fit">Unggah Gambar QRIS</PrimaryButton>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
