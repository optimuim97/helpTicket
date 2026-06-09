<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AgentAutocomplete from '@/Components/AgentAutocomplete.vue';

const props = defineProps({
    availableServices: Array,
    availableEpi:      Array,
});

const form = useForm({
    site:                '',
    building:            '',
    agent_name:          '',
    reported_fault:      '',
    observation:         '',
    incidence:           'mineur',
    concerned_services:  [],
    work_done:           '',
    supplies_used:       '',
    start_date:          '',
    end_date:            '',
    epi_used:            [],
    client_satisfaction: null,
    notes:               '',
});

const agentSearch = ref('');
function onAgentPick(user) {
    form.agent_name = user.name ?? '';
}

const submit = () => form.post(route('intervention-sheets.store'));

function toggleService(service) {
    const idx = form.concerned_services.indexOf(service);
    if (idx === -1) form.concerned_services.push(service);
    else form.concerned_services.splice(idx, 1);
}

function toggleEpi(epi) {
    const idx = form.epi_used.indexOf(epi);
    if (idx === -1) form.epi_used.push(epi);
    else form.epi_used.splice(idx, 1);
}

const satisfactionLabels = { 1: '1 – Insatisfait', 2: '2 – Peu satisfait', 3: '3 – Satisfait', 4: '4 – Très satisfait' };
</script>

