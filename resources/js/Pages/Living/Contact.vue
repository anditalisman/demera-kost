<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { waLink } from '@/lib/whatsapp';
import { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage<PageProps>();
</script>

<template>
    <Head title="Kontak — Demera Living" />

    <PublicLayout>
        <section class="border-b border-beige-200 bg-beige-100 py-14">
            <div class="mx-auto max-w-4xl px-4 text-center sm:px-6">
                <p class="text-sm font-semibold uppercase tracking-widest text-terracotta-500">Demera Living</p>
                <h1 class="mt-2 font-display text-3xl font-semibold text-charcoal-800">Hubungi Kami</h1>
            </div>
        </section>

        <section class="py-14">
            <div class="mx-auto max-w-2xl px-4 text-center sm:px-6">
                <p class="text-charcoal-500">
                    Ada pertanyaan seputar kamar, ketersediaan, atau proses sewa? Tim kami siap membantu.
                </p>

                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-beige-200 bg-white p-5 text-left">
                        <p class="text-xs font-semibold uppercase tracking-wide text-charcoal-400">Alamat</p>
                        <p class="mt-1 text-sm text-charcoal-700">{{ page.props.settings.address }}</p>
                    </div>
                    <div class="rounded-xl border border-beige-200 bg-white p-5 text-left">
                        <p class="text-xs font-semibold uppercase tracking-wide text-charcoal-400">Email</p>
                        <p class="mt-1 text-sm text-charcoal-700">{{ page.props.settings.email }}</p>
                    </div>
                </div>

                <a
                    :href="waLink(page.props.settings.whatsapp, 'Halo Demera, saya ingin bertanya seputar Demera Living.')"
                    target="_blank"
                    rel="noopener"
                    class="mt-8 inline-flex items-center gap-2 rounded-lg bg-green-500 px-6 py-3 text-sm font-semibold text-white hover:bg-green-600"
                >
                    Chat via WhatsApp
                </a>
            </div>
        </section>
    </PublicLayout>
</template>
