<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="block w-full rounded-lg border-slate-200 bg-white text-slate-800 shadow-sm transition placeholder:text-slate-400 focus:border-primary focus:ring-primary disabled:bg-slate-50 disabled:text-slate-500"
        v-model="model"
        ref="input"
    />
</template>
