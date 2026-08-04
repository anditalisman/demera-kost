<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-cream-100 px-4 py-10"
    >
        <div>
            <Link href="/">
                <ApplicationLogo />
            </Link>
        </div>

        <div
            class="mt-8 w-full overflow-hidden rounded-2xl border border-beige-200 bg-white px-6 py-8 shadow-soft sm:max-w-md sm:px-10"
        >
            <slot />
        </div>

        <p class="mt-6 text-xs text-charcoal-400">
            &copy; {{ new Date().getFullYear() }} Demera. Fashion &amp; Living.
        </p>
    </div>
</template>
