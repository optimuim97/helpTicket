<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import TableSkeleton from '@/Components/TableSkeleton.vue';
import { useInertiaLoading } from '@/composables/useInertiaLoading';

const { isLoading } = useInertiaLoading();

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

const priorityBadge = (p) => ({
    'Urgent': 'bg-red-100 text-red-700',
    'Haut': 'bg-accent-100 text-accent-700',
    'Moyen': 'bg-primary-100 text-primary-700',
    'Bas': 'bg-slate-100 text-slate-600',
}[p] || 'bg-slate-100 text-slate-600');

const statusBadge = (s) => ({
    'Nouveau': 'bg-primary-100 text-primary-700',
    'En cours': 'bg-accent-100 text-accent-700',
    'En attente': 'bg-purple-100 text-purple-700',
    'Résolu': 'bg-emerald-100 text-emerald-700',
    'Fermé': 'bg-slate-100 text-slate-600',
}[s] || 'bg-slate-100 text-slate-600');

const selectClass = 'block w-full rounded-lg border-slate-200 bg-white text-sm text-slate-800 shadow-sm focus:border-primary focus:ring-primary';
</script>

<template>
    <Head title="Tickets" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Tickets</h1>
                    <p class="text-sm text-slate-500">Suivez, filtrez et gérez les demandes support.</p>
                </div>
                <Link :href="route('tickets.create')" class="btn-accent">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Créer un ticket
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Filtres -->
            <section class="card p-5">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Recherche</label>
                        <TextInput v-model="search" type="text" placeholder="Numéro ou sujet…" class="w-full" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Statut</label>
                        <select v-model="selectedStatus" :class="selectClass">
                            <option value="">Tous</option>
                            <option v-for="status in statuses" :key="status.id" :value="status.id">{{ status.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Priorité</label>
                        <select v-model="selectedPriority" :class="selectClass">
                            <option value="">Toutes</option>
                            <option v-for="priority in priorities" :key="priority.id" :value="priority.id">{{ priority.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Type</label>
                        <select v-model="selectedType" :class="selectClass">
                            <option value="">Tous</option>
                            <option v-for="type in types" :key="type.id" :value="type.id">{{ type.name }}</option>
                        </select>
                    </div>
                    <div v-if="users">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Assigné à</label>
                        <select v-model="selectedAssignee" :class="selectClass">
                            <option value="">Tous</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <p class="text-xs text-slate-500">
                        {{ tickets.total }} résultat{{ tickets.total > 1 ? 's' : '' }}
                    </p>
                    <button
                        @click="clearFilters"
                        class="text-sm font-medium text-primary hover:text-primary-700"
                    >
                        Réinitialiser
                    </button>
                </div>
            </section>

            <!-- Tableau -->
            <section class="card overflow-hidden">
                <div v-if="!isLoading && tickets.data.length === 0" class="px-6 py-16 text-center text-sm text-slate-500">
                    Aucun ticket ne correspond à vos critères.
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
                                <th class="px-6 py-3">Assigné à</th>
                                <th class="px-6 py-3">Créé le</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <TableSkeleton v-if="isLoading" :rows="8" :cols="8" />
                            <tr
                                v-for="ticket in (isLoading ? [] : tickets.data)"
                                :key="ticket.id"
                                class="transition hover:bg-slate-50"
                            >
                                <td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-900">
                                    <Link :href="route('tickets.show', ticket.id)" class="text-primary hover:text-primary-700">
                                        {{ ticket.ticket_number }}
                                    </Link>
                                </td>
                                <td class="px-6 py-4 text-slate-700">{{ ticket.subject }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ ticket.type?.name }}</td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span :class="['badge', priorityBadge(ticket.priority?.name)]">{{ ticket.priority?.name }}</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span :class="['badge', statusBadge(ticket.status?.name)]">{{ ticket.status?.name }}</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-500">
                                    {{ ticket.assigned_to?.name || 'Non assigné' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-500">
                                    {{ new Date(ticket.created_at).toLocaleDateString('fr-FR') }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <Link
                                        :href="route('tickets.show', ticket.id)"
                                        class="text-sm font-medium text-primary hover:text-primary-700"
                                    >
                                        Voir →
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="tickets.links.length > 3" class="flex flex-col gap-3 border-t border-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-600">
                        <span class="font-semibold">{{ tickets.from }}</span>–<span class="font-semibold">{{ tickets.to }}</span>
                        sur <span class="font-semibold">{{ tickets.total }}</span>
                    </p>
                    <nav class="flex flex-wrap gap-1">
                        <Link
                            v-for="(link, index) in tickets.links"
                            :key="index"
                            :href="link.url || ''"
                            v-html="link.label"
                            :class="[
                                'min-w-[2.25rem] rounded-md px-3 py-1.5 text-center text-sm transition',
                                link.active
                                    ? 'bg-primary text-white shadow-soft'
                                    : 'border border-slate-200 text-slate-700 hover:bg-slate-50',
                                !link.url ? 'pointer-events-none opacity-40' : '',
                            ]"
                        />
                    </nav>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
