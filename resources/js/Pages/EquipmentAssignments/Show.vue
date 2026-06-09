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
    assignment:   Object,
    validations:  Object,
    roleLabels:   Object,
    statusLabels: Object,
});

const statusColors = {
    brouillon: 'bg-slate-100 text-slate-700',
    valide:    'bg-accent-100 text-accent-700',
    signe:     'bg-emerald-100 text-emerald-700',
};
const operationColors = {
    affectation:  'bg-primary-100 text-primary-700',
    remplacement: 'bg-accent-100 text-accent-700',
};

// Validation / signature modal
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
        route('equipment-assignments.sign', { equipmentAssignment: props.assignment.id, role: currentRole.value }),
        { onSuccess: () => { showModal.value = false; signForm.reset(); } }
    );
}

function deleteAssignment() {
    if (!confirm(`Supprimer la fiche ${props.assignment.reference} ?`)) return;
    router.delete(route('equipment-assignments.destroy', props.assignment.id));
}
</script>

<template>
    <Head :title="assignment.reference" />

    <AuthenticatedLayout>
        <template #header>
            <div class="no-print flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <a :href="route('equipment-assignments.index')" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold text-slate-900">{{ assignment.reference }}</h1>
                            <span :class="['badge', statusColors[assignment.status]]">{{ statusLabels[assignment.status] }}</span>
                        </div>
                        <p class="text-xs text-slate-500">DL-FTE-01 — Fiche d'affectation d'équipement</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button onclick="window.print()" class="btn-ghost">🖨 Imprimer</button>
                    <a
                        v-if="assignment.status === 'brouillon'"
                        :href="route('equipment-assignments.edit', assignment.id)"
                        class="btn-ghost"
                    >Modifier</a>
                    <button @click="deleteAssignment" class="inline-flex items-center justify-center rounded-lg bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100">
                        Supprimer
                    </button>
                </div>
            </div>
        </template>

        <div>
            <div class="mx-auto max-w-4xl">

                <!-- ══════════════ PRINT DOCUMENT ══════════════ -->
                <div id="print-area" class="card overflow-hidden print:rounded-none print:shadow-none">

                    <!-- Document header -->
                    <div class="flex items-start justify-between border-b-2 border-primary-700 p-6">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-primary-700">Port Autonome d'Abidjan</p>
                            <p class="text-xs text-slate-500">Direction des Systèmes d'Information</p>
                        </div>
                        <div class="text-center">
                            <p class="text-base font-bold uppercase text-slate-900">Fiche d'Affectation d'Équipement Informatique</p>
                            <p class="mt-0.5 text-xs text-slate-500">DL-FTE-01</p>
                        </div>
                        <div class="text-right text-xs">
                            <p class="font-bold text-slate-800">{{ assignment.reference }}</p>
                            <p class="text-slate-500">{{ new Date(assignment.created_at).toLocaleDateString('fr-FR') }}</p>
                            <span :class="['no-print mt-1 inline-flex rounded px-2 py-0.5 text-xs font-semibold', statusColors[assignment.status]]">
                                {{ statusLabels[assignment.status] }}
                            </span>
                        </div>
                    </div>

                    <div class="p-6 space-y-5">

                        <!-- Section: Type d'opération -->
                        <div class="rounded border border-slate-200 p-4">
                            <p class="mb-2 text-xs font-bold uppercase tracking-wide text-primary-700">Type d'opération</p>
                            <div class="flex gap-8 text-sm">
                                <label class="flex items-center gap-2">
                                    <span class="inline-block h-4 w-4 rounded border-2 border-slate-300 flex items-center justify-center">
                                        <span v-if="assignment.operation_type === 'affectation'" class="block h-2 w-2 rounded-sm bg-primary-700"></span>
                                    </span>
                                    Affectation
                                </label>
                                <label class="flex items-center gap-2">
                                    <span class="inline-block h-4 w-4 rounded border-2 border-slate-300 flex items-center justify-center">
                                        <span v-if="assignment.operation_type === 'remplacement'" class="block h-2 w-2 rounded-sm bg-primary-700"></span>
                                    </span>
                                    Remplacement
                                </label>
                            </div>
                        </div>

                        <!-- Section: Équipement -->
                        <div class="rounded border border-slate-200">
                            <div class="border-b border-slate-200 bg-primary-50 px-4 py-2">
                                <p class="text-xs font-bold uppercase tracking-wide text-primary-700">Identification de l'équipement</p>
                            </div>
                            <div class="grid grid-cols-2 gap-0 divide-x divide-y divide-gray-200">
                                <div class="p-3"><p class="text-xs text-slate-500">Type d'équipement</p><p class="mt-0.5 text-sm font-medium">{{ assignment.equipment_type }}</p></div>
                                <div class="p-3"><p class="text-xs text-slate-500">Modèle</p><p class="mt-0.5 text-sm font-medium">{{ assignment.equipment_model }}</p></div>
                                <div class="p-3"><p class="text-xs text-slate-500">Numéro de série</p><p class="mt-0.5 text-sm font-mono">{{ assignment.equipment_serial }}</p></div>
                                <div class="p-3"><p class="text-xs text-slate-500">Adresse MAC</p><p class="mt-0.5 text-sm font-mono">{{ assignment.equipment_mac || '—' }}</p></div>
                                <div v-if="assignment.old_equipment_serial" class="col-span-2 p-3">
                                    <p class="text-xs text-slate-500">N° série ancien équipement (remplacement)</p>
                                    <p class="mt-0.5 text-sm font-mono">{{ assignment.old_equipment_serial }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Agent -->
                        <div class="rounded border border-slate-200">
                            <div class="border-b border-slate-200 bg-primary-50 px-4 py-2">
                                <p class="text-xs font-bold uppercase tracking-wide text-primary-700">Agent bénéficiaire</p>
                            </div>
                            <div class="grid grid-cols-2 gap-0 divide-x divide-y divide-gray-200">
                                <div class="p-3"><p class="text-xs text-slate-500">Matricule</p><p class="mt-0.5 text-sm font-medium">{{ assignment.agent_matricule }}</p></div>
                                <div class="p-3"><p class="text-xs text-slate-500">Nom et prénom</p><p class="mt-0.5 text-sm font-medium">{{ assignment.agent_name }}</p></div>
                                <div class="p-3"><p class="text-xs text-slate-500">Direction</p><p class="mt-0.5 text-sm">{{ assignment.agent_direction }}</p></div>
                                <div class="p-3"><p class="text-xs text-slate-500">Département</p><p class="mt-0.5 text-sm">{{ assignment.agent_department }}</p></div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div v-if="assignment.notes" class="rounded border border-slate-200 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-primary-700">Observations</p>
                            <p class="mt-1 whitespace-pre-wrap text-sm text-slate-700">{{ assignment.notes }}</p>
                        </div>

                        <!-- ══ VALIDATION TABLE (3 validators) ══ -->
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
                                    <!-- Names & date row -->
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
                                            <p v-if="validations[role]?.comment" class="mt-1 text-xs italic text-slate-500">
                                                {{ validations[role].comment }}
                                            </p>
                                        </td>
                                    </tr>
                                    <!-- Signature row -->
                                    <tr>
                                        <td v-for="role in ['chef_atelier', 'utilisateur', 'chef_service']" :key="`sig-${role}`"
                                            class="px-4 py-3 border-l first:border-l-0 border-slate-200" style="height:100px;">
                                            <p class="text-xs text-slate-500 mb-1">Signature</p>
                                            <!-- Show saved signature -->
                                            <img
                                                v-if="validations[role]?.signature"
                                                :src="validations[role].signature"
                                                alt="Signature"
                                                class="max-h-16 max-w-full object-contain"
                                            />
                                            <!-- Empty box for print -->
                                            <div v-else class="h-14 w-full rounded border border-dashed border-slate-200 print:border-slate-300"></div>
                                            <!-- Sign button (screen only) -->
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
                        <div class="flex items-center justify-between pt-2 text-xs text-slate-400 border-t border-slate-100">
                            <span>Créée par : {{ assignment.created_by?.name }}</span>
                            <span>{{ assignment.reference }} — DL-FTE-01</span>
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
            <h3 class="text-lg font-semibold text-slate-900">
                Signature — {{ roleLabels[currentRole] }}
            </h3>
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
                        class="mt-1 block w-full rounded-md border-slate-200 text-sm shadow-sm focus:border-primary focus:ring-primary"
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
