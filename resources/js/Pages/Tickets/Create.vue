<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    types: Array,
    channels: Array,
    priorities: Array,
    users: Array,
});

const form = useForm({
    type_id: '',
    channel_id: '',
    priority_id: '',
    subject: '',
    description: '',
    assigned_to: '',
    due_date: '',
});

const showDuplicateModal = ref(false);
const duplicates = ref([]);
const checkingDuplicates = ref(false);

const checkDuplicates = async () => {
    if (!form.subject || !form.description) {
        return;
    }

    checkingDuplicates.value = true;

    try {
        const response = await fetch(route('tickets.check-duplicates'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                subject: form.subject,
                description: form.description,
            }),
        });

        const data = await response.json();

        if (data.has_duplicates) {
            duplicates.value = data.duplicates;
            showDuplicateModal.value = true;
        } else {
            submitForm();
        }
    } catch (error) {
        console.error('Error checking duplicates:', error);
        submitForm();
    } finally {
        checkingDuplicates.value = false;
    }
};

const submitForm = () => {
    form.post(route('tickets.store'));
};

const handleSubmit = () => {
    checkDuplicates();
};

const continueDespiteDuplicates = () => {
    showDuplicateModal.value = false;
    submitForm();
};

const closeDuplicateModal = () => {
    showDuplicateModal.value = false;
};
</script>

<template>
    <Head title="Créer un ticket" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Créer un ticket
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="handleSubmit">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <InputLabel for="type_id" value="Type *" />
                                    <select
                                        id="type_id"
                                        v-model="form.type_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    >
                                        <option value="">Sélectionner un type</option>
                                        <option v-for="type in types" :key="type.id" :value="type.id">
                                            {{ type.name }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.type_id" />
                                </div>

                                <div>
                                    <InputLabel for="channel_id" value="Canal *" />
                                    <select
                                        id="channel_id"
                                        v-model="form.channel_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    >
                                        <option value="">Sélectionner un canal</option>
                                        <option v-for="channel in channels" :key="channel.id" :value="channel.id">
                                            {{ channel.name }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.channel_id" />
                                </div>

                                <div>
                                    <InputLabel for="priority_id" value="Priorité *" />
                                    <select
                                        id="priority_id"
                                        v-model="form.priority_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    >
                                        <option value="">Sélectionner une priorité</option>
                                        <option v-for="priority in priorities" :key="priority.id" :value="priority.id">
                                            {{ priority.name }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.priority_id" />
                                </div>

                                <div v-if="users">
                                    <InputLabel for="assigned_to" value="Assigner à" />
                                    <select
                                        id="assigned_to"
                                        v-model="form.assigned_to"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="">Non assigné</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">
                                            {{ user.name }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.assigned_to" />
                                </div>
                            </div>

                            <div class="mt-6">
                                <InputLabel for="subject" value="Sujet *" />
                                <TextInput
                                    id="subject"
                                    v-model="form.subject"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.subject" />
                            </div>

                            <div class="mt-6">
                                <InputLabel for="description" value="Description *" />
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    rows="6"
                                    required
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>

                            <div class="mt-6">
                                <InputLabel for="due_date" value="Délai" />
                                <input
                                    id="due_date"
                                    v-model="form.due_date"
                                    type="datetime-local"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                                <InputError class="mt-2" :message="form.errors.due_date" />
                                <p class="mt-1 text-xs text-gray-500">
                                    Définir une date limite pour traiter ce ticket (optionnel)
                                </p>
                            </div>

                            <div class="mt-6 flex items-center justify-end space-x-4">
                                <SecondaryButton type="button" @click="$inertia.visit(route('tickets.index'))">
                                    Annuler
                                </SecondaryButton>
                                <PrimaryButton :disabled="form.processing || checkingDuplicates">
                                    {{ checkingDuplicates ? 'Vérification...' : 'Créer le ticket' }}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Duplicate Detection Modal -->
        <Modal :show="showDuplicateModal" @close="closeDuplicateModal">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900">
                    Tickets similaires détectés
                </h3>

                <p class="mt-2 text-sm text-gray-600">
                    Les tickets suivants semblent similaires à celui que vous essayez de créer. 
                    Veuillez vérifier s'il s'agit d'un doublon avant de continuer.
                </p>

                <div class="mt-4 space-y-3">
                    <div
                        v-for="duplicate in duplicates"
                        :key="duplicate.ticket.id"
                        class="rounded-lg border border-gray-200 p-4"
                    >
                        <div class="flex items-center justify-between">
                            <div class="font-medium text-gray-900">
                                {{ duplicate.ticket.ticket_number }}
                            </div>
                            <div class="text-sm text-gray-500">
                                Similarité: {{ Math.round(duplicate.similarity * 100) }}%
                            </div>
                        </div>
                        <div class="mt-1 text-sm font-medium text-gray-700">
                            {{ duplicate.ticket.subject }}
                        </div>
                        <div class="mt-1 text-sm text-gray-600">
                            {{ duplicate.ticket.description.substring(0, 150) }}...
                        </div>
                        <div class="mt-2 flex items-center space-x-2 text-xs text-gray-500">
                            <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 font-semibold text-blue-800">
                                {{ duplicate.ticket.status?.name }}
                            </span>
                            <span>•</span>
                            <span>Créé le {{ new Date(duplicate.ticket.created_at).toLocaleDateString('fr-FR') }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="closeDuplicateModal">
                        Annuler
                    </SecondaryButton>
                    <PrimaryButton @click="continueDespiteDuplicates">
                        Créer quand même
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>