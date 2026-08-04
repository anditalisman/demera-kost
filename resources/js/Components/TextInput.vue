<script setup lang="ts">
import { onMounted, ref } from 'vue';

const model = defineModel<string | number | null>({ required: true });

const input = ref<HTMLInputElement | null>(null);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value?.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        class="rounded-lg border-beige-300 shadow-sm focus:border-terracotta-400 focus:ring-terracotta-400"
        v-model="model"
        ref="input"
    />
</template>
