<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stats: Object,
    recentTickets: Array,
});

const getPriorityColor = (priority) => {
    const colors = {
        'Urgent': 'bg-red-100 text-red-800',
        'Haut': 'bg-orange-100 text-orange-800',
        'Moyen': 'bg-blue-100 text-blue-800',
        'Bas': 'bg-gray-100 text-gray-800',
    };
    return colors[priority] || 'bg-gray-100 text-gray-800';
};

const getStatusColor = (status) => {
    const colors = {
        'Nouveau': 'bg-blue-100 text-blue-800',
        'En cours': 'bg-yellow-100 text-yellow-800',
        'En attente': 'bg-purple-100 text-purple-800',
        'Résolu': 'bg-green-100 text-green-800',
        'Fermé': 'bg-gray-100 text-gray-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Tableau de bord" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Tableau de bord
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Statistics Cards -->
                <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500">
                                Total tickets
                            </div>
                            <div class="mt-2 text-3xl font-bold text-gray-900">
                                {{ stats.total }}
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500">
                                Tickets ouverts
                            </div>
                            <div class="mt-2 text-3xl font-bold text-blue-600">
                                {{ stats.open }}
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500">
                                Résolus aujourd'hui
                            </div>
                            <div class="mt-2 text-3xl font-bold text-green-600">
                                {{ stats.resolved_today }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Tickets Table -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Tickets récents
                        </h3>
                        <Link
                            :href="route('tickets.index')"
                            class="text-sm font-medium text-blue-600 hover:text-blue-700"
                        >
                            Voir tous les tickets →
                        </Link>
                    </div>

                    <div v-if="recentTickets.length === 0" class="p-6 text-center text-gray-500">
                        Aucun ticket disponible
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Numéro
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Sujet
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Priorité
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Statut
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Créé par
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Créé le
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr
                                    v-for="ticket in recentTickets"
                                    :key="ticket.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                        <Link
                                            :href="route('tickets.show', ticket.id)"
                                            class="text-blue-600 hover:text-blue-700"
                                        >
                                            {{ ticket.ticket_number }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ ticket.subject }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ ticket.type?.name }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span
                                            :class="getPriorityColor(ticket.priority?.name)"
                                            class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                        >
                                            {{ ticket.priority?.name }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span
                                            :class="getStatusColor(ticket.status?.name)"
                                            class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                        >
                                            {{ ticket.status?.name }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ ticket.created_by?.name || 'N/A' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ new Date(ticket.created_at).toLocaleDateString('fr-FR') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
