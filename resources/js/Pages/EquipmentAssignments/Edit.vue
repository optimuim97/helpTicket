<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AgentAutocomplete from '@/Components/AgentAutocomplete.vue';
import { ref } from 'vue';

const props = defineProps({ assignment: Object });

const form = useForm({
    equipment_type: props.assignment.equipment_type,
    equipment_model: props.assignment.equipment_model,
    equipment_serial: props.assignment.equipment_serial,
    equipment_mac: props.assignment.equipment_mac ?? '',
    agent_matricule: props.assignment.agent_matricule,
    agent_name: props.assignment.agent_name,
    agent_direction: props.assignment.agent_direction,
    agent_department: props.assignment.agent_department,
    operation_type: props.assignment.operation_type,
    old_equipment_serial: props.assignment.old_equipment_serial ?? '',
    notes: props.assignment.notes ?? '',
});

const submit = () => form.patch(route('equipment-assignments.update', props.assignment.id));

const agentSearch = ref('');
function onAgentPick(user) {
    form.agent_matricule = user.matricule ?? '';
    form.agent_name = user.name ?? '';
    if (user.service?.name) form.agent_direction = user.service.name;
    if (user.position?.metier && !form.agent_department) form.agent_department = user.position.metier;
}

const inputClass = 'mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-primary focus:ring-primary';
</script>

<template>
    <Head :title="`Modifier ${assignment.reference}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('equipment-assignments.show', assignment.id)" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-primary">Édition fiche</p>
                    <h1 class="text-2xl font-bold text-slate-900">{{ assignment.reference }}</h1>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-4xl">
            <form @submit.prevent="submit" class="space-y-6">
                <section class="card overflow-hidden">
                    <header class="border-b border-slate-100 bg-slate-50 px-6 py-3">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-600">Équipement</h3>
                    </header>
                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">
                        <div>
                            <InputLabel for="equipment_type" value="Type d'équipement *" />
                            <TextInput id="equipment_type" v-model="form.equipment_type" type="text" class="mt-1.5" required />
                            <InputError class="mt-2" :message="form.errors.equipment_type" />
                        </div>
                        <div>
                            <InputLabel for="equipment_model" value="Modèle *" />
                            <TextInput id="equipment_model" v-model="form.equipment_model" type="text" class="mt-1.5" required />
                            <InputError class="mt-2" :message="form.errors.equipment_model" />
                        </div>
                        <div>
                            <InputLabel for="equipment_serial" value="Numéro de série *" />
                            <TextInput id="equipment_serial" v-model="form.equipment_serial" type="text" class="mt-1.5" required />
                            <InputError class="mt-2" :message="form.errors.equipment_serial" />
                        </div>
                        <div>
                            <InputLabel for="equipment_mac" value="Adresse MAC" />
                            <TextInput id="equipment_mac" v-model="form.equipment_mac" type="text" class="mt-1.5" />
                            <InputError class="mt-2" :message="form.errors.equipment_mac" />
                        </div>
                    </div>
                </section>

                <section class="card overflow-hidden">
                    <header class="border-b border-slate-100 bg-slate-50 px-6 py-3">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-600">Agent bénéficiaire</h3>
                    </header>
                    <div class="space-y-5 p-6">
                        <div class="rounded-xl border border-dashed border-primary-200 bg-primary-50/40 p-4">
                            <AgentAutocomplete
                                v-model="agentSearch"
                                label="Rechercher dans l'annuaire"
                                placeholder="Tapez nom, matricule ou email…"
                                @select="onAgentPick"
                            />
                            <p class="mt-2 text-xs text-slate-500">
                                💡 Les champs ci-dessous se rempliront automatiquement.
                            </p>
                        </div>
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <InputLabel for="agent_matricule" value="Matricule *" />
                            <TextInput id="agent_matricule" v-model="form.agent_matricule" type="text" class="mt-1.5" required />
                            <InputError class="mt-2" :message="form.errors.agent_matricule" />
                        </div>
                        <div>
                            <InputLabel for="agent_name" value="Nom et prénom *" />
                            <TextInput id="agent_name" v-model="form.agent_name" type="text" class="mt-1.5" required />
                            <InputError class="mt-2" :message="form.errors.agent_name" />
                        </div>
                        <div>
                            <InputLabel for="agent_direction" value="Direction *" />
                            <TextInput id="agent_direction" v-model="form.agent_direction" type="text" class="mt-1.5" required />
                            <InputError class="mt-2" :message="form.errors.agent_direction" />
                        </div>
                        <div>
                            <InputLabel for="agent_department" value="Département *" />
                            <TextInput id="agent_department" v-model="form.agent_department" type="text" class="mt-1.5" required />
                            <InputError class="mt-2" :message="form.errors.agent_department" />
                        </div>
                        </div>
                    </div>
                </section>

                <section class="card overflow-hidden">
                    <header class="border-b border-slate-100 bg-slate-50 px-6 py-3">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-600">Type d'opération</h3>
                    </header>
                    <div class="space-y-5 p-6">
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div>
                                <InputLabel value="Opération *" />
                                <div class="mt-2 flex gap-6">
                                    <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700">
                                        <input type="radio" v-model="form.operation_type" value="affectation" class="text-primary focus:ring-primary" />
                                        Affectation
                                    </label>
                                    <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700">
                                        <input type="radio" v-model="form.operation_type" value="remplacement" class="text-primary focus:ring-primary" />
                                        Remplacement
                                    </label>
                                </div>
                                <InputError class="mt-2" :message="form.errors.operation_type" />
                            </div>
                            <div v-if="form.operation_type === 'remplacement'">
                                <InputLabel for="old_equipment_serial" value="N° série ancien équipement *" />
                                <TextInput id="old_equipment_serial" v-model="form.old_equipment_serial" type="text" class="mt-1.5" />
                                <InputError class="mt-2" :message="form.errors.old_equipment_serial" />
                            </div>
                        </div>
                        <div>
                            <InputLabel for="notes" value="Notes / Observations" />
                            <textarea id="notes" v-model="form.notes" rows="3" :class="inputClass"></textarea>
                            <InputError class="mt-2" :message="form.errors.notes" />
                        </div>
                    </div>
                </section>

                <div class="flex items-center justify-end gap-3">
                    <SecondaryButton type="button" @click="$inertia.visit(route('equipment-assignments.show', assignment.id))">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="form.processing">Enregistrer</PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
