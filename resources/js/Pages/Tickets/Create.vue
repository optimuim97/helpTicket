<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import AgentAutocomplete from '@/Components/AgentAutocomplete.vue';

const props = defineProps({
    types: Array,
    channels: Array,
    priorities: Array,
    users: Array,
    projects: Array,
    preselectedProjectId: [String, Number, null],
    interventionSheets: Array,
    equipmentAssignments: Array,
});

const form = useForm({
    project_id: props.preselectedProjectId ?? '',
    type_id: '',
    channel_id: props.channels?.[0]?.id ?? '',
    priority_id: '',
    subject: '',
    description: '',
    assigned_to: '',
    due_date: '',

    // Lier à une fiche existante
    linkable_type: '',
    linkable_id: '',

    // OU créer une fiche en parallèle
    create_intervention_sheet: false,
    intervention_sheet: { site: '', building: '', agent_name: '', incidence: 'mineur', reported_fault: '' },

    create_equipment_assignment: false,
    equipment_assignment: {
        equipment_type: '', equipment_model: '', equipment_serial: '',
        agent_matricule: '', agent_name: '', agent_direction: '', agent_department: '',
        operation_type: 'affectation',
    },
});

// === Mode "fiche liée" : 'none' | 'link_intervention' | 'link_equipment' | 'create_intervention' | 'create_equipment'
const ficheMode = ref('none');

watch(ficheMode, (m) => {
    // Reset incompatibles
    form.linkable_type = '';
    form.linkable_id = '';
    form.create_intervention_sheet = false;
    form.create_equipment_assignment = false;

    if (m === 'link_intervention') form.linkable_type = 'intervention_sheet';
    else if (m === 'link_equipment') form.linkable_type = 'equipment_assignment';
    else if (m === 'create_intervention') form.create_intervention_sheet = true;
    else if (m === 'create_equipment') form.create_equipment_assignment = true;
});

const linkableItems = computed(() => {
    if (ficheMode.value === 'link_intervention') return props.interventionSheets ?? [];
    if (ficheMode.value === 'link_equipment') return props.equipmentAssignments ?? [];
    return [];
});
const linkableLabel = (item) => {
    if (ficheMode.value === 'link_intervention')
        return `${item.reference} — ${item.site} (${item.agent_name})`;
    return `${item.reference} — ${item.equipment_type} (${item.agent_name})`;
};

// Autocomplete agent pour les fiches inline
const agentEquipSearch = ref('');
function pickAgentForEquipment(user) {
    form.equipment_assignment.agent_matricule = user.matricule ?? '';
    form.equipment_assignment.agent_name = user.name ?? '';
    if (user.service?.name) form.equipment_assignment.agent_direction = user.service.name;
    if (user.position?.metier) form.equipment_assignment.agent_department = user.position.metier;
}
const agentInterSearch = ref('');
function pickAgentForIntervention(user) {
    form.intervention_sheet.agent_name = user.name ?? '';
}

// === Doublon
const showDuplicateModal = ref(false);
const duplicates = ref([]);
const checkingDuplicates = ref(false);

const checkDuplicates = async () => {
    if (!form.subject || !form.description) return;
    checkingDuplicates.value = true;
    try {
        const response = await fetch(route('tickets.check-duplicates'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ subject: form.subject, description: form.description }),
        });
        const data = await response.json();
        if (data.has_duplicates) {
            duplicates.value = data.duplicates;
            showDuplicateModal.value = true;
        } else {
            submitForm();
        }
    } catch (e) {
        submitForm();
    } finally {
        checkingDuplicates.value = false;
    }
};

const submitForm = () => form.post(route('tickets.store'));
const handleSubmit = () => checkDuplicates();
const continueDespiteDuplicates = () => { showDuplicateModal.value = false; submitForm(); };
const closeDuplicateModal = () => { showDuplicateModal.value = false; };

