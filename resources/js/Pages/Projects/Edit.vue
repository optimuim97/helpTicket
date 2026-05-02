<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    project:  Object,
    statuses: Object,
});

const form = useForm({
    name:        props.project.name,
    description: props.project.description ?? '',
    status:      props.project.status,
    start_date:  props.project.start_date ?? '',
    due_date:    props.project.due_date ?? '',
});

const submit = () => form.put(route('projects.update', props.project.id));
</script>

<template>
    <Head :title="`Modifier — ${project.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('projects.show', project.id)" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Modifier : {{ project.name }}
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">

                            <!-- Nom -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">
                                    Nom du projet <span class="text-red-600">*</span>
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">
                                    Description
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="4"
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                ></textarea>
                                <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                            </div>

                            <!-- Statut -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">
                                    Statut <span class="text-red-600">*</span>
                                </label>
                                <select
                                    id="status"
                                    v-model="form.status"
                                    required
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option v-for="(label, key) in statuses" :key="key" :value="key">
                                        {{ label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p>
                            </div>

                            <!-- Dates -->
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-gray-700">
                                        Date de début
                                    </label>
                                    <input
                                        id="start_date"
                                        v-model="form.start_date"
                                        type="date"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">{{ form.errors.start_date }}</p>
                                </div>
                                <div>
                                    <label for="due_date" class="block text-sm font-medium text-gray-700">
                                        Date de fin prévue
                                    </label>
                                    <input
                                        id="due_date"
                                        v-model="form.due_date"
                                        type="date"
                                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <p v-if="form.errors.due_date" class="mt-1 text-sm text-red-600">{{ form.errors.due_date }}</p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-end gap-4 pt-2">
                                <Link
                                    :href="route('projects.show', project.id)"
                                    class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300"
                                >
                                    Annuler
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