<template>
    <Head title="Nouvelle fiche d'intervention" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <a :href="route('intervention-sheets.index')" class="text-slate-500 hover:text-slate-700">← Retour</a>
                <h2 class="text-2xl font-bold text-slate-900">
                    Nouvelle fiche d'intervention (DL-FTI-01)
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">

                    <!-- Localisation -->
                    <div class="card overflow-hidden">
                        <div class="border-b border-slate-200 bg-slate-50 px-6 py-3">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Localisation</h3>
                        </div>
                        <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">
                            <div>
                                <InputLabel for="site" value="Site *" />
                                <TextInput id="site" v-model="form.site" type="text" class="mt-1 block w-full" required />
                                <InputError class="mt-2" :message="form.errors.site" />
                            </div>
                            <div>
                                <InputLabel for="building" value="Bâtiment *" />
                                <TextInput id="building" v-model="form.building" type="text" class="mt-1 block w-full" required />
                                <InputError class="mt-2" :message="form.errors.building" />
                            </div>
                            <div class="md:col-span-2">
                                <AgentAutocomplete
                                    v-model="agentSearch"
                                    label="Agent signalant — Rechercher dans l'annuaire"
                                    placeholder="Tapez nom, matricule ou email…"
                                    @select="onAgentPick"
                                />
                            </div>
                            <div class="md:col-span-2">
                                <InputLabel for="agent_name" value="Agent signalant *" />
                                <TextInput id="agent_name" v-model="form.agent_name" type="text" class="mt-1 block w-full" required />
                                <InputError class="mt-2" :message="form.errors.agent_name" />
                            </div>
                        </div>
                    </div>

                    <!-- Panne & Constat -->
                    <div class="card overflow-hidden">
                        <div class="border-b border-slate-200 bg-slate-50 px-6 py-3">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Panne & Constat</h3>
                        </div>
                        <div class="space-y-4 p-6">
                            <div>
                                <InputLabel for="reported_fault" value="Panne signalée *" />
                                <textarea id="reported_fault" v-model="form.reported_fault" rows="3" required
                                    class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-primary focus:ring-primary"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.reported_fault" />
                            </div>
                            <div>
                                <InputLabel for="observation" value="Constat de l'intervenant" />
                                <textarea id="observation" v-model="form.observation" rows="3"
                                    class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-primary focus:ring-primary"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.observation" />
                            </div>
                            <div>
                                <InputLabel value="Incidence *" />
                                <div class="mt-2 flex gap-6">
                                    <label v-for="inc in ['critique', 'majeur', 'mineur']" :key="inc" class="flex items-center gap-2 text-sm">
                                        <input type="radio" v-model="form.incidence" :value="inc" class="text-primary" />
                                        <span :class="inc === 'critique' ? 'text-red-600 font-medium' : inc === 'majeur' ? 'text-orange-600 font-medium' : ''">
                                            {{ inc.charAt(0).toUpperCase() + inc.slice(1) }}
                                        </span>
                                    </label>
                                </div>
                                <InputError class="mt-2" :message="form.errors.incidence" />
                            </div>
                        </div>
                    </div>

                    <!-- Services concernés -->
                    <div class="card overflow-hidden">
                        <div class="border-b border-slate-200 bg-slate-50 px-6 py-3">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Services concernés</h3>
                        </div>
                        <div class="p-6">
                            <div class="flex flex-wrap gap-3">
                                <label
                                    v-for="service in availableServices"
                                    :key="service"
                                    class="flex cursor-pointer items-center gap-2 rounded-md border px-3 py-1.5 text-sm transition"
                                    :class="form.concerned_services.includes(service)
                                        ? 'border-blue-500 bg-primary-50 text-primary-700'
                                        : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300'"
                                >
                                    <input type="checkbox" :value="service" :checked="form.concerned_services.includes(service)" @change="toggleService(service)" class="sr-only" />
                                    {{ service }}
                                </label>
                            </div>
                            <InputError class="mt-2" :message="form.errors.concerned_services" />
                        </div>
                    </div>

                    <!-- Travaux & Fournitures -->
                    <div class="card overflow-hidden">
                        <div class="border-b border-slate-200 bg-slate-50 px-6 py-3">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Travaux réalisés</h3>
                        </div>
                        <div class="space-y-4 p-6">
                            <div>
                                <InputLabel for="work_done" value="Description des travaux" />
                                <textarea id="work_done" v-model="form.work_done" rows="4"
                                    class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-primary focus:ring-primary"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.work_done" />
                            </div>
                            <div>
                                <InputLabel for="supplies_used" value="Fournitures utilisées" />
                                <textarea id="supplies_used" v-model="form.supplies_used" rows="2"
                                    class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-primary focus:ring-primary"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.supplies_used" />
                            </div>
                        </div>
                    </div>

                    <!-- Délai & EPI -->
                    <div class="card overflow-hidden">
                        <div class="border-b border-slate-200 bg-slate-50 px-6 py-3">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Délai d'intervention & EPI</h3>
                        </div>
                        <div class="space-y-4 p-6">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <InputLabel for="start_date" value="Date / heure de début *" />
                                    <input id="start_date" v-model="form.start_date" type="datetime-local" required
                                        class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-primary focus:ring-primary"
                                    />
                                    <InputError class="mt-2" :message="form.errors.start_date" />
                                </div>
                                <div>
                                    <InputLabel for="end_date" value="Date / heure de fin" />
                                    <input id="end_date" v-model="form.end_date" type="datetime-local"
                                        class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-primary focus:ring-primary"
                                    />
                                    <InputError class="mt-2" :message="form.errors.end_date" />
                                </div>
                            </div>
                            <div>
                                <InputLabel value="EPI utilisés" />
                                <div class="mt-2 flex flex-wrap gap-3">
                                    <label
                                        v-for="epi in availableEpi"
                                        :key="epi"
                                        class="flex cursor-pointer items-center gap-2 rounded-md border px-3 py-1.5 text-sm transition"
                                        :class="form.epi_used.includes(epi)
                                            ? 'border-green-500 bg-green-50 text-green-700'
                                            : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300'"
                                    >
                                        <input type="checkbox" :value="epi" :checked="form.epi_used.includes(epi)" @change="toggleEpi(epi)" class="sr-only" />
                                        {{ epi }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Satisfaction client -->
                    <div class="card overflow-hidden">
                        <div class="border-b border-slate-200 bg-slate-50 px-6 py-3">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Satisfaction client</h3>
                        </div>
                        <div class="p-6">
                            <div class="flex flex-wrap gap-4">
                                <label
                                    v-for="(label, score) in satisfactionLabels"
                                    :key="score"
                                    class="flex cursor-pointer items-center gap-2 rounded-md border px-4 py-2 text-sm transition"
                                    :class="form.client_satisfaction == score
                                        ? 'border-blue-500 bg-primary-50 font-medium text-primary-700'
                                        : 'border-slate-200 text-slate-700 hover:border-slate-300'"
                                >
                                    <input type="radio" v-model="form.client_satisfaction" :value="Number(score)" class="sr-only" />
                                    {{ label }}
                                </label>
                            </div>
                            <InputError class="mt-2" :message="form.errors.client_satisfaction" />
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="card overflow-hidden">
                        <div class="p-6">
                            <InputLabel for="notes" value="Notes complémentaires" />
                            <textarea id="notes" v-model="form.notes" rows="2"
                                class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-primary focus:ring-primary"
                            ></textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4">
                        <SecondaryButton type="button" @click="$inertia.visit(route('intervention-sheets.index'))">Annuler</SecondaryButton>
                        <PrimaryButton :disabled="form.processing">Créer la fiche</PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
