import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

// Renvoie une ref `isLoading` qui passe à true 180 ms après le début d'une
// navigation Inertia (pour éviter le flash sur les chargements instantanés),
// et redevient false dès la fin (ou erreur).
export function useInertiaLoading(delay = 180) {
    const isLoading = ref(false);
    let timer = null;
    let removeStart, removeFinish, removeError;

    const start = () => {
        clearTimeout(timer);
        timer = setTimeout(() => { isLoading.value = true; }, delay);
    };
    const stop = () => {
        clearTimeout(timer);
        isLoading.value = false;
    };

    onMounted(() => {
        removeStart = router.on('start', start);
        removeFinish = router.on('finish', stop);
        removeError = router.on('error', stop);
    });

    onUnmounted(() => {
        clearTimeout(timer);
        removeStart?.();
        removeFinish?.();
        removeError?.();
    });

    return { isLoading };
}
