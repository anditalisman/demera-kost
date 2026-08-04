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

interface FaqItem {
    id: number;
    question: string;
    answer: string;
    category: string;
    is_published: boolean;
    sort_order: number;
}

defineProps<{ faqs: FaqItem[] }>();

const showModal = ref(false);
const editing = ref<FaqItem | null>(null);

const form = useForm({
    question: '',
    answer: '',
    category: 'general',
    is_published: true as boolean,
    sort_order: 0,
});

function openCreate() {
    editing.value = null;
    form.reset();
    showModal.value = true;
}

function openEdit(item: FaqItem) {
    editing.value = item;
    form.question = item.question;
    form.answer = item.answer;
    form.category = item.category;
    form.is_published = item.is_published;
    form.sort_order = item.sort_order;
    showModal.value = true;
}

function submit() {
    const options = { onSuccess: () => (showModal.value = false) };

    if (editing.value) {
        form.put(route('admin.faqs.update', editing.value.id), options);
    } else {
        form.post(route('admin.faqs.store'), options);
    }
}

function destroy(item: FaqItem) {
    if (confirm('Hapus FAQ ini?')) {
        router.delete(route('admin.faqs.destroy', item.id));
    }
}
</script>

<template>
    <Head title="Kelola FAQ" />

    <AdminLayout>
        <div class="flex items-center justify-between">
            <h1 class="font-display text-2xl font-semibold text-charcoal-800">FAQ</h1>
            <PrimaryButton @click="openCreate">+ Tambah FAQ</PrimaryButton>
        </div>

        <div class="mt-6 space-y-3">
            <div
                v-for="item in faqs"
                :key="item.id"
                class="rounded-xl border border-beige-200 bg-white p-4 shadow-soft"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wide text-terracotta-500">{{ item.category }}</span>
                        <p class="mt-1 font-medium text-charcoal-800">{{ item.question }}</p>
                        <p class="mt-1 text-sm text-charcoal-500">{{ item.answer }}</p>
                    </div>
                    <div class="flex shrink-0 flex-col items-end gap-2">
                        <span
                            class="rounded-full px-2.5 py-1 text-xs font-medium"
                            :class="item.is_published ? 'bg-green-50 text-green-700' : 'bg-charcoal-100 text-charcoal-500'"
                        >
                            {{ item.is_published ? 'Tayang' : 'Draft' }}
                        </span>
                        <div class="flex gap-3">
                            <button class="text-xs font-medium text-terracotta-600 hover:underline" @click="openEdit(item)">Edit</button>
                            <button class="text-xs font-medium text-red-600 hover:underline" @click="destroy(item)">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>

            <p v-if="faqs.length === 0" class="text-sm text-charcoal-400">Belum ada FAQ.</p>
        </div>

        <Modal :show="showModal" @close="showModal = false">
            <form class="p-6" @submit.prevent="submit">
                <h2 class="font-display text-lg font-semibold text-charcoal-800">
                    {{ editing ? 'Edit FAQ' : 'Tambah FAQ' }}
                </h2>

                <div class="mt-4 grid gap-4">
                    <div>
                        <InputLabel value="Pertanyaan" />
                        <TextInput v-model="form.question" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.question" />
                    </div>

                    <div>
                        <InputLabel value="Jawaban" />
                        <textarea v-model="form.answer" rows="4" required class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400" />
                        <InputError :message="form.errors.answer" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Kategori" />
                            <select v-model="form.category" class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400">
                                <option value="general">Umum</option>
                                <option value="booking">Pemesanan</option>
                                <option value="payment">Pembayaran</option>
                                <option value="fashion">Fashion</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="Urutan" />
                            <TextInput v-model.number="form.sort_order" type="number" class="mt-1 block w-full" />
                        </div>
                    </div>

                    <label class="flex items-center gap-2">
                        <Checkbox v-model:checked="form.is_published" />
                        <span class="text-sm text-charcoal-600">Tayangkan</span>
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
