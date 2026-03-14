<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

defineProps({
    permissionsGrouped: Object,
});

const expandedCategories = ref({});

const toggleCategory = (category) => {
    expandedCategories.value[category] = !expandedCategories.value[category];
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
    <Head title="Liste des permissions" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Liste des permissions
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-4 rounded-md bg-blue-50 p-4">
                    <p class="text-sm text-blue-800">
                        <strong>Note:</strong> Les permissions sont gérées par le système et ne peuvent pas être créées ou supprimées manuellement. 
                        Elles peuvent être assignées aux rôles ou directement aux utilisateurs.
                    </p>
                </div>

                <div class="space-y-4">
                    <div 
                        v-for="(group, category) in permissionsGrouped" 
                        :key="category"
                        class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                    >
                        <button
                            @click="toggleCategory(category)"
                            class="w-full px-6 py-4 text-left hover:bg-gray-50"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        {{ getCategoryLabel(category) }}
                                    </h3>
                                    <span class="inline-flex rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold text-indigo-800">
                                        {{ group.count }} {{ group.count > 1 ? 'permissions' : 'permission' }}
                                    </span>
                                </div>
                                <svg 
                                    :class="{ 'rotate-180': expandedCategories[category] }"
                                    class="h-5 w-5 text-gray-500 transition-transform"
                                    fill="none" 
                                    stroke="currentColor" 
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </button>

                        <div 
                            v-show="expandedCategories[category]"
                            class="border-t border-gray-200 bg-gray-50 px-6 py-4"
                        >
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                <div
                                    v-for="permission in group.permissions"
                                    :key="permission.id"
                                    class="rounded-md border border-gray-200 bg-white p-3"
                                >
                                    <code class="text-sm text-gray-700">
                                        {{ permission.name }}
                                    </code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
