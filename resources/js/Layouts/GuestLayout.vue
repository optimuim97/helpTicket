<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import PageLoader from '@/Components/PageLoader.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const appName = computed(() => {
    const v = page.props.appSettings?.app_name;
    return (!v || /helpticket/i.test(v)) ? "Port Autonome d'Abidjan" : v;
});
const appLogo = computed(() => {
    const v = page.props.appSettings?.app_logo;
    return v && !/helpticket/i.test(v) ? v : null;
});
</script>

<template>
    <div class="flex min-h-screen bg-slate-50">
        <PageLoader />

        <!-- Brand panel avec image PAA en background -->
        <aside
            class="relative hidden w-1/2 overflow-hidden bg-primary-900 lg:flex lg:flex-col lg:justify-between lg:p-12 xl:w-3/5"
            :style="{
                backgroundImage: 'url(/logo/logo_port.jpg)',
                backgroundSize: 'cover',
                backgroundPosition: 'center',
            }"
        >
            <!-- Voile gradient pour lisibilité texte -->
            <div class="absolute inset-0 bg-gradient-to-br from-primary-900/85 via-primary-800/70 to-primary-700/80"></div>

            <div class="relative z-10">
                <Link href="/" class="inline-flex items-center gap-3 text-white">
                    <span class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-xl bg-white/95 p-1 shadow-lg">
                        <img v-if="appLogo" :src="`/storage/${appLogo}`" :alt="appName" class="h-full w-full object-contain" />
                        <ApplicationLogo v-else />
                    </span>
                    <span class="text-xl font-bold leading-tight tracking-tight">{{ appName }}</span>
                </Link>
            </div>

            <div class="relative z-10 max-w-md text-white">
                <h2 class="text-3xl font-bold leading-tight drop-shadow-md xl:text-4xl">
                    Gestion des tickets <br />
                    <span class="text-accent-300">support &amp; intervention.</span>
                </h2>
                <p class="mt-4 text-base text-white/90 drop-shadow">
                    Plateforme interne du Port Autonome d'Abidjan pour le suivi des demandes, affectations d'équipement et fiches d'intervention.
                </p>

                <div class="mt-10 grid grid-cols-3 gap-4">
                    <div class="rounded-xl bg-white/10 p-4 backdrop-blur-md ring-1 ring-white/15">
                        <p class="text-2xl font-bold">24/7</p>
                        <p class="mt-1 text-xs text-white/80">Disponibilité</p>
                    </div>
                    <div class="rounded-xl bg-white/10 p-4 backdrop-blur-md ring-1 ring-white/15">
                        <p class="text-2xl font-bold">100%</p>
                        <p class="mt-1 text-xs text-white/80">Traçabilité</p>
                    </div>
                    <div class="rounded-xl bg-white/10 p-4 backdrop-blur-md ring-1 ring-white/15">
                        <p class="text-2xl font-bold">1k+</p>
                        <p class="mt-1 text-xs text-white/80">Agents</p>
                    </div>
                </div>
            </div>

            <p class="relative z-10 text-xs text-white/70">© {{ new Date().getFullYear() }} {{ appName }}. Tous droits réservés.</p>
        </aside>

        <!-- Form panel -->
        <main class="flex w-full flex-1 items-center justify-center px-4 py-12 sm:px-8 lg:w-1/2 xl:w-2/5">
            <div class="w-full max-w-md">
                <!-- Mobile logo -->
                <div class="mb-8 flex justify-center lg:hidden">
                    <Link href="/" class="flex items-center gap-3">
                        <span class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-xl bg-white p-1 shadow-card ring-1 ring-slate-200">
                            <ApplicationLogo />
                        </span>
                        <span class="text-base font-bold leading-tight text-slate-800">{{ appName }}</span>
                    </Link>
                </div>

                <slot />
            </div>
        </main>
    </div>
</template>
