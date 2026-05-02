<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    projects: Object,
    filters:  Object,
    statuses: Object,
});

const search = ref(props.filters?.search ?? '');
const status = ref(props.filters?.status ?? '');

const statusColors = {
    active:    'bg-green-100 text-green-800',
    on_hold:   'bg-yellow-100 text-yellow-800',
    completed: 'bg-blue-100 text-blue-800',
    cancelled: 'bg-red-100 text-red-800',
};

let searchTimeout = null;
watch(search, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 400);
});
watch(status, () => applyFilters());

function applyFilters() {
    router.get(route('projects.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true, replace: true });
}

function deleteProject(project) {
    if (confirm(`Supprimer le projet "${project.name}" ? Cette action est irréversible.`)) {
        router.delete(route('projects.destroy', project.id));
    }
}
</script>

<template>
    <Head title="Projets" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Gestion des projets
                </h2>
                <Link
                    :href="route('projects.create')"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                >
                    Nouveau projet
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-4">

                <!-- Filtres -->
                <div class="flex gap-3">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Rechercher un projet…"
                        class="w-64 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <select
                        v-model="status"
                        class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Tous les statuts</option>
                        <option v-for="(label, key) in statuses" :key="key" :value="key">
                            {{ label }}
                        </option>
                    </select>
                </div>

                <!-- Tableau -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nom</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Statut</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tickets</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Échéance</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Créé par</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-if="projects.data.length === 0">
                                        <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                                            Aucun projet trouvé.
                                        </td>
                                    </tr>
                                    <tr v-for="project in projects.data" :key="project.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <Link
                                                :href="route('projects.show', project.id)"
                                                class="text-sm font-medium text-indigo-600 hover:text-indigo-900"
                                            >
                                                {{ project.name }}
                                            </Link>
                                            <p v-if="project.description" class="mt-0.5 text-xs text-gray-400 line-clamp-1">
                                                {{ project.description }}
                                            </p>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span
                                                class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                                :class="statusColors[project.status]"
                                            >
                                                {{ statuses[project.status] ?? project.status }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span class="inline-flex rounded-full bg-blue-100 px-2 text-xs font-semibold leading-5 text-blue-800">
                                                {{ project.tickets_count }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                            {{ project.due_date
                                                ? new Date(project.due_date).toLocaleDateString('fr-FR')
                                                : '—' }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                            {{ project.created_by?.name ?? '—' }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                            <Link
                                                :href="route('projects.show', project.id)"
                                                class="mr-3 text-gray-600 hover:text-gray-900"
                                            >
                                                Voir
                                            </Link>
                                            <Link
                                                :href="route('projects.edit', project.id)"
                                                class="mr-3 text-indigo-600 hover:text-indigo-900"
                                            >
                                                Modifier
                                            </Link>
                                            <button
                                                @click="deleteProject(project)"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                Supprimer
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="projects.last_page > 1" class="mt-4 flex justify-between text-sm text-gray-600">
                            <span>
                                {{ projects.from }}–{{ projects.to }} sur {{ projects.total }} projets
                            </span>
                            <div class="flex gap-1">
                                <Link
                                    v-for="link in projects.links"
                                    :key="link.label"
                                    :href="link.url ?? '#'"
                                    v-html="link.label"
                                    class="rounded px-3 py-1"
                                    :class="link.active
                                        ? 'bg-indigo-600 text-white'
                                        : link.url
                                            ? 'hover:bg-gray-100'
                                            : 'cursor-default opacity-40'"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
