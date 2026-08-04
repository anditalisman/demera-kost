<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from './Partials/LogoutOtherBrowserSessionsForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();
</script>

<template>
    <Head title="Profil Saya" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-display text-xl font-semibold text-charcoal-800">Profil Saya</h2>
        </template>

        <div class="space-y-6">
            <div class="rounded-2xl border border-beige-200 bg-white p-6 shadow-soft sm:p-8">
                <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" class="max-w-xl" />
            </div>

            <div class="rounded-2xl border border-beige-200 bg-white p-6 shadow-soft sm:p-8">
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <div class="rounded-2xl border border-beige-200 bg-white p-6 shadow-soft sm:p-8">
                <LogoutOtherBrowserSessionsForm class="max-w-xl" />
            </div>

            <div class="rounded-2xl border border-beige-200 bg-white p-6 shadow-soft sm:p-8">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
