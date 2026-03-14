<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const form = useForm({
    name: '',
    description: '',
    is_active: true,
});

const submit = () => {
    form.post(route('services.store'));
};
</script>

<template>
    <Head title="Créer un service" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Créer un nouveau service
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit">
                            <!-- Service Name -->
                            <div class="mb-6">
                                <label for="name" class="block mb-2 text-sm font-medium text-gray-700">
                                    Nom du service <span class="text-red-600">*</span>
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Ex: Support Technique"
                                />
                                <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <!-- Description -->
                            <div class="mb-6">
                                <label for="description" class="block mb-2 text-sm font-medium text-gray-700">
                                    Description
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="4"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Description du service..."
                                ></textarea>
                                <p v-if="form.errors.description" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.description }}
                                </p>
                            </div>

                            <!-- Active Status -->
                            <div class="mb-6">
                                <label class="flex items-center">
                                    <input
                                        v-model="form.is_active"
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">
                                        Service actif
                                    </span>
                                </label>
                                <p class="mt-1 text-sm text-gray-500">
                                    Les services inactifs ne peuvent pas être assignés à de nouveaux utilisateurs
                                </p>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center justify-end gap-4">
                                <a
                                    :href="route('services.index')"
                                    class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300"
                                >
                                    Annuler
                                </a>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Création...' : 'Créer le service' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
