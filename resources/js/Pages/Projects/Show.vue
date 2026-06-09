<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TableSkeleton from '@/Components/TableSkeleton.vue';
import { useInertiaLoading } from '@/composables/useInertiaLoading';

const { isLoading } = useInertiaLoading();

const props = defineProps({
    project: Object,
    statuses: Object,
    stats: Object,
    canEdit: Boolean,
    canDelete: Boolean,
});

const statusBadge = {
    active: 'bg-emerald-100 text-emerald-700',
    on_hold: 'bg-accent-100 text-accent-700',
    completed: 'bg-primary-100 text-primary-700',
    cancelled: 'bg-red-100 text-red-700',
};

const priorityBadge = {
    Critique: 'bg-red-100 text-red-700',
    Haute: 'bg-accent-100 text-accent-700',
    Moyenne: 'bg-primary-100 text-primary-700',
    Basse: 'bg-emerald-100 text-emerald-700',
};

const formatDate = (d) => d ? new Date(d).toLocaleDateString('fr-FR') : '—';

const deleteProject = () => {
    if (confirm(`Supprimer le projet « ${props.project.name} » ? Action irréversible.`)) {
        router.delete(route('projects.destroy', props.project.id));
    }
};
</script>

<template>
    <Head :title="project.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('projects.index')" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold text-slate-900">{{ project.name }}</h1>
                            <span :class="['badge', statusBadge[project.status]]">{{ statuses[project.status] ?? project.status }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Link v-if="canEdit" :href="route('projects.edit', project.id)" class="btn-ghost">Modifier</Link>
                    <button v-if="canDelete" @click="deleteProject" class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-soft hover:bg-red-700">Supprimer</button>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Détails -->
            <section class="card p-6 lg:col-span-2">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Détails</h3>
                <dl class="mt-4 grid grid-cols-1 gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-xs text-slate-500">Créé par</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ project.created_by?.name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Date de début</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ formatDate(project.start_date) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Date de fin prévue</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ formatDate(project.due_date) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Créé le</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ formatDate(project.created_at) }}</dd>
                    </div>
                    <div v-if="project.description" class="sm:col-span-2">
                        <dt class="text-xs text-slate-500">Description</dt>
                        <dd class="mt-1 whitespace-pre-line text-slate-700">{{ project.description }}</dd>
                    </div>
                </dl>
            </section>

            <!-- Stats -->
            <aside class="grid grid-cols-2 gap-4 lg:grid-cols-1">
                <div class="stat-card">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ stats.total }}</p>
                </div>
                <div class="stat-card">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">En cours</p>
                    <p class="mt-2 text-3xl font-bold text-accent-600">{{ stats.open }}</p>
                </div>
                <div class="stat-card">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Résolus</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-600">{{ stats.resolved }}</p>
                </div>
                <div class="stat-card">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Fermés</p>
                    <p class="mt-2 text-3xl font-bold text-slate-400">{{ stats.closed }}</p>
                </div>
            </aside>
        </div>

        <!-- Tickets -->
        <section class="card mt-6 overflow-hidden">
            <header class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h2 class="text-base font-semibold text-slate-800">Tickets du projet</h2>
                <Link :href="route('tickets.create', { project_id: project.id })" class="btn-accent">
                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nouveau ticket
                </Link>
            </header>

            <div v-if="!isLoading && project.tickets.length === 0" class="px-6 py-12 text-center text-sm text-slate-500">
                Aucun ticket associé.
            </div>
            <div v-else class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <th class="px-6 py-3">N°</th>
                            <th class="px-6 py-3">Sujet</th>
                            <th class="px-6 py-3">Type</th>
                            <th class="px-6 py-3">Priorité</th>
                            <th class="px-6 py-3">Statut</th>
                            <th class="px-6 py-3">Assigné à</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <TableSkeleton v-if="isLoading" :rows="4" :cols="6" />
                        <tr v-for="ticket in (isLoading ? [] : project.tickets)" :key="ticket.id" class="transition hover:bg-slate-50">
                            <td class="whitespace-nowrap px-6 py-3">
                                <Link :href="route('tickets.show', ticket.id)" class="font-mono text-xs font-semibold text-primary hover:text-primary-700">
                                    {{ ticket.ticket_number }}
                                </Link>
                            </td>
                            <td class="px-6 py-3 text-slate-700">
                                <Link :href="route('tickets.show', ticket.id)" class="hover:underline">{{ ticket.subject }}</Link>
                            </td>
                            <td class="whitespace-nowrap px-6 py-3 text-slate-500">{{ ticket.type?.name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-3">
                                <span v-if="ticket.priority" :class="['badge', priorityBadge[ticket.priority.name] ?? 'bg-slate-100 text-slate-600']">
                                    {{ ticket.priority.name }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-3">
                                <span v-if="ticket.status" class="badge bg-slate-100 text-slate-700">{{ ticket.status.name }}</span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-3 text-slate-500">{{ ticket.assigned_to?.name ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
