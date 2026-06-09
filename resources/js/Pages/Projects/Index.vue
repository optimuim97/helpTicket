<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import TableSkeleton from '@/Components/TableSkeleton.vue';
import { useInertiaLoading } from '@/composables/useInertiaLoading';

const { isLoading } = useInertiaLoading();

const props = defineProps({
    projects: Object,
    filters: Object,
    statuses: Object,
});

const search = ref(props.filters?.search ?? '');
const status = ref(props.filters?.status ?? '');

const statusBadge = {
    active:    'bg-emerald-100 text-emerald-700',
    on_hold:   'bg-accent-100 text-accent-700',
    completed: 'bg-primary-100 text-primary-700',
    cancelled: 'bg-red-100 text-red-700',
};

let searchTimeout = null;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
});
watch(status, applyFilters);

function applyFilters() {
    router.get(route('projects.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true, replace: true });
}

function deleteProject(project) {
    if (confirm(`Supprimer le projet « ${project.name} » ? Action irréversible.`)) {
        router.delete(route('projects.destroy', project.id));
    }
}

const selectClass = 'block w-full rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-primary focus:ring-primary';
</script>

<template>
    <Head title="Projets" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Projets</h1>
                    <p class="text-sm text-slate-500">Suivez l'avancement et associez vos tickets.</p>
                </div>
                <Link :href="route('projects.create')" class="btn-accent">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nouveau projet
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <section class="card p-5">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Recherche</label>
                        <TextInput v-model="search" type="text" placeholder="Rechercher un projet…" class="w-full" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Statut</label>
                        <select v-model="status" :class="selectClass">
                            <option value="">Tous</option>
                            <option v-for="(label, key) in statuses" :key="key" :value="key">{{ label }}</option>
                        </select>
                    </div>
                </div>
            </section>

            <section class="card overflow-hidden">
                <div v-if="!isLoading && projects.data.length === 0" class="px-6 py-16 text-center text-sm text-slate-500">
                    Aucun projet trouvé.
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <th class="px-6 py-3">Nom</th>
                                <th class="px-6 py-3">Statut</th>
                                <th class="px-6 py-3">Tickets</th>
                                <th class="px-6 py-3">Échéance</th>
                                <th class="px-6 py-3">Créé par</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <TableSkeleton v-if="isLoading" :rows="6" :cols="6" />
                            <tr v-for="project in (isLoading ? [] : projects.data)" :key="project.id" class="transition hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <Link
                                        :href="route('projects.show', project.id)"
                                        class="text-sm font-semibold text-primary hover:text-primary-700"
                                    >
                                        {{ project.name }}
                                    </Link>
                                    <p v-if="project.description" class="mt-0.5 line-clamp-1 text-xs text-slate-500">
                                        {{ project.description }}
                                    </p>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span :class="['badge', statusBadge[project.status]]">
                                        {{ statuses[project.status] ?? project.status }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="badge bg-primary-100 text-primary-700">{{ project.tickets_count }}</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-600">
                                    {{ project.due_date ? new Date(project.due_date).toLocaleDateString('fr-FR') : '—' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-600">
                                    {{ project.created_by?.name ?? '—' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    <Link :href="route('projects.show', project.id)" class="font-medium text-slate-500 hover:text-slate-900">Voir</Link>
                                    <Link :href="route('projects.edit', project.id)" class="ml-4 font-medium text-primary hover:text-primary-700">Modifier</Link>
                                    <button @click="deleteProject(project)" class="ml-4 font-medium text-red-600 hover:text-red-700">Supprimer</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="projects.last_page > 1" class="flex flex-col gap-3 border-t border-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-600">
                        <span class="font-semibold">{{ projects.from }}</span>–<span class="font-semibold">{{ projects.to }}</span>
                        sur <span class="font-semibold">{{ projects.total }}</span>
                    </p>
                    <nav class="flex flex-wrap gap-1">
                        <Link
                            v-for="link in projects.links"
                            :key="link.label"
                            :href="link.url ?? '#'"
                            v-html="link.label"
                            :class="[
                                'min-w-[2.25rem] rounded-md px-3 py-1.5 text-center text-sm transition',
                                link.active ? 'bg-primary text-white shadow-soft' : 'border border-slate-200 text-slate-700 hover:bg-slate-50',
                                !link.url ? 'pointer-events-none opacity-40' : '',
                            ]"
                        />
                    </nav>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
