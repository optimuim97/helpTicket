<script setup>
import { ref } from 'vue';

const props = defineProps({
    value: { type: [String, Number, null], default: null },
    label: { type: String, default: null },  // optionnel : libellé affiché à la place de la valeur (ex: icône)
    icon: { type: String, default: null },   // emoji ou texte préfixe (📞, 📱…)
    mono: { type: Boolean, default: false },
    placeholder: { type: String, default: '—' },
});

const copied = ref(false);
let timer = null;

async function copy() {
    if (!props.value) return;
    try {
        await navigator.clipboard.writeText(String(props.value));
    } catch {
        // fallback ancienne API
        const ta = document.createElement('textarea');
        ta.value = String(props.value);
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); } catch (_) {}
        document.body.removeChild(ta);
    }
    copied.value = true;
    clearTimeout(timer);
    timer = setTimeout(() => { copied.value = false; }, 1400);
}
</script>

<template>
    <span v-if="!value" class="text-slate-400">{{ placeholder }}</span>
    <button
        v-else
        type="button"
        @click.stop="copy"
        :title="copied ? 'Copié !' : `Copier « ${value} »`"
        class="group relative inline-flex max-w-full items-center gap-1.5 rounded-md px-1.5 py-0.5 text-left transition hover:bg-primary-50"
        :class="{ 'bg-emerald-50': copied }"
    >
        <span v-if="icon" class="shrink-0 text-xs">{{ icon }}</span>
        <span
            class="truncate"
            :class="[
                mono ? 'font-mono text-xs' : 'text-sm',
                copied ? 'text-emerald-700' : 'text-slate-700 group-hover:text-primary-700',
            ]"
        >{{ label ?? value }}</span>

        <!-- Icône copier / coché -->
        <svg
            v-if="!copied"
            class="h-3.5 w-3.5 shrink-0 text-slate-300 opacity-0 transition group-hover:opacity-100 group-hover:text-primary"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            aria-hidden="true"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
            />
        </svg>
        <svg
            v-else
            class="h-3.5 w-3.5 shrink-0 text-emerald-600"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            aria-hidden="true"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
        </svg>
    </button>
</template>
