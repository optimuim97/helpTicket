<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    service: Object,
});

const form = useForm({
    name: props.service.name,
    description: props.service.description || '',
    is_active: props.service.is_active,
});

const submit = () => {
    form.put(route('services.update', props.service.id));
};
</script>

<template>
    <Head :title="`Modifier le service ${service.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Modifier le service: {{ service.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8 space-y-6">
                <!-- Edit Form -->
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
                                    {{ form.processing ? 'Enregistrement...' : 'Enregistrer' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Users in this service -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">
                            Utilisateurs dans ce service ({{ service.users?.length || 0 }})
                        </h3>
                        <div v-if="service.users && service.users.length > 0" class="space-y-2">
                            <div 
                                v-for="user in service.users" 
                                :key="user.id"
                                class="flex items-center justify-between rounded-md border p-3"
                            >
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
                                    <p class="text-sm text-gray-500">{{ user.email }}</p>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-sm text-gray-500">
                            Aucun utilisateur n'est assigné à ce service pour le moment.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
