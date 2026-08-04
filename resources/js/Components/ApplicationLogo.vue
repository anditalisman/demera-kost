<script setup lang="ts">
withDefaults(
    defineProps<{
        variant?: 'dark' | 'light';
    }>(),
    { variant: 'dark' },
);
</script>

<template>
    <span
        class="inline-flex items-baseline gap-0.5 font-display text-2xl font-semibold tracking-tight"
        :class="variant === 'dark' ? 'text-charcoal-800' : 'text-cream-50'"
    >
        Demera
        <span class="text-terracotta-500">.</span>
    </span>
</template>
