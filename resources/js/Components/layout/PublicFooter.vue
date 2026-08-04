<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { waLink } from '@/lib/whatsapp';
import { PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage<PageProps>();
</script>

<template>
    <footer class="border-t border-beige-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <ApplicationLogo />
                    <p class="mt-3 max-w-xs text-sm text-charcoal-500">
                        Demera menghadirkan hunian kost yang nyaman dan terpercaya, serta fashion
                        editorial yang akan segera hadir.
                    </p>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-charcoal-800">Jelajahi</h3>
                    <ul class="mt-3 space-y-2 text-sm text-charcoal-500">
                        <li><Link :href="route().has('living.index') ? route('living.index') : '/living'" class="hover:text-terracotta-600">Demera Living</Link></li>
                        <li><Link :href="route().has('living.rooms.index') ? route('living.rooms.index') : '/living/rooms'" class="hover:text-terracotta-600">Katalog Kamar</Link></li>
                        <li><Link :href="route().has('fashion.index') ? route('fashion.index') : '/fashion'" class="hover:text-terracotta-600">Demera Fashion</Link></li>
                        <li><Link :href="route('register')" class="hover:text-terracotta-600">Daftar Akun</Link></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-charcoal-800">Kebijakan</h3>
                    <ul class="mt-3 space-y-2 text-sm text-charcoal-500">
                        <li><Link :href="route('policies.show', 'syarat-penggunaan')" class="hover:text-terracotta-600">Syarat Penggunaan</Link></li>
                        <li><Link :href="route('policies.show', 'kebijakan-privasi')" class="hover:text-terracotta-600">Kebijakan Privasi</Link></li>
                        <li><Link :href="route('policies.show', 'kebijakan-pembayaran')" class="hover:text-terracotta-600">Kebijakan Pembayaran &amp; Pembatalan</Link></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-charcoal-800">Kontak</h3>
                    <ul class="mt-3 space-y-2 text-sm text-charcoal-500">
                        <li>{{ page.props.settings.address }}</li>
                        <li>{{ page.props.settings.email }}</li>
                        <li>
                            <a
                                :href="waLink(page.props.settings.whatsapp, 'Halo Demera, saya ingin bertanya.')"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex items-center gap-1.5 font-medium text-green-600 hover:text-green-700"
                            >
                                WhatsApp Kami
                            </a>
                        </li>
                    </ul>
                    <div class="mt-4 flex gap-3">
                        <a v-if="page.props.settings.instagram" :href="page.props.settings.instagram" target="_blank" rel="noopener" class="text-charcoal-400 hover:text-terracotta-600">Instagram</a>
                        <a v-if="page.props.settings.facebook" :href="page.props.settings.facebook" target="_blank" rel="noopener" class="text-charcoal-400 hover:text-terracotta-600">Facebook</a>
                        <a v-if="page.props.settings.tiktok" :href="page.props.settings.tiktok" target="_blank" rel="noopener" class="text-charcoal-400 hover:text-terracotta-600">TikTok</a>
                    </div>
                </div>
            </div>

            <div class="mt-10 border-t border-beige-200 pt-6 text-center text-xs text-charcoal-400">
                &copy; {{ new Date().getFullYear() }} Demera. Seluruh hak cipta dilindungi.
            </div>
        </div>
    </footer>
</template>
