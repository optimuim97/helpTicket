<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({ project: Object, statuses: Object });

const form = useForm({
    name: props.project.name,
    description: props.project.description ?? '',
    status: props.project.status,
    start_date: props.project.start_date ?? '',
    due_date: props.project.due_date ?? '',
});

const submit = () => form.put(route('projects.update', props.project.id));

const inputClass = 'mt-1.5 block w-full rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-primary focus:ring-primary';
</script>

<template>
    <Head :title="`Modifier — ${project.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-primary">Édition projet</p>
                <h1 class="text-2xl font-bold text-slate-900">{{ project.name }}</h1>
            </div>
        </template>

        <div class="mx-auto max-w-3xl">
            <form @submit.prevent="submit" class="space-y-6">
                <section class="card p-6">
                    <h2 class="text-base font-semibold text-slate-800">Informations</h2>
                    <div class="mt-5 space-y-5">
                        <div>
                            <InputLabel for="name" value="Nom du projet *" />
                            <TextInput id="name" v-model="form.name" type="text" required class="mt-1.5" />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                        <div>
                            <InputLabel for="description" value="Description" />
                            <textarea id="description" v-model="form.description" rows="4" :class="inputClass"></textarea>
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>
                    </div>
                </section>

                <section class="card p-6">
                    <h2 class="text-base font-semibold text-slate-800">Statut & dates</h2>
                    <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-3">
                        <div>
                            <InputLabel for="status" value="Statut *" />
                            <select id="status" v-model="form.status" required :class="inputClass">
                                <option v-for="(label, key) in statuses" :key="key" :value="key">{{ label }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.status" />
                        </div>
                        <div>
                            <InputLabel for="start_date" value="Date de début" />
                            <input id="start_date" v-model="form.start_date" type="date" :class="inputClass" />
                            <InputError class="mt-2" :message="form.errors.start_date" />
                        </div>
                        <div>
                            <InputLabel for="due_date" value="Date de fin prévue" />
                            <input id="due_date" v-model="form.due_date" type="date" :class="inputClass" />
                            <InputError class="mt-2" :message="form.errors.due_date" />
                        </div>
                    </div>
                </section>

                <div class="flex items-center justify-end gap-3">
                    <SecondaryButton type="button" @click="$inertia.visit(route('projects.show', project.id))">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="form.processing">{{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}</PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
