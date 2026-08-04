<script setup lang="ts">
withDefaults(
    defineProps<{
        type?: 'button' | 'submit' | 'reset';
    }>(),
    {
        type: 'button',
    },
);
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center rounded-lg border border-beige-300 bg-white px-4 py-2.5 text-sm font-semibold text-charcoal-600 shadow-sm transition duration-150 ease-in-out hover:bg-cream-100 focus:outline-none focus:ring-2 focus:ring-terracotta-400 focus:ring-offset-2 disabled:opacity-25"
    >
        <slot />
    </button>
</template>
