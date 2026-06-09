<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import TableSkeleton from '@/Components/TableSkeleton.vue';
import { useInertiaLoading } from '@/composables/useInertiaLoading';

const { isLoading } = useInertiaLoading();

const props = defineProps({
    sheets: Object,
    filters: Object,
    statuses: Object,
    incidences: Object,
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const incidence = ref(props.filters.incidence ?? '');

let searchTimer = null;
watch(search, () => { clearTimeout(searchTimer); searchTimer = setTimeout(applyFilters, 400); });
watch([status, incidence], applyFilters);

function applyFilters() {
    router.get(route('intervention-sheets.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
        incidence: incidence.value || undefined,
    }, { preserveState: true, replace: true });
}

const statusBadge = {
    brouillon: 'bg-slate-100 text-slate-700',
    valide: 'bg-accent-100 text-accent-700',
    signe: 'bg-emerald-100 text-emerald-700',
};
const incidenceBadge = {
    critique: 'bg-red-100 text-red-700',
    majeur: 'bg-accent-100 text-accent-700',
    mineur: 'bg-primary-100 text-primary-700',
};

const selectClass = 'rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-primary focus:ring-primary w-full';
</script>

<template>
    <Head title="Fiches d'intervention" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Fiches d'intervention</h1>
                    <p class="text-sm text-slate-500">DL-FTI-01 — Interventions techniques.</p>
                </div>
                <Link :href="route('intervention-sheets.create')" class="btn-accent">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nouvelle fiche
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <section class="card p-5">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Recherche</label>
                        <TextInput v-model="search" type="text" placeholder="Référence, agent, site…" class="w-full" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Statut</label>
                        <select v-model="status" :class="selectClass">
                            <option value="">Tous</option>
                            <option v-for="(label, key) in statuses" :key="key" :value="key">{{ label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Incidence</label>
                        <select v-model="incidence" :class="selectClass">
                            <option value="">Toutes</option>
                            <option v-for="(label, key) in incidences" :key="key" :value="key">{{ label }}</option>
                        </select>
                    </div>
                </div>
            </section>

            <section class="card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <th class="px-6 py-3">Référence</th>
                                <th class="px-6 py-3">Site / Bâtiment</th>
                                <th class="px-6 py-3">Agent</th>
                                <th class="px-6 py-3">Incidence</th>
                                <th class="px-6 py-3">Validations</th>
                                <th class="px-6 py-3">Statut</th>
                                <th class="px-6 py-3">Date début</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <TableSkeleton v-if="isLoading" :rows="6" :cols="8" />
                            <tr v-else-if="!sheets.data.length">
                                <td colspan="8" class="px-6 py-12 text-center text-sm text-slate-500">Aucune fiche trouvée.</td>
                            </tr>
                            <tr v-for="s in (isLoading ? [] : sheets.data)" :key="s.id" class="transition hover:bg-slate-50">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <Link :href="route('intervention-sheets.show', s.id)" class="font-semibold text-primary hover:text-primary-700">
                                        {{ s.reference }}
                                    </Link>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-900">{{ s.site }}</div>
                                    <div class="text-xs text-slate-500">{{ s.building }}</div>
                                </td>
                                <td class="px-6 py-4 text-slate-900">{{ s.agent_name }}</td>
                                <td class="px-6 py-4">
                                    <span :class="['badge', incidenceBadge[s.incidence]]">{{ incidences[s.incidence] }}</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-600">
                                    {{ s.validations?.filter(v => v.validated_at).length ?? 0 }} / 3
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="['badge', statusBadge[s.status]]">{{ statuses[s.status] }}</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-500">
                                    {{ new Date(s.start_date).toLocaleDateString('fr-FR') }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    <Link :href="route('intervention-sheets.show', s.id)" class="font-medium text-primary hover:text-primary-700">Voir</Link>
                                    <Link :href="route('intervention-sheets.edit', s.id)" class="ml-4 font-medium text-slate-600 hover:text-slate-900">Modifier</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="sheets.last_page > 1" class="flex items-center justify-between border-t border-slate-100 px-6 py-3">
                    <p class="text-sm text-slate-600">
                        <span class="font-semibold">{{ sheets.from }}</span>–<span class="font-semibold">{{ sheets.to }}</span>
                        sur <span class="font-semibold">{{ sheets.total }}</span>
                    </p>
                    <div class="flex gap-2">
                        <a v-if="sheets.prev_page_url" :href="sheets.prev_page_url" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm hover:bg-slate-50">Précédent</a>
                        <a v-if="sheets.next_page_url" :href="sheets.next_page_url" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm hover:bg-slate-50">Suivant</a>
                    </div>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
