<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

defineProps({ permissionsGrouped: Object });

const expandedCategories = ref({});
const toggleCategory = (c) => { expandedCategories.value[c] = !expandedCategories.value[c]; };

const categoryLabels = {
    view: 'Visualisation', create: 'Création', update: 'Modification', delete: 'Suppression',
    manage: 'Gestion', assign: 'Attribution', close: 'Fermeture', resolve: 'Résolution', export: 'Export',
};
const getCategoryLabel = (c) => categoryLabels[c] || c;
</script>

<template>
    <Head title="Permissions" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Permissions</h1>
                <p class="text-sm text-slate-500">Liste de référence des permissions système.</p>
            </div>
        </template>

        <div class="space-y-4">
            <div class="rounded-xl border border-primary-100 bg-primary-50 p-4">
                <p class="text-sm text-primary-800">
                    <strong>Info :</strong> les permissions sont gérées par le système et ne peuvent pas être créées ou supprimées manuellement.
                    Elles sont assignables aux rôles ou directement aux utilisateurs.
                </p>
            </div>

            <div class="space-y-3">
                <div
                    v-for="(group, category) in permissionsGrouped"
                    :key="category"
                    class="card overflow-hidden"
                >
                    <button
                        @click="toggleCategory(category)"
                        class="flex w-full items-center justify-between px-6 py-4 text-left transition hover:bg-slate-50"
                    >
                        <div class="flex items-center gap-3">
                            <h3 class="text-base font-semibold text-slate-900">{{ getCategoryLabel(category) }}</h3>
                            <span class="badge bg-primary-100 text-primary-700">
                                {{ group.count }} {{ group.count > 1 ? 'permissions' : 'permission' }}
                            </span>
                        </div>
                        <svg
                            :class="{ 'rotate-180': expandedCategories[category] }"
                            class="h-5 w-5 text-slate-400 transition-transform"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div
                        v-show="expandedCategories[category]"
                        class="border-t border-slate-100 bg-slate-50 px-6 py-4"
                    >
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            <div
                                v-for="permission in group.permissions"
                                :key="permission.id"
                                class="rounded-lg border border-slate-200 bg-white p-3"
                            >
                                <code class="text-xs text-slate-700">{{ permission.name }}</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
