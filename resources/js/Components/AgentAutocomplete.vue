<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: 'Rechercher par nom, matricule ou email…' },
    label: { type: String, default: 'Rechercher dans l\'annuaire' },
});

const emit = defineEmits(['update:modelValue', 'select']);

const query = ref(props.modelValue || '');
const results = ref([]);
const open = ref(false);
const loading = ref(false);
const highlighted = ref(-1);
const wrapper = ref(null);

let debounceTimer = null;
let abortController = null;

watch(() => props.modelValue, (v) => {
    if (v !== query.value) query.value = v;
});

watch(query, (v) => {
    emit('update:modelValue', v);
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchResults(v), 220);
});

async function fetchResults(q) {
    abortController?.abort();
    abortController = new AbortController();
    loading.value = true;
    try {
        const res = await fetch(route('users.search') + '?q=' + encodeURIComponent(q || ''), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            signal: abortController.signal,
            credentials: 'same-origin',
        });
        if (!res.ok) throw new Error('search failed');
        results.value = await res.json();
        open.value = true;
        highlighted.value = -1;
    } catch (e) {
        if (e.name !== 'AbortError') {
            results.value = [];
        }
    } finally {
        loading.value = false;
    }
}

function focusInput() {
    if (!results.value.length) fetchResults(query.value);
    else open.value = true;
}

function pick(user) {
    emit('select', user);
    query.value = user.name;
    emit('update:modelValue', user.name);
    open.value = false;
}

function onKeydown(e) {
    if (!open.value) return;
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        highlighted.value = Math.min(highlighted.value + 1, results.value.length - 1);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        highlighted.value = Math.max(highlighted.value - 1, 0);
    } else if (e.key === 'Enter' && highlighted.value >= 0) {
        e.preventDefault();
        pick(results.value[highlighted.value]);
    } else if (e.key === 'Escape') {
        open.value = false;
    }
}

function onDocClick(e) {
    if (wrapper.value && !wrapper.value.contains(e.target)) open.value = false;
}

onMounted(() => document.addEventListener('mousedown', onDocClick));
onUnmounted(() => {
    document.removeEventListener('mousedown', onDocClick);
    abortController?.abort();
    clearTimeout(debounceTimer);
});

const initials = (name) => (name || '').split(' ').filter(Boolean).slice(0, 2).map(s => s[0]).join('').toUpperCase();
</script>

<template>
    <div ref="wrapper" class="relative">
        <label class="block text-sm font-medium text-slate-700">{{ label }}</label>
        <div class="relative mt-1.5">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
            </svg>
            <input
                v-model="query"
                @focus="focusInput"
                @keydown="onKeydown"
                type="text"
                :placeholder="placeholder"
                autocomplete="off"
                class="block w-full rounded-lg border-slate-200 bg-white pl-9 text-sm text-slate-800 shadow-sm focus:border-primary focus:ring-primary"
            />
            <svg v-if="loading" class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 animate-spin text-primary" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
            </svg>
        </div>

        <div
            v-if="open"
            class="absolute z-30 mt-1 max-h-80 w-full overflow-auto rounded-xl border border-slate-200 bg-white py-1 shadow-card"
        >
            <div v-if="!results.length && !loading" class="px-4 py-6 text-center text-sm text-slate-500">
                Aucun utilisateur trouvé pour « {{ query }} ».
            </div>
            <button
                v-for="(user, idx) in results"
                :key="user.id"
                type="button"
                @click="pick(user)"
                @mouseenter="highlighted = idx"
                class="flex w-full items-center gap-3 px-3 py-2 text-left text-sm transition"
                :class="highlighted === idx ? 'bg-primary-50' : 'hover:bg-slate-50'"
            >
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-semibold text-white">
                    {{ initials(user.name) }}
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate font-medium text-slate-900">{{ user.name }}</span>
                    <span class="block truncate text-xs text-slate-500">
                        <span v-if="user.matricule" class="font-mono">{{ user.matricule }}</span>
                        <span v-if="user.matricule && user.position?.fonction"> • </span>
                        <span v-if="user.position?.fonction">{{ user.position.fonction }}</span>
                        <span v-if="(user.matricule || user.position) && user.service?.name"> • </span>
                        <span v-if="user.service?.name">{{ user.service.name }}</span>
                    </span>
                </span>
                <svg class="h-4 w-4 shrink-0 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    </div>
</template>
