<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    tickets: Object,
    filters: Object,
    types: Array,
    priorities: Array,
    statuses: Array,
    users: Array,
});

const search = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status_id || '');
const selectedPriority = ref(props.filters.priority_id || '');
const selectedType = ref(props.filters.type_id || '');
const selectedAssignee = ref(props.filters.assigned_to || '');

watch([search, selectedStatus, selectedPriority, selectedType, selectedAssignee], () => {
    router.get(route('tickets.index'), {
        search: search.value,
        status_id: selectedStatus.value,
        priority_id: selectedPriority.value,
        type_id: selectedType.value,
        assigned_to: selectedAssignee.value,
    }, {
        preserveState: true,
        replace: true,
    });
}, { throttle: 300 });

const clearFilters = () => {
    search.value = '';
    selectedStatus.value = '';
    selectedPriority.value = '';
    selectedType.value = '';
    selectedAssignee.value = '';
};

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
    <Head title="Tickets" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Tickets
                </h2>
                <Link
                    :href="route('tickets.create')"
                    class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-blue-700 focus:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:bg-blue-900"
                >
                    Créer un ticket
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Recherche</label>
                                <TextInput
                                    v-model="search"
                                    type="text"
                                    placeholder="Numéro ou sujet..."
                                    class="w-full"
                                />
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Statut</label>
                                <select
                                    v-model="selectedStatus"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="">Tous</option>
                                    <option v-for="status in statuses" :key="status.id" :value="status.id">
                                        {{ status.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Priorité</label>
                                <select
                                    v-model="selectedPriority"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="">Toutes</option>
                                    <option v-for="priority in priorities" :key="priority.id" :value="priority.id">
                                        {{ priority.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Type</label>
                                <select
                                    v-model="selectedType"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="">Tous</option>
                                    <option v-for="type in types" :key="type.id" :value="type.id">
                                        {{ type.name }}
                                    </option>
                                </select>
                            </div>

                            <div v-if="users">
                                <label class="mb-1 block text-sm font-medium text-gray-700">Assigné à</label>
                                <select
                                    v-model="selectedAssignee"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="">Tous</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button
                                @click="clearFilters"
                                class="text-sm text-gray-600 hover:text-gray-900"
                            >
                                Réinitialiser les filtres
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tickets Table -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div v-if="tickets.data.length === 0" class="p-6 text-center text-gray-500">
                        Aucun ticket trouvé
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
                                        Assigné à
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Créé le
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr
                                    v-for="ticket in tickets.data"
                                    :key="ticket.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ ticket.ticket_number }}
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
                                        {{ ticket.assigned_to?.name || 'Non assigné' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ new Date(ticket.created_at).toLocaleDateString('fr-FR') }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <Link
                                            :href="route('tickets.show', ticket.id)"
                                            class="text-blue-600 hover:text-blue-900"
                                        >
                                            Voir
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="tickets.links.length > 3" class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Affichage de
                                <span class="font-medium">{{ tickets.from }}</span>
                                à
                                <span class="font-medium">{{ tickets.to }}</span>
                                sur
                                <span class="font-medium">{{ tickets.total }}</span>
                                résultats
                            </div>

                            <div class="flex space-x-2">
                                <Link
                                    v-for="(link, index) in tickets.links"
                                    :key="index"
                                    :href="link.url"
                                    :class="[
                                        'rounded border px-3 py-1 text-sm',
                                        link.active
                                            ? 'border-blue-600 bg-blue-600 text-white'
                                            : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50',
                                        !link.url ? 'cursor-not-allowed opacity-50' : '',
                                    ]"
                                    :disabled="!link.url"
                                    :v-html="link.label"
                                >
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>