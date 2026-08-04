<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({ password: '' });

function submit() {
    form.delete(route('other-browser-sessions.destroy'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => form.reset(),
    });
}
</script>

<template>
    <section>
        <header>
            <h2 class="font-display text-lg font-semibold text-charcoal-800">Keluar dari Perangkat Lain</h2>
            <p class="mt-1 text-sm text-charcoal-400">
                Keluar dari sesi aktif Anda di perangkat lain, tanpa memengaruhi sesi di perangkat ini.
                Masukkan kata sandi Anda untuk konfirmasi.
            </p>
        </header>

        <form class="mt-6 max-w-sm space-y-4" @submit.prevent="submit">
            <div>
                <InputLabel for="sessions_password" value="Kata Sandi" />
                <TextInput id="sessions_password" v-model="form.password" type="password" class="mt-1 block w-full" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <PrimaryButton :disabled="form.processing">Keluar dari Perangkat Lain</PrimaryButton>

            <p v-if="form.recentlySuccessful" class="text-sm text-green-600">
                Berhasil keluar dari perangkat lain.
            </p>
        </form>
    </section>
</template>
