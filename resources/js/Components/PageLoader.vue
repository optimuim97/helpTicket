<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const loading = ref(false);

let removeStart, removeFinish;

onMounted(() => {
    removeStart = router.on('start', () => { loading.value = true; });
    removeFinish = router.on('finish', () => { loading.value = false; });
});

onUnmounted(() => {
    removeStart?.();
    removeFinish?.();
});
</script>

<template>
    <Transition
        enter-active-class="transition-opacity duration-150"
        enter-from-class="opacity-0"
        leave-active-class="transition-opacity duration-200"
        leave-to-class="opacity-0"
    >
        <div
            v-if="loading"
            class="fixed inset-0 z-50 flex items-center justify-center bg-white/60 backdrop-blur-sm"
            aria-live="polite"
            aria-label="Chargement en cours"
        >
            <div class="flex flex-col items-center gap-3">
                <svg
                    class="h-10 w-10 animate-spin text-indigo-600"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle
                        class="opacity-25"
                        cx="12" cy="12" r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    />
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                    />
                </svg>
                <span class="text-sm font-medium text-gray-600">Chargement…</span>
            </div>
        </div>
    </Transition>
</template>
