<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { PageProps } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface UserRow {
    id: number;
    name: string;
    email: string;
    whatsapp_number: string;
    is_active: boolean;
    roles: { id: number; name: string }[];
}

interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    users: Paginated<UserRow>;
    roles: string[];
    filters: { search?: string };
}>();

const page = usePage<PageProps>();
const search = ref(props.filters.search ?? '');

watch(search, (value) => {
    router.get(route('admin.users.index'), { search: value }, { preserveState: true, replace: true });
});

const ROLE_LABELS: Record<string, string> = {
    admin: 'Admin',
    customer: 'Customer',
};

const showModal = ref(false);
const editing = ref<UserRow | null>(null);
const form = useForm({ roles: [] as string[] });

function openEdit(user: UserRow) {
    editing.value = user;
    form.roles = user.roles.map((r) => r.name);
    showModal.value = true;
}

function submit() {
    if (!editing.value) return;
    form.put(route('admin.users.roles.update', editing.value.id), {
        onSuccess: () => (showModal.value = false),
    });
}

function toggleActive(user: UserRow) {
    if (user.id === page.props.auth.user?.id) return;
    router.put(route('admin.users.toggle-active', user.id));
}
</script>

<template>
    <Head title="Kelola Pengguna" />

    <AdminLayout>
        <div class="flex items-center justify-between">
            <h1 class="font-display text-2xl font-semibold text-charcoal-800">Pengguna</h1>
        </div>

        <div class="mt-4">
            <TextInput v-model="search" placeholder="Cari nama, email, atau nomor WhatsApp..." class="w-full max-w-sm" />
        </div>

        <div class="mt-6 overflow-x-auto rounded-2xl border border-beige-200 bg-white shadow-soft">
            <table class="min-w-full divide-y divide-beige-200 text-sm">
                <thead class="bg-cream-100 text-left text-xs font-semibold uppercase tracking-wide text-charcoal-500">
                    <tr>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Kontak</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-beige-100">
                    <tr v-for="user in users.data" :key="user.id">
                        <td class="px-4 py-3 font-medium text-charcoal-800">{{ user.name }}</td>
                        <td class="px-4 py-3 text-charcoal-500">
                            <div>{{ user.email }}</div>
                            <div class="text-xs">{{ user.whatsapp_number }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span
                                v-for="role in user.roles"
                                :key="role.id"
                                class="mr-1 inline-block rounded-full bg-terracotta-50 px-2 py-0.5 text-xs font-medium text-terracotta-700"
                            >
                                {{ ROLE_LABELS[role.name] ?? role.name }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-full px-2.5 py-1 text-xs font-medium"
                                :class="user.is_active ? 'bg-green-50 text-green-700' : 'bg-charcoal-100 text-charcoal-500'"
                            >
                                {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button class="mr-3 text-terracotta-600 hover:underline" @click="openEdit(user)">Atur Role</button>
                            <button
                                v-if="user.id !== page.props.auth.user?.id"
                                class="text-red-600 hover:underline"
                                @click="toggleActive(user)"
                            >
                                {{ user.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="users.links" />

        <Modal :show="showModal" @close="showModal = false">
            <form class="p-6" @submit.prevent="submit">
                <h2 class="font-display text-lg font-semibold text-charcoal-800">
                    Atur Role — {{ editing?.name }}
                </h2>

                <div class="mt-4 space-y-2">
                    <label v-for="role in roles" :key="role" class="flex items-center gap-2">
                        <Checkbox :checked="form.roles.includes(role)" @update:checked="(checked) => {
                            form.roles = checked ? [...form.roles, role] : form.roles.filter((r) => r !== role);
                        }" />
                        <span class="text-sm text-charcoal-600">{{ ROLE_LABELS[role] ?? role }}</span>
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