// Helpers visuels pour pills
const priorityPillClass = (id) => {
    const isActive = form.priority_id === id;
    const p = props.priorities.find(x => x.id === id);
    const name = p?.name ?? '';
    const colors = {
        Urgent: 'bg-red-500 text-white border-red-500',
        Haut: 'bg-accent text-white border-accent',
        Moyen: 'bg-primary text-white border-primary',
        Bas: 'bg-slate-500 text-white border-slate-500',
    };
    if (isActive) return colors[name] ?? 'bg-primary text-white border-primary';
    return 'bg-white text-slate-700 border-slate-200 hover:border-primary-300 hover:text-primary';
};
</script>

<template>
    <Head title="Nouveau ticket" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Nouveau ticket</h1>
                <p class="text-sm text-slate-500">Décris la demande — les options avancées sont optionnelles.</p>
            </div>
        </template>

        <div class="mx-auto max-w-3xl">
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- ZONE PRINCIPALE : sujet + description (le "quoi") -->
                <section class="card overflow-hidden">
                    <div class="space-y-0 p-6">
                        <input
                            id="subject"
                            v-model="form.subject"
                            type="text"
                            placeholder="Sujet du ticket (ex : Imprimante 3ème étage ne fonctionne plus)"
                            required
                            autofocus
                            class="block w-full border-0 border-b border-transparent bg-transparent px-0 py-2 text-xl font-semibold text-slate-900 placeholder:font-normal placeholder:text-slate-400 focus:border-primary focus:outline-none focus:ring-0"
                        />
                        <InputError class="mt-1" :message="form.errors.subject" />

                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="5"
                            placeholder="Décrivez le problème : contexte, étapes pour reproduire, impact…"
                            required
                            class="mt-3 block w-full resize-y border-0 bg-transparent px-0 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-0"
                        ></textarea>
                        <InputError class="mt-1" :message="form.errors.description" />
                    </div>

                    <!-- Pills priorité juste en dessous -->
                    <div class="border-t border-slate-100 px-6 py-4">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Priorité *</p>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="priority in priorities"
                                :key="priority.id"
                                type="button"
                                @click="form.priority_id = priority.id"
                                :class="['rounded-full border px-4 py-1.5 text-sm font-medium transition', priorityPillClass(priority.id)]"
                            >
                                {{ priority.name }}
                            </button>
                        </div>
                        <InputError class="mt-2" :message="form.errors.priority_id" />
                    </div>
                </section>

                <!-- CLASSIFICATION compacte -->
                <section class="card p-6">
                    <h2 class="text-base font-semibold text-slate-800">Classification</h2>
                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <InputLabel for="type_id" value="Type *" />
                            <select id="type_id" v-model="form.type_id" required
                                class="mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-primary focus:ring-primary">
                                <option value="">Sélectionner</option>
                                <option v-for="t in types" :key="t.id" :value="t.id">{{ t.name }}</option>
                            </select>
                            <InputError class="mt-1" :message="form.errors.type_id" />
                        </div>
                        <div>
                            <InputLabel for="channel_id" value="Canal *" />
                            <select id="channel_id" v-model="form.channel_id" required
                                class="mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-primary focus:ring-primary">
                                <option value="">Sélectionner</option>
                                <option v-for="c in channels" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                            <InputError class="mt-1" :message="form.errors.channel_id" />
                        </div>
                        <div v-if="projects?.length">
                            <InputLabel for="project_id" value="Projet" />
                            <select id="project_id" v-model="form.project_id"
                                class="mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-primary focus:ring-primary">
                                <option value="">Aucun</option>
                                <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                            <InputError class="mt-1" :message="form.errors.project_id" />
                        </div>
                    </div>
                </section>

                <!-- AFFECTATION / ÉCHÉANCE -->
                <section class="card p-6">
                    <h2 class="text-base font-semibold text-slate-800">Affectation & délai</h2>
                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div v-if="users?.length">
                            <InputLabel for="assigned_to" value="Assigné à" />
                            <select id="assigned_to" v-model="form.assigned_to"
                                class="mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-primary focus:ring-primary">
                                <option value="">Non assigné</option>
                                <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                            </select>
                            <InputError class="mt-1" :message="form.errors.assigned_to" />
                        </div>
                        <div>
                            <InputLabel for="due_date" value="Échéance" />
                            <input id="due_date" v-model="form.due_date" type="datetime-local"
                                class="mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-primary focus:ring-primary" />
                            <InputError class="mt-1" :message="form.errors.due_date" />
                        </div>
                    </div>
                </section>

                <!-- FICHE LIÉE — choix radio puis contenu adaptatif -->
                <section class="card overflow-hidden">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-base font-semibold text-slate-800">Fiche liée</h2>
                        <p class="text-xs text-slate-500">Optionnel — liez ce ticket à une fiche existante ou créez-en une en même temps.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2 p-4 sm:grid-cols-5">
                        <button type="button" @click="ficheMode = 'none'"
                            :class="['rounded-lg border px-3 py-2 text-xs font-medium transition', ficheMode === 'none' ? 'border-primary bg-primary-50 text-primary-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50']">
                            Aucune
                        </button>
                        <button type="button" @click="ficheMode = 'link_intervention'"
                            :class="['rounded-lg border px-3 py-2 text-xs font-medium transition', ficheMode === 'link_intervention' ? 'border-primary bg-primary-50 text-primary-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50']">
                            🔗 Lier intervention
                        </button>
                        <button type="button" @click="ficheMode = 'link_equipment'"
                            :class="['rounded-lg border px-3 py-2 text-xs font-medium transition', ficheMode === 'link_equipment' ? 'border-primary bg-primary-50 text-primary-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50']">
                            🔗 Lier équipement
                        </button>
                        <button type="button" @click="ficheMode = 'create_intervention'"
                            :class="['rounded-lg border px-3 py-2 text-xs font-medium transition', ficheMode === 'create_intervention' ? 'border-accent bg-accent-50 text-accent-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50']">
                            ➕ Créer intervention
                        </button>
                        <button type="button" @click="ficheMode = 'create_equipment'"
                            :class="['rounded-lg border px-3 py-2 text-xs font-medium transition', ficheMode === 'create_equipment' ? 'border-accent bg-accent-50 text-accent-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50']">
                            ➕ Créer affectation
                        </button>
                    </div>

                    <!-- Lier existante -->
                    <div v-if="ficheMode === 'link_intervention' || ficheMode === 'link_equipment'" class="border-t border-slate-100 p-6">
                        <InputLabel value="Fiche à lier" />
                        <select v-model="form.linkable_id"
                            class="mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-primary focus:ring-primary">
                            <option value="">Sélectionner une fiche</option>
                            <option v-for="item in linkableItems" :key="item.id" :value="item.id">
                                {{ linkableLabel(item) }}
                            </option>
                        </select>
                        <InputError class="mt-1" :message="form.errors.linkable_id" />
                    </div>

                    <!-- Créer intervention inline -->
                    <div v-if="ficheMode === 'create_intervention'" class="space-y-4 border-t border-slate-100 p-6">
                        <div class="rounded-lg border border-accent-100 bg-accent-50 p-3 text-xs text-accent-800">
                            💡 La fiche sera créée en brouillon et liée à ce ticket. Vous pourrez la compléter ensuite.
                        </div>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <InputLabel value="Site *" />
                                <TextInput v-model="form.intervention_sheet.site" type="text" class="mt-1.5" />
                            </div>
                            <div>
                                <InputLabel value="Bâtiment *" />
                                <TextInput v-model="form.intervention_sheet.building" type="text" class="mt-1.5" />
                            </div>
                            <div class="md:col-span-2">
                                <AgentAutocomplete
                                    v-model="agentInterSearch"
                                    label="Agent signalant *"
                                    placeholder="Rechercher dans l'annuaire…"
                                    @select="pickAgentForIntervention"
                                />
                            </div>
                            <div class="md:col-span-2">
                                <InputLabel value="Nom de l'agent (rempli automatiquement)" />
                                <TextInput v-model="form.intervention_sheet.agent_name" type="text" class="mt-1.5" />
                            </div>
                            <div>
                                <InputLabel value="Incidence" />
                                <select v-model="form.intervention_sheet.incidence"
                                    class="mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-primary focus:ring-primary">
                                    <option value="mineur">Mineur</option>
                                    <option value="majeur">Majeur</option>
                                    <option value="critique">Critique</option>
                                </select>
                            </div>
                            <div>
                                <InputLabel value="Panne signalée" />
                                <TextInput v-model="form.intervention_sheet.reported_fault" type="text" class="mt-1.5" placeholder="Bref résumé" />
                            </div>
                        </div>
                    </div>

                    <!-- Créer affectation inline -->
                    <div v-if="ficheMode === 'create_equipment'" class="space-y-4 border-t border-slate-100 p-6">
                        <div class="rounded-lg border border-accent-100 bg-accent-50 p-3 text-xs text-accent-800">
                            💡 La fiche d'affectation sera créée en brouillon et liée à ce ticket. Complétez-la après pour signature.
                        </div>
                        <div>
                            <AgentAutocomplete
                                v-model="agentEquipSearch"
                                label="Bénéficiaire (annuaire)"
                                placeholder="Rechercher dans l'annuaire…"
                                @select="pickAgentForEquipment"
                            />
                        </div>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <InputLabel value="Matricule *" />
                                <TextInput v-model="form.equipment_assignment.agent_matricule" type="text" class="mt-1.5" />
                            </div>
                            <div>
                                <InputLabel value="Nom de l'agent *" />
                                <TextInput v-model="form.equipment_assignment.agent_name" type="text" class="mt-1.5" />
                            </div>
                            <div>
                                <InputLabel value="Direction" />
                                <TextInput v-model="form.equipment_assignment.agent_direction" type="text" class="mt-1.5" />
                            </div>
                            <div>
                                <InputLabel value="Département" />
                                <TextInput v-model="form.equipment_assignment.agent_department" type="text" class="mt-1.5" />
                            </div>
                            <div>
                                <InputLabel value="Type équipement *" />
                                <TextInput v-model="form.equipment_assignment.equipment_type" type="text" class="mt-1.5" placeholder="Laptop, écran…" />
                            </div>
                            <div>
                                <InputLabel value="Modèle *" />
                                <TextInput v-model="form.equipment_assignment.equipment_model" type="text" class="mt-1.5" />
                            </div>
                            <div>
                                <InputLabel value="N° série *" />
                                <TextInput v-model="form.equipment_assignment.equipment_serial" type="text" class="mt-1.5" />
                            </div>
                            <div>
                                <InputLabel value="Opération" />
                                <select v-model="form.equipment_assignment.operation_type"
                                    class="mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-primary focus:ring-primary">
                                    <option value="affectation">Affectation</option>
                                    <option value="remplacement">Remplacement</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="flex items-center justify-end gap-3 pb-12">
                    <SecondaryButton type="button" @click="$inertia.visit(route('tickets.index'))">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="form.processing || checkingDuplicates">
                        {{ checkingDuplicates ? 'Vérification…' : 'Créer le ticket' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>

        <!-- Modal doublons -->
        <Modal :show="showDuplicateModal" @close="closeDuplicateModal">
            <div class="p-6">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-accent-100 text-accent-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Tickets similaires détectés</h3>
                        <p class="mt-1 text-sm text-slate-600">Ces tickets ressemblent au vôtre. Vérifiez avant de créer un doublon.</p>
                    </div>
                </div>

                <div class="mt-5 max-h-80 space-y-3 overflow-y-auto pr-1">
                    <div v-for="duplicate in duplicates" :key="duplicate.ticket.id" class="rounded-xl border border-slate-200 p-4">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-slate-900">{{ duplicate.ticket.ticket_number }}</span>
                            <span class="badge bg-primary-100 text-primary-700">{{ Math.round(duplicate.similarity * 100) }}% similaire</span>
                        </div>
                        <p class="mt-1 text-sm font-medium text-slate-700">{{ duplicate.ticket.subject }}</p>
                        <p class="mt-1 line-clamp-2 text-sm text-slate-500">{{ duplicate.ticket.description }}</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="closeDuplicateModal">Annuler</SecondaryButton>
                    <PrimaryButton @click="continueDespiteDuplicates">Créer quand même</PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
