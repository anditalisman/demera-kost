<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { formatIdr } from '@/lib/roomStatus';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface RoomTypeItem {
    id: number;
    property_id: number;
    name: string;
    description: string | null;
    base_price: string;
    base_deposit: string;
    size_sqm: string | null;
    default_capacity: number;
    property: { name: string } | null;
}

defineProps<{ roomTypes: RoomTypeItem[]; properties: { id: number; name: string }[] }>();

const showModal = ref(false);
const editing = ref<RoomTypeItem | null>(null);
const form = useForm({
    property_id: '' as number | string,
    name: '',
    description: '',
    base_price: '' as number | string,
    base_deposit: '' as number | string,
    size_sqm: '' as number | string,
    default_capacity: 1,
});

function openCreate() {
    editing.value = null;
    form.reset();
    showModal.value = true;
}
function openEdit(item: RoomTypeItem) {
    editing.value = item;
    form.property_id = item.property_id;
    form.name = item.name;
    form.description = item.description ?? '';
    form.base_price = item.base_price;
    form.base_deposit = item.base_deposit;
    form.size_sqm = item.size_sqm ?? '';
    form.default_capacity = item.default_capacity;
    showModal.value = true;
}
function submit() {
    const options = { onSuccess: () => (showModal.value = false) };
    if (editing.value) {
        form.put(route('admin.room-types.update', editing.value.id), options);
    } else {
        form.post(route('admin.room-types.store'), options);
    }
}
function destroy(item: RoomTypeItem) {
    if (confirm(`Hapus tipe kamar "${item.name}"?`)) {
        router.delete(route('admin.room-types.destroy', item.id));
    }
}
</script>

<template>
    <Head title="Tipe Kamar" />

    <AdminLayout>
        <div class="flex items-center justify-between">
            <h1 class="font-display text-2xl font-semibold text-charcoal-800">Tipe Kamar</h1>
            <PrimaryButton @click="openCreate">+ Tambah Tipe Kamar</PrimaryButton>
        </div>

        <div class="mt-6 overflow-x-auto rounded-xl border border-beige-200 bg-white shadow-soft">
            <table class="min-w-full divide-y divide-beige-100 text-sm">
                <thead class="bg-cream-50 text-xs uppercase tracking-wide text-charcoal-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-left">Properti</th>
                        <th class="px-4 py-3 text-left">Harga Dasar</th>
                        <th class="px-4 py-3 text-left">Deposit Dasar</th>
                        <th class="px-4 py-3 text-left">Kapasitas</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-beige-100">
                    <tr v-for="item in roomTypes" :key="item.id">
                        <td class="px-4 py-3 font-medium text-charcoal-800">{{ item.name }}</td>
                        <td class="px-4 py-3 text-charcoal-500">{{ item.property?.name }}</td>
                        <td class="px-4 py-3 text-charcoal-600">{{ formatIdr(item.base_price) }}</td>
                        <td class="px-4 py-3 text-charcoal-600">{{ formatIdr(item.base_deposit) }}</td>
                        <td class="px-4 py-3 text-charcoal-600">{{ item.default_capacity }} orang</td>
                        <td class="px-4 py-3 text-right">
                            <button class="text-xs font-medium text-terracotta-600 hover:underline" @click="openEdit(item)">Edit</button>
                            <button class="ml-3 text-xs font-medium text-red-600 hover:underline" @click="destroy(item)">Hapus</button>
                        </td>
                    </tr>
                    <tr v-if="roomTypes.length === 0">
                        <td colspan="6" class="px-4 py-6 text-center text-sm text-charcoal-400">Belum ada tipe kamar.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Modal :show="showModal" @close="showModal = false">
            <form class="p-6" @submit.prevent="submit">
                <h2 class="font-display text-lg font-semibold text-charcoal-800">
                    {{ editing ? 'Edit Tipe Kamar' : 'Tambah Tipe Kamar' }}
                </h2>
                <div class="mt-4 grid gap-4">
                    <div>
                        <InputLabel value="Properti" />
                        <select v-model="form.property_id" required class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400">
                            <option value="" disabled>Pilih properti</option>
                            <option v-for="p in properties" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                        <InputError :message="form.errors.property_id" />
                    </div>
                    <div>
                        <InputLabel value="Nama Tipe" />
                        <TextInput v-model="form.name" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Harga Dasar (Rp/bulan)" />
                            <TextInput v-model="form.base_price" type="number" min="0" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.base_price" />
                        </div>
                        <div>
                            <InputLabel value="Deposit Dasar (Rp)" />
                            <TextInput v-model="form.base_deposit" type="number" min="0" class="mt-1 block w-full" required />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Luas (m²)" />
                            <TextInput v-model="form.size_sqm" type="number" min="0" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="Kapasitas Default" />
                            <TextInput v-model.number="form.default_capacity" type="number" min="1" class="mt-1 block w-full" required />
                        </div>
                    </div>
                    <div>
                        <InputLabel value="Deskripsi" />
                        <textarea v-model="form.description" rows="3" class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="showModal = false">Batal</SecondaryButton>
                    <PrimaryButton :disabled="form.processing">Simpan</PrimaryButton>
                </div>
            </form>
        </Modal>
    </AdminLayout>
</template>
