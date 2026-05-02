<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    statuses: Object,
});

const form = useForm({
    name:        '',
    description: '',
    status:      'active',
    start_date:  '',
    due_date:    '',
});

const submit = () => form.post(route('projects.store'));
</script>

<template>
    <Head title="Nouveau projet" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Créer un nouveau projet
            </h2>
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
                                    placeholder="Ex : Refonte portail client"
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
                                    placeholder="Décrivez l'objectif et le périmètre du projet…"
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
                                    :href="route('projects.index')"
                                    class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300"
                                >
                                    Annuler
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Création…' : 'Créer le projet' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
