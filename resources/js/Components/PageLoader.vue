<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

// Barre de progression top (style YouTube / NProgress) — non bloquante.
// - Délai de 180 ms avant affichage : les chargements instantanés ne flashent pas.
// - Progression auto qui se ralentit (jamais 100% avant le finish).
// - Complète à 100 % puis fade-out au `finish`.

const visible = ref(false);
const progress = ref(0);

let showTimer = null;
let progressTimer = null;
let removeStart, removeProgress, removeFinish, removeError;

const startTimers = () => {
    clearTimeout(showTimer);
    clearInterval(progressTimer);
    showTimer = setTimeout(() => {
        visible.value = true;
        progress.value = 8;
        progressTimer = setInterval(() => {
            // approche 90% asymptotiquement, jamais plus
            if (progress.value < 90) {
                const remaining = 90 - progress.value;
                progress.value += Math.max(0.3, remaining * 0.06);
            }
        }, 200);
    }, 180);
};

const finishTimers = () => {
    clearTimeout(showTimer);
    clearInterval(progressTimer);
    if (visible.value) {
        progress.value = 100;
        // laisse le temps à la transition de jouer
        setTimeout(() => {
            visible.value = false;
            progress.value = 0;
        }, 250);
    } else {
        progress.value = 0;
    }
};

onMounted(() => {
    removeStart = router.on('start', startTimers);
    removeProgress = router.on('progress', (event) => {
        // Si Inertia envoie un événement de progression (uploads), on s'aligne dessus
        if (event.detail.progress?.percentage) {
            const p = event.detail.progress.percentage;
            if (p > progress.value) progress.value = Math.min(p, 92);
        }
    });
    removeFinish = router.on('finish', finishTimers);
    removeError = router.on('error', finishTimers);
});

onUnmounted(() => {
    clearTimeout(showTimer);
    clearInterval(progressTimer);
    removeStart?.();
    removeProgress?.();
    removeFinish?.();
    removeError?.();
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
            v-if="visible"
            class="pointer-events-none fixed inset-x-0 top-0 z-[60] h-0.5"
            aria-live="polite"
            aria-label="Chargement en cours"
            role="progressbar"
            :aria-valuenow="Math.round(progress)"
            aria-valuemin="0"
            aria-valuemax="100"
        >
            <div
                class="h-full bg-gradient-to-r from-primary via-accent to-primary shadow-[0_0_10px_rgba(0,141,220,0.7)] transition-[width] duration-200 ease-out"
                :style="{ width: progress + '%' }"
            ></div>
            <!-- petit shimmer pour donner du mouvement -->
            <div
                class="absolute right-0 top-0 h-full w-12 -translate-x-2 rounded-full bg-white/60 blur-sm"
                :style="{ left: 'calc(' + progress + '% - 3rem)' }"
            ></div>
        </div>
    </Transition>
</template>
