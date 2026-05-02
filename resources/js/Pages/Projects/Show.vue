<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    project:   Object,
    statuses:  Object,
    stats:     Object,
    canEdit:   Boolean,
    canDelete: Boolean,
});

const statusColors = {
    active:    'bg-green-100 text-green-800',
    on_hold:   'bg-yellow-100 text-yellow-800',
    completed: 'bg-blue-100 text-blue-800',
    cancelled: 'bg-red-100 text-red-800',
};

const priorityColors = {
    Critique: 'bg-red-100 text-red-700',
    Haute:    'bg-orange-100 text-orange-700',
    Moyenne:  'bg-yellow-100 text-yellow-700',
    Basse:    'bg-green-100 text-green-700',
};

function formatDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('fr-FR');
}

function deleteProject() {
    if (confirm(`Supprimer le projet "${props.project.name}" ? Cette action est irréversible.`)) {
        router.delete(route('projects.destroy', props.project.id));
    }
}
</script>

<template>
    <Head :title="project.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('projects.index')" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <div>
                        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ project.name }}</h2>
                        <span
                            class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                            :class="statusColors[project.status]"
                        >
                            {{ statuses[project.status] ?? project.status }}
                        </span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Link
                        v-if="canEdit"
                        :href="route('projects.edit', project.id)"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Modifier
                    </Link>
                    <button
                        v-if="canDelete"
                        @click="deleteProject"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500"
                    >
                        Supprimer
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- Infos + Stats -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                    <!-- Détails projet -->
                    <div class="lg:col-span-2 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-500">Détails</h3>
                            <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                                <div>
                                    <dt class="text-gray-500">Créé par</dt>
                                    <dd class="font-medium text-gray-900">{{ project.created_by?.name ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Date de début</dt>
                                    <dd class="font-medium text-gray-900">{{ formatDate(project.start_date) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Date de fin prévue</dt>
                                    <dd class="font-medium text-gray-900">{{ formatDate(project.due_date) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Créé le</dt>
                                    <dd class="font-medium text-gray-900">{{ formatDate(project.created_at) }}</dd>
                                </div>
                                <div v-if="project.description" class="col-span-2">
                                    <dt class="text-gray-500">Description</dt>
                                    <dd class="mt-1 whitespace-pre-line text-gray-900">{{ project.description }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-2 gap-4 lg:grid-cols-1 lg:gap-4">
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6 text-center">
                            <p class="text-3xl font-bold text-gray-900">{{ stats.total }}</p>
                            <p class="text-sm text-gray-500">Tickets total</p>
                        </div>
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6 text-center">
                            <p class="text-3xl font-bold text-yellow-600">{{ stats.open }}</p>
                            <p class="text-sm text-gray-500">En cours</p>
                        </div>
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6 text-center">
                            <p class="text-3xl font-bold text-green-600">{{ stats.resolved }}</p>
                            <p class="text-sm text-gray-500">Résolus</p>
                        </div>
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6 text-center">
                            <p class="text-3xl font-bold text-gray-400">{{ stats.closed }}</p>
                            <p class="text-sm text-gray-500">Fermés</p>
                        </div>
                    </div>
                </div>

                <!-- Tickets du projet -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                        <h3 class="font-semibold text-gray-900">Tickets du projet</h3>
                        <Link
                            :href="route('tickets.create', { project_id: project.id })"
                            class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500"
                        >
                            + Nouveau ticket
                        </Link>
                    </div>
                    <div class="p-6">
                        <div v-if="project.tickets.length === 0" class="py-6 text-center text-sm text-gray-500">
                            Aucun ticket associé à ce projet.
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">N°</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Sujet</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Type</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Priorité</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Statut</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Assigné à</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="ticket in project.tickets" :key="ticket.id" class="hover:bg-gray-50">
                                        <td class="whitespace-nowrap px-4 py-3">
                                            <Link
                                                :href="route('tickets.show', ticket.id)"
                                                class="text-xs font-mono font-semibold text-indigo-600 hover:text-indigo-900"
                                            >
                                                {{ ticket.ticket_number }}
                                            </Link>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            <Link :href="route('tickets.show', ticket.id)" class="hover:underline">
                                                {{ ticket.subject }}
                                            </Link>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                                            {{ ticket.type?.name ?? '—' }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3">
                                            <span
                                                v-if="ticket.priority"
                                                class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                                :class="priorityColors[ticket.priority.name] ?? 'bg-gray-100 text-gray-700'"
                                            >
                                                {{ ticket.priority.name }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3">
                                            <span
                                                v-if="ticket.status"
                                                class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-gray-100 text-gray-700"
                                            >
                                                {{ ticket.status.name }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                                            {{ ticket.assigned_to?.name ?? '—' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
