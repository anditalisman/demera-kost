<script setup lang="ts">
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({
    code: '',
});

const resendForm = useForm({});

const submit = () => {
    form.post(route('verification.whatsapp.verify'), {
        onFinish: () => form.reset('code'),
    });
};

const resend = () => {
    resendForm.post(route('verification.whatsapp.send'));
};

const otpSent = computed(() => props.status === 'whatsapp-otp-sent');
</script>

<template>
    <GuestLayout>
        <Head title="Verifikasi WhatsApp" />

        <h1 class="font-display text-xl font-semibold text-charcoal-800">
            Verifikasi nomor WhatsApp Anda
        </h1>
        <p class="mt-1 text-sm text-charcoal-400">
            Kami mengirimkan kode verifikasi 6 digit ke nomor WhatsApp yang Anda
            daftarkan. Masukkan kodenya di bawah ini untuk melanjutkan.
        </p>

        <div
            class="mt-4 text-sm font-medium text-green-600"
            v-if="otpSent"
        >
            Kode verifikasi baru telah dikirim ke WhatsApp Anda.
        </div>

        <form class="mt-6" @submit.prevent="submit">
            <div>
                <InputLabel for="code" value="Kode Verifikasi" />

                <TextInput
                    id="code"
                    type="text"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    maxlength="6"
                    class="mt-1 block w-full tracking-widest"
                    v-model="form.code"
                    required
                    autofocus
                />

                <InputError class="mt-2" :message="form.errors.code" />
            </div>

            <div class="mt-6 flex items-center justify-between">
                <button
                    type="button"
                    class="rounded-md text-sm text-charcoal-500 underline hover:text-terracotta-600 focus:outline-none focus:ring-2 focus:ring-terracotta-400 disabled:opacity-25"
                    :disabled="resendForm.processing"
                    @click="resend"
                >
                    Kirim ulang kode
                </button>

                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Verifikasi
                </PrimaryButton>
            </div>
        </form>

        <p class="mt-6 text-sm text-charcoal-400">
            Tidak menerima kode? Anda juga dapat memverifikasi lewat tautan yang
            kami kirim ke email Anda saat mendaftar.
        </p>

        <Link
            :href="route('logout')"
            method="post"
            as="button"
            class="mt-4 rounded-md text-sm text-charcoal-500 underline hover:text-terracotta-600 focus:outline-none focus:ring-2 focus:ring-terracotta-400"
        >
            Keluar
        </Link>
    </GuestLayout>
</template>
