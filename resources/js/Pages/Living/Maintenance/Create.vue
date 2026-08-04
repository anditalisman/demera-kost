<script setup lang="ts">
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{ room: { name: string | null; room_number: string } }>();

const form = useForm({
    category: 'other',
    title: '',
    description: '',
    priority: 'normal' as 'low' | 'normal' | 'high' | 'urgent',
    photos: [] as File[],
});

function onFilesChange(event: Event) {
    const files = (event.target as HTMLInputElement).files;
    form.photos = files ? Array.from(files) : [];
}

function submit() {
    form.post(route('maintenance-requests.store'), { forceFormData: true });
}
</script>

<template>
    <Head title="Ajukan Keluhan" />

    <CustomerLayout>
        <h1 class="font-display text-2xl font-semibold text-charcoal-800">Ajukan Keluhan</h1>
        <p class="mt-1 text-sm text-charcoal-500">Kamar: {{ room.name ?? `Kamar ${room.room_number}` }}</p>

        <form class="mt-6 max-w-xl space-y-4 rounded-2xl border border-beige-200 bg-white p-6 shadow-soft" @submit.prevent="submit">
            <div>
                <InputLabel value="Kategori" />
                <select v-model="form.category" class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400">
                    <option value="electrical">Kelistrikan</option>
                    <option value="plumbing">Air / Pipa</option>
                    <option value="furniture">Perabotan</option>
                    <option value="other">Lainnya</option>
                </select>
            </div>
            <div>
                <InputLabel value="Judul" />
                <TextInput v-model="form.title" class="mt-1 block w-full" required />
                <InputError :message="form.errors.title" />
            </div>
            <div>
                <InputLabel value="Deskripsi" />
                <textarea v-model="form.description" rows="4" required class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400" />
                <InputError :message="form.errors.description" />
            </div>
            <div>
                <InputLabel value="Prioritas" />
                <select v-model="form.priority" class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400">
                    <option value="low">Rendah</option>
                    <option value="normal">Normal</option>
                    <option value="high">Tinggi</option>
                    <option value="urgent">Mendesak</option>
                </select>
            </div>
            <div>
                <InputLabel value="Foto (opsional, maks 5)" />
                <input type="file" accept="image/*" multiple class="mt-1 block w-full text-sm" @change="onFilesChange" />
                <InputError :message="form.errors.photos" />
            </div>

            <PrimaryButton :disabled="form.processing" class="w-full justify-center">Kirim Keluhan</PrimaryButton>
        </form>
    </CustomerLayout>
</template>
