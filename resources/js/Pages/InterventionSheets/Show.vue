<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import SignaturePad from '@/Components/SignaturePad.vue';

const props = defineProps({
    sheet:        Object,
    validations:  Object,
    roleLabels:   Object,
    statusLabels: Object,
});

const statusColors = {
    brouillon: 'bg-slate-100 text-slate-700',
    valide:    'bg-accent-100 text-accent-700',
    signe:     'bg-emerald-100 text-emerald-700',
};
const incidenceColors = {
    critique: 'bg-red-100 text-red-700',
    majeur:   'bg-accent-100 text-accent-700',
    mineur:   'bg-blue-100 text-primary-700',
};
const satisfactionLabels = { 1: 'Insatisfait', 2: 'Peu satisfait', 3: 'Satisfait', 4: 'Très satisfait' };
const satisfactionColors  = { 1: 'text-red-600', 2: 'text-orange-500', 3: 'text-primary', 4: 'text-emerald-600' };

const showModal   = ref(false);
const currentRole = ref('');
const signForm    = useForm({ validator_name: '', comment: '', signature: null });

function openModal(role) {
    currentRole.value = role;
    const existing = props.validations[role];
    signForm.validator_name = existing?.validator_name ?? '';
    signForm.comment        = existing?.comment ?? '';
    signForm.signature      = null;
    showModal.value = true;
}

function submitSign() {
    signForm.post(
        route('intervention-sheets.sign', { interventionSheet: props.sheet.id, role: currentRole.value }),
        { onSuccess: () => { showModal.value = false; signForm.reset(); } }
    );
}

function deleteSheet() {
    if (!confirm(`Supprimer la fiche ${props.sheet.reference} ?`)) return;
    router.delete(route('intervention-sheets.destroy', props.sheet.id));
}

