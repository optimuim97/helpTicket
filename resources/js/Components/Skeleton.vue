<script setup>
defineProps({
    // line | circle | block
    variant: { type: String, default: 'line' },
    width: { type: String, default: null },   // ex: 'w-24', 'w-1/3'
    height: { type: String, default: null },  // ex: 'h-4'
    rounded: { type: String, default: null }, // ex: 'rounded-full'
});
</script>

<template>
    <span
        v-if="variant === 'circle'"
        class="inline-block animate-pulse rounded-full bg-slate-200"
        :class="[width ?? 'w-9', height ?? 'h-9']"
    />
    <div
        v-else-if="variant === 'block'"
        class="animate-pulse rounded-xl bg-slate-200"
        :class="[width ?? 'w-full', height ?? 'h-24', rounded]"
    />
    <span
        v-else
        class="inline-block animate-pulse rounded bg-slate-200"
        :class="[width ?? 'w-full', height ?? 'h-3.5', rounded]"
    />
</template>
