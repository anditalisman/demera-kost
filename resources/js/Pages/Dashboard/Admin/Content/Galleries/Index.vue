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

interface GalleryItem {
    id: number;
    title: string | null;
    category: string;
    image_url: string;
    thumbnail_url: string | null;
    caption: string | null;
    is_published: boolean;
    sort_order: number;
}

const props = defineProps<{ galleries: GalleryItem[] }>();

const CATEGORIES = [
    { value: 'property', label: 'Properti / Eksterior' },
    { value: 'room_common_area', label: 'Ruang Bersama' },
    { value: 'facility', label: 'Fasilitas' },
    { value: 'event', label: 'Kegiatan / Event' },
    { value: 'company', label: 'Perusahaan' },
];

const showModal = ref(false);
const draggingId = ref<number | null>(null);

const form = useForm({
    title: '',
    category: CATEGORIES[0].value,
    caption: '',
    is_published: true as boolean,
    image: null as File | null,
});

function openCreate() {
    form.reset();
    showModal.value = true;
}

function submit() {
    form.post(route('admin.galleries.store'), {
        forceFormData: true,
        onSuccess: () => (showModal.value = false),
    });
}

function destroy(item: GalleryItem) {
    if (confirm('Hapus foto ini dari galeri?')) {
        router.delete(route('admin.galleries.destroy', item.id));
    }
}

function togglePublish(item: GalleryItem) {
    router.put(route('admin.galleries.update', item.id), {
        title: item.title,
        category: item.category,
        caption: item.caption,
        is_published: !item.is_published,
    });
}

function onDragStart(id: number) {
    draggingId.value = id;
}

function onDrop(targetId: number) {
    if (draggingId.value === null || draggingId.value === targetId) return;

    const ids = props.galleries.map((g) => g.id);
    const fromIndex = ids.indexOf(draggingId.value);
    const toIndex = ids.indexOf(targetId);
    ids.splice(toIndex, 0, ids.splice(fromIndex, 1)[0]);

    router.post(route('admin.galleries.reorder'), { ids }, { preserveScroll: true });
    draggingId.value = null;
}
</script>

<template>
    <Head title="Kelola Galeri" />

    <AdminLayout>
        <div class="flex items-center justify-between">
            <h1 class="font-display text-2xl font-semibold text-charcoal-800">Galeri</h1>
            <PrimaryButton @click="openCreate">+ Tambah Foto</PrimaryButton>
        </div>
        <p class="mt-1 text-sm text-charcoal-400">Seret (drag) kartu untuk mengubah urutan tampil di galeri publik.</p>

        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            <div
                v-for="item in galleries"
                :key="item.id"
                class="group relative cursor-move overflow-hidden rounded-xl border border-beige-200 bg-white shadow-soft"
                draggable="true"
                @dragstart="onDragStart(item.id)"
                @dragover.prevent
                @drop="onDrop(item.id)"
            >
                <img :src="item.thumbnail_url ?? item.image_url" class="h-32 w-full object-cover" :alt="item.title ?? ''" />
                <div class="p-2">
                    <p class="truncate text-xs font-medium text-charcoal-600">{{ item.title || item.category }}</p>
                    <div class="mt-1 flex items-center justify-between">
                        <button class="text-xs text-terracotta-600 hover:underline" @click="togglePublish(item)">
                            {{ item.is_published ? 'Tayang' : 'Draft' }}
                        </button>
                        <button class="text-xs text-red-600 hover:underline" @click="destroy(item)">Hapus</button>
                    </div>
                </div>
            </div>

            <p v-if="galleries.length === 0" class="col-span-full text-sm text-charcoal-400">
                Galeri masih kosong.
            </p>
        </div>

        <Modal :show="showModal" @close="showModal = false">
            <form class="p-6" @submit.prevent="submit">
                <h2 class="font-display text-lg font-semibold text-charcoal-800">Tambah Foto Galeri</h2>

                <div class="mt-4 grid gap-4">
                    <div>
                        <InputLabel value="Kategori" />
                        <select v-model="form.category" class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400">
                            <option v-for="c in CATEGORIES" :key="c.value" :value="c.value">{{ c.label }}</option>
                        </select>
                    </div>

                    <div>
                        <InputLabel value="Judul (opsional)" />
                        <TextInput v-model="form.title" class="mt-1 block w-full" />
                    </div>

                    <div>
                        <InputLabel value="Keterangan" />
                        <TextInput v-model="form.caption" class="mt-1 block w-full" />
                    </div>

                    <div>
                        <InputLabel value="Foto" />
                        <input type="file" accept="image/*" required class="mt-1 block w-full text-sm" @change="form.image = ($event.target as HTMLInputElement).files?.[0] ?? null" />
                        <InputError :message="form.errors.image" />
                    </div>

                    <label class="flex items-center gap-2">
                        <Checkbox v-model:checked="form.is_published" />
                        <span class="text-sm text-charcoal-600">Tayangkan foto ini</span>
                    </label>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="showModal = false">Batal</SecondaryButton>
                    <PrimaryButton :disabled="form.processing">Unggah</PrimaryButton>
                </div>
            </form>
        </Modal>
    </AdminLayout>
</template>
