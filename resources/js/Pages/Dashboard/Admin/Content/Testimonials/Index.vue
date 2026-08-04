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

interface TestimonialItem {
    id: number;
    author_name: string;
    author_role: string | null;
    author_photo_url: string | null;
    rating: number | null;
    content: string;
    source: string;
    is_published: boolean;
    is_featured: boolean;
    sort_order: number;
}

defineProps<{ testimonials: TestimonialItem[] }>();

const showModal = ref(false);
const editing = ref<TestimonialItem | null>(null);

const form = useForm({
    author_name: '',
    author_role: '',
    rating: 5 as number | null,
    content: '',
    source: 'living',
    is_published: true as boolean,
    is_featured: false as boolean,
    sort_order: 0,
    photo: null as File | null,
});

function openCreate() {
    editing.value = null;
    form.reset();
    showModal.value = true;
}

function openEdit(item: TestimonialItem) {
    editing.value = item;
    form.author_name = item.author_name;
    form.author_role = item.author_role ?? '';
    form.rating = item.rating;
    form.content = item.content;
    form.source = item.source;
    form.is_published = item.is_published;
    form.is_featured = item.is_featured;
    form.sort_order = item.sort_order;
    form.photo = null;
    showModal.value = true;
}

function submit() {
    const options = { forceFormData: true, onSuccess: () => (showModal.value = false) };

    if (editing.value) {
        form.put(route('admin.testimonials.update', editing.value.id), options);
    } else {
        form.post(route('admin.testimonials.store'), options);
    }
}

function destroy(item: TestimonialItem) {
    if (confirm('Hapus testimoni ini?')) {
        router.delete(route('admin.testimonials.destroy', item.id));
    }
}
</script>

<template>
    <Head title="Kelola Testimoni" />

    <AdminLayout>
        <div class="flex items-center justify-between">
            <h1 class="font-display text-2xl font-semibold text-charcoal-800">Testimoni</h1>
            <PrimaryButton @click="openCreate">+ Tambah Testimoni</PrimaryButton>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="item in testimonials"
                :key="item.id"
                class="rounded-2xl border border-beige-200 bg-white p-5 shadow-soft"
            >
                <div class="flex items-center gap-3">
                    <img
                        v-if="item.author_photo_url"
                        :src="item.author_photo_url"
                        class="h-10 w-10 rounded-full object-cover"
                        :alt="item.author_name"
                    />
                    <div v-else class="flex h-10 w-10 items-center justify-center rounded-full bg-beige-200 text-sm font-semibold text-charcoal-600">
                        {{ item.author_name.charAt(0) }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-charcoal-800">{{ item.author_name }}</p>
                        <p class="text-xs text-charcoal-400">{{ item.author_role }}</p>
                    </div>
                </div>
                <p class="mt-3 line-clamp-3 text-sm text-charcoal-600">&ldquo;{{ item.content }}&rdquo;</p>
                <div class="mt-4 flex items-center justify-between">
                    <span
                        class="rounded-full px-2.5 py-1 text-xs font-medium"
                        :class="item.is_published ? 'bg-green-50 text-green-700' : 'bg-charcoal-100 text-charcoal-500'"
                    >
                        {{ item.is_published ? 'Tayang' : 'Draft' }}{{ item.is_featured ? ' · Unggulan' : '' }}
                    </span>
                    <div class="flex gap-3">
                        <button class="text-xs font-medium text-terracotta-600 hover:underline" @click="openEdit(item)">Edit</button>
                        <button class="text-xs font-medium text-red-600 hover:underline" @click="destroy(item)">Hapus</button>
                    </div>
                </div>
            </div>

            <p v-if="testimonials.length === 0" class="col-span-full text-sm text-charcoal-400">
                Belum ada testimoni.
            </p>
        </div>

        <Modal :show="showModal" @close="showModal = false">
            <form class="p-6" @submit.prevent="submit">
                <h2 class="font-display text-lg font-semibold text-charcoal-800">
                    {{ editing ? 'Edit Testimoni' : 'Tambah Testimoni' }}
                </h2>

                <div class="mt-4 grid gap-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Nama" />
                            <TextInput v-model="form.author_name" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.author_name" />
                        </div>
                        <div>
                            <InputLabel value="Peran / Status (contoh: Penghuni sejak 2024)" />
                            <TextInput v-model="form.author_role" class="mt-1 block w-full" />
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Isi Testimoni" />
                        <textarea v-model="form.content" rows="3" required class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400" />
                        <InputError :message="form.errors.content" />
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <InputLabel value="Rating (1-5)" />
                            <TextInput v-model.number="form.rating" type="number" min="1" max="5" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="Sumber" />
                            <select v-model="form.source" class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400">
                                <option value="living">Living</option>
                                <option value="fashion">Fashion</option>
                                <option value="general">Umum</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="Urutan" />
                            <TextInput v-model.number="form.sort_order" type="number" class="mt-1 block w-full" />
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Foto" />
                        <input type="file" accept="image/*" class="mt-1 block w-full text-sm" @change="form.photo = ($event.target as HTMLInputElement).files?.[0] ?? null" />
                    </div>

                    <div class="flex gap-6">
                        <label class="flex items-center gap-2">
                            <Checkbox v-model:checked="form.is_published" />
                            <span class="text-sm text-charcoal-600">Tayangkan</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <Checkbox v-model:checked="form.is_featured" />
                            <span class="text-sm text-charcoal-600">Jadikan unggulan</span>
                        </label>
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
