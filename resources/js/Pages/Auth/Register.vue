<script setup lang="ts">
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    whatsapp_number: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Daftar" />

        <h1 class="font-display text-xl font-semibold text-charcoal-800">
            Buat akun Demera
        </h1>
        <p class="mt-1 text-sm text-charcoal-400">
            Satu akun untuk memesan dan mengelola sewa kamar Demera Living.
        </p>

        <form class="mt-6" @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Nama Lengkap" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="whatsapp_number" value="Nomor WhatsApp" />

                <TextInput
                    id="whatsapp_number"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.whatsapp_number"
                    required
                    autocomplete="tel"
                    placeholder="08xxxxxxxxxx"
                />

                <InputError class="mt-2" :message="form.errors.whatsapp_number" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Kata Sandi" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    value="Konfirmasi Kata Sandi"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="mt-4">
                <label class="flex items-start gap-2">
                    <Checkbox name="terms" v-model:checked="form.terms" class="mt-0.5" />
                    <span class="text-sm text-charcoal-500">
                        Saya menyetujui
                        <Link :href="route('policies.show', 'syarat-penggunaan')" class="font-medium text-terracotta-600 underline" target="_blank">Syarat Penggunaan</Link>
                        dan
                        <Link :href="route('policies.show', 'kebijakan-privasi')" class="font-medium text-terracotta-600 underline" target="_blank">Kebijakan Privasi</Link>
                        Demera.
                    </span>
                </label>
                <InputError class="mt-2" :message="form.errors.terms" />
            </div>

            <div class="mt-6 flex items-center justify-between">
                <Link
                    :href="route('login')"
                    class="rounded-md text-sm text-charcoal-500 underline hover:text-terracotta-600 focus:outline-none focus:ring-2 focus:ring-terracotta-400"
                >
                    Sudah punya akun?
                </Link>

                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Daftar
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
