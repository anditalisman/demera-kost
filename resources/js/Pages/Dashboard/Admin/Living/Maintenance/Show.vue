<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface AttachmentRow {
    id: number;
    url: string;
    caption: string | null;
}
interface CommentRow {
    id: number;
    comment: string;
    created_at: string;
    user: { name: string };
}
interface MaintenanceDetail {
    id: number;
    title: string;
    description: string;
    category: string | null;
    priority: string;
    status: string;
    resolution_notes: string | null;
    created_at: string;
    tenant: { user: { name: string; email: string } } | null;
    room: { name: string | null; room_number: string; property: { name: string } };
    attachments: AttachmentRow[];
    comments: CommentRow[];
    assigned_to: { name: string } | null;
}

const props = defineProps<{ maintenanceRequest: MaintenanceDetail; statuses: { value: string; label: string }[] }>();

const statusForm = useForm({ status: props.maintenanceRequest.status, resolution_notes: props.maintenanceRequest.resolution_notes ?? '' });
function submitStatus() {
    statusForm.put(route('admin.maintenance-requests.status.update', props.maintenanceRequest.id), { preserveScroll: true });
}

const commentForm = useForm({ comment: '' });
function submitComment() {
    commentForm.post(route('admin.maintenance-requests.comments.store', props.maintenanceRequest.id), {
        preserveScroll: true,
        onSuccess: () => commentForm.reset(),
    });
}
</script>

<template>
    <Head :title="maintenanceRequest.title" />

    <AdminLayout>
        <div class="flex items-center justify-between">
            <h1 class="font-display text-2xl font-semibold text-charcoal-800">{{ maintenanceRequest.title }}</h1>
            <Link :href="route('admin.maintenance-requests.index')" class="text-sm text-charcoal-500 hover:underline">Kembali</Link>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-xl border border-beige-200 bg-white p-5 shadow-soft">
                    <p class="text-sm text-charcoal-500">
                        Dilaporkan oleh {{ maintenanceRequest.tenant?.user.name ?? '-' }} &middot;
                        {{ maintenanceRequest.room.name ?? `Kamar ${maintenanceRequest.room.room_number}` }},
                        {{ maintenanceRequest.room.property.name }}
                    </p>
                    <p class="mt-3 whitespace-pre-line text-sm text-charcoal-700">{{ maintenanceRequest.description }}</p>

                    <div v-if="maintenanceRequest.attachments.length" class="mt-4 grid grid-cols-3 gap-2">
                        <a v-for="att in maintenanceRequest.attachments" :key="att.id" :href="att.url" target="_blank">
                            <img :src="att.url" class="h-24 w-full rounded-lg object-cover" />
                        </a>
                    </div>
                </div>

                <div class="rounded-xl border border-beige-200 bg-white p-5 shadow-soft">
                    <h2 class="font-display text-sm font-semibold uppercase tracking-wide text-charcoal-500">Diskusi</h2>
                    <ul class="mt-3 space-y-3">
                        <li v-for="c in maintenanceRequest.comments" :key="c.id" class="rounded-lg bg-cream-50 p-3 text-sm">
                            <p class="font-medium text-charcoal-800">{{ c.user.name }}</p>
                            <p class="mt-1 text-charcoal-600">{{ c.comment }}</p>
                            <p class="mt-1 text-xs text-charcoal-400">{{ c.created_at }}</p>
                        </li>
                        <li v-if="maintenanceRequest.comments.length === 0" class="text-xs text-charcoal-400">Belum ada komentar.</li>
                    </ul>

                    <form class="mt-4 flex gap-3" @submit.prevent="submitComment">
                        <input v-model="commentForm.comment" type="text" required placeholder="Tulis balasan..." class="flex-1 rounded-lg border-beige-300 text-sm focus:border-terracotta-400 focus:ring-terracotta-400" />
                        <PrimaryButton :disabled="commentForm.processing">Kirim</PrimaryButton>
                    </form>
                </div>
            </div>

            <div class="rounded-xl border border-beige-200 bg-white p-5 shadow-soft">
                <h2 class="font-display text-sm font-semibold uppercase tracking-wide text-charcoal-500">Kelola Status</h2>
                <form class="mt-3 space-y-3" @submit.prevent="submitStatus">
                    <div>
                        <InputLabel value="Status" />
                        <select v-model="statusForm.status" class="mt-1 block w-full rounded-lg border-beige-300 text-sm focus:border-terracotta-400 focus:ring-terracotta-400">
                            <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Catatan Penyelesaian" />
                        <textarea v-model="statusForm.resolution_notes" rows="3" class="mt-1 block w-full rounded-lg border-beige-300 text-sm focus:border-terracotta-400 focus:ring-terracotta-400" />
                    </div>
                    <PrimaryButton :disabled="statusForm.processing" class="w-full justify-center">Simpan Status</PrimaryButton>
                </form>
                <p v-if="maintenanceRequest.assigned_to" class="mt-3 text-xs text-charcoal-400">Ditangani oleh {{ maintenanceRequest.assigned_to.name }}</p>
            </div>
        </div>
    </AdminLayout>
</template>
