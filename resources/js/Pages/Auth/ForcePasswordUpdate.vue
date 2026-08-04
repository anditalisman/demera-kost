<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.put(route('password.force-update.store'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Ganti Kata Sandi" />

        <h1 class="font-display text-xl font-semibold text-charcoal-800">
            Ganti kata sandi Anda
        </h1>
        <p class="mt-1 text-sm text-charcoal-400">
            Demi keamanan, Anda wajib mengganti kata sandi bawaan sebelum melanjutkan.
        </p>

        <form class="mt-6" @submit.prevent="submit">
            <div>
                <InputLabel for="password" value="Kata Sandi Baru" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autofocus
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Konfirmasi Kata Sandi Baru" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="mt-6 flex justify-end">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Simpan Kata Sandi
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
