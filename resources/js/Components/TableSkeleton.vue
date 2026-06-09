<script setup>
defineProps({
    rows: { type: Number, default: 6 },
    cols: { type: Number, default: 5 },
});
</script>

<template>
    <tr
        v-for="r in rows"
        :key="`sk-${r}`"
        class="border-b border-slate-100 last:border-0"
    >
        <td v-for="c in cols" :key="`sk-${r}-${c}`" class="px-6 py-4">
            <span
                class="block h-3.5 animate-pulse rounded bg-slate-200"
                :class="c === 1 ? 'w-24' : (c === cols ? 'w-16 ml-auto' : 'w-full max-w-[200px]')"
                :style="{ animationDelay: ((r - 1) * 60) + 'ms' }"
            ></span>
        </td>
    </tr>
</template>
