<script setup lang="ts">
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { formatDateTime } from '@/lib/date';

interface AttachmentRow {
    id: number;
    url: string;
}
interface CommentRow {
    id: number;
    comment: string;
    created_at: string;
    user: { name: string } | null;
}
interface MaintenanceDetail {
    id: number;
    title: string;
    description: string;
    status: string;
    room: { name: string | null; room_number: string };
    attachments: AttachmentRow[];
    comments: CommentRow[];
}

const props = defineProps<{ maintenanceRequest: MaintenanceDetail }>();

const STATUS_LABEL: Record<string, string> = { new: 'Baru', in_progress: 'Diproses', waiting: 'Menunggu', completed: 'Selesai', closed: 'Ditutup' };

const commentForm = useForm({ comment: '' });
function submitComment() {
    commentForm.post(route('maintenance-requests.comments.store', props.maintenanceRequest.id), {
        preserveScroll: true,
        onSuccess: () => commentForm.reset(),
    });
}
</script>

<template>
    <Head :title="maintenanceRequest.title" />

    <CustomerLayout>
        <div class="rounded-2xl border border-beige-200 bg-white p-6 shadow-soft">
            <div class="flex items-center justify-between">
                <h1 class="font-display text-2xl font-semibold text-charcoal-800">{{ maintenanceRequest.title }}</h1>
                <span class="rounded-full bg-cream-100 px-3 py-1 text-xs font-medium text-charcoal-600">{{ STATUS_LABEL[maintenanceRequest.status] }}</span>
            </div>
            <p class="mt-1 text-sm text-charcoal-500">{{ maintenanceRequest.room.name ?? `Kamar ${maintenanceRequest.room.room_number}` }}</p>
            <p class="mt-4 whitespace-pre-line text-sm text-charcoal-700">{{ maintenanceRequest.description }}</p>

            <div v-if="maintenanceRequest.attachments.length" class="mt-4 grid grid-cols-3 gap-2">
                <a v-for="att in maintenanceRequest.attachments" :key="att.id" :href="att.url" target="_blank">
                    <img :src="att.url" class="h-24 w-full rounded-lg object-cover" />
                </a>
            </div>

            <div class="mt-6 border-t border-beige-100 pt-6">
                <h2 class="font-display text-lg font-semibold text-charcoal-800">Diskusi</h2>
                <ul class="mt-3 space-y-3">
                    <li v-for="c in maintenanceRequest.comments" :key="c.id" class="rounded-lg bg-cream-50 p-3 text-sm">
                        <p class="font-medium text-charcoal-800">{{ c.user?.name ?? '(akun dihapus)' }}</p>
                        <p class="mt-1 text-charcoal-600">{{ c.comment }}</p>
                        <p class="mt-1 text-xs text-charcoal-400">{{ formatDateTime(c.created_at) }}</p>
                    </li>
                    <li v-if="maintenanceRequest.comments.length === 0" class="text-xs text-charcoal-400">Belum ada komentar.</li>
                </ul>

                <form class="mt-4 flex gap-3" @submit.prevent="submitComment">
                    <input v-model="commentForm.comment" type="text" required placeholder="Tulis balasan..." class="flex-1 rounded-lg border-beige-300 text-sm focus:border-terracotta-400 focus:ring-terracotta-400" />
                    <PrimaryButton :disabled="commentForm.processing">Kirim</PrimaryButton>
                </form>
            </div>
        </div>
    </CustomerLayout>
</template>
