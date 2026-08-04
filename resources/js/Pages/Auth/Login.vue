<script setup lang="ts">
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    login: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk" />

        <h1 class="font-display text-xl font-semibold text-charcoal-800">
            Selamat datang kembali
        </h1>
        <p class="mt-1 text-sm text-charcoal-400">
            Masuk untuk memesan kamar dan mengelola sewa Anda.
        </p>

        <div
            v-if="status"
            class="mt-4 rounded-lg bg-terracotta-50 px-4 py-3 text-sm font-medium text-terracotta-600"
        >
            {{ status }}
        </div>

        <form class="mt-6" @submit.prevent="submit">
            <div>
                <InputLabel for="login" value="Email atau Nomor WhatsApp" />

                <TextInput
                    id="login"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.login"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="nama@email.com atau 08xxxxxxxxxx"
                />

                <InputError class="mt-2" :message="form.errors.login" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Kata Sandi" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-charcoal-500">Ingat saya</span>
                </label>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="rounded-md text-sm text-charcoal-500 underline hover:text-terracotta-600 focus:outline-none focus:ring-2 focus:ring-terracotta-400"
                >
                    Lupa kata sandi?
                </Link>

                <PrimaryButton
                    class="ms-auto"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Masuk
                </PrimaryButton>
            </div>
        </form>

        <p class="mt-6 text-center text-sm text-charcoal-400">
            Belum punya akun?
            <Link :href="route('register')" class="font-semibold text-terracotta-600 underline">
                Daftar sekarang
            </Link>
        </p>
    </GuestLayout>
</template>
