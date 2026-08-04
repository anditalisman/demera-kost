<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface FacilityItem {
    id: number;
    name: string;
    icon: string | null;
    type: 'room' | 'shared';
    description: string | null;
    sort_order: number;
    is_active: boolean;
}

defineProps<{ facilities: FacilityItem[] }>();

const showModal = ref(false);
const editing = ref<FacilityItem | null>(null);
const form = useForm({
    name: '',
    icon: '',
    type: 'room' as 'room' | 'shared',
    description: '',
    sort_order: 0,
    is_active: true,
});

function openCreate() {
    editing.value = null;
    form.reset();
    showModal.value = true;
}
function openEdit(item: FacilityItem) {
    editing.value = item;
    form.name = item.name;
    form.icon = item.icon ?? '';
    form.type = item.type;
    form.description = item.description ?? '';
    form.sort_order = item.sort_order;
    form.is_active = item.is_active;
    showModal.value = true;
}
function submit() {
    const options = { onSuccess: () => (showModal.value = false) };
    if (editing.value) {
        form.put(route('admin.facilities.update', editing.value.id), options);
    } else {
        form.post(route('admin.facilities.store'), options);
    }
}
function destroy(item: FacilityItem) {
    if (confirm(`Hapus fasilitas "${item.name}"?`)) {
        router.delete(route('admin.facilities.destroy', item.id));
    }
}
</script>

<template>
    <Head title="Fasilitas" />

    <AdminLayout>
        <div class="flex items-center justify-between">
            <h1 class="font-display text-2xl font-semibold text-charcoal-800">Fasilitas</h1>
            <PrimaryButton @click="openCreate">+ Tambah Fasilitas</PrimaryButton>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="item in facilities" :key="item.id" class="rounded-xl border border-beige-200 bg-white p-4 shadow-soft">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-terracotta-500">
                            {{ item.type === 'room' ? 'Fasilitas Kamar' : 'Fasilitas Bersama' }}
                        </span>
                        <p class="mt-1 font-medium text-charcoal-800">{{ item.name }}</p>
                    </div>
                    <span
                        class="rounded-full px-2 py-0.5 text-xs font-medium"
                        :class="item.is_active ? 'bg-green-50 text-green-700' : 'bg-charcoal-100 text-charcoal-500'"
                    >
                        {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <div class="mt-3 flex gap-3">
                    <button class="text-xs font-medium text-terracotta-600 hover:underline" @click="openEdit(item)">Edit</button>
                    <button class="text-xs font-medium text-red-600 hover:underline" @click="destroy(item)">Hapus</button>
                </div>
            </div>
            <p v-if="facilities.length === 0" class="text-sm text-charcoal-400">Belum ada fasilitas.</p>
        </div>

        <Modal :show="showModal" @close="showModal = false">
            <form class="p-6" @submit.prevent="submit">
                <h2 class="font-display text-lg font-semibold text-charcoal-800">
                    {{ editing ? 'Edit Fasilitas' : 'Tambah Fasilitas' }}
                </h2>
                <div class="mt-4 grid gap-4">
                    <div>
                        <InputLabel value="Nama Fasilitas" />
                        <TextInput v-model="form.name" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Tipe" />
                            <select v-model="form.type" class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400">
                                <option value="room">Fasilitas Kamar</option>
                                <option value="shared">Fasilitas Bersama</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="Urutan" />
                            <TextInput v-model.number="form.sort_order" type="number" class="mt-1 block w-full" />
                        </div>
                    </div>
                    <div>
                        <InputLabel value="Ikon (opsional, nama kelas ikon)" />
                        <TextInput v-model="form.icon" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <InputLabel value="Deskripsi" />
                        <textarea v-model="form.description" rows="2" class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400" />
                    </div>
                    <label class="flex items-center gap-2">
                        <Checkbox v-model:checked="form.is_active" />
                        <span class="text-sm text-charcoal-600">Aktif</span>
                    </label>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="showModal = false">Batal</SecondaryButton>
                    <PrimaryButton :disabled="form.processing">Simpan</PrimaryButton>
                </div>
            </form>
        </Modal>
    </AdminLayout>
</template>
