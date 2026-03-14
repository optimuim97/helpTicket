<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    ticket: Object,
    types: Array,
    channels: Array,
    priorities: Array,
    statuses: Array,
});

const form = useForm({
    type_id: props.ticket.type_id,
    channel_id: props.ticket.channel_id,
    priority_id: props.ticket.priority_id,
    status_id: props.ticket.status_id,
    subject: props.ticket.subject,
    description: props.ticket.description,
    due_date: props.ticket.due_date ? new Date(props.ticket.due_date).toISOString().slice(0, 16) : '',
});

const submit = () => {
    form.patch(route('tickets.update', props.ticket.id));
};
</script>

<template>
    <Head :title="`Modifier le ticket ${ticket.ticket_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Modifier le ticket {{ ticket.ticket_number }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit">
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

                                <div>
                                    <InputLabel for="status_id" value="Statut *" />
                                    <select
                                        id="status_id"
                                        v-model="form.status_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    >
                                        <option value="">Sélectionner un statut</option>
                                        <option v-for="status in statuses" :key="status.id" :value="status.id">
                                            {{ status.name }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.status_id" />
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
                                    Date limite pour traiter ce ticket (optionnel)
                                </p>
                            </div>

                            <div class="mt-6 flex items-center justify-end space-x-4">
                                <SecondaryButton type="button" @click="$inertia.visit(route('tickets.show', ticket.id))">
                                    Annuler
                                </SecondaryButton>
                                <PrimaryButton :disabled="form.processing">
                                    Enregistrer les modifications
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>