function formatDt(v) {
    if (!v) return '—';
    return new Date(v).toLocaleString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function duration() {
    if (!props.sheet.end_date) return null;
    const ms = new Date(props.sheet.end_date) - new Date(props.sheet.start_date);
    const h  = Math.floor(ms / 3600000);
    const m  = Math.floor((ms % 3600000) / 60000);
    return `${h}h${String(m).padStart(2, '0')}`;
}
</script>

<template>
    <Head :title="sheet.reference" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between no-print">
                <div class="flex items-center gap-4">
                    <a :href="route('intervention-sheets.index')" class="text-slate-500 hover:text-slate-700">← Retour</a>
                    <h2 class="text-xl font-semibold text-slate-800">{{ sheet.reference }}</h2>
                    <span :class="['inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium', statusColors[sheet.status]]">
                        {{ statusLabels[sheet.status] }}
                    </span>
                    <span :class="['inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold', incidenceColors[sheet.incidence]]">
                        {{ sheet.incidence.charAt(0).toUpperCase() + sheet.incidence.slice(1) }}
                    </span>
                </div>
                <div class="flex gap-2">
                    <button onclick="window.print()" class="rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        🖨 Imprimer
                    </button>
                    <a v-if="sheet.status === 'brouillon'" :href="route('intervention-sheets.edit', sheet.id)"
                        class="rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Modifier
                    </a>
                    <button @click="deleteSheet" class="rounded-md bg-red-50 px-3 py-1.5 text-sm font-medium text-red-700 hover:bg-red-100">
                        Supprimer
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">

                <!-- ══════════════ PRINT DOCUMENT ══════════════ -->
                <div id="print-area" class="card print:shadow-none print:rounded-none">

                    <!-- Document header -->
                    <div class="flex items-start justify-between border-b-2 border-blue-700 p-6">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-primary-700">Port Autonome d'Abidjan</p>
                            <p class="text-xs text-slate-500">Direction des Systèmes d'Information</p>
                        </div>
                        <div class="text-center">
                            <p class="text-base font-bold uppercase text-slate-900">Fiche d'Intervention</p>
                            <p class="text-xs text-slate-500 mt-0.5">DL-FTI-01</p>
                        </div>
                        <div class="text-right text-xs">
                            <p class="font-bold text-slate-800">{{ sheet.reference }}</p>
                            <p class="text-slate-500">{{ new Date(sheet.created_at).toLocaleDateString('fr-FR') }}</p>
                        </div>
                    </div>

                    <div class="p-6 space-y-5">

                        <!-- Section: Localisation & Agent -->
                        <div class="rounded border border-slate-200">
                            <div class="border-b border-slate-200 bg-primary-50 px-4 py-2">
                                <p class="text-xs font-bold uppercase tracking-wide text-primary-700">Localisation & Agent</p>
                            </div>
                            <div class="grid grid-cols-3 gap-0 divide-x divide-y divide-gray-200">
                                <div class="p-3"><p class="text-xs text-slate-500">Site</p><p class="mt-0.5 text-sm font-medium">{{ sheet.site }}</p></div>
                                <div class="p-3"><p class="text-xs text-slate-500">Bâtiment</p><p class="mt-0.5 text-sm font-medium">{{ sheet.building }}</p></div>
                                <div class="p-3"><p class="text-xs text-slate-500">Agent signalant</p><p class="mt-0.5 text-sm font-medium">{{ sheet.agent_name }}</p></div>
                            </div>
                        </div>

                        <!-- Section: Panne & Incidence -->
                        <div class="rounded border border-slate-200">
                            <div class="border-b border-slate-200 bg-primary-50 px-4 py-2 flex items-center justify-between">
                                <p class="text-xs font-bold uppercase tracking-wide text-primary-700">Panne signalée & Incidence</p>
                                <span :class="['text-xs font-bold uppercase px-2 py-0.5 rounded', incidenceColors[sheet.incidence]]">
                                    {{ sheet.incidence }}
                                </span>
                            </div>
                            <div class="space-y-3 p-4">
                                <div>
                                    <p class="text-xs text-slate-500">Panne signalée</p>
                                    <p class="mt-0.5 whitespace-pre-wrap text-sm">{{ sheet.reported_fault }}</p>
                                </div>
                                <div v-if="sheet.observation">
                                    <p class="text-xs text-slate-500">Constat de l'intervenant</p>
                                    <p class="mt-0.5 whitespace-pre-wrap text-sm">{{ sheet.observation }}</p>
                                </div>
                                <div v-if="sheet.concerned_services?.length">
                                    <p class="text-xs text-slate-500">Services concernés</p>
                                    <p class="mt-0.5 text-sm">{{ sheet.concerned_services.join(' — ') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Travaux -->
                        <div class="rounded border border-slate-200">
                            <div class="border-b border-slate-200 bg-primary-50 px-4 py-2">
                                <p class="text-xs font-bold uppercase tracking-wide text-primary-700">Travaux réalisés</p>
                            </div>
                            <div class="space-y-3 p-4">
                                <div v-if="sheet.work_done">
                                    <p class="text-xs text-slate-500">Description des travaux</p>
                                    <p class="mt-0.5 whitespace-pre-wrap text-sm">{{ sheet.work_done }}</p>
                                </div>
                                <div v-if="sheet.supplies_used">
                                    <p class="text-xs text-slate-500">Fournitures utilisées</p>
                                    <p class="mt-0.5 text-sm">{{ sheet.supplies_used }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Délai & EPI -->
                        <div class="rounded border border-slate-200">
                            <div class="border-b border-slate-200 bg-primary-50 px-4 py-2">
                                <p class="text-xs font-bold uppercase tracking-wide text-primary-700">Délai d'intervention & EPI</p>
                            </div>
                            <div class="grid grid-cols-3 gap-0 divide-x divide-y divide-gray-200">
                                <div class="p-3"><p class="text-xs text-slate-500">Début</p><p class="mt-0.5 text-sm">{{ formatDt(sheet.start_date) }}</p></div>
                                <div class="p-3"><p class="text-xs text-slate-500">Fin</p><p class="mt-0.5 text-sm">{{ formatDt(sheet.end_date) }}</p></div>
                                <div class="p-3">
                                    <p class="text-xs text-slate-500">Durée</p>
                                    <p class="mt-0.5 text-sm font-semibold text-primary-700">{{ duration() || '—' }}</p>
                                </div>
                                <div v-if="sheet.epi_used?.length" class="col-span-3 p-3">
                                    <p class="text-xs text-slate-500">EPI utilisés</p>
                                    <p class="mt-0.5 text-sm">{{ sheet.epi_used.join(' — ') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Satisfaction -->
                        <div v-if="sheet.client_satisfaction" class="rounded border border-slate-200 p-4 flex items-center gap-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-primary-700">Satisfaction client :</p>
                            <p :class="['text-xl font-bold', satisfactionColors[sheet.client_satisfaction]]">
                                {{ sheet.client_satisfaction }}/4
                            </p>
                            <p class="text-sm text-slate-700">— {{ satisfactionLabels[sheet.client_satisfaction] }}</p>
                        </div>

                        <!-- Notes -->
                        <div v-if="sheet.notes" class="rounded border border-yellow-200 bg-yellow-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-yellow-700">Notes</p>
                            <p class="mt-1 whitespace-pre-wrap text-sm">{{ sheet.notes }}</p>
                        </div>

                        <!-- ══ VALIDATION TABLE ══ -->
                        <div class="rounded border border-slate-200">
                            <div class="border-b border-slate-200 bg-primary-50 px-4 py-2">
                                <p class="text-xs font-bold uppercase tracking-wide text-primary-700">Validations et signatures</p>
                            </div>
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 bg-slate-50">
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-slate-600 w-1/3">Chef d'atelier</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-slate-600 border-l border-slate-200 w-1/3">Utilisateur</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-slate-600 border-l border-slate-200 w-1/3">Chef de service</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Names row -->
                                    <tr class="border-b border-slate-200">
                                        <td v-for="role in ['chef_atelier', 'utilisateur', 'chef_service']" :key="role"
                                            class="px-4 py-3 align-top border-l first:border-l-0 border-slate-200">
                                            <p class="text-xs text-slate-500">Nom</p>
                                            <p class="text-sm font-medium">{{ validations[role]?.validator_name || '________________' }}</p>
                                            <p class="mt-1 text-xs text-slate-500">Date</p>
                                            <p class="text-xs">
                                                {{ validations[role]?.validated_at
                                                    ? new Date(validations[role].validated_at).toLocaleDateString('fr-FR')
                                                    : '__/__/____' }}
                                            </p>
                                            <p v-if="validations[role]?.comment" class="mt-1 text-xs italic text-slate-500">{{ validations[role].comment }}</p>
                                        </td>
                                    </tr>
                                    <!-- Signature row -->
                                    <tr>
                                        <td v-for="role in ['chef_atelier', 'utilisateur', 'chef_service']" :key="`sig-${role}`"
                                            class="px-4 py-3 border-l first:border-l-0 border-slate-200" style="height:110px;">
                                            <p class="text-xs text-slate-500 mb-1">Signature</p>
                                            <img
                                                v-if="validations[role]?.signature"
                                                :src="validations[role].signature"
                                                alt="Signature"
                                                class="max-h-16 max-w-full object-contain"
                                            />
                                            <div v-else class="h-14 w-full rounded border border-dashed border-slate-200 print:border-slate-300"></div>
                                            <button
                                                v-if="!validations[role]?.validated_at"
                                                @click="openModal(role)"
                                                class="no-print mt-1 text-xs font-medium text-primary hover:text-primary-700"
                                            >+ Signer</button>
                                            <button
                                                v-else
                                                @click="openModal(role)"
                                                class="no-print mt-1 text-xs text-slate-400 hover:text-slate-600"
                                            >Modifier</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-between pt-2 text-xs text-slate-400 border-t border-gray-100">
                            <span>Créée par : {{ sheet.created_by?.name }}</span>
                            <span>{{ sheet.reference }} — DL-FTI-01</span>
                        </div>
                    </div>
                </div>
                <!-- end print-area -->
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Signature Modal -->
    <Modal :show="showModal" @close="showModal = false">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-slate-900">Signature — {{ roleLabels[currentRole] }}</h3>
            <div class="mt-4 space-y-4">
                <div>
                    <InputLabel for="validator_name" value="Nom et prénom *" />
                    <TextInput id="validator_name" v-model="signForm.validator_name" type="text" class="mt-1 block w-full" />
                    <InputError class="mt-1" :message="signForm.errors.validator_name" />
                </div>
                <div>
                    <InputLabel value="Signature manuscrite" />
                    <div class="mt-1">
                        <SignaturePad v-model="signForm.signature" />
                    </div>
                </div>
                <div>
                    <InputLabel for="val_comment" value="Commentaire (optionnel)" />
                    <textarea id="val_comment" v-model="signForm.comment" rows="2"
                        class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-primary focus:ring-primary"
                    ></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton @click="showModal = false">Annuler</SecondaryButton>
                <PrimaryButton @click="submitSign" :disabled="signForm.processing">Valider et signer</PrimaryButton>
            </div>
        </div>
    </Modal>
</template>

<style>
@media print {
    body * { visibility: hidden; }
    #print-area, #print-area * { visibility: visible; }
    #print-area {
        position: absolute;
        top: 0; left: 0;
        width: 100%;
        padding: 0;
        margin: 0;
    }
    .no-print { display: none !important; }
    * {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>
