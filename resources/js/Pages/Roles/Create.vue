<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    permissions: Object,
});

const form = useForm({
    name: '',
    permissions: [],
});

const submit = () => {
    form.post(route('roles.store'));
};

const getCategoryLabel = (category) => {
    const labels = {
        'view': 'Visualisation',
        'create': 'Création',
        'update': 'Modification',
        'delete': 'Suppression',
        'manage': 'Gestion',
        'assign': 'Attribution',
        'close': 'Fermeture',
        'resolve': 'Résolution',
        'export': 'Export',
    };
    return labels[category] || category;
};
</script>

<template>
    <Head title="Créer un rôle" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Créer un nouveau rôle
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit">
                            <!-- Role Name -->
                            <div class="mb-6">
                                <label for="name" class="block mb-2 text-sm font-medium text-gray-700">
                                    Nom du rôle <span class="text-red-600">*</span>
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Ex: Manager"
                                />
                                <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <!-- Permissions -->
                            <div class="mb-6">
                                <h3 class="mb-4 text-lg font-medium text-gray-900">Permissions</h3>
                                <div v-for="(group, category) in permissions" :key="category" class="mb-4">
                                    <h4 class="mb-2 text-sm font-semibold text-gray-700">
                                        {{ getCategoryLabel(category) }}
                                    </h4>
                                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                        <label 
                                            v-for="permission in group" 
                                            :key="permission.id"
                                            class="flex items-center rounded-md border p-3 hover:bg-gray-50"
                                        >
                                            <input
                                                v-model="form.permissions"
                                                type="checkbox"
                                                :value="permission.name"
                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            />
                                            <span class="ml-2 text-sm text-gray-700">
                                                {{ permission.name.replace(/_/g, ' ') }}
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center justify-end gap-4">
                                <a
                                    :href="route('roles.index')"
                                    class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300"
                                >
                                    Annuler
                                </a>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Création...' : 'Créer le rôle' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
