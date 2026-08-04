<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface ContentPage {
    id: number;
    group: string;
    key: string | null;
    title: string | null;
    subtitle: string | null;
    body: string | null;
    image_path: string | null;
    cta_label: string | null;
    cta_url: string | null;
    meta_title: string | null;
    meta_description: string | null;
    is_published: boolean;
    sort_order: number;
}

const props = defineProps<{ pages: ContentPage[] }>();

const GROUPS = [
    { value: 'hero_slide', label: 'Hero Banner (Landing Page)' },
    { value: 'business_info', label: 'Info Bisnis (Fashion / Living)' },
    { value: 'policy', label: 'Kebijakan (ToS, Privasi, Pembayaran)' },
];

const activeGroup = ref(GROUPS[0].value);
const filteredPages = computed(() => props.pages.filter((p) => p.group === activeGroup.value));

const showModal = ref(false);
const editing = ref<ContentPage | null>(null);

const form = useForm({
    group: GROUPS[0].value,
    key: '',
    title: '',
    subtitle: '',
    body: '',
    cta_label: '',
    cta_url: '',
    meta_title: '',
    meta_description: '',
    is_published: true as boolean,
    sort_order: 0,
    image: null as File | null,
});

function openCreate() {
    editing.value = null;
    form.reset();
    form.group = activeGroup.value;
    showModal.value = true;
}

function openEdit(page: ContentPage) {
    editing.value = page;
    form.group = page.group;
    form.key = page.key ?? '';
    form.title = page.title ?? '';
    form.subtitle = page.subtitle ?? '';
    form.body = page.body ?? '';
    form.cta_label = page.cta_label ?? '';
    form.cta_url = page.cta_url ?? '';
    form.meta_title = page.meta_title ?? '';
    form.meta_description = page.meta_description ?? '';
    form.is_published = page.is_published;
    form.sort_order = page.sort_order;
    form.image = null;
    showModal.value = true;
}

function submit() {
    const options = { forceFormData: true, onSuccess: () => (showModal.value = false) };

    if (editing.value) {
        form.put(route('admin.content-pages.update', editing.value.id), options);
    } else {
        form.post(route('admin.content-pages.store'), options);
    }
}

function destroy(page: ContentPage) {
    if (confirm('Hapus konten ini?')) {
        router.delete(route('admin.content-pages.destroy', page.id));
    }
}
</script>

<template>
    <Head title="Kelola Konten" />

    <AdminLayout>
        <div class="flex items-center justify-between">
            <h1 class="font-display text-2xl font-semibold text-charcoal-800">Hero & Halaman</h1>
            <PrimaryButton @click="openCreate">+ Tambah Konten</PrimaryButton>
        </div>

        <div class="mt-6 flex gap-2 border-b border-beige-200">
            <button
                v-for="g in GROUPS"
                :key="g.value"
                class="border-b-2 px-4 py-2 text-sm font-medium"
                :class="activeGroup === g.value ? 'border-terracotta-500 text-terracotta-600' : 'border-transparent text-charcoal-400 hover:text-charcoal-600'"
                @click="activeGroup = g.value"
            >
                {{ g.label }}
            </button>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <div
                v-for="page in filteredPages"
                :key="page.id"
                class="rounded-2xl border border-beige-200 bg-white p-5 shadow-soft"
            >
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-charcoal-400">{{ page.key || '(tanpa key)' }}</p>
                        <h3 class="mt-1 font-display text-lg font-semibold text-charcoal-800">{{ page.title || '(tanpa judul)' }}</h3>
                    </div>
                    <span
                        class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium"
                        :class="page.is_published ? 'bg-green-50 text-green-700' : 'bg-charcoal-100 text-charcoal-500'"
                    >
                        {{ page.is_published ? 'Tayang' : 'Draft' }}
                    </span>
                </div>
                <p class="mt-2 line-clamp-2 text-sm text-charcoal-500">{{ page.subtitle || page.body }}</p>
                <div class="mt-4 flex gap-3">
                    <button class="text-sm font-medium text-terracotta-600 hover:underline" @click="openEdit(page)">Edit</button>
                    <button class="text-sm font-medium text-red-600 hover:underline" @click="destroy(page)">Hapus</button>
                </div>
            </div>

            <p v-if="filteredPages.length === 0" class="text-sm text-charcoal-400">
                Belum ada konten pada grup ini.
            </p>
        </div>

        <Modal :show="showModal" @close="showModal = false">
            <form class="p-6" @submit.prevent="submit">
                <h2 class="font-display text-lg font-semibold text-charcoal-800">
                    {{ editing ? 'Edit Konten' : 'Tambah Konten' }}
                </h2>

                <div class="mt-4 grid gap-4">
                    <div>
                        <InputLabel value="Grup" />
                        <select v-model="form.group" class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400">
                            <option v-for="g in GROUPS" :key="g.value" :value="g.value">{{ g.label }}</option>
                        </select>
                    </div>

                    <div>
                        <InputLabel value="Key (identifier unik, contoh: tos, privacy-policy)" />
                        <TextInput v-model="form.key" class="mt-1 block w-full" />
                        <InputError :message="form.errors.key" />
                    </div>

                    <div>
                        <InputLabel value="Judul" />
                        <TextInput v-model="form.title" class="mt-1 block w-full" />
                        <InputError :message="form.errors.title" />
                    </div>

                    <div>
                        <InputLabel value="Subjudul" />
                        <TextInput v-model="form.subtitle" class="mt-1 block w-full" />
                    </div>

                    <div>
                        <InputLabel value="Isi (mendukung HTML dasar)" />
                        <textarea v-model="form.body" rows="5" class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Label Tombol (CTA)" />
                            <TextInput v-model="form.cta_label" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="URL Tombol (CTA)" />
                            <TextInput v-model="form.cta_url" class="mt-1 block w-full" />
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Gambar" />
                        <input type="file" accept="image/*" class="mt-1 block w-full text-sm" @change="form.image = ($event.target as HTMLInputElement).files?.[0] ?? null" />
                        <InputError :message="form.errors.image" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Meta Title (SEO)" />
                            <TextInput v-model="form.meta_title" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="Urutan Tampil" />
                            <TextInput v-model.number="form.sort_order" type="number" class="mt-1 block w-full" />
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Meta Description (SEO)" />
                        <textarea v-model="form.meta_description" rows="2" class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400" />
                    </div>

                    <label class="flex items-center gap-2">
                        <Checkbox v-model:checked="form.is_published" />
                        <span class="text-sm text-charcoal-600">Tayangkan konten ini</span>
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
