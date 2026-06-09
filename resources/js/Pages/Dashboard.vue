<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import TableSkeleton from '@/Components/TableSkeleton.vue';
import Skeleton from '@/Components/Skeleton.vue';
import { useInertiaLoading } from '@/composables/useInertiaLoading';

const { isLoading } = useInertiaLoading();

defineProps({
    stats: Object,
    recentTickets: Array,
});

const page = usePage();
const firstName = computed(() => (page.props.auth.user.name || '').split(' ')[0]);

const priorityBadge = (priority) => ({
    'Urgent': 'bg-red-100 text-red-700',
    'Haut': 'bg-accent-100 text-accent-700',
    'Moyen': 'bg-primary-100 text-primary-700',
    'Bas': 'bg-slate-100 text-slate-600',
}[priority] || 'bg-slate-100 text-slate-600');

const statusBadge = (status) => ({
    'Nouveau': 'bg-primary-100 text-primary-700',
    'En cours': 'bg-accent-100 text-accent-700',
    'En attente': 'bg-purple-100 text-purple-700',
    'Résolu': 'bg-emerald-100 text-emerald-700',
    'Fermé': 'bg-slate-100 text-slate-600',
}[status] || 'bg-slate-100 text-slate-600');
</script>

<template>
    <Head title="Tableau de bord" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Hero greeting -->
            <section class="card relative overflow-hidden bg-gradient-to-r from-primary-600 to-primary p-6 text-white sm:p-8">
                <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm/relaxed text-white/80">Bienvenue,</p>
                        <h1 class="text-2xl font-bold sm:text-3xl">{{ firstName }} 👋</h1>
                        <p class="mt-1 max-w-xl text-sm text-white/80">
                            Voici un aperçu de l'activité support et de vos tickets en cours.
                        </p>
                    </div>
                    <Link :href="route('tickets.create')" class="btn-accent shadow-lg">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nouveau ticket
                    </Link>
                </div>
                <div class="pointer-events-none absolute -right-12 -top-12 h-48 w-48 rounded-full bg-white/10"></div>
                <div class="pointer-events-none absolute -bottom-8 right-32 h-24 w-24 rounded-full bg-accent/20"></div>
            </section>

            <!-- Stat cards -->
            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="stat-card">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total tickets</p>
                            <Skeleton v-if="isLoading" width="w-16" height="h-8" class="mt-2" />
                            <p v-else class="mt-2 text-3xl font-bold text-slate-900">{{ stats.total }}</p>
                        </div>
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary-50 text-primary">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Tickets ouverts</p>
                            <Skeleton v-if="isLoading" width="w-16" height="h-8" class="mt-2" />
                            <p v-else class="mt-2 text-3xl font-bold text-primary">{{ stats.open }}</p>
                        </div>
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-accent-50 text-accent">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Résolus aujourd'hui</p>
                            <Skeleton v-if="isLoading" width="w-16" height="h-8" class="mt-2" />
                            <p v-else class="mt-2 text-3xl font-bold text-emerald-600">{{ stats.resolved_today }}</p>
                        </div>
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                    </div>
                </div>
            </section>

            <!-- Recent tickets -->
            <section class="card">
                <header class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h2 class="text-base font-semibold text-slate-800">Tickets récents</h2>
                    <Link
                        :href="route('tickets.index')"
                        class="text-sm font-medium text-primary hover:text-primary-700"
                    >
                        Voir tout →
                    </Link>
                </header>

                <div v-if="!isLoading && (!recentTickets || recentTickets.length === 0)" class="px-6 py-12 text-center text-sm text-slate-500">
                    Aucun ticket disponible
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <th class="px-6 py-3">Numéro</th>
                                <th class="px-6 py-3">Sujet</th>
                                <th class="px-6 py-3">Type</th>
                                <th class="px-6 py-3">Priorité</th>
                                <th class="px-6 py-3">Statut</th>
                                <th class="px-6 py-3">Créé par</th>
                                <th class="px-6 py-3">Créé le</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <TableSkeleton v-if="isLoading" :rows="5" :cols="7" />
                            <tr
                                v-for="ticket in (isLoading ? [] : recentTickets)"
                                :key="ticket.id"
                                class="transition hover:bg-slate-50"
                            >
                                <td class="whitespace-nowrap px-6 py-4 font-medium">
                                    <Link
                                        :href="route('tickets.show', ticket.id)"
                                        class="text-primary hover:text-primary-700"
                                    >
                                        {{ ticket.ticket_number }}
                                    </Link>
                                </td>
                                <td class="px-6 py-4 text-slate-700">{{ ticket.subject }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ ticket.type?.name }}</td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span :class="['badge', priorityBadge(ticket.priority?.name)]">
                                        {{ ticket.priority?.name }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span :class="['badge', statusBadge(ticket.status?.name)]">
                                        {{ ticket.status?.name }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-500">
                                    {{ ticket.created_by?.name || 'N/A' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-500">
                                    {{ new Date(ticket.created_at).toLocaleDateString('fr-FR') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
