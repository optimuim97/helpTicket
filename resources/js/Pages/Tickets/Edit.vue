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
    projects: Array,
});

const form = useForm({
    project_id: props.ticket.project_id ?? '',
    type_id: props.ticket.type_id,
    channel_id: props.ticket.channel_id,
    priority_id: props.ticket.priority_id,
    status_id: props.ticket.status_id,
    subject: props.ticket.subject,
    description: props.ticket.description,
    due_date: props.ticket.due_date ? new Date(props.ticket.due_date).toISOString().slice(0, 16) : '',
});

const submit = () => form.patch(route('tickets.update', props.ticket.id));

const selectClass = 'mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-sm text-slate-800 shadow-sm focus:border-primary focus:ring-primary';
</script>

<template>
    <Head :title="`Modifier ${ticket.ticket_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-primary">Édition</p>
                <h1 class="text-2xl font-bold text-slate-900">Ticket {{ ticket.ticket_number }}</h1>
            </div>
        </template>

        <div class="mx-auto max-w-4xl">
            <form @submit.prevent="submit" class="space-y-6">
                <section class="card p-6">
                    <h2 class="text-base font-semibold text-slate-800">Classification</h2>
                    <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div class="md:col-span-2" v-if="projects && projects.length > 0">
                            <InputLabel for="project_id" value="Projet" />
                            <select id="project_id" v-model="form.project_id" :class="selectClass">
                                <option value="">Aucun projet</option>
                                <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.project_id" />
                        </div>

                        <div>
                            <InputLabel for="type_id" value="Type *" />
                            <select id="type_id" v-model="form.type_id" :class="selectClass" required>
                                <option value="">Sélectionner</option>
                                <option v-for="type in types" :key="type.id" :value="type.id">{{ type.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.type_id" />
                        </div>

                        <div>
                            <InputLabel for="channel_id" value="Canal *" />
                            <select id="channel_id" v-model="form.channel_id" :class="selectClass" required>
                                <option value="">Sélectionner</option>
                                <option v-for="channel in channels" :key="channel.id" :value="channel.id">{{ channel.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.channel_id" />
                        </div>

                        <div>
                            <InputLabel for="priority_id" value="Priorité *" />
                            <select id="priority_id" v-model="form.priority_id" :class="selectClass" required>
                                <option value="">Sélectionner</option>
                                <option v-for="priority in priorities" :key="priority.id" :value="priority.id">{{ priority.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.priority_id" />
                        </div>

                        <div>
                            <InputLabel for="status_id" value="Statut *" />
                            <select id="status_id" v-model="form.status_id" :class="selectClass" required>
                                <option value="">Sélectionner</option>
                                <option v-for="status in statuses" :key="status.id" :value="status.id">{{ status.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.status_id" />
                        </div>
                    </div>
                </section>

                <section class="card p-6">
                    <h2 class="text-base font-semibold text-slate-800">Contenu</h2>
                    <div class="mt-5 space-y-5">
                        <div>
                            <InputLabel for="subject" value="Sujet *" />
                            <TextInput id="subject" v-model="form.subject" type="text" class="mt-1.5" required />
                            <InputError class="mt-2" :message="form.errors.subject" />
                        </div>

                        <div>
                            <InputLabel for="description" value="Description *" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="6"
                                class="mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-sm text-slate-800 shadow-sm focus:border-primary focus:ring-primary"
                                required
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>

                        <div>
                            <InputLabel for="due_date" value="Délai" />
                            <input
                                id="due_date"
                                v-model="form.due_date"
                                type="datetime-local"
                                class="mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-sm text-slate-800 shadow-sm focus:border-primary focus:ring-primary"
                            />
                            <InputError class="mt-2" :message="form.errors.due_date" />
                            <p class="mt-1 text-xs text-slate-500">Date limite pour traiter ce ticket (optionnel).</p>
                        </div>
                    </div>
                </section>

                <div class="flex items-center justify-end gap-3">
                    <SecondaryButton type="button" @click="$inertia.visit(route('tickets.show', ticket.id))">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="form.processing">Enregistrer</PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
